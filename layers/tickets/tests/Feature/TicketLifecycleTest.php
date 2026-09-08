<?php

namespace Tickets\Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\Notification;
use Symfony\Component\HttpFoundation\Response;
use Tickets\Actions\AssignTicket;
use Tickets\Actions\CloseTicket;
use Tickets\Actions\PauseTicketWork;
use Tickets\Actions\ReopenTicket;
use Tickets\Actions\ResolveTicket;
use Tickets\Actions\StartTicketWork;
use Tickets\Actions\UnassignTicket;
use Tickets\Enums\TicketStatus;
use Tickets\Exceptions\IllegalTicketTransition;
use Tickets\Models\Ticket;
use Tickets\Tests\TestCase;

class TicketLifecycleTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Notification::fake();
    }

    /**
     * Walks every arrow of the specification table in one run.
     */
    public function test_it_walks_the_whole_lifecycle(): void
    {
        $technician = User::factory()->create();
        $ticket = Ticket::factory()->create(['status' => TicketStatus::Open]);

        $this->assertStatusAfter(TicketStatus::Assigned, fn () => app(AssignTicket::class)->handle($ticket, $technician));
        $this->assertStatusAfter(TicketStatus::Open, fn () => app(UnassignTicket::class)->handle($ticket));
        $this->assertStatusAfter(TicketStatus::Assigned, fn () => app(AssignTicket::class)->handle($ticket, $technician));
        $this->assertStatusAfter(TicketStatus::InProgress, fn () => app(StartTicketWork::class)->handle($ticket));
        $this->assertStatusAfter(TicketStatus::Assigned, fn () => app(PauseTicketWork::class)->handle($ticket));
        $this->assertStatusAfter(TicketStatus::InProgress, fn () => app(StartTicketWork::class)->handle($ticket));
        $this->assertStatusAfter(TicketStatus::Resolved, fn () => app(ResolveTicket::class)->handle($ticket));
        $this->assertStatusAfter(TicketStatus::InProgress, fn () => app(ReopenTicket::class)->handle($ticket));
        $this->assertStatusAfter(TicketStatus::Resolved, fn () => app(ResolveTicket::class)->handle($ticket));
        $this->assertStatusAfter(TicketStatus::Closed, fn () => app(CloseTicket::class)->handle($ticket));
    }

    public function test_it_clears_the_technician_when_unassigning(): void
    {
        $technician = User::factory()->create();
        $ticket = Ticket::factory()->create([
            'status' => TicketStatus::Assigned,
            'assigned_technician_id' => $technician->id,
        ]);

        app(UnassignTicket::class)->handle($ticket);

        $this->assertDatabaseHas('tickets', [
            'id' => $ticket->id,
            'assigned_technician_id' => null,
        ]);
    }

    public function test_it_refuses_a_transition_absent_from_the_table(): void
    {
        $ticket = Ticket::factory()->create(['status' => TicketStatus::Open]);

        $this->expectException(IllegalTicketTransition::class);
        $this->expectExceptionMessage('A ticket cannot move from "open" to "closed".');

        app(CloseTicket::class)->handle($ticket);
    }

    public function test_a_refused_transition_leaves_the_ticket_untouched(): void
    {
        $ticket = Ticket::factory()->create(['status' => TicketStatus::Open]);

        $this->assertThrows(
            fn () => app(ResolveTicket::class)->handle($ticket),
            IllegalTicketTransition::class,
        );

        $this->assertDatabaseHas('tickets', [
            'id' => $ticket->id,
            'status' => TicketStatus::Open->value,
            'resolved_at' => null,
        ]);
    }

    public function test_an_illegal_transition_is_reported_as_a_conflict(): void
    {
        $exception = IllegalTicketTransition::between(TicketStatus::Open, TicketStatus::Closed);

        $this->assertSame(Response::HTTP_CONFLICT, $exception->getStatusCode());
    }

    private function assertStatusAfter(TicketStatus $expected, callable $transition): void
    {
        $ticket = $transition();

        $this->assertSame($expected, $ticket->status);
        $this->assertDatabaseHas('tickets', ['id' => $ticket->id, 'status' => $expected->value]);
    }
}
