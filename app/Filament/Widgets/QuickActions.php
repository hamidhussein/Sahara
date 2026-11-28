<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\BloodDonors\BloodDonorResource;
use App\Filament\Resources\ContactMessages\ContactMessageResource;
use App\Filament\Resources\Donations\DonationResource;
use App\Filament\Resources\HelpRequests\HelpRequestResource;
use App\Filament\Resources\Posts\PostResource;
use App\Filament\Resources\Projects\ProjectResource;
use Filament\Widgets\Widget;

class QuickActions extends Widget
{
    protected static ?int $sort = -5;

    protected static bool $isLazy = false;

    protected int|string|array $columnSpan = 'full';

    protected string $view = 'filament.widgets.quick-actions';

    /** @return array{links: list<array{label: string, description: string, icon: string, url: string}>} */
    protected function getViewData(): array
    {
        $links = [];

        if (DonationResource::canViewAny()) {
            $links[] = ['label' => 'Review payments', 'description' => 'Match receipts with your statements', 'icon' => 'heroicon-o-banknotes', 'url' => DonationResource::getUrl('index', ['tab' => 'to_confirm'])];
        }

        if (HelpRequestResource::canViewAny()) {
            $links[] = ['label' => 'Open help requests', 'description' => 'Follow up with people who need support', 'icon' => 'heroicon-o-lifebuoy', 'url' => HelpRequestResource::getUrl('index')];
        }

        if (PostResource::canCreate()) {
            $links[] = ['label' => 'Write a news update', 'description' => 'Share recent work from the field', 'icon' => 'heroicon-o-pencil-square', 'url' => PostResource::getUrl('create')];
        }

        if (ProjectResource::canCreate()) {
            $links[] = ['label' => 'Add a project', 'description' => 'Document a project and its impact', 'icon' => 'heroicon-o-map-pin', 'url' => ProjectResource::getUrl('create')];
        }

        if (ContactMessageResource::canViewAny()) {
            $links[] = ['label' => 'Answer messages', 'description' => 'Respond to website enquiries', 'icon' => 'heroicon-o-inbox', 'url' => ContactMessageResource::getUrl('index')];
        }

        if (BloodDonorResource::canViewAny()) {
            $links[] = ['label' => 'Find a blood donor', 'description' => 'Search by blood group and district', 'icon' => 'heroicon-o-heart', 'url' => BloodDonorResource::getUrl('index')];
        }

        return ['links' => $links];
    }
}
