<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

/**
 * Contact messages are read and answered by Editors (and Super Admin).
 * They only arrive through the website form, so nobody creates them here.
 */
class ContactMessagePolicy extends ContentPolicy
{
    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, Model $model): bool
    {
        return $this->isEditor($user);
    }
}
