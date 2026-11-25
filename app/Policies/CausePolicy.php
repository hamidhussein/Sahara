<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class CausePolicy extends FinancePolicy
{
    /** A cause with donations can be switched off, never deleted. */
    public function delete(User $user, Model $model): bool
    {
        return $this->isFinance($user) && ! $model->donations()->exists();
    }

    public function deleteAny(User $user): bool
    {
        return false;
    }
}
