<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

/**
 * Editors edit the text of existing pages. Pages are not created or deleted
 * in the admin yet: each page needs a route, and the legal pages must stay.
 */
class PagePolicy extends ContentPolicy
{
    public function create(User $user): bool
    {
        return false;
    }

    public function delete(User $user, Model $model): bool
    {
        return false;
    }

    public function deleteAny(User $user): bool
    {
        return false;
    }
}
