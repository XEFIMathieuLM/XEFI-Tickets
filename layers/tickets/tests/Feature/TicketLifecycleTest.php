<?php

namespace Tickets\Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\Notification;
use Symfony\Component\HttpFoundation\Response;
use Tickets\Actions\AssignTicket;
use Tickets\Actions\CloseTicket;
use Tickets\Actions\ResolveTicket;
use Tickets\Actions\StartTicketWork;
use Tickets\Actions\UnassignTicket;
use Tickets\Enums\TicketStatus;
use Tickets\Exceptions\IllegalTicketTransition;
use Tickets\Exceptions\TicketIsClosed;
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
     * Walks the lifecycle back and forth in one run.
     */
    public function test_it_walks_the_whole_lifecycle(): void
    {
        $ticket = Ticket::factory()->create(['status' => TicketStatus::Open]);

        $this->assertStatusAfter(TicketStatus::InProgress, fn () => app(StartTicketWork::class)->handle($ticket));
        $this->assertStatusAfter(TicketStatus::Open, fn () => app(UnassignTicket::class)->handle($ticket));
        $this->assertStatusAfter(TicketStatus::InProgress, fn () => app(StartTicketWork::class)->handle($ticket));
        $this->assertStatusAfter(TicketStatus::Resolved, fn () => app(ResolveTicket::class)->handle($ticket));
        $this->assertStatusAfter(TicketStatus::InProgress, fn () => app(StartTicketWork::class)->handle($ticket));
        $this->assertStatusAfter(TicketStatus::Resolved, fn () => app(ResolveTicket::class)->handle($ticket));
        $this->assertStatusAfter(TicketStatus::Closed, fn () => app(CloseTicket::class)->handle($ticket));
    }

    public function test_handing_a_ticket_over_does_not_move_it(): void
    {
        $technician = User::factory()->create();
        $ticket = Ticket::factory()->create(['status' => TicketStatus::Open]);

        app(AssignTicket::class)->handle($ticket, $technician);

        $this->assertDatabaseHas('tickets', [
            'id' => $ticket->id,
            'status' => TicketStatus::Open->value,
            'assigned_technician_id' => $technician->id,
        ]);
    }

    public function test_a_closed_ticket_is_handed_to_nobody(): void
    {
        $ticket = Ticket::factory()->create(['status' => TicketStatus::Closed]);

        $this->assertThrows(
            fn () => app(AssignTicket::class)->handle($ticket, User::factory()->create()),
            TicketIsClosed::class,
        );

        $this->assertDatabaseHas('tickets', [
            'id' => $ticket->id,
            'assigned_technician_id' => null,
        ]);
    }

    public function test_it_clears_the_technician_when_returning_the_ticket_to_the_queue(): void
    {
        $technician = User::factory()->create();
        $ticket = Ticket::factory()->create([
            'status' => TicketStatus::InProgress,
            'assigned_technician_id' => $technician->id,
        ]);

        app(UnassignTicket::class)->handle($ticket);

        $this->assertDatabaseHas('tickets', [
            'id' => $ticket->id,
            'status' => TicketStatus::Open->value,
            'assigned_technician_id' => null,
        ]);
    }

    public function test_the_support_team_may_jump_straight_to_any_status(): void
    {
        $ticket = Ticket::factory()->create(['status' => TicketStatus::Open]);

        $this->assertStatusAfter(TicketStatus::Resolved, fn () => app(ResolveTicket::class)->handle($ticket));
        $this->assertStatusAfter(TicketStatus::Closed, fn () => app(CloseTicket::class)->handle($ticket));
    }

    public function test_a_closed_ticket_refuses_to_move_again(): void
    {
        $ticket = Ticket::factory()->create(['status' => TicketStatus::Closed]);

        $this->expectException(IllegalTicketTransition::class);
        $this->expectExceptionMessage('A ticket cannot move from "closed" to "in_progress".');

        app(StartTicketWork::class)->handle($ticket);
    }

    public function test_a_refused_transition_leaves_the_ticket_untouched(): void
    {
        $ticket = Ticket::factory()->create([
            'status' => TicketStatus::Closed,
            'resolved_at' => null,
        ]);

        $this->assertThrows(
            fn () => app(ResolveTicket::class)->handle($ticket),
            IllegalTicketTransition::class,
        );

        $this->assertDatabaseHas('tickets', [
            'id' => $ticket->id,
            'status' => TicketStatus::Closed->value,
            'resolved_at' => null,
        ]);
    }

    public function test_leaving_the_resolved_side_drops_the_resolution_stamp(): void
    {
        $ticket = Ticket::factory()->create([
            'status' => TicketStatus::Resolved,
            'resolved_at' => now(),
        ]);

        app(StartTicketWork::class)->handle($ticket);

        $this->assertDatabaseHas('tickets', [
            'id' => $ticket->id,
            'status' => TicketStatus::InProgress->value,
            'resolved_at' => null,
            'is_resolved_on_time' => null,
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
