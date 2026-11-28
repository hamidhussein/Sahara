<?php

namespace App\Filament\Pages;

use Filament\Actions\Action;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    public function getSubheading(): ?string
    {
        return 'Your daily overview of donations, community requests and website content.';
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('website')->label('View website')->color('gray')
                ->icon('heroicon-o-arrow-top-right-on-square')
                ->url(route('home'), shouldOpenInNewTab: true),
        ];
    }
}
