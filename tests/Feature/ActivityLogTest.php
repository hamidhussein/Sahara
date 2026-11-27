<?php

use App\Enums\DonationStatus;
use App\Enums\PaymentMethod;
use App\Enums\Role;
use App\Filament\Resources\ActivityLog\ActivityLogResource;
use App\Filament\Resources\ActivityLog\Pages\ListActivities;
use App\Models\Donation;
use App\Models\GivingType;
use App\Models\Project;
use App\Models\User;
use App\Services\DonationService;
use App\Settings\BackupSettings;
use App\Settings\DonationSettings;
use Database\Seeders\DatabaseSeeder;
use Filament\Actions\Testing\TestAction;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Livewire\Livewire;
use Spatie\Activitylog\Models\Activity;

beforeEach(function () {
    $this->seed(DatabaseSeeder::class);
    Mail::fake();
    Activity::query()->delete(); // only what each test does
});

function staffMember(Role $role, array $attributes = []): User
{
    $user = User::factory()->create($attributes);
    $user->assignRole($role->value);

    return $user;
}

function lastActivity(): Activity
{
    return Activity::query()->latest('id')->firstOrFail();
}

it('records who edited a project, with the old and new values', function () {
    $editor = staffMember(Role::Editor, ['name' => 'Sana Editor']);
    $this->actingAs($editor);

    Project::where('slug', 'ration-distribution')->firstOrFail()->update(['name' => 'Ration Distribution 2026']);

    $entry = lastActivity();
    expect($entry->description)->toBe('Updated project')
        ->and($entry->log_name)->toBe('content')
        ->and($entry->causer->is($editor))->toBeTrue()
        ->and($entry->attribute_changes['old'])->toBe(['name' => 'Ration Distribution'])
        ->and($entry->attribute_changes['attributes'])->toBe(['name' => 'Ration Distribution 2026'])
        ->and(ActivityLogResource::item($entry))->toBe('Project · Ration Distribution 2026');
});

it('records Finance confirming a payment, but never the donor’s details', function () {
    $donation = Donation::create([
        'giving_type_id' => GivingType::where('slug', 'sadaqah')->value('id'),
        'amount' => 2500, 'donor_name' => 'Ayesha Khan', 'donor_phone' => '0312 7654321',
        'donor_email' => 'ayesha@example.com', 'is_anonymous' => false,
        'payment_method' => PaymentMethod::JazzCash, 'status' => DonationStatus::AwaitingConfirmation,
    ]);
    expect(Activity::count())->toBe(0); // a donor giving on the website is not "activity"

    $finance = staffMember(Role::Finance);
    $this->actingAs($finance);
    app(DonationService::class)->confirmTransfer($donation, $finance);

    $entry = Activity::where('description', 'Confirmed payment')->sole();
    expect($entry->causer->is($finance))->toBeTrue()
        ->and($entry->log_name)->toBe('donations')
        ->and($entry->attribute_changes['attributes']['status'])->toBe('paid')
        ->and(json_encode($entry->attribute_changes))->not->toContain('Ayesha')->not->toContain('0312')->not->toContain('ayesha@');
});

it('records settings changes with old and new values, ignoring the system’s own bookkeeping', function () {
    $this->actingAs(staffMember(Role::SuperAdmin));

    $settings = app(DonationSettings::class);
    $before = $settings->account_number;
    $settings->account_number = '9999-0000-1111';
    $settings->save();

    $entry = lastActivity();
    expect($entry->description)->toBe('Updated donation settings')
        ->and($entry->attribute_changes['old'])->toBe(['account_number' => $before])
        ->and($entry->attribute_changes['attributes'])->toBe(['account_number' => '9999-0000-1111'])
        ->and(ActivityLogResource::item($entry))->toBe('Donation settings');

    $count = Activity::count();
    $backup = app(BackupSettings::class);
    $backup->last_manual_at = now()->toDateTimeString();
    $backup->last_manual_result = 'Completed';
    $backup->save();
    expect(Activity::count())->toBe($count);
});

it('records role changes and admin user edits, never passwords', function () {
    $this->actingAs(staffMember(Role::SuperAdmin));
    $user = staffMember(Role::Editor, ['name' => 'New Volunteer']);

    $user->assignRole(Role::Finance->value);
    expect(lastActivity()->description)->toBe('Gave role: Finance');

    $user->removeRole(Role::Editor->value);
    expect(lastActivity()->description)->toBe('Removed role: Editor');

    $user->update(['password' => 'a-new-secret-password']);
    expect(Activity::where('description', 'Updated admin user')->count())->toBe(0);
    expect(Activity::all()->pluck('attribute_changes')->toJson())->not->toContain('password');
});

it('records sign-ins and failed sign-ins', function () {
    $user = staffMember(Role::Editor, ['email' => 'editor@example.org', 'password' => 'correct-password']);

    Auth::attempt(['email' => 'editor@example.org', 'password' => 'wrong']);
    $failed = lastActivity();
    expect($failed->description)->toBe('Failed sign-in')
        ->and($failed->log_name)->toBe('security')
        ->and($failed->getProperty('email'))->toBe('editor@example.org')
        ->and(ActivityLogResource::who($failed))->toBe('Not signed in');

    Auth::attempt(['email' => 'editor@example.org', 'password' => 'correct-password']);
    expect(lastActivity()->description)->toBe('Signed in')
        ->and(lastActivity()->causer->is($user))->toBeTrue();
});

it('records every backup download', function () {
    Storage::fake('backups');
    Storage::disk('backups')->put('sswf-website/sswf-2026-11-27-01-30-00.zip', 'zip');
    $admin = staffMember(Role::SuperAdmin);

    $url = URL::temporarySignedRoute('backups.download', now()->addMinutes(5), ['path' => 'sswf-website/sswf-2026-11-27-01-30-00.zip']);
    $this->actingAs($admin)->get($url)->assertOk();

    $entry = lastActivity();
    expect($entry->description)->toBe('Downloaded a backup')
        ->and($entry->causer->is($admin))->toBeTrue()
        ->and($entry->getProperty('file'))->toBe('sswf-2026-11-27-01-30-00.zip');
});

it('shows the activity log to Super Admin only, read-only', function () {
    $admin = staffMember(Role::SuperAdmin, ['name' => 'Hamid Admin']);
    $this->actingAs($admin);
    Project::where('slug', 'ration-distribution')->firstOrFail()->update(['name' => 'Ration Distribution 2026']);
    $entry = Activity::where('description', 'Updated project')->sole();

    Livewire::test(ListActivities::class)
        ->assertCanSeeTableRecords([$entry])
        ->assertSee('Hamid Admin')
        ->assertSee('Project · Ration Distribution 2026')
        ->assertActionVisible(TestAction::make('view')->table($entry))
        ->assertActionDoesNotExist(TestAction::make('edit')->table($entry))
        ->assertActionDoesNotExist(TestAction::make('delete')->table($entry))
        ->mountAction(TestAction::make('view')->table($entry))
        ->assertHasNoErrors();

    expect(ActivityLogResource::changes($entry)->toHtml())->toContain('<strong>Name:</strong> Ration Distribution → Ration Distribution 2026');

    foreach ([Role::Finance, Role::Editor, Role::Services] as $role) {
        $this->actingAs(staffMember($role))->get('/admin/activity-log')->assertForbidden();
    }
});

it('removes entries older than a year every night', function () {
    Illuminate\Support\Facades\Schedule::swap($schedule = new Schedule('Asia/Karachi'));
    require base_path('routes/console.php');

    $clean = collect($schedule->events())->first(fn ($event) => str_contains($event->command, 'activitylog:clean'));
    expect($clean)->not->toBeNull()
        ->and($clean->expression)->toBe('0 4 * * *')
        ->and(config('activitylog.clean_after_days'))->toBe(365);
});
