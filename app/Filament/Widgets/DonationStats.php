<?php

namespace App\Filament\Widgets;

use App\Enums\DonationStatus;
use App\Enums\Role;
use App\Filament\Resources\Donations\DonationResource;
use App\Models\Donation;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

/** Dashboard: donations this month and what Finance needs to act on (plan 1.3). */
class DonationStats extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    public static function canView(): bool
    {
        return (bool) auth()->user()?->hasAnyRole([Role::Finance->value, Role::SuperAdmin->value]);
    }

    protected function getStats(): array
    {
        $month = Donation::paid()->where('paid_at', '>=', now()->startOfMonth());
        $lastMonth = Donation::paid()->whereBetween('paid_at', [now()->subMonthNoOverflow()->startOfMonth(), now()->subMonthNoOverflow()->endOfMonth()]);
        $toConfirm = Donation::where('status', DonationStatus::AwaitingConfirmation)->count();
        $toReview = Donation::where('needs_eligibility_review', true)->whereIn('status', [DonationStatus::Paid, DonationStatus::AwaitingConfirmation])->count();

        return [
            Stat::make('Donations this month', 'Rs '.number_format((clone $month)->sum('amount')))
                ->description((clone $month)->count().' donations · last month Rs '.number_format($lastMonth->sum('amount'))),
            Stat::make('Bank transfers to confirm', $toConfirm)
                ->description($toConfirm ? 'Check each against the bank statement' : 'All caught up')
                ->color($toConfirm ? 'warning' : 'success')
                ->url(DonationResource::getUrl('index', ['tab' => 'to_confirm'])),
            Stat::make('Zakat/Khums to review', $toReview)
                ->description($toReview ? 'Contact these donors before using the gift' : 'Nothing to review')
                ->color($toReview ? 'warning' : 'success')
                ->url(DonationResource::getUrl('index', ['tab' => 'review'])),
        ];
    }
}
