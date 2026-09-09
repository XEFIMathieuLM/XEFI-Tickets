<?php

namespace Tickets\Policies;

use App\Models\User;
use Tickets\Models\Attachment;

/**
 * Reading is settled upstream by the parent ticket's perimeters; removal
 * targets one named attachment, so it is checked against that parent.
 */
class AttachmentPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Attachment $attachment): bool
    {
        return true;
    }

    public function delete(User $user, Attachment $attachment): bool
    {
        return $user->can('view', $attachment->ticket);
    }
}
