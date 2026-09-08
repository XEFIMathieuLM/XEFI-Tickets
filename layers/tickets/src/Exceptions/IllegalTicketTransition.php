<?php

namespace Tickets\Exceptions;

use RuntimeException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Tickets\Enums\TicketStatus;

/**
 * Raised when a ticket is asked to move somewhere its current status does not
 * allow. It carries its own HTTP status, so the API answers 409 without any
 * endpoint writing a response code by hand.
 */
class IllegalTicketTransition extends RuntimeException implements HttpExceptionInterface
{
    private function __construct(
        private readonly TicketStatus $from,
        private readonly TicketStatus $target,
    ) {
        parent::__construct(sprintf(
            'A ticket cannot move from "%s" to "%s".',
            $from->value,
            $target->value,
        ));
    }

    public static function between(TicketStatus $from, TicketStatus $target): self
    {
        return new self($from, $target);
    }

    /**
     * The two ends of the refused move, so a user interface can name them in
     * its own language instead of showing this exception's message.
     */
    public function from(): TicketStatus
    {
        return $this->from;
    }

    public function target(): TicketStatus
    {
        return $this->target;
    }

    public function getStatusCode(): int
    {
        return Response::HTTP_CONFLICT;
    }

    /**
     * @return array<string, string>
     */
    public function getHeaders(): array
    {
        return [];
    }
}
