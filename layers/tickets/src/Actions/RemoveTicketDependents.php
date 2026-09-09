<?php

namespace Tickets\Actions;

use Illuminate\Support\Facades\Storage;
use Tickets\Models\Attachment;
use Tickets\Models\Ticket;

/**
 * Nothing cascades in this schema, so the children of a ticket about to be
 * destroyed have to go first, files included.
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
