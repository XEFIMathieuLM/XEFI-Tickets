<?php

namespace Tickets\Tests\Feature;

use Illuminate\Support\Facades\Queue;
use Tickets\Actions\ReopenTicket;
use Tickets\Actions\ResolveTicket;
use Tickets\Enums\TicketPriority;
use Tickets\Enums\TicketStatus;
use Tickets\Jobs\EvaluateResolutionDelay;
use Tickets\Models\Ticket;
use Tickets\Tests\TestCase;

class ResolutionDelayTest extends TestCase
{
    public function test_it_dispatches_the_delay_job_only_after_the_commit(): void
    {
        Queue::fake();

        $ticket = Ticket::factory()->create(['status' => TicketStatus::InProgress]);

        app(ResolveTicket::class)->handle($ticket);

        Queue::assertPushed(
            EvaluateResolutionDelay::class,
            fn (EvaluateResolutionDelay $job): bool => $job->afterCommit === true,
        );
    }

    public function test_it_records_that_the_target_was_met(): void
    {
        $ticket = $this->resolveAfter(TicketPriority::High, hours: 2);

        $this->assertTrue($ticket->fresh()?->is_resolved_on_time);
    }

    public function test_it_records_that_the_target_was_missed(): void
    {
        $ticket = $this->resolveAfter(TicketPriority::Critical, hours: 50);

        $this->assertFalse($ticket->fresh()?->is_resolved_on_time);
    }

    public function test_it_settles_nothing_on_a_ticket_that_is_not_resolved(): void
    {
        $ticket = Ticket::factory()->create([
            'status' => TicketStatus::InProgress,
            'resolved_at' => null,
        ]);

        (new EvaluateResolutionDelay($ticket->id))->handle();

        $this->assertNull($ticket->fresh()?->is_resolved_on_time);
    }

    public function test_it_settles_nothing_on_a_ticket_that_no_longer_exists(): void
    {
        $ticket = Ticket::factory()->create(['status' => TicketStatus::InProgress]);
        $missingId = $ticket->id + 1000;

        (new EvaluateResolutionDelay($missingId))->handle();

        $this->assertDatabaseMissing('tickets', ['id' => $missingId]);
    }

    public function test_reopening_a_ticket_clears_the_verdict(): void
    {
        $ticket = $this->resolveAfter(TicketPriority::High, hours: 2);

        app(ReopenTicket::class)->handle($ticket);

        $this->assertNull($ticket->fresh()?->is_resolved_on_time);
    }

    /**
     * Opens a ticket the given number of hours ago, resolves it now, and lets
     * the job settle the verdict.
     */
    private function resolveAfter(TicketPriority $priority, int $hours): Ticket
    {
        $ticket = Ticket::factory()->create([
            'status' => TicketStatus::InProgress,
            'priority' => $priority,
            'created_at' => now()->subHours($hours),
        ]);

        app(ResolveTicket::class)->handle($ticket);

        (new EvaluateResolutionDelay($ticket->id))->handle();

        return $ticket;
    }
}
