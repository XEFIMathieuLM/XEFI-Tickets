<?php

namespace Tickets\Actions;

use Illuminate\Support\Facades\Storage;
use Tickets\Models\Attachment;
use Tickets\Models\Ticket;

/**
 * Clears what hangs on a ticket before it is destroyed for good.
 *
 * No foreign key cascades in this schema, so the children have to go first and
 * explicitly. The attachment files go with their rows, otherwise the disk keeps
 * growing on bytes nothing points at any more.
 */
class RemoveTicketDependents
{
    public function handle(Ticket $ticket): void
    {
        $ticket->attachments->each(
            fn (Attachment $attachment) => Storage::disk($attachment->disk)->delete($attachment->path),
        );

        $ticket->attachments()->delete();
        $ticket->comments()->delete();
    }
}
