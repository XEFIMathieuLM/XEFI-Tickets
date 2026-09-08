<?php

namespace Tickets\Exceptions;

use RuntimeException;
use Tickets\Models\Ticket;

/**
 * The disk refused the file. Named rather than generic, so a handler can tell
 * this apart from any other write failure.
 */
class AttachmentStorageFailed extends RuntimeException
{
    public static function forTicket(Ticket $ticket): self
    {
        return new self(sprintf('The attachment could not be stored for ticket %d.', $ticket->getKey()));
    }
}
