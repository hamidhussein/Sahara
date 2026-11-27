<?php

namespace App\Http\Controllers;

use App\Enums\ReportType;
use App\Enums\TeamGroup;
use App\Models\Report;
use App\Models\TeamMember;
use App\Settings\DonationSettings;
use Illuminate\View\View;

class AboutController extends Controller
{
    public function about(): View
    {
        return view('about.index');
    }

    public function leadership(): View
    {
        $members = TeamMember::published()->with('media')->get()->groupBy(fn ($m) => $m->group->value);

        return view('about.leadership', [
            'groups' => collect(TeamGroup::cases())
                ->mapWithKeys(fn ($g) => [$g->getLabel() => $members->get($g->value, collect())])
                ->filter->isNotEmpty(),
        ]);
    }

    public function transparency(): View
    {
        $documents = Report::published()->with('media')->get();

        return view('about.transparency', [
            'certificates' => $documents->where('type', ReportType::Certificate),
            'reports' => $documents->whereIn('type', [ReportType::AnnualReport, ReportType::AuditedAccounts]),
            // The latest annual report that has a spending breakdown drives the chart.
            'spendingReport' => $documents->where('type', ReportType::AnnualReport)
                ->first(fn (Report $r) => count($r->spendingBreakdown()) > 0),
            'bank' => app(DonationSettings::class),
        ]);
    }
}
