<?php

namespace App\Policies;

use App\Enums\Role;
use App\Models\Donation;
use App\Models\User;

/**
 * Donations come only from the website and are financial records: nobody
 * creates or deletes them in the admin, not even Super Admin.
 */
class DonationPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        // Overrides Gate::before so Super Admin cannot create or delete either.
        if (in_array($ability, ['create', 'delete', 'deleteAny', 'forceDelete', 'forceDeleteAny', 'restore', 'restoreAny', 'replicate'], true)) {
            return false;
        }

        return null;
    }

    protected function canManage(User $user): bool
    {
        return $user->hasAnyRole([Role::Finance->value, Role::SuperAdmin->value]);
    }

    public function viewAny(User $user): bool
    {
        return $this->canManage($user);
    }

    public function view(User $user, Donation $donation): bool
    {
        return $this->canManage($user);
    }

    public function update(User $user, Donation $donation): bool
    {
        return $this->canManage($user);
    }
}
