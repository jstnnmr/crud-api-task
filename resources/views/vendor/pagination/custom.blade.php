@if ($paginator->hasPages())
    <nav role="navigation" aria-label="{{ __('Pagination Navigation') }}" class="flex items-center justify-between mt-6">
        <div class="flex justify-between flex-1 sm:hidden">
            @if ($paginator->onFirstPage())
                <span class="btn btn-secondary btn-sm opacity-50 cursor-not-allowed">« Previous</span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" class="btn btn-secondary btn-sm">« Previous</a>
            @endif

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" class="btn btn-secondary btn-sm">Next »</a>
            @else
                <span class="btn btn-secondary btn-sm opacity-50 cursor-not-allowed">Next »</span>
            @endif
        </div>

        <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
            <div>
                <p class="text-xs" style="color: var(--text-muted);">
                    {!! __('Showing') !!}
                    <span class="font-semibold" style="color: var(--text);">{{ $paginator->firstItem() }}</span>
                    {!! __('to') !!}
                    <span class="font-semibold" style="color: var(--text);">{{ $paginator->lastItem() }}</span>
                    {!! __('of') !!}
                    <span class="font-semibold" style="color: var(--text);">{{ $paginator->total() }}</span>
                    {!! __('results') !!}
                </p>
            </div>

            <div>
                <span class="relative z-0 inline-flex rounded-md gap-1">
                    {{-- Previous Page Link --}}
                    @if ($paginator->onFirstPage())
                        <span aria-disabled="true" aria-label="{{ __('previous') }}">
                            <span class="btn btn-secondary btn-sm opacity-40 cursor-not-allowed" aria-hidden="true">&lsaquo;</span>
                        </span>
                    @else
                        <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="btn btn-secondary btn-sm" aria-label="{{ __('previous') }}">&lsaquo;</a>
                    @endif

                    {{-- Pagination Elements --}}
                    @foreach ($elements as $element)
                        {{-- "Three Dots" Separator --}}
                        @if (is_string($element))
                            <span class="btn btn-secondary btn-sm opacity-50 cursor-default" aria-disabled="true">{{ $element }}</span>
                        @endif

                        {{-- Array Of Links --}}
                        @if (is_array($element))
                            @foreach ($element as $page => $url)
                                @if ($page == $paginator->currentPage())
                                    <span aria-current="page">
                                        <span class="btn btn-primary btn-sm" style="min-width: 36px;">{{ $page }}</span>
                                    </span>
                                @else
                                    <a href="{{ $url }}" class="btn btn-secondary btn-sm" style="min-width: 36px;" aria-label="{{ __('Go to page :page', ['page' => $page]) }}">
                                        {{ $page }}
                                    </a>
                                @endif
                            @endforeach
                        @endif
                    @endforeach

                    {{-- Next Page Link --}}
                    @if ($paginator->hasMorePages())
                        <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="btn btn-secondary btn-sm" aria-label="{{ __('next') }}">&rsaquo;</a>
                    @else
                        <span aria-disabled="true" aria-label="{{ __('next') }}">
                            <span class="btn btn-secondary btn-sm opacity-40 cursor-not-allowed" aria-hidden="true">&rsaquo;</span>
                        </span>
                    @endif
                </span>
            </div>
        </div>
    </nav>
@endif
