<?php

namespace App\Filament\Widgets;

use App\Enums\Role;
use App\Filament\Resources\ActivityLog\ActivityLogResource;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use Spatie\Activitylog\Models\Activity;

/**
 * Dashboard: the latest changes (plan 1.3, "latest activity", widgets by role).
 * Super Admin sees everything; other staff see only their own area.
 */
class LatestActivity extends TableWidget
{
    protected static ?int $sort = 10;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $heading = 'Latest activity';

    /** Which activity log areas each role sees on its dashboard. */
    public const AREAS_BY_ROLE = [
        'Finance' => ['donations'],
        'Editor' => ['content', 'messages'],
        'Services' => ['services'],
    ];

    /** @return list<string>|null null means every area (Super Admin) */
    public static function areasFor(?\App\Models\User $user): ?array
    {
        if (! $user) {
            return [];
        }

        if ($user->hasRole(Role::SuperAdmin->value)) {
            return null;
        }

        return collect(self::AREAS_BY_ROLE)
            ->filter(fn ($areas, $role) => $user->hasRole($role))
            ->flatten()->unique()->values()->all();
    }

    public static function canView(): bool
    {
        return static::areasFor(auth()->user()) !== [];
    }

    public function table(Table $table): Table
    {
        $areas = static::areasFor(auth()->user());

        return $table
            ->query(Activity::query()
                ->with(['causer', 'subject'])
                ->when($areas !== null, fn (Builder $query) => $query->whereIn('log_name', $areas))
                // Routine sign-ins would bury real changes; they stay in the full log. Failed sign-ins still show.
                ->where(fn (Builder $query) => $query->whereNull('event')->orWhereNotIn('event', ['login', 'logout']))
                ->latest('id'))
            ->paginated(false)
            ->modifyQueryUsing(fn (Builder $query) => $query->limit(10))
            ->emptyStateHeading('Nothing yet')
            ->emptyStateDescription('Changes made in the admin will appear here.')
            ->columns([
                TextColumn::make('created_at')->label('When')->since('Asia/Karachi')
                    ->dateTimeTooltip('j M Y, g:i a', 'Asia/Karachi'),
                TextColumn::make('who')->label('Who')->state(fn (Activity $record) => ActivityLogResource::who($record)),
                TextColumn::make('description')->label('What')->weight('semibold')->wrap()
                    ->description(fn (Activity $record) => ActivityLogResource::item($record)),
                TextColumn::make('log_name')->label('Area')->badge()->color('gray')
                    ->formatStateUsing(fn (?string $state) => ActivityLogResource::AREAS[$state] ?? Str::headline((string) $state)),
            ])
            ->headerActions([
                Action::make('fullLog')->label('See the full log')->link()
                    ->url(ActivityLogResource::getUrl('index'))
                    ->visible(fn () => ActivityLogResource::canViewAny()),
            ]);
    }
}
