<?php

namespace App\Policies;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

/**
 * Donations, giving types and causes are managed by Finance (plan 1.2).
 * Super Admin passes every check through Gate::before.
 */
abstract class FinancePolicy
{
    protected function isFinance(User $user): bool
    {
        return $user->hasRole(Role::Finance->value);
    }

    public function viewAny(User $user): bool
    {
        return $this->isFinance($user);
    }

    public function view(User $user, Model $model): bool
    {
        return $this->isFinance($user);
    }

    public function create(User $user): bool
    {
        return $this->isFinance($user);
    }

    public function update(User $user, Model $model): bool
    {
        return $this->isFinance($user);
    }

    public function delete(User $user, Model $model): bool
    {
        return $this->isFinance($user);
    }

    public function deleteAny(User $user): bool
    {
        return $this->isFinance($user);
    }
}
