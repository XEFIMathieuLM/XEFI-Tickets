<?php

namespace Tickets\Enums;

enum TicketStatus: string
{
    case Open = 'open';
    case Assigned = 'assigned';
    case InProgress = 'in_progress';
    case Resolved = 'resolved';
    case Closed = 'closed';

    /**
     * Whether the ticket has reached the status it never leaves again.
     */
    public function isTerminal(): bool
    {
        return $this === self::Closed;
    }

    /**
     * The statuses this one may move to. Anything absent is illegal.
     *
     * @return array<int, self>
     */
    public function allowedTransitions(): array
    {
        return match ($this) {
            self::Open => [self::Assigned],
            self::Assigned => [self::InProgress, self::Open],
            self::InProgress => [self::Resolved, self::Assigned],
            self::Resolved => [self::Closed, self::InProgress],
            self::Closed => [],
        };
    }

    public function canTransitionTo(self $target): bool
    {
        return in_array($target, $this->allowedTransitions(), true);
    }
}
