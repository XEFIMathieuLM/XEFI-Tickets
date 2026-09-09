<?php

namespace Tickets\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Tickets\Enums\TicketStatus;

/**
 * The state machine is a piece of architecture on its own: no database, no
 * container, no framework. Two rules survive: closing is final, and standing
 * still is not a move.
 */
class TicketStatusTest extends TestCase
{
    public function test_a_live_ticket_reaches_every_other_status(): void
    {
        foreach (TicketStatus::cases() as $from) {
            if ($from->isTerminal()) {
                continue;
            }

            foreach (TicketStatus::cases() as $target) {
                if ($from === $target) {
                    continue;
                }

                $this->assertTrue(
                    $from->canTransitionTo($target),
                    sprintf('%s should reach %s.', $from->value, $target->value),
                );
            }
        }
    }

    public function test_no_status_may_stand_still(): void
    {
        foreach (TicketStatus::cases() as $status) {
            $this->assertFalse(
                $status->canTransitionTo($status),
                sprintf('%s should not reach itself.', $status->value),
            );
        }
    }

    public function test_a_closed_ticket_has_nowhere_left_to_go(): void
    {
        $this->assertSame([], TicketStatus::Closed->allowedTransitions());

        foreach (TicketStatus::cases() as $target) {
            $this->assertFalse(TicketStatus::Closed->canTransitionTo($target));
        }
    }

    public function test_a_live_status_offers_exactly_the_four_others(): void
    {
        foreach (TicketStatus::cases() as $status) {
            if ($status->isTerminal()) {
                continue;
            }

            $this->assertCount(count(TicketStatus::cases()) - 1, $status->allowedTransitions());
            $this->assertNotContains($status, $status->allowedTransitions());
        }
    }

    public function test_only_the_closed_status_is_terminal(): void
    {
        foreach (TicketStatus::cases() as $status) {
            $this->assertSame($status === TicketStatus::Closed, $status->isTerminal());
        }
    }
}
