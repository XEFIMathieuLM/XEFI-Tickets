<?php

namespace Tickets\Exceptions;

use RuntimeException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

/**
 * Raised when something is asked of a ticket that has been closed. Closing is
 * final, so nothing about the ticket moves again. It carries its own HTTP
 * status, so the API answers 409 without any endpoint writing a code by hand.
 */
class TicketIsClosed extends RuntimeException implements HttpExceptionInterface
{
    private function __construct()
    {
        parent::__construct('A ticket that is closed can no longer be changed.');
    }

    public static function already(): self
    {
        return new self;
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
