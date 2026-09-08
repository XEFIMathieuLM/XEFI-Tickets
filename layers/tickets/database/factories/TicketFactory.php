<?php

namespace Tickets\Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Tickets\Enums\TicketPriority;
use Tickets\Enums\TicketStatus;
use Tickets\Models\Ticket;

/**
 * @extends Factory<Ticket>
 */
class TicketFactory extends Factory
{
    /**
     * Define the model's default state: a freshly opened, unassigned ticket.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'requester_id' => User::factory(),
            'assigned_technician_id' => null,
            'title' => faker()->ucfirst()->words(6),
            'description' => faker()->paragraphs(2),
            'status' => TicketStatus::Open,
            'priority' => faker()->randomElement(TicketPriority::cases()),
            'resolved_at' => null,
        ];
    }

    /**
     * Indicate that the ticket has been handed over to a technician.
     */
    public function assigned(): static
    {
        return $this->state(fn (array $attributes) => [
            'assigned_technician_id' => User::factory(),
            'status' => TicketStatus::Assigned,
        ]);
    }
}
