<div class="space-y-5 border border-xefi-line bg-white p-8 shadow-sm">
    <h2 class="text-xs font-semibold uppercase tracking-wide text-xefi-graphite">
        {{ __('tickets::attachments.heading') }}
    </h2>

    @if ($successMessage !== null)
        <p role="status" class="border-l-4 border-xefi-green bg-xefi-green/10 px-4 py-3 text-sm font-medium text-xefi-graphite">
            {{ $successMessage }}
        </p>
    @endif

    <form wire:submit="attach" class="space-y-3">
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
                {{ __('tickets::attachments.hints.max_size', ['size' => $maxSizeInKilobytes]) }}
            </p>
        </div>

        <button
            type="submit"
            class="rounded-sm border border-xefi-line px-3 py-1.5 text-sm font-medium text-xefi-graphite hover:border-xefi-red hover:bg-xefi-red-tint hover:text-xefi-red"
        >
            {{ __('tickets::attachments.actions.attach') }}
        </button>
    </form>

    <ul class="divide-y divide-xefi-line border-t border-xefi-line">
        @forelse ($attachments as $attachment)
            <li wire:key="attachment-{{ $attachment->id }}" class="flex items-center justify-between gap-4 py-3">
                <div class="min-w-0">
                    <p class="truncate text-sm font-medium text-xefi-ink">{{ $attachment->original_name }}</p>
                    <p class="text-xs text-xefi-steel">{{ $attachment->uploadedBy->name }}</p>
                </div>

                <button
                    type="button"
                    wire:click="remove({{ $attachment->id }})"
                    aria-label="{{ __('tickets::attachments.actions.remove', ['name' => $attachment->original_name]) }}"
                    class="shrink-0 rounded-sm px-2 py-1 text-sm text-xefi-steel hover:bg-xefi-red-tint hover:text-xefi-red"
                >
                    &times;
                </button>
            </li>
        @empty
            <li class="py-3 text-sm text-xefi-steel">{{ __('tickets::attachments.empty') }}</li>
        @endforelse
    </ul>
</div>
