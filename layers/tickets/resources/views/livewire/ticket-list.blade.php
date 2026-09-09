@use('Tickets\Enums\TicketPriority')
@use('Tickets\Enums\TicketStatus')

<div>
    <div class="flex flex-wrap items-end justify-between gap-6">
        <div>
            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-xefi-red">XEFI Support</p>
            <h1 class="mt-1 text-2xl font-bold tracking-tight text-xefi-ink">
                {{ $reachesOthersTickets ? __('tickets::list.heading') : __('tickets::list.heading_own') }}
            </h1>
        </div>

        <div class="flex gap-4">
            <label class="text-sm">
                <span class="block text-xs font-semibold uppercase tracking-wide text-xefi-steel">
                    {{ __('tickets::list.filters.status') }}
                </span>
                <select
                    wire:model.live="status"
                    class="mt-1.5 rounded-sm border border-xefi-line bg-white px-3 py-2 text-sm text-xefi-ink focus:border-xefi-red focus:outline-none"
                >
                    <option value="">{{ __('tickets::list.filters.any') }}</option>
                    @foreach ($statuses as $availableStatus)
                        <option value="{{ $availableStatus->value }}" wire:key="status-{{ $availableStatus->value }}">
                            {{ __($availableStatus->translationKey()) }}
                        </option>
                    @endforeach
                </select>
            </label>

            <label class="text-sm">
                <span class="block text-xs font-semibold uppercase tracking-wide text-xefi-steel">
                    {{ __('tickets::list.filters.priority') }}
                </span>
                <select
                    wire:model.live="priority"
                    class="mt-1.5 rounded-sm border border-xefi-line bg-white px-3 py-2 text-sm text-xefi-ink focus:border-xefi-red focus:outline-none"
                >
                    <option value="">{{ __('tickets::list.filters.any') }}</option>
                    @foreach ($priorities as $availablePriority)
                        <option value="{{ $availablePriority->value }}" wire:key="priority-{{ $availablePriority->value }}">
                            {{ __($availablePriority->translationKey()) }}
                        </option>
                    @endforeach
                </select>
            </label>
        </div>
    </div>

    <div class="mt-6 overflow-x-auto border border-xefi-line bg-white shadow-sm">
        <table class="w-full text-left text-sm">
            <thead class="border-b-2 border-xefi-ink bg-white text-xs uppercase tracking-wide text-xefi-graphite">
                <tr>
                    @foreach (['title', 'status', 'priority', 'created_at'] as $column)
                        <th class="px-4 py-3 font-bold" wire:key="head-{{ $column }}">
                            <button type="button" wire:click="sortBy('{{ $column }}')" class="hover:text-xefi-red">
                                {{ __("tickets::list.columns.{$column}") }}
                                @if ($sortColumn === $column)
                                    <span aria-hidden="true" class="text-xefi-red">
                                        {{ $sortDirection === 'asc' ? '↑' : '↓' }}
                                    </span>
                                @endif
                            </button>
                        </th>
                    @endforeach
                    @if ($reachesOthersTickets)
                        <th class="px-4 py-3 font-bold">{{ __('tickets::list.columns.requester') }}</th>
                    @endif
                    <th class="px-4 py-3 font-bold">{{ __('tickets::list.columns.technician') }}</th>
                    <th class="px-4 py-3 font-bold">{{ __('tickets::list.columns.comments') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-xefi-line">
                @forelse ($tickets as $ticket)
                    <tr wire:key="ticket-{{ $ticket->id }}" class="hover:bg-xefi-mist">
                        <td class="px-4 py-3">
                            <a
                                href="{{ route('tickets.edit', $ticket) }}"
                                class="font-semibold text-xefi-ink underline decoration-xefi-red decoration-2 underline-offset-4 hover:text-xefi-red"
                            >
                                {{ $ticket->title }}
                            </a>
                        </td>
                        <td class="px-4 py-3">
                            <span @class([
                                'inline-flex rounded-sm px-2 py-0.5 text-xs font-bold uppercase tracking-wide',
                                'bg-xefi-green/15 text-xefi-green' => $ticket->status === TicketStatus::Resolved,
                                'bg-xefi-ink text-white' => $ticket->status === TicketStatus::InProgress,
                                'bg-xefi-mist text-xefi-steel' => ! in_array(
                                    $ticket->status,
                                    [TicketStatus::Resolved, TicketStatus::InProgress],
                                    true,
                                ),
                            ])>
                                {{ __($ticket->status->translationKey()) }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <span @class([
                                'inline-flex rounded-sm px-2 py-0.5 text-xs font-bold uppercase tracking-wide',
                                'bg-xefi-amber/25 text-xefi-graphite' => $ticket->priority === TicketPriority::High,
                                'bg-xefi-red text-white' => $ticket->priority === TicketPriority::Critical,
                                'bg-xefi-mist text-xefi-steel' => ! in_array(
                                    $ticket->priority,
                                    [TicketPriority::High, TicketPriority::Critical],
                                    true,
                                ),
                            ])>
                                {{ __($ticket->priority->translationKey()) }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-xefi-steel">{{ $ticket->created_at?->format('d/m/Y') }}</td>
                        @if ($reachesOthersTickets)
                            <td class="px-4 py-3 text-xefi-graphite">{{ $ticket->requester->name }}</td>
                        @endif
                        <td class="px-4 py-3 text-xefi-graphite">
                            {{ $ticket->assignedTechnician?->name ?? __('tickets::list.unassigned') }}
                        </td>
                        <td class="px-4 py-3 text-xefi-steel">{{ $ticket->comments_count }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ $reachesOthersTickets ? 7 : 6 }}" class="px-4 py-12 text-center text-sm text-xefi-steel">
                            {{ __('tickets::list.empty') }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-6">
        {{ $tickets->links() }}
    </div>
</div>
