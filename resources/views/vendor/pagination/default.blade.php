@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Pagination Navigation" class="flex items-center space-x-1">

        {{-- Previous --}}
        @if ($paginator->onFirstPage())
            <span class="px-3 py-1 bg-gray-200 text-gray-400 rounded-lg cursor-not-allowed">
                ‹
            </span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}"
                class="px-3 py-1 bg-white border border-gray-300 rounded-lg hover:bg-blue-50 transition">
                ‹
            </a>
        @endif

        {{-- Pagination Elements --}}
        @foreach ($elements as $element)
            @if (is_string($element))
                <span class="px-3 py-1 text-gray-400">{{ $element }}</span>
            @endif

            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <span class="px-3 py-1 bg-blue-600 text-white rounded-lg shadow">
                            {{ $page }}
                        </span>
                    @else
                        <a href="{{ $url }}"
                            class="px-3 py-1 bg-white border border-gray-300 rounded-lg hover:bg-blue-50 transition">
                            {{ $page }}
                        </a>
                    @endif
                @endforeach
            @endif
        @endforeach

        {{-- Next --}}
        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}"
                class="px-3 py-1 bg-white border border-gray-300 rounded-lg hover:bg-blue-50 transition">
                ›
            </a>
        @else
            <span class="px-3 py-1 bg-gray-200 text-gray-400 rounded-lg cursor-not-allowed">
                ›
            </span>
        @endif
    </nav>
@endif
