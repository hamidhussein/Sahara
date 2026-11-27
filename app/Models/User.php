<?php

namespace App\Models;

use App\Models\Concerns\RecordsActivity;
use App\Enums\Role;
use Database\Factories\UserFactory;
use Filament\Auth\MultiFactor\App\Contracts\HasAppAuthentication;
use Filament\Auth\MultiFactor\App\Contracts\HasAppAuthenticationRecovery;
use Filament\Auth\MultiFactor\Email\Contracts\HasEmailAuthentication;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use SensitiveParameter;
use Spatie\Permission\Traits\HasRoles;

#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'remember_token', 'app_authentication_secret', 'app_authentication_recovery_codes'])]
class User extends Authenticatable implements FilamentUser, HasAppAuthentication, HasAppAuthenticationRecovery, HasEmailAuthentication
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasRoles, Notifiable, RecordsActivity;

    protected function activityArea(): string
    {
        return 'users';
    }

    /** Never passwords or two-factor secrets. */
    protected function activityOnly(): ?array
    {
        return ['name', 'email'];
    }

    protected function activityDescription(string $event): string
    {
        return ucfirst($event).' admin user';
    }

    /**
     * Only staff with one of the admin roles can sign in to /admin.
     */
    public function canAccessPanel(Panel $panel): bool
    {
        return $this->hasAnyRole(Role::names());
    }

    // Two-factor login: authenticator app (with recovery codes) or codes by email.

    public function getAppAuthenticationSecret(): ?string
    {
        return $this->app_authentication_secret;
    }

    public function saveAppAuthenticationSecret(#[SensitiveParameter] ?string $secret): void
    {
        $this->app_authentication_secret = $secret;
        $this->save();
    }

    /** Shown in the authenticator app next to the six-digit code. */
    public function getAppAuthenticationHolderName(): string
    {
        return $this->email;
    }

    public function getAppAuthenticationRecoveryCodes(): ?array
    {
        return $this->app_authentication_recovery_codes;
    }

    public function saveAppAuthenticationRecoveryCodes(#[SensitiveParameter] ?array $codes): void
    {
        $this->app_authentication_recovery_codes = $codes;
        $this->save();
    }

    public function hasEmailAuthentication(): bool
    {
        return (bool) $this->has_email_authentication;
    }

    public function toggleEmailAuthentication(bool $condition): void
    {
        $this->has_email_authentication = $condition;
        $this->save();
    }

    public function hasTwoFactor(): bool
    {
        return filled($this->app_authentication_secret) || $this->hasEmailAuthentication();
    }

    /** Lost phone: clear both methods, so the next sign-in asks to set two-factor up again. */
    public function resetTwoFactor(): void
    {
        $this->forceFill([
            'app_authentication_secret' => null,
            'app_authentication_recovery_codes' => null,
            'has_email_authentication' => false,
        ])->save();
    }

    public function isLastSuperAdmin(): bool
    {
        return $this->hasRole(Role::SuperAdmin->value)
            && static::role(Role::SuperAdmin->value)->count() <= 1;
    }

    protected static function boot(): void
    {
        // Last line of defence: the site must always have someone who can manage it.
        // Registered before the traits boot, because HasRoles removes the roles on
        // "deleting"; this check has to run first or a refused delete would still
        // strip the last Super Admin of the role.
        static::deleting(function (User $user) {
            if ($user->isLastSuperAdmin()) {
                throw new \LogicException('The last Super Admin cannot be deleted.');
            }
        });

        parent::boot();
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'app_authentication_secret' => 'encrypted',
            'app_authentication_recovery_codes' => 'encrypted:array',
            'has_email_authentication' => 'boolean',
        ];
    }
}
