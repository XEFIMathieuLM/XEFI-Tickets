<?php

namespace App\Policies;

use App\Models\User;

/**
 * Users are only ever read through a ticket's relations for now, so the policy
 * covers the abilities the REST layer checks when including them.
 */
class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, User $viewed): bool
    {
        return true;
    }
}
