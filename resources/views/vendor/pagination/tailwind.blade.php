@if ($paginator->hasPages())
    <nav role="navigation" aria-label="{{ __('Pagination Navigation') }}" class="flex flex-wrap items-center justify-between gap-4">
        <p class="text-xs text-xefi-steel">
            {!! __('Showing') !!}
            @if ($paginator->firstItem())
                <span class="font-bold text-xefi-ink">{{ $paginator->firstItem() }}</span>
                {!! __('to') !!}
                <span class="font-bold text-xefi-ink">{{ $paginator->lastItem() }}</span>
            @else
                {{ $paginator->count() }}
            @endif
            {!! __('of') !!}
            <span class="font-bold text-xefi-ink">{{ $paginator->total() }}</span>
            {!! __('results') !!}
        </p>

        <span class="inline-flex rtl:flex-row-reverse">
            @if ($paginator->onFirstPage())
                <span aria-disabled="true" aria-label="{{ __('pagination.previous') }}"
                      class="cursor-not-allowed border border-xefi-line bg-white px-3 py-2 text-sm font-medium text-xefi-line">
                    &lsaquo;
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="{{ __('pagination.previous') }}"
                   class="border border-xefi-line bg-white px-3 py-2 text-sm font-medium text-xefi-graphite hover:border-xefi-red hover:text-xefi-red">
                    &lsaquo;
                </a>
            @endif

            @foreach ($elements as $element)
                @if (is_string($element))
                    <span aria-disabled="true"
                          class="-ml-px cursor-default border border-xefi-line bg-white px-3 py-2 text-sm text-xefi-steel">
                        {{ $element }}
                    </span>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span aria-current="page"
                                  class="-ml-px cursor-default border border-xefi-red bg-xefi-red px-3.5 py-2 text-sm font-bold text-white">
                                {{ $page }}
                            </span>
                        @else
                            <a href="{{ $url }}" aria-label="{{ __('Go to page :page', ['page' => $page]) }}"
                               class="-ml-px border border-xefi-line bg-white px-3.5 py-2 text-sm font-medium text-xefi-graphite hover:border-xefi-red hover:text-xefi-red">
                                {{ $page }}
                            </a>
                        @endif
                    @endforeach
                @endif
            @endforeach

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="{{ __('pagination.next') }}"
                   class="-ml-px border border-xefi-line bg-white px-3 py-2 text-sm font-medium text-xefi-graphite hover:border-xefi-red hover:text-xefi-red">
                    &rsaquo;
                </a>
            @else
                <span aria-disabled="true" aria-label="{{ __('pagination.next') }}"
                      class="-ml-px cursor-not-allowed border border-xefi-line bg-white px-3 py-2 text-sm font-medium text-xefi-line">
                    &rsaquo;
                </span>
            @endif
        </span>
    </nav>
@endif
