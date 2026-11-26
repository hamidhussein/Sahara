<?php

namespace App\Filament\Resources\Donations\Pages;

use App\Enums\DonationStatus;
use App\Filament\Resources\Donations\DonationResource;
use App\Models\Donation;
use Filament\Actions\Action;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Builder;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ListDonations extends ListRecords
{
    protected static string $resource = DonationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('export')
                ->label('Export to Excel (CSV)')
                ->icon(Heroicon::OutlinedArrowDownTray)
                ->color('gray')
                ->action(fn () => $this->exportCsv()),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('All'),
            'to_confirm' => Tab::make('Payments to confirm')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', DonationStatus::AwaitingConfirmation))
                ->badge(fn () => Donation::where('status', DonationStatus::AwaitingConfirmation)->count() ?: null)
                ->badgeColor('warning'),
            'review' => Tab::make('Zakat/Khums to review')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('needs_eligibility_review', true)->whereIn('status', [DonationStatus::Paid, DonationStatus::AwaitingConfirmation])),
            'paid' => Tab::make('Paid')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', DonationStatus::Paid)),
        ];
    }

    /** Exports exactly what the table shows (tab, filters and search), for matching bank statements. */
    protected function exportCsv(): StreamedResponse
    {
        $query = $this->getFilteredSortedTableQuery()->with(['givingType', 'cause']);

        return response()->streamDownload(function () use ($query) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF"); // UTF-8 BOM so Excel shows Urdu names correctly
            fputcsv($out, ['Reference', 'Date', 'Paid on', 'Status', 'Amount (Rs)', 'Giving type', 'Cause', 'Method', 'Transaction ID', 'Donor', 'Phone', 'Email', 'Anonymous', 'Needs Zakat/Khums review']);

            $query->chunk(500, function ($donations) use ($out) {
                foreach ($donations as $d) {
                    fputcsv($out, [
                        $d->reference,
                        $d->created_at->format('Y-m-d H:i'),
                        $d->paid_at?->format('Y-m-d H:i'),
                        $d->status->getLabel(),
                        $d->amount,
                        $d->givingType->name,
                        $d->causeLabel(),
                        $d->payment_method->getLabel(),
                        $d->gateway_transaction_id,
                        $d->donor_name,
                        $d->donor_phone,
                        $d->donor_email,
                        $d->is_anonymous ? 'Yes' : 'No',
                        $d->needs_eligibility_review ? 'Yes' : 'No',
                    ]);
                }
            });

            fclose($out);
        }, 'sswf-donations-'.now()->format('Y-m-d').'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
