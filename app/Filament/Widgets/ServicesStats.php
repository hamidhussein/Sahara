<?php

namespace App\Filament\Widgets;

use App\Enums\HelpStatus;
use App\Enums\HelpType;
use App\Enums\Role;
use App\Filament\Resources\BloodDonors\BloodDonorResource;
use App\Filament\Resources\HelpRequests\HelpRequestResource;
use App\Filament\Resources\Volunteers\VolunteerResource;
use App\Models\BloodDonor;
use App\Models\HelpRequest;
use App\Models\Volunteer;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

/** Dashboard: what the Services team needs to act on (plan 2.2, dashboard widgets). */
class ServicesStats extends StatsOverviewWidget
{
    protected static ?int $sort = 2;

    public static function canView(): bool
    {
        return (bool) auth()->user()?->hasAnyRole([Role::Services->value, Role::SuperAdmin->value]);
    }

    protected function getStats(): array
    {
        $new = HelpRequest::where('status', HelpStatus::New)->count();
        $blood = HelpRequest::where('status', HelpStatus::New)->where('type', HelpType::Blood)->count();
        $toCall = Volunteer::whereNull('contacted_at')->count();
        $donors = BloodDonor::where('is_available', true)->count();

        return [
            Stat::make('New help requests', $new)
                ->description(match (true) {
                    $blood > 0 => "{$blood} need blood: call first",
                    $new > 0 => 'Call each person as soon as you can',
                    default => 'All picked up',
                })
                ->color($blood ? 'danger' : ($new ? 'warning' : 'success'))
                ->url(HelpRequestResource::getUrl('index')),
            Stat::make('Volunteers to call', $toCall)
                ->description($toCall ? 'Signed up, not contacted yet' : 'Everyone has been contacted')
                ->color($toCall ? 'warning' : 'success')
                ->url(VolunteerResource::getUrl('index')),
            Stat::make('Blood donors available', $donors)
                ->description('Find one by blood group and district')
                ->url(BloodDonorResource::getUrl('index')),
        ];
    }
}
