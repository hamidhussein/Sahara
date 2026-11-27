<?php

namespace App\Filament\Widgets;

use App\Enums\DonationStatus;
use App\Enums\Role;
use App\Models\GivingType;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

/**
 * Paid totals per fund. Zakat and Khums are separate funds and appear on
 * their own rows (plan 1.2, Reports).
 */
class FundTotals extends TableWidget
{
    protected static ?int $sort = 5; // after the to-do numbers

    protected int|string|array $columnSpan = 'full';

    protected static ?string $heading = 'Funds';

    public static function canView(): bool
    {
        return (bool) auth()->user()?->hasAnyRole([Role::Finance->value, Role::SuperAdmin->value]);
    }

    public function table(Table $table): Table
    {
        $paid = fn ($q) => $q->where('status', DonationStatus::Paid);

        return $table
            ->query(fn (): Builder => GivingType::query()
                ->withSum(['donations as month_total' => fn ($q) => $paid($q)->where('paid_at', '>=', now()->startOfMonth())], 'amount')
                ->withSum(['donations as year_total' => fn ($q) => $paid($q)->where('paid_at', '>=', now()->startOfYear())], 'amount')
                ->withSum(['donations as all_total' => $paid], 'amount'))
            ->defaultSort('sort_order')
            ->paginated(false)
            ->columns([
                TextColumn::make('name')->label('Fund')->weight('semibold'),
                IconColumn::make('is_restricted')->label('Restricted')->boolean(),
                TextColumn::make('month_total')->label('This month')->money('PKR', locale: 'en_PK', decimalPlaces: 0)->placeholder('Rs 0'),
                TextColumn::make('year_total')->label('This year')->money('PKR', locale: 'en_PK', decimalPlaces: 0)->placeholder('Rs 0'),
                TextColumn::make('all_total')->label('All time')->money('PKR', locale: 'en_PK', decimalPlaces: 0)->placeholder('Rs 0'),
            ]);
    }
}
