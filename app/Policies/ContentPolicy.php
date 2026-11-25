<?php

namespace App\Policies;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

/**
 * Content (pages, news, programs, projects, partners, media) is managed by
 * Editors (plan 1.2). Super Admin passes every check through Gate::before.
 */
abstract class ContentPolicy
{
    protected function isEditor(User $user): bool
    {
        return $user->hasRole(Role::Editor->value);
    }

    public function viewAny(User $user): bool
    {
        return $this->isEditor($user);
    }

    public function view(User $user, Model $model): bool
    {
        return $this->isEditor($user);
    }

    public function create(User $user): bool
    {
        return $this->isEditor($user);
    }

    public function update(User $user, Model $model): bool
    {
        return $this->isEditor($user);
    }

    public function delete(User $user, Model $model): bool
    {
        return $this->isEditor($user);
    }

    public function deleteAny(User $user): bool
    {
        return $this->isEditor($user);
    }
}
