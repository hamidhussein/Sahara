<?php

use App\Enums\ReportType;
use App\Enums\Role;
use App\Enums\TeamGroup;
use App\Filament\Resources\Reports\Pages\CreateReport;
use App\Filament\Resources\TeamMembers\Pages\CreateTeamMember;
use App\Models\Report;
use App\Models\TeamMember;
use App\Models\User;
use App\Settings\SiteSettings;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed(DatabaseSeeder::class);
    Storage::fake('public');
});

function aboutStaff(Role $role): User
{
    $user = User::factory()->create();
    $user->assignRole($role->value);

    return $user;
}

// ── About ─────────────────────────────────────────────────────────────────

it('tells the story with the plan’s confirmed timeline only', function () {
    $this->get('/about')->assertOk()
        ->assertSee('Subka Sahara Welfare Foundation began in 2006 as Syedabad Sahara Welfare Organization')
        ->assertSee('Registered under the Voluntary Social Welfare Agencies Ordinance 1961 (Reg. No. J-6 (16)/203/2012)')
        ->assertSee('The commitment is the same; the reach is wider.')
        ->assertSee('id="sahara-complex"', false)
        ->assertSee(route('donate', ['cause' => 'sahara-complex']), false)
        ->assertDontSee('[Year]')
        ->assertDontSee('First water filtration plants open');
});

it('links to the Sahara Complex from the About menu', function () {
    $this->get('/')->assertSee(route('about').'#sahara-complex', false);
});

// ── Leadership ────────────────────────────────────────────────────────────

it('shows published leaders by section and hides advisers until confirmed', function () {
    $this->get('/about/leadership')->assertOk()
        ->assertSee('Patrons')->assertSee('Agha Sheikh Mushtaq Hussain Ansari')
        ->assertSee('Executive Body')->assertSee('Arif Hussain')->assertSee('Secretary Finance')
        ->assertDontSee('Syed Riaz Kazmi')
        ->assertDontSee('Advisers');

    TeamMember::where('name', 'Syed Riaz Kazmi')->update(['is_published' => true]);

    $this->get('/about/leadership')->assertSee('Advisers')->assertSee('Syed Riaz Kazmi');
});

it('shows the chairman’s message only after it is approved and entered', function () {
    $this->get('/about/leadership')->assertDontSee('Chairman’s message');

    $settings = app(SiteSettings::class);
    $settings->chairman_message = 'Every rupee you give will reach the people who need it.';
    $settings->save();

    $this->get('/about/leadership')
        ->assertSee('Every rupee you give will reach the people who need it.')
        ->assertSee('Chairman, Subka Sahara Welfare Foundation');
});

it('uses initials when a leader has no photo', function () {
    expect(TeamMember::where('name', 'Agha Sheikh Mushtaq Hussain Ansari')->sole()->initials())->toBe('MA')
        ->and(TeamMember::where('name', 'Arif Hussain')->sole()->initials())->toBe('AH');
});

// ── Transparency ──────────────────────────────────────────────────────────

it('shows registration, bank account, funding and the Zakat and Khums policy', function () {
    $this->get('/about/transparency')->assertOk()
        ->assertSee('J-6 (16)/203/2012')
        ->assertSee('Registration under the Companies Act in process')
        ->assertSee('2010-0081-002350-01-8')
        ->assertSee('Rent from the shops in our building')
        ->assertSee('Zakat and Fitrana go only to eligible (mustahiq) individuals and families.')
        ->assertSee('Our annual reports and audited accounts will be published here.')
        ->assertDontSee('Where each rupee goes');
});

it('lists published reports for download and hides unpublished ones', function () {
    $report = Report::create(['title' => 'Annual Report 2026', 'type' => ReportType::AnnualReport, 'year' => 2026]);
    $report->addMedia(UploadedFile::fake()->createWithContent('report.pdf', '%PDF-1.4
1 0 obj <<>> endobj
trailer <<>>
%%EOF'))->toMediaCollection('file');
    Report::create(['title' => 'Draft Accounts', 'type' => ReportType::AuditedAccounts, 'year' => 2026, 'is_published' => false]);

    $this->get('/about/transparency')
        ->assertSee('Annual Report 2026')
        ->assertSee($report->fileUrl(), false)
        ->assertDontSee('Draft Accounts')
        ->assertDontSee('will be published here');
});

it('charts spending only from real figures entered with the annual report', function () {
    Report::create([
        'title' => 'Annual Report 2026', 'type' => ReportType::AnnualReport, 'year' => 2026,
        'spending' => [
            ['label' => 'Social Services', 'amount' => 300000],
            ['label' => 'Clean Water', 'amount' => 600000],
            ['label' => 'Health', 'amount' => 100000],
            ['label' => 'Empty line', 'amount' => 0],
        ],
    ]);

    $this->get('/about/transparency')
        ->assertSee('Where each rupee goes')
        ->assertSeeInOrder(['Clean Water', '60%', 'Rs 600,000', 'Social Services', '30%', 'Health', '10%'])
        ->assertDontSee('Empty line');
});

// ── Admin ─────────────────────────────────────────────────────────────────

it('gives reports to Finance and leadership to Editors', function () {
    $this->actingAs(aboutStaff(Role::Finance))->get('/admin/reports')->assertOk();
    $this->actingAs(aboutStaff(Role::Finance))->get('/admin/team-members')->assertForbidden();
    $this->actingAs(aboutStaff(Role::Editor))->get('/admin/team-members')->assertOk();
    $this->actingAs(aboutStaff(Role::Editor))->get('/admin/reports')->assertForbidden();
});

it('lets Finance publish a report with its spending breakdown', function () {
    Livewire::actingAs(aboutStaff(Role::Finance))->test(CreateReport::class)
        ->fillForm([
            'type' => ReportType::AnnualReport->value,
            'year' => 2026,
            'title' => 'Annual Report 2026',
            'file' => [UploadedFile::fake()->createWithContent('annual-report-2026.pdf', '%PDF-1.4
1 0 obj <<>> endobj
trailer <<>>
%%EOF')],
            'spending' => [
                ['label' => 'Clean Water', 'amount' => 750000],
                ['label' => 'Health', 'amount' => 250000],
            ],
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $report = Report::sole();
    expect($report->hasMedia('file'))->toBeTrue()
        ->and(collect($report->spendingBreakdown())->pluck('percent')->all())->toBe([75.0, 25.0]);

    $this->get('/about/transparency')->assertSee('Annual Report 2026')->assertSee('75%');
});

it('lets Editors add a leader', function () {
    $this->actingAs(aboutStaff(Role::Editor));

    Livewire::test(CreateTeamMember::class)
        ->fillForm(['name' => 'Habibullah', 'role' => 'Former office-bearer', 'group' => TeamGroup::Adviser->value, 'is_published' => true])
        ->call('create')
        ->assertHasNoFormErrors();

    $this->get('/about/leadership')->assertSee('Habibullah')->assertSee('Former office-bearer');
});
