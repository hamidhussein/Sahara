<?php

namespace App\Filament\Resources\ActivityLog;

use App\Enums\Role;
use App\Filament\Resources\ActivityLog\Pages\ListActivities;
use App\Models\User;
use BackedEnum;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;
use Spatie\Activitylog\Models\Activity;
use UnitEnum;

/**
 * Who changed what, and when (plan: Users → activity log, Super Admin only).
 * Read-only: nobody can edit or delete entries here; old ones expire after a year.
 */
class ActivityLogResource extends Resource
{
    protected static ?string $model = Activity::class;

    protected static ?string $slug = 'activity-log';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;

    protected static string|UnitEnum|null $navigationGroup = 'Users';

    protected static ?string $navigationLabel = 'Activity log';

    protected static ?int $navigationSort = 1;

    protected static ?string $modelLabel = 'activity';

    protected static ?string $pluralModelLabel = 'activity log';

    public const AREAS = [
        'content' => 'Content',
        'donations' => 'Donations',
        'messages' => 'Messages',
        'services' => 'Services',
        'settings' => 'Settings',
        'users' => 'Users',
        'security' => 'Sign-ins',
        'backups' => 'Backups',
    ];

    public static function canViewAny(): bool
    {
        return (bool) auth()->user()?->hasRole(Role::SuperAdmin->value);
    }

    public static function canView(Model $record): bool
    {
        return static::canViewAny();
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(Model $record): bool
    {
        return false;
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }

    public static function canDeleteAny(): bool
    {
        return false;
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['causer', 'subject']);
    }

    /** "Ayesha Khan", or who acted when nobody was signed in. */
    public static function who(Activity $activity): string
    {
        if ($activity->causer) {
            return $activity->causer->getAttribute('name') ?? 'User #'.$activity->causer_id;
        }

        return match ($activity->log_name) {
            'security' => 'Not signed in',
            'donations' => 'Payment provider or system',
            default => 'System',
        };
    }

    /** The item the entry is about, e.g. "Project · Ration Distribution". */
    public static function item(Activity $activity): ?string
    {
        if ($settings = $activity->getProperty('settings')) {
            return Str::of($settings)->beforeLast('Settings')->headline().' settings';
        }

        if (! $activity->subject_type) {
            return null;
        }

        $label = Str::of(class_basename($activity->subject_type))->headline();
        $subject = $activity->subject;
        $title = $subject && method_exists($subject, 'activityTitle') ? $subject->activityTitle() : '#'.$activity->subject_id.($subject ? '' : ' (deleted)');

        return "{$label} · {$title}";
    }

    /** Old → new for each changed field, as an HTML list. */
    public static function changes(Activity $activity): HtmlString
    {
        $new = collect($activity->attribute_changes?->get('attributes') ?? []);
        $old = collect($activity->attribute_changes?->get('old') ?? []);

        $rows = $new->keys()->merge($old->keys())->unique()->map(function ($field) use ($new, $old, $activity) {
            $label = e(Str::of($field)->replace('_', ' ')->ucfirst());
            $before = static::value($old->get($field));
            $after = $activity->event === 'deleted' ? '<em>removed</em>' : static::value($new->get($field));

            return $activity->event === 'created'
                ? "<li><strong>{$label}:</strong> {$after}</li>"
                : "<li><strong>{$label}:</strong> {$before} → {$after}</li>";
        });

        $extra = collect($activity->properties?->except(['settings']) ?? [])
            ->map(fn ($value, $key) => '<li><strong>'.e(Str::ucfirst(str_replace('_', ' ', $key))).':</strong> '.static::value($value).'</li>');

        $items = $rows->merge($extra);

        return new HtmlString($items->isEmpty()
            ? '<p>No details recorded.</p>'
            : '<ul class="space-y-1">'.$items->implode('').'</ul>');
    }

    private static function value(mixed $value): string
    {
        return match (true) {
            $value === null || $value === '' => '<em>empty</em>',
            is_bool($value) => $value ? 'Yes' : 'No',
            is_array($value) => e(json_encode($value, JSON_UNESCAPED_UNICODE)),
            default => e(Str::limit(strip_tags((string) $value), 300)),
        };
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema->components([
            Section::make()->columns(2)->schema([
                TextEntry::make('description')->label('What')->weight('bold'),
                TextEntry::make('created_at')->label('When')->dateTime('j M Y, g:i:s a', 'Asia/Karachi'),
                TextEntry::make('who')->label('Who')->state(fn (Activity $record) => static::who($record)),
                TextEntry::make('item')->label('Item')->state(fn (Activity $record) => static::item($record))->placeholder('–'),
                TextEntry::make('changes')->label('Details')->columnSpanFull()
                    ->state(fn (Activity $record) => static::changes($record))->html(),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('id', 'desc')
            ->columns([
                TextColumn::make('created_at')->label('When')->since('Asia/Karachi')->sortable()
                    ->dateTimeTooltip('j M Y, g:i a', 'Asia/Karachi'),
                TextColumn::make('causer.name')->label('Who')
                    ->state(fn (Activity $record) => static::who($record)),
                TextColumn::make('description')->label('What')->weight('semibold')->wrap()->searchable()
                    ->description(fn (Activity $record) => static::item($record)),
                TextColumn::make('log_name')->label('Area')->badge()
                    ->formatStateUsing(fn (?string $state) => self::AREAS[$state] ?? Str::headline((string) $state))
                    ->color(fn (?string $state) => match ($state) {
                        'donations' => 'success',
                        'security' => 'warning',
                        'settings', 'users' => 'info',
                        'backups' => 'danger',
                        default => 'gray',
                    }),
            ])
            ->filters([
                SelectFilter::make('log_name')->label('Area')->options(self::AREAS),
                SelectFilter::make('causer_id')->label('Person')
                    ->options(fn () => User::orderBy('name')->pluck('name', 'id')->all())
                    ->query(fn (Builder $query, array $data) => $query->when($data['value'] ?? null,
                        fn (Builder $query, $id) => $query->where('causer_type', (new User)->getMorphClass())->where('causer_id', $id))),
                Filter::make('date')
                    ->schema([
                        DatePicker::make('from')->label('From'),
                        DatePicker::make('until')->label('Until'),
                    ])
                    ->query(fn (Builder $query, array $data) => $query
                        ->when($data['from'] ?? null, fn (Builder $query, $date) => $query->whereDate('created_at', '>=', $date))
                        ->when($data['until'] ?? null, fn (Builder $query, $date) => $query->whereDate('created_at', '<=', $date))),
            ])
            ->recordActions([ViewAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListActivities::route('/'),
        ];
    }
}
