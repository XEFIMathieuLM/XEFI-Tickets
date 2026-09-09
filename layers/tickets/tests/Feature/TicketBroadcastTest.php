<?php

namespace Tickets\Tests\Feature;

use App\Models\User;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Support\Facades\Event;
use Tickets\Actions\AssignTicket;
use Tickets\Enums\TicketRole;
use Tickets\Enums\TicketStatus;
use Tickets\Events\TicketAssigned;
use Tickets\Models\Ticket;
use Tickets\Tests\TestCase;

class TicketBroadcastTest extends TestCase
{
    /**
     * The suite runs on the null broadcaster, which answers every channel
     * request without consulting the callbacks. The real driver is put back and
     * the layer's channels are declared on it, so authorisation is exercised.
     */
    protected function setUp(): void
    {
        parent::setUp();

        config(['broadcasting.default' => 'pusher']);

        require base_path('layers/tickets/routes/channels.php');
    }

    public function test_the_domain_event_of_the_lifecycle_is_the_one_broadcast(): void
    {
        Event::fake([TicketAssigned::class]);

        $ticket = Ticket::factory()->create(['status' => TicketStatus::Open]);
        app(AssignTicket::class)->handle($ticket, $this->userWith(TicketRole::Technician));

        Event::assertDispatched(TicketAssigned::class);
        $this->assertInstanceOf(ShouldBroadcast::class, new TicketAssigned($ticket, User::factory()->create()));
    }

    public function test_it_speaks_on_a_private_channel_named_after_the_ticket(): void
    {
        $ticket = Ticket::factory()->create();

        $channels = (new TicketAssigned($ticket, User::factory()->create()))->broadcastOn();

        $this->assertCount(1, $channels);
        $this->assertInstanceOf(PrivateChannel::class, $channels[0]);
        $this->assertSame(sprintf('private-tickets.%s', $ticket->id), (string) $channels[0]);
    }

    public function test_a_manager_is_admitted_on_the_channel(): void
    {
        $ticket = Ticket::factory()->create();

        $this->assertTrue($this->mayListen($this->userWith(TicketRole::Manager), $ticket));
    }

    public function test_the_requester_of_the_ticket_is_admitted(): void
    {
        $requester = $this->userWith(TicketRole::Requester);
        $ticket = Ticket::factory()->create(['requester_id' => $requester->id]);

        $this->assertTrue($this->mayListen($requester, $ticket));
    }

    public function test_a_requester_is_refused_on_a_ticket_that_is_not_its_own(): void
    {
        $ticket = Ticket::factory()->create();

        $this->assertFalse($this->mayListen($this->userWith(TicketRole::Requester), $ticket));
    }

    public function test_a_technician_is_refused_on_a_ticket_it_was_not_given(): void
    {
        $ticket = Ticket::factory()->create();

        $this->assertFalse($this->mayListen($this->userWith(TicketRole::Technician), $ticket));
    }

    public function test_a_user_without_any_permission_is_refused(): void
    {
        $ticket = Ticket::factory()->create();

        $this->assertFalse($this->mayListen(User::factory()->create(), $ticket));
    }

    /**
     * Asserts the endpoint really answered, so a refusal can never be a route
     * that simply does not exist.
     */
    private function mayListen(User $listener, Ticket $ticket): bool
    {
        $status = $this->actingAs($listener)->postJson('/broadcasting/auth', [
            'socket_id' => '1234.5678',
            'channel_name' => sprintf('private-tickets.%s', $ticket->id),
        ])->status();

        $this->assertContains($status, [200, 403], 'The channel endpoint did not answer.');

        return $status === 200;
    }
}
