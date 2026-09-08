<?php

namespace Tickets\Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Seeder;
use Tickets\Enums\TicketPriority;
use Tickets\Enums\TicketRole;
use Tickets\Enums\TicketStatus;
use Tickets\Models\Attachment;
use Tickets\Models\Comment;
use Tickets\Models\Ticket;

class TicketsSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed one ticket per status/priority combination, so no enum case is missing
     * from the environment, spread over users of the three access profiles.
     */
    public function run(): void
    {
        $this->call(TicketAccessSeeder::class);

        $technicians = $this->usersOf(TicketRole::Technician, 2);
        $requesters = $this->usersOf(TicketRole::Requester, 5);

        foreach (TicketStatus::cases() as $status) {
            foreach (TicketPriority::cases() as $priority) {
                $ticket = $this->createTicket($status, $priority, $requesters, $technicians);

                $this->createComments($ticket, $requesters->merge($technicians));
                $this->createAttachments($ticket, $requesters);
            }
        }
    }

    /**
     * The reference account of the profile, plus a few extra holders of the role.
     *
     * @return Collection<int, User>
     */
    private function usersOf(TicketRole $role, int $extraCount): Collection
    {
        $extras = User::factory()
            ->count($extraCount)
            ->create()
            ->each(fn (User $user) => $user->assignRole($role->value));

        return $extras->push(User::where('email', $role->referenceEmail())->sole());
    }

    /**
     * @param  Collection<int, User>  $requesters
     * @param  Collection<int, User>  $technicians
     */
    private function createTicket(
        TicketStatus $status,
        TicketPriority $priority,
        Collection $requesters,
        Collection $technicians,
    ): Ticket {
        $isResolved = in_array($status, [TicketStatus::Resolved, TicketStatus::Closed], true);

        return Ticket::factory()->create([
            'requester_id' => $requesters->random()->getKey(),
            'assigned_technician_id' => $status === TicketStatus::Open
                ? null
                : $technicians->random()->getKey(),
            'status' => $status,
            'priority' => $priority,
            'resolved_at' => $isResolved ? faker()->dateTime('-20 days', '-1 day') : null,
        ]);
    }

    /**
     * Not every ticket carries a file, but enough do that the case is never
     * missing from the environment.
     *
     * @param  Collection<int, User>  $uploaders
     */
    private function createAttachments(Ticket $ticket, Collection $uploaders): void
    {
        if (faker()->number(1, 3) !== 1) {
            return;
        }

        Attachment::factory()
            ->stored()
            ->for($ticket)
            ->create(['uploaded_by_id' => $uploaders->random()->getKey()]);
    }

    /**
     * @param  Collection<int, User>  $authors
     */
    private function createComments(Ticket $ticket, Collection $authors): void
    {
        Comment::factory()
            ->count(faker()->number(1, 3))
            ->for($ticket)
            ->create(['author_id' => $authors->random()->getKey()]);
    }
}
