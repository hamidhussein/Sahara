<?php

use App\Enums\Role;
use App\Filament\Resources\Users\Pages\CreateUser;
use App\Filament\Resources\Users\Pages\EditUser;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Filament\Actions\Testing\TestAction;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Spatie\Activitylog\Models\Activity;

beforeEach(fn () => $this->seed(DatabaseSeeder::class));

function adminWith(Role ...$roles): User
{
    $user = User::factory()->create();
    $user->assignRole(array_map(fn (Role $role) => $role->value, $roles));

    return $user;
}

it('adds an admin user with roles and a password', function () {
    $this->actingAs(adminWith(Role::SuperAdmin));

    Livewire::test(CreateUser::class)
        ->fillForm([
            'name' => 'Zahra Finance',
            'email' => 'zahra@subkasahara.org',
            'role_names' => [Role::Finance->value],
            'password' => 'a-long-safe-password',
            'password_confirmation' => 'a-long-safe-password',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $user = User::where('email', 'zahra@subkasahara.org')->sole();
    expect($user->getRoleNames()->all())->toBe([Role::Finance->value])
        ->and(Hash::check('a-long-safe-password', $user->password))->toBeTrue()
        ->and(Activity::where('description', 'Gave role: Finance')->exists())->toBeTrue();
});

it('needs a role, a unique email and a password of 12 characters', function () {
    $this->actingAs(adminWith(Role::SuperAdmin));
    User::factory()->create(['email' => 'taken@subkasahara.org']);

    Livewire::test(CreateUser::class)
        ->fillForm(['name' => 'X', 'email' => 'taken@subkasahara.org', 'role_names' => [], 'password' => 'short', 'password_confirmation' => 'short'])
        ->call('create')
        ->assertHasFormErrors(['email' => 'unique', 'role_names' => 'required', 'password']);
});

it('changes roles and keeps the password unless a new one is typed', function () {
    $this->actingAs(adminWith(Role::SuperAdmin));
    $user = adminWith(Role::Editor);
    $hash = $user->password;

    Livewire::test(EditUser::class, ['record' => $user->getKey()])
        ->assertSchemaStateSet(['role_names' => [Role::Editor->value]])
        ->fillForm(['role_names' => [Role::Finance->value], 'password' => ''])
        ->call('save')
        ->assertHasNoFormErrors();

    $user->refresh();
    expect($user->getRoleNames()->all())->toBe([Role::Finance->value])
        ->and($user->password)->toBe($hash)
        ->and(Activity::where('description', 'Removed role: Editor')->exists())->toBeTrue()
        ->and(Activity::where('description', 'Gave role: Finance')->exists())->toBeTrue();
});

it('never lets you lock yourself or the site out', function () {
    $me = adminWith(Role::SuperAdmin);
    $this->actingAs($me);

    // You cannot drop your own Super Admin role…
    Livewire::test(EditUser::class, ['record' => $me->getKey()])
        ->fillForm(['role_names' => [Role::Editor->value]])
        ->call('save')
        ->assertHasFormErrors(['role_names']);
    expect($me->fresh()->hasRole(Role::SuperAdmin->value))->toBeTrue();

    // …delete yourself, or remove your own access.
    Livewire::test(ListUsers::class)
        ->assertActionHidden(TestAction::make('delete')->table($me))
        ->assertActionHidden(TestAction::make('removeAccess')->table($me));
    expect($me->isLastSuperAdmin())->toBeTrue()
        ->and(fn () => $me->delete())->toThrow(LogicException::class)
        ->and($me->fresh()->hasRole(Role::SuperAdmin->value))->toBeTrue(); // the refused delete did not strip the role

    // With a second Super Admin, you may remove the other one.
    $other = adminWith(Role::SuperAdmin);
    Livewire::test(ListUsers::class)->callAction(TestAction::make('delete')->table($other));
    expect(User::find($other->getKey()))->toBeNull();
});

it('removes someone’s access but keeps their history', function () {
    $this->actingAs(adminWith(Role::SuperAdmin));
    $leaver = adminWith(Role::Finance, Role::Editor);

    Livewire::test(ListUsers::class)
        ->callAction(TestAction::make('removeAccess')->table($leaver))
        ->assertNotified();

    $leaver->refresh();
    expect($leaver->roles)->toBeEmpty()
        ->and($leaver->canAccessPanel(filament()->getPanel('admin')))->toBeFalse()
        ->and(User::find($leaver->getKey()))->not->toBeNull();
});

it('resets two-factor login for someone who lost their phone', function () {
    $this->actingAs(adminWith(Role::SuperAdmin));
    $user = adminWith(Role::Editor);
    $user->saveAppAuthenticationSecret('JBSWY3DPEHPK3PXP');
    $user->saveAppAuthenticationRecoveryCodes(['code-1', 'code-2']);

    Livewire::test(ListUsers::class)
        ->assertActionVisible(TestAction::make('resetTwoFactor')->table($user))
        ->callAction(TestAction::make('resetTwoFactor')->table($user));

    $user->refresh();
    expect($user->hasTwoFactor())->toBeFalse()
        ->and($user->getAppAuthenticationRecoveryCodes())->toBeNull()
        ->and(Activity::where('description', 'Reset two-factor login')->where('subject_id', $user->getKey())->exists())->toBeTrue();
});

it('shows roles, two-factor status and the last sign-in', function () {
    $admin = adminWith(Role::SuperAdmin);
    $this->actingAs($admin);
    $editor = adminWith(Role::Editor);
    activity('security')->causedBy($editor)->performedOn($editor)->event('login')->log('Signed in');

    Livewire::test(ListUsers::class)
        ->assertCanSeeTableRecords([$admin, $editor])
        ->assertSee(Role::Editor->value)
        ->assertTableColumnStateSet('two_factor', false, $editor)
        ->filterTable('role', Role::Editor->value)
        ->assertCanSeeTableRecords([$editor])
        ->assertCanNotSeeTableRecords([$admin]);

    expect(App\Filament\Resources\Users\UserResource::getEloquentQuery()->find($editor->getKey())->last_signed_in_at)->not->toBeNull();
});

it('keeps the Users pages for Super Admin only', function (Role $role) {
    $this->actingAs(adminWith($role));

    $this->get('/admin/users')->assertForbidden();
    $this->get('/admin/users/create')->assertForbidden();
})->with([Role::Finance, Role::Editor, Role::Services]);
