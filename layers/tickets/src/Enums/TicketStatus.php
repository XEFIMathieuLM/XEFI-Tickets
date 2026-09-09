<?php

namespace Tickets\Enums;

enum TicketStatus: string
{
    case Open = 'open';
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
     * Every status a ticket still moves through. What a ticket never leaves
     * belongs to the archive, not to the working list.
     *
     * @return array<int, self>
     */
    public static function live(): array
    {
        return array_values(array_filter(
            self::cases(),
            fn (self $status): bool => ! $status->isTerminal(),
        ));
    }

    /**
     * The key a view translates. The wording itself never enters the enum.
     */
    public function translationKey(): string
    {
        return "tickets::status.{$this->value}";
    }

    /**
     * The statuses this one may move to. The support team moves a ticket where
     * it judges useful, under two rules only: closing is final, and standing
     * still is not a move.
     *
     * @return array<int, self>
     */
    public function allowedTransitions(): array
    {
        if ($this->isTerminal()) {
            return [];
        }

        return array_values(array_filter(
            self::cases(),
            fn (self $target): bool => $target !== $this,
        ));
    }

    public function canTransitionTo(self $target): bool
    {
        return in_array($target, $this->allowedTransitions(), true);
    }
}
