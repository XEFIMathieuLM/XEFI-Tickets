<?php

namespace Tickets\Events;

use App\Models\User;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Tickets\Models\Ticket;

/**
 * The domain event of TP4, now also sent to the browsers. Its channel is the
 * ticket itself, so only readers the policy admits ever subscribe.
 */
class TicketAssigned implements ShouldBroadcast
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public readonly Ticket $ticket,
        public readonly User $technician,
    ) {}

    /**
     * @return array<int, PrivateChannel>
     */
    public function broadcastOn(): array
    {
        return [new PrivateChannel(sprintf('tickets.%s', $this->ticket->getKey()))];
    }

    public function broadcastAs(): string
    {
        return 'ticket.assigned';
    }

    /**
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return ['ticket_id' => $this->ticket->getKey()];
    }
}
