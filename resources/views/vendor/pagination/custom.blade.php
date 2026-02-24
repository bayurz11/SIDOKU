@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Pagination Navigation"
        class="flex items-center justify-center md:justify-end gap-1">

        {{-- Previous --}}
        @if ($paginator->onFirstPage())
            <span class="px-3 py-1.5 text-sm rounded-lg bg-gray-100 text-gray-400 cursor-not-allowed select-none">
                ‹
            </span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}"
                class="px-3 py-1.5 text-sm rounded-lg bg-white border border-gray-300 hover:bg-blue-50 hover:border-blue-400 transition">
                ‹
            </a>
        @endif


        {{-- Page Numbers --}}
        @foreach ($elements as $element)
            {{-- "..." Separator --}}
            @if (is_string($element))
                <span class="px-3 py-1.5 text-sm text-gray-400">
                    {{ $element }}
                </span>
            @endif

            {{-- Array Of Links --}}
            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <span class="px-3 py-1.5 text-sm rounded-lg bg-blue-600 text-white shadow font-medium">
                            {{ $page }}
                        </span>
                    @else
                        <a href="{{ $url }}"
                            class="px-3 py-1.5 text-sm rounded-lg bg-white border border-gray-300 hover:bg-blue-50 hover:border-blue-400 transition">
                            {{ $page }}
                        </a>
                    @endif
                @endforeach
            @endif
        @endforeach


        {{-- Next --}}
        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}"
                class="px-3 py-1.5 text-sm rounded-lg bg-white border border-gray-300 hover:bg-blue-50 hover:border-blue-400 transition">
                ›
            </a>
        @else
            <span class="px-3 py-1.5 text-sm rounded-lg bg-gray-100 text-gray-400 cursor-not-allowed select-none">
                ›
            </span>
        @endif

    </nav>
@endif
