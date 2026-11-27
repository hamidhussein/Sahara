<?php

namespace App\Support;

use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;
use Spatie\LaravelSettings\Events\SavingSettings;
use Spatie\Permission\Events\RoleAttachedEvent;
use Spatie\Permission\Events\RoleDetachedEvent;
use Spatie\Permission\Models\Role as RoleModel;

/**
 * Activity that isn't a model change: sign-ins, role changes and settings.
 * Model changes are recorded by App\Models\Concerns\RecordsActivity.
 */
class ActivityRecorder
{
    /** Written by the system (backup results), not by people. */
    private const IGNORED_SETTINGS = ['last_check_at', 'last_check_passed', 'last_check_summary', 'last_manual_at', 'last_manual_result'];

    public static function register(): void
    {
        Event::listen(Login::class, fn (Login $event) => activity('security')
            ->causedBy($event->user)->performedOn($event->user)->event('login')
            ->withProperties(['ip' => request()->ip()])
            ->log('Signed in'));

        Event::listen(Logout::class, function (Logout $event) {
            if ($event->user instanceof Model) {
                activity('security')->causedBy($event->user)->performedOn($event->user)->event('logout')->log('Signed out');
            }
        });

        // Who tried, from where: helps spot someone guessing passwords.
        Event::listen(Failed::class, fn (Failed $event) => activity('security')
            ->event('failed-login')
            ->withProperties(['email' => Str::limit((string) ($event->credentials['email'] ?? ''), 190, ''), 'ip' => request()->ip()])
            ->log('Failed sign-in'));

        Event::listen(RoleAttachedEvent::class, fn (RoleAttachedEvent $event) => static::roles($event->model, $event->rolesOrIds, 'Gave role'));
        Event::listen(RoleDetachedEvent::class, fn (RoleDetachedEvent $event) => static::roles($event->model, $event->rolesOrIds, 'Removed role'));

        Event::listen(SavingSettings::class, fn (SavingSettings $event) => static::settings($event));
    }

    private static function roles(Model $user, mixed $rolesOrIds, string $verb): void
    {
        $names = collect(Arr::wrap($rolesOrIds instanceof \Traversable ? iterator_to_array($rolesOrIds) : $rolesOrIds))
            ->map(fn ($role) => match (true) {
                $role instanceof Model => $role->getAttribute('name'),
                is_numeric($role) => RoleModel::find($role)?->name,
                default => (string) $role,
            })
            ->filter()->implode(', ');

        if ($names !== '') {
            activity('users')->performedOn($user)->event('role')->withProperty('roles', $names)->log("{$verb}: {$names}");
        }
    }

    private static function settings(SavingSettings $event): void
    {
        $new = $event->properties->all();
        $old = $event->originalValues?->all() ?? [];

        $changed = collect($new)
            ->reject(fn ($value, $key) => in_array($key, self::IGNORED_SETTINGS, true))
            ->filter(fn ($value, $key) => ! array_key_exists($key, $old) || $old[$key] != $value)
            ->keys();

        if ($changed->isEmpty()) {
            return;
        }

        $label = Str::of(class_basename($event->settings))->beforeLast('Settings')->headline()->lower();

        activity('settings')
            ->event('updated')
            ->withProperty('settings', class_basename($event->settings))
            ->withChanges([
                'attributes' => $changed->mapWithKeys(fn ($key) => [$key => static::short($new[$key])])->all(),
                'old' => $changed->mapWithKeys(fn ($key) => [$key => static::short($old[$key] ?? null)])->all(),
            ])
            ->log("Updated {$label} settings");
    }

    /** Long texts (message templates) are cut so the log stays small. */
    private static function short(mixed $value): mixed
    {
        if (is_array($value) || is_object($value)) {
            $value = json_encode($value, JSON_UNESCAPED_UNICODE);
        }

        return is_string($value) ? Str::limit($value, 300) : $value;
    }
}
