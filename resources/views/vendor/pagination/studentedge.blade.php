@if ($paginator->hasPages())
    <nav class="se-pagination" role="navigation" aria-label="{{ __('ui.pagination_navigation') }}">
        <p class="se-pagination-summary">
            {{ __('ui.pagination_showing') }}
            <strong>{{ $paginator->firstItem() }}</strong>
            {{ __('ui.pagination_to') }}
            <strong>{{ $paginator->lastItem() }}</strong>
            {{ __('ui.pagination_of') }}
            <strong>{{ $paginator->total() }}</strong>
        </p>

        <div class="se-pagination-controls">
            @if ($paginator->onFirstPage())
                <span class="se-page-nav is-disabled" aria-disabled="true">
                    <svg aria-hidden="true" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m12 15-5-5 5-5"/>
                    </svg>
                    <span>{{ __('ui.pagination_previous') }}</span>
                </span>
            @else
                <a class="se-page-nav" href="{{ $paginator->previousPageUrl() }}" rel="prev">
                    <svg aria-hidden="true" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m12 15-5-5 5-5"/>
                    </svg>
                    <span>{{ __('ui.pagination_previous') }}</span>
                </a>
            @endif

            <div class="se-pagination-pages" aria-label="{{ __('ui.pagination_page_selection') }}">
                @foreach ($elements as $element)
                    @if (is_string($element))
                        <span class="se-page-ellipsis" aria-hidden="true">{{ $element }}</span>
                    @endif

                    @if (is_array($element))
                        @foreach ($element as $page => $url)
                            @if ($page == $paginator->currentPage())
                                <span class="se-page-number is-current" aria-current="page">{{ $page }}</span>
                            @else
                                <a class="se-page-number" href="{{ $url }}" aria-label="{{ __('ui.pagination_go_to_page', ['page' => $page]) }}">{{ $page }}</a>
                            @endif
                        @endforeach
                    @endif
                @endforeach
            </div>

            <span class="se-pagination-mobile-page">
                {{ __('ui.pagination_page') }} <strong>{{ $paginator->currentPage() }}</strong> / {{ $paginator->lastPage() }}
            </span>

            @if ($paginator->hasMorePages())
                <a class="se-page-nav" href="{{ $paginator->nextPageUrl() }}" rel="next">
                    <span>{{ __('ui.pagination_next') }}</span>
                    <svg aria-hidden="true" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m8 5 5 5-5 5"/>
                    </svg>
                </a>
            @else
                <span class="se-page-nav is-disabled" aria-disabled="true">
                    <span>{{ __('ui.pagination_next') }}</span>
                    <svg aria-hidden="true" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m8 5 5 5-5 5"/>
                    </svg>
                </span>
            @endif
        </div>
    </nav>
@endif
