<?php

namespace Tickets\Livewire\Concerns;

use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Locked;
use Tickets\Access\TicketAbility;

/**
 * What every screen showing a table of tickets needs: an ordering the reader
 * drives, and the answer to whether naming the requester is worth a column.
 */
trait ListsTickets
{
    #[Locked]
    public string $sortColumn = 'created_at';

    #[Locked]
    public string $sortDirection = 'desc';

    /**
     * Only a name from the allow list ever reaches the query; anything else is
     * dropped before it gets there.
     */
    public function sortBy(string $column): void
    {
        if (! in_array($column, $this->sortableColumns(), true)) {
            return;
        }

        $this->sortDirection = $this->sortColumn === $column && $this->sortDirection === 'asc' ? 'desc' : 'asc';
        $this->sortColumn = $column;

        $this->resetPage();
    }

    /**
     * Whether the reader works the queue rather than following its own
     * requests. What is worth a column, and what belongs to the support team
     * alone, both hang on this.
     */
    public function reachesOthersTickets(): bool
    {
        return Gate::allows(TicketAbility::ReachesOthersTickets->value);
    }

    /**
     * @return array<int, string>
     */
    abstract protected function sortableColumns(): array;
}
