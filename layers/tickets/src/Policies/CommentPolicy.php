<?php

namespace Tickets\Policies;

use App\Models\User;
use Tickets\Models\Comment;

/**
 * Comments are only ever read, as an include on a ticket. The two abilities the
 * REST layer checks are declared; the day comments become mutable, the missing
 * ones will have to be added deliberately rather than inherited by accident.
 */
class CommentPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Comment $comment): bool
    {
        return true;
    }
}
