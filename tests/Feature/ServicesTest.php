<?php

use App\Enums\BloodGroup;
use App\Enums\HelpStatus;
use App\Enums\HelpType;
use App\Enums\Role;
use App\Filament\Resources\BloodDonors\Pages\ListBloodDonors;
use App\Filament\Resources\HelpRequests\Pages\ListHelpRequests;
use App\Filament\Resources\HelpRequests\Pages\ViewHelpRequest;
use App\Filament\Resources\Volunteers\Pages\ListVolunteers;
use App\Livewire\BloodDonorForm;
use App\Livewire\HelpRequestForm;
use App\Livewire\VolunteerForm;
use App\Mail\HelpRequestReceived;
use App\Models\BloodDonor;
use App\Models\HelpRequest;
use App\Models\User;
use App\Models\Volunteer;
use Database\Seeders\DatabaseSeeder;
use Filament\Actions\Testing\TestAction;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Spatie\Activitylog\Models\Activity;

beforeEach(function () {
    Storage::fake('local');
    $this->seed(DatabaseSeeder::class);
    Mail::fake();
});

function servicesUser(Role $role = Role::Services, string $email = 'duty@subkasahara.org'): User
{
    $user = User::factory()->create(['email' => $email]);
    $user->assignRole($role->value);

    return $user;
}

function helpForm()
{
    return Livewire::test(HelpRequestForm::class)
        ->call('choose', 'blood')
        ->set('name', 'Fatima Bibi')
        ->set('phone', '0355 1234567')
        ->set('district', 'Nagar')
        ->set('village', 'Phekar')
        ->set('details', 'Two bottles of B+ needed at DHQ Gilgit by tomorrow morning.')
        ->set('consent', true);
}

// ── Public pages ──────────────────────────────────────────────────────────

it('shows the emergency panel and all six kinds of help', function () {
    $response = $this->get('/get-help')->assertOk()
        ->assertSee('In an emergency, call now.')
        ->assertSee('tel:', false);

    foreach (HelpType::cases() as $type) {
        $response->assertSee($type->getLabel());
    }
});

it('shows the ways to get involved with working forms', function () {
    $this->get('/get-involved')->assertOk()
        ->assertSee('Join as a volunteer')
        ->assertSee('Become a blood donor')
        ->assertSee(route('partner'))
        ->assertSee('id="volunteer"', false)
        ->assertSee('id="blood-donor"', false);

    $this->get('/')->assertSee(route('get-involved').'#volunteer')->assertSee(route('get-involved').'#blood-donor');
});

// ── Help requests ─────────────────────────────────────────────────────────

it('takes a help request with a document, gives a reference and alerts the Services team', function () {
    servicesUser();

    helpForm()
        ->set('document', UploadedFile::fake()->image('prescription.jpg'))
        ->call('send')
        ->assertHasNoErrors()
        ->assertSee('We have received your request')
        ->assertSee('HR-'.now()->year.'-000001');

    $request = HelpRequest::sole();
    expect($request->type)->toBe(HelpType::Blood)
        ->and($request->status)->toBe(HelpStatus::New)
        ->and($request->phone)->toBe('+923551234567')
        ->and($request->getFirstMedia('document')->disk)->toBe('local');

    Mail::assertQueued(HelpRequestReceived::class, fn ($mail) => $mail->hasTo('duty@subkasahara.org') && $mail->helpRequest->is($request));
});

it('marks blood requests as urgent and falls back to Super Admin when there is no Services user', function () {
    servicesUser(Role::SuperAdmin, 'chair@subkasahara.org');

    helpForm()->call('send')->assertHasNoErrors();

    Mail::assertQueued(HelpRequestReceived::class, fn ($mail) => $mail->hasTo('chair@subkasahara.org'));
    expect((new HelpRequestReceived(HelpRequest::sole()))->envelope()->subject)->toStartWith('URGENT: Help request HR-');
});

it('checks the help form and needs consent', function () {
    Livewire::test(HelpRequestForm::class)
        ->set('phone', '12')
        ->call('send')
        ->assertHasErrors(['type', 'name', 'district', 'details', 'consent']);

    helpForm()->set('phone', '12')->call('send')->assertHasErrors(['phone']);
    expect(HelpRequest::count())->toBe(0);
});

it('keeps nothing from bots and limits repeated requests', function () {
    helpForm()->set('website', 'http://spam.example')->call('send')->assertSee('We have received your request');
    expect(HelpRequest::count())->toBe(0);

    foreach (range(1, 5) as $i) {
        helpForm()->call('send')->assertHasNoErrors();
    }
    helpForm()->call('send')->assertHasErrors(['details']);
    expect(HelpRequest::count())->toBe(5);
});

it('lets the Services team work through a request, recorded in the activity log', function () {
    $duty = servicesUser();
    $this->actingAs($duty);
    helpForm()->call('send');
    $request = HelpRequest::sole();

    Livewire::test(ListHelpRequests::class)->assertCanSeeTableRecords([$request]);

    Livewire::test(ViewHelpRequest::class, ['record' => $request->getKey()])
        ->assertSee('+923551234567')
        ->callAction('updateStatus', ['status' => HelpStatus::Done->value, 'staff_notes' => 'Two donors arranged.']);

    $request->refresh();
    expect($request->status)->toBe(HelpStatus::Done)
        ->and($request->handledBy->is($duty))->toBeTrue()
        ->and(Activity::where('description', 'Marked help request as done')->exists())->toBeTrue()
        ->and(Activity::all()->pluck('attribute_changes')->toJson())->not->toContain('B+ needed'); // what they wrote stays private

    // Finished requests drop off the default (open) list.
    Livewire::test(ListHelpRequests::class)->assertCanNotSeeTableRecords([$request]);
});

it('shows a help request’s document to the Services team only', function () {
    servicesUser();
    helpForm()->set('document', UploadedFile::fake()->image('slip.jpg'))->call('send');
    $request = HelpRequest::sole();
    $url = route('services.help-document', $request);

    $this->get($url)->assertRedirect(); // signed out
    $this->actingAs(servicesUser(Role::Finance, 'finance@subkasahara.org'))->get($url)->assertForbidden();
    $this->actingAs(User::where('email', 'duty@subkasahara.org')->sole())->get($url)->assertOk();
});

// ── Volunteers and blood donors ───────────────────────────────────────────

it('signs up a volunteer, and updates them if they sign up again', function () {
    $form = fn () => Livewire::test(VolunteerForm::class)
        ->set('name', 'Ali Raza')->set('phone', '0312-7654321')->set('area', 'Jutial, Gilgit')
        ->set('skills', ['Driving', 'Medical'])->set('availability', 'Weekends');

    $form()->call('join')->assertHasNoErrors()->assertSee('Welcome to the Sahara family!');
    $form()->set('availability', 'Evenings')->call('join');

    $volunteer = Volunteer::sole();
    expect($volunteer->phone)->toBe('+923127654321')
        ->and($volunteer->skills)->toBe(['Medical', 'Driving'])
        ->and($volunteer->availability)->toBe('Evenings');
});

it('registers a blood donor, refusing a future donation date', function () {
    $form = fn () => Livewire::test(BloodDonorForm::class)
        ->set('name', 'Sajid Hussain')->set('bloodGroup', 'O-')->set('district', 'Gilgit')
        ->set('phone', '+92 344 1112223')->set('consent', true);

    $form()->set('lastDonatedOn', now()->addDay()->toDateString())->call('register')->assertHasErrors(['lastDonatedOn']);
    $form()->set('lastDonatedOn', now()->subMonths(4)->toDateString())->call('register')->assertHasNoErrors();

    $donor = BloodDonor::sole();
    expect($donor->blood_group)->toBe(BloodGroup::ONegative)
        ->and($donor->is_available)->toBeTrue()
        ->and($donor->canDonateAgain())->toBeTrue();
});

it('finds a blood donor by group and district', function () {
    $this->actingAs(servicesUser());
    $match = BloodDonor::create(['name' => 'Match', 'blood_group' => 'B+', 'district' => 'Nagar', 'phone' => '+923001', 'consent_at' => now()]);
    $otherGroup = BloodDonor::create(['name' => 'Other group', 'blood_group' => 'A+', 'district' => 'Nagar', 'phone' => '+923002', 'consent_at' => now()]);
    $otherPlace = BloodDonor::create(['name' => 'Other place', 'blood_group' => 'B+', 'district' => 'Hunza', 'phone' => '+923003', 'consent_at' => now()]);
    $away = BloodDonor::create(['name' => 'Away', 'blood_group' => 'B+', 'district' => 'Nagar', 'phone' => '+923004', 'consent_at' => now(), 'is_available' => false]);

    Livewire::test(ListBloodDonors::class)
        ->filterTable('blood_group', 'B+')
        ->filterTable('district', 'Nagar')
        ->assertCanSeeTableRecords([$match])
        ->assertCanNotSeeTableRecords([$otherGroup, $otherPlace, $away]);
});

it('lets staff mark a volunteer as contacted', function () {
    $this->actingAs(servicesUser());
    $volunteer = Volunteer::create(['name' => 'Ali', 'phone' => '+923127654321', 'area' => 'Gilgit']);

    Livewire::test(ListVolunteers::class)->callAction(TestAction::make('markContacted')->table($volunteer));

    expect($volunteer->fresh()->contacted_at)->not->toBeNull();
});

it('keeps services data from other roles and off public pages', function (Role $role) {
    $this->actingAs(servicesUser($role, strtolower($role->name).'@subkasahara.org'));

    $this->get('/admin/help-requests')->assertForbidden();
    $this->get('/admin/volunteers')->assertForbidden();
    $this->get('/admin/blood-donors')->assertForbidden();
})->with([Role::Finance, Role::Editor]);
