<?php

namespace Tickets\Tests\Unit;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Tickets\Enums\TicketStatus;

/**
 * The state machine is a piece of architecture on its own: no database, no
 * container, no framework.
 */
class TicketStatusTest extends TestCase
{
    /**
     * Every arrow of the specification table, one case each.
     *
     * @return array<string, array{TicketStatus, TicketStatus}>
     */
    public static function legalTransitions(): array
    {
        return [
            'open to assigned' => [TicketStatus::Open, TicketStatus::Assigned],
            'assigned to in progress' => [TicketStatus::Assigned, TicketStatus::InProgress],
            'assigned back to open' => [TicketStatus::Assigned, TicketStatus::Open],
            'in progress to resolved' => [TicketStatus::InProgress, TicketStatus::Resolved],
            'in progress back to assigned' => [TicketStatus::InProgress, TicketStatus::Assigned],
            'resolved to closed' => [TicketStatus::Resolved, TicketStatus::Closed],
            'resolved back to in progress' => [TicketStatus::Resolved, TicketStatus::InProgress],
        ];
    }

    #[DataProvider('legalTransitions')]
    public function test_it_allows_a_transition_of_the_table(TicketStatus $from, TicketStatus $target): void
    {
        $this->assertTrue($from->canTransitionTo($target));
    }

    /**
     * Anything the table does not list must be refused, including staying put.
     */
    public function test_it_refuses_every_transition_absent_from_the_table(): void
    {
        $legalPairs = array_map(
            fn (array $pair): string => $pair[0]->value.'>'.$pair[1]->value,
            array_values(self::legalTransitions()),
        );

        foreach (TicketStatus::cases() as $from) {
            foreach (TicketStatus::cases() as $target) {
                if (in_array($from->value.'>'.$target->value, $legalPairs, true)) {
                    continue;
                }

                $this->assertFalse(
                    $from->canTransitionTo($target),
                    sprintf('%s should not reach %s.', $from->value, $target->value),
                );
            }
        }
    }

    public function test_a_closed_ticket_has_nowhere_left_to_go(): void
    {
        $this->assertSame([], TicketStatus::Closed->allowedTransitions());
    }

    public function test_only_the_closed_status_is_terminal(): void
    {
        foreach (TicketStatus::cases() as $status) {
            $this->assertSame($status === TicketStatus::Closed, $status->isTerminal());
        }
    }
}
