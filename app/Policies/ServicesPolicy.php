<?php

namespace App\Policies;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

/**
 * Help requests, volunteers and blood donors hold people's phone numbers and
 * private needs (plan 2.2): only the Services role sees them. Super Admin
 * passes every check through Gate::before. Records come from the public forms,
 * so nobody creates them in the admin.
 */
abstract class ServicesPolicy
{
    protected function isServices(User $user): bool
    {
        return $user->hasRole(Role::Services->value);
    }

    public function viewAny(User $user): bool
    {
        return $this->isServices($user);
    }

    public function view(User $user, Model $model): bool
    {
        return $this->isServices($user);
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, Model $model): bool
    {
        return $this->isServices($user);
    }

    /** For spam and duplicates. */
    public function delete(User $user, Model $model): bool
    {
        return $this->isServices($user);
    }
}
