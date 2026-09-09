<?php

namespace Tickets\Livewire;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Component;
use Livewire\WithPagination;
use Tickets\Access\Controls\TicketControl;
use Tickets\Enums\TicketPriority;
use Tickets\Enums\TicketStatus;
use Tickets\Livewire\Concerns\ListsTickets;
use Tickets\Models\Ticket;

class TicketList extends Component
{
    use ListsTickets;
    use WithPagination;

    public const PER_PAGE = 25;

    public string $status = '';

    public string $priority = '';

    /**
     * Listens to the tickets currently on screen. A reader the channel refuses
     * simply never receives anything.
     *
     * @return array<string, string>
     */
    public function getListeners(): array
    {
        $listeners = [];

        foreach ($this->visibleTicketIds() as $ticketId) {
            $listeners[sprintf('echo-private:tickets.%s,.ticket.assigned', $ticketId)] = '$refresh';
        }

        return $listeners;
    }

    public function updatedStatus(): void
    {
        $this->resetPage();
    }

    public function updatedPriority(): void
    {
        $this->resetPage();
    }

    public function render(): View
    {
        return view('tickets::livewire.ticket-list', [
            'tickets' => $this->tickets(),
            'statuses' => TicketStatus::live(),
            'priorities' => TicketPriority::cases(),
            'reachesOthersTickets' => $this->reachesOthersTickets(),
        ]);
    }

    /**
     * @return array<int, string>
     */
    protected function sortableColumns(): array
    {
        return ['title', 'status', 'priority', 'created_at'];
    }

    /**
     * @return array<int, int>
     */
    private function visibleTicketIds(): array
    {
        return app(TicketControl::class)
            ->forCurrentUser(Ticket::query())
            ->whereIn('status', array_column(TicketStatus::live(), 'value'))
            ->orderBy($this->sortColumn, $this->sortDirection)
            ->limit(self::PER_PAGE)
            ->pluck('id')
            ->all();
    }

    /**
     * @return LengthAwarePaginator<int, Ticket>
     */
    private function tickets(): LengthAwarePaginator
    {
        $status = TicketStatus::tryFrom($this->status);
        $priority = TicketPriority::tryFrom($this->priority);

        return app(TicketControl::class)
            ->forCurrentUser(Ticket::query())
            ->whereIn('status', array_column(TicketStatus::live(), 'value'))
            ->with(['requester', 'assignedTechnician'])
            ->withCount('comments')
            ->when($status !== null, fn (Builder $query) => $query->where('status', $status))
            ->when($priority !== null, fn (Builder $query) => $query->where('priority', $priority))
            ->orderBy($this->sortColumn, $this->sortDirection)
            ->paginate(self::PER_PAGE);
    }
}
