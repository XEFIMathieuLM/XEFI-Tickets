<?php

namespace Tickets\Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;
use Tickets\Enums\TicketRole;
use Tickets\Enums\TicketStatus;
use Tickets\Livewire\TicketTransitions;
use Tickets\Models\Ticket;
use Tickets\Tests\TestCase;

/**
 * The support team sends a ticket wherever it needs to. Only two things hold
 * it back: a closed ticket is final, and nobody can be said to have taken on a
 * ticket that no technician holds.
 */
class TicketTransitionsScreenTest extends TestCase
{
    public function test_a_technician_sends_its_ticket_straight_to_resolved(): void
    {
        $technician = $this->userWith(TicketRole::Technician);
        $ticket = $this->assignedTo($technician, TicketStatus::Open);

        Livewire::actingAs($technician)
            ->test(TicketTransitions::class, ['ticket' => $ticket])
            ->call('moveTo', TicketStatus::Resolved->value)
            ->assertHasNoErrors();

        $this->assertDatabaseHas('tickets', [
            'id' => $ticket->id,
            'status' => TicketStatus::Resolved->value,
        ]);
    }

    public function test_a_technician_may_now_close_a_ticket(): void
    {
        $technician = $this->userWith(TicketRole::Technician);
        $ticket = $this->assignedTo($technician, TicketStatus::InProgress);

        Livewire::actingAs($technician)
            ->test(TicketTransitions::class, ['ticket' => $ticket])
            ->call('moveTo', TicketStatus::Closed->value)
            ->assertHasNoErrors();

        $this->assertDatabaseHas('tickets', [
            'id' => $ticket->id,
            'status' => TicketStatus::Closed->value,
        ]);
    }

    public function test_every_other_status_is_offered_to_the_support_team(): void
    {
        $technician = $this->userWith(TicketRole::Technician);
        $ticket = $this->assignedTo($technician, TicketStatus::Open);

        $screen = Livewire::actingAs($technician)
            ->test(TicketTransitions::class, ['ticket' => $ticket]);

        foreach (TicketStatus::cases() as $status) {
            if ($status === TicketStatus::Open) {
                continue;
            }

            $screen->assertSee(__($status->translationKey()));
        }
    }

    public function test_a_closed_ticket_offers_nothing_at_all(): void
    {
        $technician = $this->userWith(TicketRole::Technician);
        $ticket = $this->assignedTo($technician, TicketStatus::Closed);

        Livewire::actingAs($technician)
            ->test(TicketTransitions::class, ['ticket' => $ticket])
            ->assertSee(__('tickets::transition.empty'));
    }

    public function test_a_closed_ticket_refuses_a_move_asked_for_anyway(): void
    {
        $technician = $this->userWith(TicketRole::Technician);
        $ticket = $this->assignedTo($technician, TicketStatus::Closed);

        Livewire::actingAs($technician)
            ->test(TicketTransitions::class, ['ticket' => $ticket])
            ->call('moveTo', TicketStatus::InProgress->value)
            ->assertHasErrors('transition');

        $this->assertDatabaseHas('tickets', [
            'id' => $ticket->id,
            'status' => TicketStatus::Closed->value,
        ]);
    }

    public function test_a_technician_may_not_touch_a_ticket_it_was_not_given(): void
    {
        Livewire::actingAs($this->userWith(TicketRole::Technician))
            ->test(TicketTransitions::class, [
                'ticket' => Ticket::factory()->create(['status' => TicketStatus::InProgress]),
            ])
            ->assertForbidden();
    }

    public function test_a_requester_closes_its_own_ticket(): void
    {
        $requester = $this->userWith(TicketRole::Requester);
        $ticket = Ticket::factory()->create([
            'requester_id' => $requester->id,
            'status' => TicketStatus::Open,
        ]);

        Livewire::actingAs($requester)
            ->test(TicketTransitions::class, ['ticket' => $ticket])
            ->call('moveTo', TicketStatus::Closed->value)
            ->assertHasNoErrors();

        $this->assertDatabaseHas('tickets', [
            'id' => $ticket->id,
            'status' => TicketStatus::Closed->value,
        ]);
    }

    public function test_closing_is_the_only_move_a_requester_is_offered(): void
    {
        $requester = $this->userWith(TicketRole::Requester);
        $ticket = Ticket::factory()->create([
            'requester_id' => $requester->id,
            'status' => TicketStatus::Open,
        ]);

        $screen = Livewire::actingAs($requester)
            ->test(TicketTransitions::class, ['ticket' => $ticket])
            ->assertSee(__(TicketStatus::Closed->translationKey()));

        foreach ([TicketStatus::InProgress, TicketStatus::Resolved] as $outOfReach) {
            $screen->assertDontSee(__($outOfReach->translationKey()));
        }
    }

    public function test_a_requester_asking_for_another_move_is_refused(): void
    {
        $requester = $this->userWith(TicketRole::Requester);
        $ticket = Ticket::factory()->create([
            'requester_id' => $requester->id,
            'status' => TicketStatus::Open,
        ]);

        Livewire::actingAs($requester)
            ->test(TicketTransitions::class, ['ticket' => $ticket])
            ->call('moveTo', TicketStatus::Resolved->value)
            ->assertForbidden();

        $this->assertDatabaseHas('tickets', [
            'id' => $ticket->id,
            'status' => TicketStatus::Open->value,
        ]);
    }

    public function test_a_requester_may_not_close_somebody_elses_ticket(): void
    {
        $somebodyElse = Ticket::factory()->create(['status' => TicketStatus::Open]);

        Livewire::actingAs($this->userWith(TicketRole::Requester))
            ->test(TicketTransitions::class, ['ticket' => $somebodyElse])
            ->assertForbidden();

        $this->assertDatabaseHas('tickets', [
            'id' => $somebodyElse->id,
            'status' => TicketStatus::Open->value,
        ]);
    }

    public function test_sending_a_resolved_ticket_back_drops_its_resolution_stamp(): void
    {
        Notification::fake();

        $technician = $this->userWith(TicketRole::Technician);
        $ticket = $this->assignedTo($technician, TicketStatus::Resolved);
        $ticket->forceFill(['resolved_at' => now()])->save();

        Livewire::actingAs($technician)
            ->test(TicketTransitions::class, ['ticket' => $ticket])
            ->call('moveTo', TicketStatus::InProgress->value)
            ->assertHasNoErrors();

        $this->assertDatabaseHas('tickets', [
            'id' => $ticket->id,
            'status' => TicketStatus::InProgress->value,
            'resolved_at' => null,
        ]);
    }

    private function assignedTo(
        User $technician,
        TicketStatus $status = TicketStatus::InProgress,
    ): Ticket {
        return Ticket::factory()->create([
            'assigned_technician_id' => $technician->id,
            'status' => $status,
        ]);
    }
}
