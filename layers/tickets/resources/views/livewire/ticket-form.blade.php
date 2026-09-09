@use('Tickets\Enums\TicketPermission')

<div class="mx-auto max-w-3xl space-y-6">
    <div>
        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-xefi-red">XEFI Support</p>
        <h1 class="mt-1 text-2xl font-bold tracking-tight text-xefi-ink">
            {{ $ticket === null ? __('tickets::form.heading.create') : __('tickets::form.heading.edit') }}
        </h1>
    </div>

    @if ($successMessage !== null)
        <p role="status" class="border-l-4 border-xefi-green bg-xefi-green/10 px-4 py-3 text-sm font-medium text-xefi-graphite">
            {{ $successMessage }}
        </p>
    @endif

    @error('transition')
        <p role="alert" class="border-l-4 border-xefi-red bg-xefi-red-tint px-4 py-3 text-sm font-medium text-xefi-red">
            {{ $message }}
        </p>
    @enderror

    <form wire:submit="save" class="space-y-5 border-t-4 border-xefi-red bg-white p-8 shadow-sm">
        <div>
            <label for="title" class="block text-xs font-semibold uppercase tracking-wide text-xefi-graphite">
                {{ __('tickets::form.labels.title') }}
            </label>
            <input
                id="title"
                type="text"
                wire:model="title"
                class="mt-2 w-full rounded-sm border border-xefi-line bg-white px-3 py-2.5 text-sm text-xefi-ink focus:border-xefi-red focus:outline-none"
            >
            @error('title')
                <p role="alert" class="mt-1.5 text-sm font-medium text-xefi-red">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="description" class="block text-xs font-semibold uppercase tracking-wide text-xefi-graphite">
                {{ __('tickets::form.labels.description') }}
            </label>
            <textarea
                id="description"
                rows="5"
                wire:model="description"
                class="mt-2 w-full rounded-sm border border-xefi-line bg-white px-3 py-2.5 text-sm text-xefi-ink focus:border-xefi-red focus:outline-none"
            ></textarea>
            @error('description')
                <p role="alert" class="mt-1.5 text-sm font-medium text-xefi-red">{{ $message }}</p>
            @enderror
        </div>

        @if ($maySetPriority)
            <div>
                <label for="priority" class="block text-xs font-semibold uppercase tracking-wide text-xefi-graphite">
                    {{ __('tickets::form.labels.priority') }}
                </label>
                <select
                    id="priority"
                    wire:model="priority"
                    class="mt-2 w-full rounded-sm border border-xefi-line bg-white px-3 py-2.5 text-sm text-xefi-ink focus:border-xefi-red focus:outline-none"
                >
                    <option value=""></option>
                    @foreach ($priorities as $availablePriority)
                        <option value="{{ $availablePriority->value }}" wire:key="priority-{{ $availablePriority->value }}">
                            {{ __($availablePriority->translationKey()) }}
                        </option>
                    @endforeach
                </select>
                @error('priority')
                    <p role="alert" class="mt-1.5 text-sm font-medium text-xefi-red">{{ $message }}</p>
                @enderror
            </div>
        @elseif ($ticket !== null)
            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-xefi-graphite">
                    {{ __('tickets::form.labels.priority') }}
                </p>
                <p class="mt-2">
                    <span class="rounded-sm bg-xefi-mist px-2 py-0.5 text-xs font-bold uppercase tracking-wide text-xefi-steel">
                        {{ __($ticket->priority->translationKey()) }}
                    </span>
                </p>
            </div>
        @endif

        @if ($ticket === null)
            <div>
                <label for="upload" class="block text-xs font-semibold uppercase tracking-wide text-xefi-graphite">
                    {{ __('tickets::attachments.labels.upload') }}
                </label>
                <input
                    id="upload"
                    type="file"
                    wire:model="upload"
                    class="mt-2 block w-full text-sm text-xefi-graphite file:mr-4 file:rounded-sm file:border-0 file:bg-xefi-ink file:px-3 file:py-2 file:text-sm file:font-bold file:uppercase file:tracking-wide file:text-white hover:file:bg-xefi-red"
                >
                @error('upload')
                    <p role="alert" class="mt-1.5 text-sm font-medium text-xefi-red">{{ $message }}</p>
                @enderror
                <p class="mt-1.5 text-xs text-xefi-steel">
                    {{ __('tickets::attachments.hints.optional_at_opening', ['size' => $maxSizeInKilobytes]) }}
                </p>
            </div>
        @endif

        <button
            type="submit"
            class="rounded-sm bg-xefi-red px-5 py-2.5 text-sm font-bold uppercase tracking-wide text-white hover:bg-xefi-red-dark"
        >
            {{ __('tickets::form.actions.save') }}
        </button>
    </form>

    @if ($ticket !== null)
        <section class="space-y-5 border border-xefi-line bg-white p-8 shadow-sm">
            <p class="flex items-center gap-2 text-xs font-semibold uppercase tracking-wide text-xefi-steel">
                {{ __('tickets::form.labels.current_status') }}
                <span class="rounded-sm bg-xefi-ink px-2 py-0.5 text-xs font-bold uppercase tracking-wide text-white">
                    {{ __($ticket->status->translationKey()) }}
                </span>
            </p>

            @can(TicketPermission::Assign->value)
                <div>
                    <h2 class="text-xs font-semibold uppercase tracking-wide text-xefi-graphite">
                        {{ __('tickets::form.labels.assign_to') }}
                    </h2>

                    <div class="mt-3 flex flex-wrap gap-2">
                        @forelse ($technicians as $technician)
                            <button
                                type="button"
                                wire:key="technician-{{ $technician->id }}"
                                wire:click="assign({{ $technician->id }})"
                                aria-label="{{ __('tickets::form.actions.assign', ['name' => $technician->name]) }}"
                                class="rounded-sm border border-xefi-line px-3 py-1.5 text-sm font-medium text-xefi-graphite hover:border-xefi-red hover:bg-xefi-red-tint hover:text-xefi-red"
                            >
                                {{ $technician->name }}
                            </button>
                        @empty
                            <p class="text-sm text-xefi-steel">{{ __('tickets::form.empty.technicians') }}</p>
                        @endforelse
                    </div>
                </div>
            @endcan
        </section>

        <livewire:tickets.ticket-transitions :ticket="$ticket" />

        <livewire:tickets.ticket-attachments :ticket="$ticket" />
    @endif
</div>
