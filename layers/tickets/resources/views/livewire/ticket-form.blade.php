<div>
    <h1>{{ $ticket === null ? __('tickets::form.heading.create') : __('tickets::form.heading.edit') }}</h1>

    @if ($successMessage !== null)
        <p role="status">{{ $successMessage }}</p>
    @endif

    @error('transition')
        <p role="alert">{{ $message }}</p>
    @enderror

    <form wire:submit="save">
        <label for="title">{{ __('tickets::form.labels.title') }}</label>
        <input id="title" type="text" wire:model="title">
        @error('title')
            <span role="alert">{{ $message }}</span>
        @enderror

        <label for="description">{{ __('tickets::form.labels.description') }}</label>
        <textarea id="description" wire:model="description"></textarea>
        @error('description')
            <span role="alert">{{ $message }}</span>
        @enderror

        <label for="priority">{{ __('tickets::form.labels.priority') }}</label>
        <select id="priority" wire:model="priority">
            <option value=""></option>
            @foreach ($priorities as $availablePriority)
                <option value="{{ $availablePriority->value }}" wire:key="priority-{{ $availablePriority->value }}">
                    {{ __('tickets::priority.'.$availablePriority->value) }}
                </option>
            @endforeach
        </select>
        @error('priority')
            <span role="alert">{{ $message }}</span>
        @enderror

        <button type="submit">{{ __('tickets::form.actions.save') }}</button>
    </form>

    @if ($ticket !== null)
        <section>
            <p>{{ __('tickets::form.labels.current_status') }} : {{ __('tickets::status.'.$ticket->status->value) }}</p>

            <h2>{{ __('tickets::form.labels.assign_to') }}</h2>

            @forelse ($technicians as $technician)
                <button
                    type="button"
                    wire:key="technician-{{ $technician->id }}"
                    wire:click="assign({{ $technician->id }})"
                    aria-label="{{ __('tickets::form.actions.assign', ['name' => $technician->name]) }}"
                >
                    {{ $technician->name }}
                </button>
            @empty
                <p>{{ __('tickets::form.empty.technicians') }}</p>
            @endforelse
        </section>

        <livewire:tickets.ticket-attachments :ticket="$ticket" :key="'attachments-'.$ticket->id" />
    @endif
</div>
