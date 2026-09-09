<div class="space-y-4 border border-xefi-line bg-white p-8 shadow-sm">
    <h2 class="text-xs font-semibold uppercase tracking-wide text-xefi-graphite">
        {{ __('tickets::transition.heading') }}
    </h2>

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

    <div class="flex flex-wrap gap-2">
        @forelse ($destinations as $destination)
            <button
                type="button"
                wire:key="destination-{{ $destination->value }}"
                wire:click="moveTo('{{ $destination->value }}')"
                class="rounded-sm border border-xefi-line px-3 py-1.5 text-sm font-medium text-xefi-graphite hover:border-xefi-red hover:bg-xefi-red-tint hover:text-xefi-red"
            >
                {{ __($destination->status()->translationKey()) }}
            </button>
        @empty
            <p class="text-sm text-xefi-steel">{{ __('tickets::transition.empty') }}</p>
        @endforelse
    </div>
</div>
