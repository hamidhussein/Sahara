<?php

namespace App\Filament\Resources\Users;

use App\Enums\Role;
use App\Filament\Resources\Users\Pages\CreateUser;
use App\Filament\Resources\Users\Pages\EditUser;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Models\User;
use BackedEnum;
use Closure;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\Rules\Password;
use Spatie\Activitylog\Models\Activity;
use UnitEnum;

/** Admin users and their roles (plan: Users → admin users, roles). Super Admin only. */
class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUsers;

    protected static string|UnitEnum|null $navigationGroup = 'Users';

    protected static ?string $navigationLabel = 'Admin users';

    protected static ?string $modelLabel = 'admin user';

    protected static ?int $navigationSort = 0;

    protected static ?string $recordTitleAttribute = 'name';

    /** What each role can do, in plain words, shown on the form. */
    public static function roleDescriptions(): array
    {
        return [
            Role::SuperAdmin->value => 'Everything, including admin users, settings, backups and the activity log.',
            Role::Editor->value => 'Website content and contact messages: pages, news, programs, projects, gallery, partners and team.',
            Role::Finance->value => 'Donations, causes, types of giving, and transparency reports.',
            Role::Services->value => 'Requests for help (from Phase 2).',
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        // Last sign-in, from the activity log.
        return parent::getEloquentQuery()->with('roles')->addSelect([
            'last_signed_in_at' => Activity::select('created_at')
                ->whereColumn('causer_id', 'users.id')
                ->where('causer_type', (new User)->getMorphClass())
                ->where('event', 'login')
                ->latest('id')
                ->limit(1),
        ]);
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Details')->columns(2)->schema([
                TextInput::make('name')->required()->maxLength(120),
                TextInput::make('email')->label('Email address')->email()->required()->maxLength(190)
                    ->unique(ignoreRecord: true)
                    ->helperText('They sign in with this address, and email sign-in codes go here.'),
            ]),

            Section::make('Roles')->schema([
                CheckboxList::make('role_names')->hiddenLabel()
                    ->options(array_combine(Role::names(), Role::names()))
                    ->descriptions(static::roleDescriptions())
                    ->required()
                    ->validationMessages(['required' => 'Give them at least one role, or they cannot sign in.'])
                    ->rules([fn (?User $record): Closure => function (string $attribute, mixed $value, Closure $fail) use ($record) {
                        $keepsSuperAdmin = in_array(Role::SuperAdmin->value, (array) $value, true);
                        if (! $record || $keepsSuperAdmin || ! $record->hasRole(Role::SuperAdmin->value)) {
                            return;
                        }
                        if ($record->is(auth()->user())) {
                            $fail('You cannot remove your own Super Admin role. Ask another Super Admin to do it.');
                        } elseif ($record->isLastSuperAdmin()) {
                            $fail('This is the only Super Admin. Make someone else Super Admin first.');
                        }
                    }]),
            ]),

            Section::make(fn (string $operation) => $operation === 'create' ? 'Password' : 'Change password')
                ->description(fn (string $operation) => $operation === 'create'
                    ? 'Share it with them privately. On first sign-in they will be asked to set up two-factor login.'
                    : 'Leave empty to keep their current password.')
                ->columns(2)
                ->schema([
                    TextInput::make('password')->label(fn (string $operation) => $operation === 'create' ? 'Password' : 'New password')
                        ->password()->revealable()
                        ->required(fn (string $operation) => $operation === 'create')
                        ->rule(Password::min(12))
                        ->confirmed()
                        ->dehydrated(fn (?string $state) => filled($state))
                        ->autocomplete('new-password')
                        ->helperText('At least 12 characters.'),
                    TextInput::make('password_confirmation')->label('Type it again')
                        ->password()->revealable()
                        ->required(fn (string $operation, $get) => $operation === 'create' || filled($get('password')))
                        ->dehydrated(false)
                        ->autocomplete('new-password'),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('name')
            ->columns([
                TextColumn::make('name')->weight('semibold')->searchable()->sortable()
                    ->description(fn (User $record) => $record->email),
                TextColumn::make('roles.name')->label('Roles')->badge()->placeholder('No access')
                    ->color(fn (string $state) => $state === Role::SuperAdmin->value ? 'danger' : 'gray'),
                IconColumn::make('two_factor')->label('Two-factor')->boolean()
                    ->state(fn (User $record) => $record->hasTwoFactor())
                    ->tooltip(fn (User $record) => $record->hasTwoFactor() ? 'Set up' : 'Not set up yet: asked at next sign-in'),
                TextColumn::make('last_signed_in_at')->label('Last sign-in')->since('Asia/Karachi')->placeholder('Never')
                    ->dateTimeTooltip('j M Y, g:i a', 'Asia/Karachi')->sortable(),
            ])
            ->filters([
                SelectFilter::make('role')->label('Role')
                    ->options(array_combine(Role::names(), Role::names()))
                    ->query(fn (Builder $query, array $data) => $query->when($data['value'] ?? null, fn (Builder $query, $role) => $query->role($role))),
            ])
            ->recordActions([EditAction::make(), static::resetTwoFactorAction(), static::removeAccessAction(), static::deleteAction()]);
    }

    /** When someone leaves: they can no longer sign in, but their name stays on what they did. */
    public static function removeAccessAction(): Action
    {
        return Action::make('removeAccess')
            ->label('Remove access')
            ->icon(Heroicon::OutlinedNoSymbol)
            ->color('danger')
            ->visible(fn (User $record) => $record->roles->isNotEmpty()
                && ! $record->is(auth()->user())
                && ! $record->isLastSuperAdmin())
            ->requiresConfirmation()
            ->modalHeading(fn (User $record) => "Remove {$record->name}’s access?")
            ->modalDescription('They will no longer be able to sign in. Their name stays on the donations they confirmed and the messages they answered. You can give them a role again later.')
            ->modalSubmitActionLabel('Remove access')
            ->action(function (User $record) {
                foreach ($record->getRoleNames() as $role) {
                    $record->removeRole($role); // each removal is recorded in the activity log
                }
                Notification::make()->success()->title("{$record->name} can no longer sign in")->send();
            });
    }

    public static function deleteAction(): DeleteAction
    {
        return DeleteAction::make()
            ->modalDescription('Deleting removes their name from the donations they confirmed and the messages they answered. To stop someone signing in but keep that history, use “Remove access” instead.');
    }

    /** For staff who lost their phone: they set two-factor up again at their next sign-in. */
    public static function resetTwoFactorAction(): Action
    {
        return Action::make('resetTwoFactor')
            ->label('Reset two-factor')
            ->icon(Heroicon::OutlinedKey)
            ->color('warning')
            ->visible(fn (User $record) => $record->hasTwoFactor())
            ->requiresConfirmation()
            ->modalHeading(fn (User $record) => "Reset two-factor login for {$record->name}?")
            ->modalDescription('Use this when they have lost their phone or recovery codes. Their authenticator app will stop working, and they will set two-factor login up again the next time they sign in.')
            ->modalSubmitActionLabel('Reset')
            ->action(function (User $record) {
                $record->resetTwoFactor();
                activity('users')->performedOn($record)->event('two-factor-reset')->log('Reset two-factor login');
                Notification::make()->success()->title('Two-factor login reset')
                    ->body("{$record->name} will set it up again at their next sign-in.")->send();
            });
    }

    public static function getPages(): array
    {
        return [
            'index' => ListUsers::route('/'),
            'create' => CreateUser::route('/create'),
            'edit' => EditUser::route('/{record}/edit'),
        ];
    }
}
