@props(['tone' => 'light'])

<span {{ $attributes->merge(['class' => 'inline-flex items-center gap-2.5']) }}>
    <span aria-hidden="true" class="block h-5 w-1.5 bg-xefi-red"></span>
    <span class="text-lg font-black uppercase tracking-[0.2em] {{ $tone === 'light' ? 'text-white' : 'text-xefi-ink' }}">
        Xefi
    </span>
</span>
