<?php

use App\Enums\Role;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Filament\Auth\Pages\Login;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use PragmaRX\Google2FA\Google2FA;

beforeEach(fn () => $this->seed(DatabaseSeeder::class));

function admin2fa(array $attributes = []): User
{
    $user = User::factory()->create($attributes + ['password' => 'correct-horse-battery']);
    $user->assignRole(Role::Finance->value);

    return $user;
}

it('requires two-factor login for the admin panel unless switched off', function () {
    // The test run switches it off (phpunit.xml); the live default is on (config/sswf.php).
    expect(Filament::getPanel('admin')->isMultiFactorAuthenticationRequired())->toBeFalse();

    config(['sswf.admin_two_factor_required' => true]);
    expect(Filament::getPanel('admin')->isMultiFactorAuthenticationRequired())->toBeTrue();

    expect(require base_path('config/sswf.php'))->toHaveKey('admin_two_factor_required');
});

it('lets admins with two-factor login in', function () {
    config(['sswf.admin_two_factor_required' => true]);
    $user = admin2fa();
    $user->saveAppAuthenticationSecret((new Google2FA)->generateSecretKey());

    $this->actingAs($user)->get('/admin')->assertOk();
});

it('asks for the authenticator code after the password, and only signs in with the right one', function () {
    $secret = (new Google2FA)->generateSecretKey();
    $user = admin2fa();
    $user->saveAppAuthenticationSecret($secret);

    $login = Livewire::test(Login::class)
        ->set('data.email', $user->email)
        ->set('data.password', 'correct-horse-battery')
        ->call('authenticate');

    // Password alone is not enough.
    $this->assertGuest();
    expect($login->get('userUndertakingMultiFactorAuthentication'))->not->toBeNull();

    $login->set('data.multiFactor.app.code', '000000')->call('authenticate');
    $this->assertGuest();

    $login->set('data.multiFactor.app.code', (new Google2FA)->getCurrentOtp($secret))->call('authenticate');
    $this->assertAuthenticatedAs($user);
});

it('stores the secret and recovery codes encrypted', function () {
    $user = admin2fa();
    $user->saveAppAuthenticationSecret('JBSWY3DPEHPK3PXP');
    $user->saveAppAuthenticationRecoveryCodes(['abcd-efgh', 'ijkl-mnop']);

    $raw = DB::table('users')->where('id', $user->id)->first();
    expect($raw->app_authentication_secret)->not->toContain('JBSWY3DPEHPK3PXP')
        ->and($raw->app_authentication_recovery_codes)->not->toContain('abcd-efgh')
        ->and($user->fresh()->getAppAuthenticationSecret())->toBe('JBSWY3DPEHPK3PXP')
        ->and($user->fresh()->getAppAuthenticationRecoveryCodes())->toBe(['abcd-efgh', 'ijkl-mnop'])
        ->and($user->fresh()->toArray())->not->toHaveKey('app_authentication_secret');
});

it('offers email codes as an alternative to an authenticator app', function () {
    $user = admin2fa();
    expect($user->hasEmailAuthentication())->toBeFalse()->and($user->hasTwoFactor())->toBeFalse();

    $user->toggleEmailAuthentication(true);
    expect($user->fresh()->hasEmailAuthentication())->toBeTrue()->and($user->fresh()->hasTwoFactor())->toBeTrue();
});

it('lets admins manage two-factor login on their profile page', function () {
    $this->actingAs(admin2fa())->get('/admin/profile')->assertOk()
        ->assertSee('Authenticator app')
        ->assertSee('Email');
});
