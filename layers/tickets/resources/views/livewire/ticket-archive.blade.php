<div>
    <div>
        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-xefi-red">XEFI Support</p>
        <h1 class="mt-1 text-2xl font-bold tracking-tight text-xefi-ink">{{ __('tickets::archive.heading') }}</h1>
        <p class="mt-1 text-sm text-xefi-steel">{{ __('tickets::archive.subheading') }}</p>
    </div>

    <div class="mt-6 overflow-x-auto border border-xefi-line bg-white shadow-sm">
        <table class="w-full text-left text-sm">
            <thead class="border-b-2 border-xefi-ink bg-white text-xs uppercase tracking-wide text-xefi-graphite">
                <tr>
                    @foreach (['title', 'priority', 'created_at', 'resolved_at'] as $column)
                        <th class="px-4 py-3 font-bold" wire:key="head-{{ $column }}">
                            <button type="button" wire:click="sortBy('{{ $column }}')" class="hover:text-xefi-red">
                                {{ __("tickets::archive.columns.{$column}") }}
                                @if ($sortColumn === $column)
                                    <span aria-hidden="true" class="text-xefi-red">
                                        {{ $sortDirection === 'asc' ? '↑' : '↓' }}
                                    </span>
                                @endif
                            </button>
                        </th>
                    @endforeach
                    @if ($reachesOthersTickets)
                        <th class="px-4 py-3 font-bold">{{ __('tickets::archive.columns.requester') }}</th>
                    @endif
                    <th class="px-4 py-3 font-bold">{{ __('tickets::archive.columns.technician') }}</th>
                    @if ($reachesOthersTickets)
                        <th class="px-4 py-3 font-bold">{{ __('tickets::archive.columns.on_time') }}</th>
                    @endif
                </tr>
            </thead>
            <tbody class="divide-y divide-xefi-line">
                @forelse ($tickets as $ticket)
                    <tr wire:key="archived-{{ $ticket->id }}" class="hover:bg-xefi-mist">
                        <td class="px-4 py-3">
                            <a
                                href="{{ route('tickets.edit', $ticket) }}"
                                class="font-semibold text-xefi-ink underline decoration-xefi-red decoration-2 underline-offset-4 hover:text-xefi-red"
                            >
                                {{ $ticket->title }}
                            </a>
                        </td>
                        <td class="px-4 py-3 text-xefi-steel">{{ __($ticket->priority->translationKey()) }}</td>
                        <td class="px-4 py-3 text-xefi-steel">{{ $ticket->created_at?->format('d/m/Y') }}</td>
                        <td class="px-4 py-3 text-xefi-steel">
                            {{ $ticket->resolved_at?->format('d/m/Y') ?? __('tickets::archive.never_resolved') }}
                        </td>
                        @if ($reachesOthersTickets)
                            <td class="px-4 py-3 text-xefi-graphite">{{ $ticket->requester->name }}</td>
                        @endif
                        <td class="px-4 py-3 text-xefi-graphite">
                            {{ $ticket->assignedTechnician?->name ?? __('tickets::archive.unassigned') }}
                        </td>
                        @if ($reachesOthersTickets)
                            <td class="px-4 py-3">
                                <span @class([
                                    'inline-flex rounded-sm px-2 py-0.5 text-xs font-bold uppercase tracking-wide',
                                    'bg-xefi-green/15 text-xefi-green' => $ticket->is_resolved_on_time === true,
                                    'bg-xefi-red text-white' => $ticket->is_resolved_on_time === false,
                                    'bg-xefi-mist text-xefi-steel' => $ticket->is_resolved_on_time === null,
                                ])>
                                    @if ($ticket->is_resolved_on_time === null)
                                        {{ __('tickets::archive.on_time.unknown') }}
                                    @elseif ($ticket->is_resolved_on_time)
                                        {{ __('tickets::archive.on_time.met') }}
                                    @else
                                        {{ __('tickets::archive.on_time.missed') }}
                                    @endif
                                </span>
                            </td>
                        @endif
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ $reachesOthersTickets ? 7 : 5 }}" class="px-4 py-12 text-center text-sm text-xefi-steel">
                            {{ __('tickets::archive.empty') }}
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
