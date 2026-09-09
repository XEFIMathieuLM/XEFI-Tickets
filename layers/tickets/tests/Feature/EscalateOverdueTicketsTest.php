<?php

namespace Tickets\Tests\Feature;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Tickets\Enums\TicketPriority;
use Tickets\Enums\TicketRole;
use Tickets\Enums\TicketStatus;
use Tickets\Models\Ticket;
use Tickets\Notifications\TicketsEscalatedNotification;
use Tickets\Tests\TestCase;

class EscalateOverdueTicketsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Notification::fake();
    }

    public function test_it_raises_a_ticket_that_outran_its_target(): void
    {
        $late = $this->unresolvedTicket(TicketPriority::Low, hoursAgo: 73);

        $this->artisan('tickets:escalate-overdue')->assertSuccessful();

        $this->assertDatabaseHas('tickets', [
            'id' => $late->id,
            'priority' => TicketPriority::Normal->value,
        ]);
        $this->assertNotNull($late->fresh()?->escalated_at);
    }

    public function test_it_leaves_a_ticket_that_is_still_within_its_target(): void
    {
        $onTime = $this->unresolvedTicket(TicketPriority::Low, hoursAgo: 71);

        $this->artisan('tickets:escalate-overdue')->assertSuccessful();

        $this->assertDatabaseHas('tickets', [
            'id' => $onTime->id,
            'priority' => TicketPriority::Low->value,
            'escalated_at' => null,
        ]);
    }

    public function test_a_critical_ticket_is_flagged_but_never_climbs(): void
    {
        $critical = $this->unresolvedTicket(TicketPriority::Critical, hoursAgo: 50);

        $this->artisan('tickets:escalate-overdue')
            ->expectsOutputToContain(__('tickets::escalation.report.flagged', ['count' => 1]))
            ->assertSuccessful();

        $this->assertDatabaseHas('tickets', [
            'id' => $critical->id,
            'priority' => TicketPriority::Critical->value,
            'escalated_at' => null,
        ]);
    }

    public function test_a_resolved_ticket_is_never_escalated(): void
    {
        $resolved = Ticket::factory()->create([
            'priority' => TicketPriority::Low,
            'status' => TicketStatus::Resolved,
            'created_at' => now()->subHours(200),
        ]);

        $this->artisan('tickets:escalate-overdue')->assertSuccessful();

        $this->assertDatabaseHas('tickets', [
            'id' => $resolved->id,
            'priority' => TicketPriority::Low->value,
        ]);
    }

    public function test_running_it_twice_in_a_row_changes_nothing_more(): void
    {
        $late = $this->unresolvedTicket(TicketPriority::Low, hoursAgo: 200);

        $this->artisan('tickets:escalate-overdue')->assertSuccessful();
        $this->artisan('tickets:escalate-overdue')->assertSuccessful();

        $this->assertDatabaseHas('tickets', [
            'id' => $late->id,
            'priority' => TicketPriority::Normal->value,
        ]);
        Notification::assertSentTimes(TicketsEscalatedNotification::class, 1);
    }

    public function test_it_notifies_whoever_may_see_every_ticket(): void
    {
        $recipient = $this->userWith(TicketRole::Manager);
        $this->unresolvedTicket(TicketPriority::High, hoursAgo: 9);

        $this->artisan('tickets:escalate-overdue')->assertSuccessful();

        Notification::assertSentTo($recipient, TicketsEscalatedNotification::class);
    }

    public function test_the_query_count_does_not_follow_the_number_of_tickets(): void
    {
        Ticket::factory()->count(3)->create($this->overdueAttributes(73));
        $forThree = $this->ticketStatementsOfOneRun();

        Ticket::factory()->count(30)->create($this->overdueAttributes(73));

        $this->assertSame($forThree, $this->ticketStatementsOfOneRun());
    }

    /**
     * Counts only the statements that read or write tickets: the permission
     * cache warms up on the first run and would otherwise blur the measure.
     */
    private function ticketStatementsOfOneRun(): int
    {
        $collected = [];
        DB::listen(function ($query) use (&$collected): void {
            $collected[] = $query->sql;
        });

        $this->artisan('tickets:escalate-overdue')->assertSuccessful();

        return count(array_filter(
            $collected,
            fn (string $sql): bool => str_contains($sql, '`tickets`'),
        ));
    }

    /**
     * @return array<string, mixed>
     */
    private function overdueAttributes(int $hoursAgo): array
    {
        return [
            'priority' => TicketPriority::Low,
            'status' => TicketStatus::Open,
            'created_at' => now()->subHours($hoursAgo),
        ];
    }

    private function unresolvedTicket(TicketPriority $priority, int $hoursAgo): Ticket
    {
        return Ticket::factory()->create([
            'priority' => $priority,
            'status' => TicketStatus::Open,
            'created_at' => now()->subHours($hoursAgo),
        ]);
    }
}
