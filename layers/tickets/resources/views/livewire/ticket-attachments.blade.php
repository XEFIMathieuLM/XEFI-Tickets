<div>
    <h2>{{ __('tickets::attachments.heading') }}</h2>

    @if ($successMessage !== null)
        <p role="status">{{ $successMessage }}</p>
    @endif

    <form wire:submit="attach">
        <label for="upload">{{ __('tickets::attachments.labels.upload') }}</label>
        <input id="upload" type="file" wire:model="upload">
        @error('upload')
            <span role="alert">{{ $message }}</span>
        @enderror

        <p>{{ __('tickets::attachments.hints.max_size', ['size' => \Tickets\Models\Attachment::MAX_SIZE_IN_KILOBYTES]) }}</p>

        <button type="submit">{{ __('tickets::attachments.actions.attach') }}</button>
    </form>

    <ul>
        @forelse ($attachments as $attachment)
            <li wire:key="attachment-{{ $attachment->id }}">
                <span>{{ $attachment->original_name }}</span>
                <span>{{ $attachment->uploadedBy->name }}</span>

                <button
                    type="button"
                    wire:click="remove({{ $attachment->id }})"
                    aria-label="{{ __('tickets::attachments.actions.remove', ['name' => $attachment->original_name]) }}"
                >
                    &times;
                </button>
            </li>
        @empty
            <li>{{ __('tickets::attachments.empty') }}</li>
        @endforelse
    </ul>
</div>
