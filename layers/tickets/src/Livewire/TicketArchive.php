<?php

namespace Tickets\Livewire;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\View\View;
use Livewire\Component;
use Livewire\WithPagination;
use Tickets\Access\Controls\TicketControl;
use Tickets\Enums\TicketStatus;
use Tickets\Livewire\Concerns\ListsTickets;
use Tickets\Models\Ticket;

/**
 * Everything the support team has finished with. A closed ticket never moves
 * again, so this screen only reads: what each account may see here is decided
 * by the very perimeters that rule the live list.
 */
class TicketArchive extends Component
{
    use ListsTickets;
    use WithPagination;

    public const PER_PAGE = 25;

    public function render(): View
    {
        return view('tickets::livewire.ticket-archive', [
            'tickets' => $this->tickets(),
            'reachesOthersTickets' => $this->reachesOthersTickets(),
        ]);
    }

    /**
     * @return array<int, string>
     */
    protected function sortableColumns(): array
    {
        return ['title', 'priority', 'created_at', 'resolved_at'];
    }

    /**
     * @return LengthAwarePaginator<int, Ticket>
     */
    private function tickets(): LengthAwarePaginator
    {
        return app(TicketControl::class)
            ->forCurrentUser(Ticket::query())
            ->where('status', TicketStatus::Closed)
            ->with(['requester', 'assignedTechnician'])
            ->withCount('comments')
            ->orderBy($this->sortColumn, $this->sortDirection)
            ->paginate(self::PER_PAGE);
    }
}
