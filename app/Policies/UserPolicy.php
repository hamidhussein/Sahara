<?php

namespace App\Policies;

use App\Enums\Role;
use App\Models\User;

/**
 * Admin users (Admin → Users). Only Super Admin manages users; Super Admin
 * passes the other checks through Gate::before, but deleting is decided here
 * so that nobody can delete themselves or the last Super Admin.
 */
class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return false;
    }

    public function view(User $user, User $model): bool
    {
        return false;
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, User $model): bool
    {
        return false;
    }

    public function delete(User $user, User $model): bool
    {
        return $user->hasRole(Role::SuperAdmin->value)
            && ! $user->is($model)
            && ! $model->isLastSuperAdmin();
    }

    public function deleteAny(User $user): bool
    {
        return false;
    }
}
