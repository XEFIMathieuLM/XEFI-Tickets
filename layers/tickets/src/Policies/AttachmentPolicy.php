<?php

namespace Tickets\Policies;

use App\Models\User;
use Tickets\Models\Attachment;

/**
 * An attachment is reachable only through the ticket it hangs on, and that
 * ticket has already passed the perimeters of TicketControl. Reading is
 * therefore settled upstream, and re-asking here would mean one query per row.
 *
 * Removal is a different matter: it targets a single attachment named by the
 * client, so it is checked against the parent for real.
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
