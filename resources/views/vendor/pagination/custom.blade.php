@if ($paginator->hasPages())
    <nav class="flex items-center justify-center md:justify-end gap-1">

        {{-- Previous --}}
        @if ($paginator->onFirstPage())
            <span class="px-3 py-1.5 text-sm rounded-lg bg-gray-100 text-gray-400">
                ‹
            </span>
        @else
            <button wire:click="previousPage" wire:loading.attr="disabled"
                class="px-3 py-1.5 text-sm rounded-lg bg-white border border-gray-300 hover:bg-blue-50 transition">
                ‹
            </button>
        @endif


        {{-- Page Numbers --}}
        @foreach ($elements as $element)
            @if (is_string($element))
                <span class="px-3 py-1.5 text-sm text-gray-400">
                    {{ $element }}
                </span>
            @endif

            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <span class="px-3 py-1.5 text-sm rounded-lg bg-blue-600 text-white shadow font-medium">
                            {{ $page }}
                        </span>
                    @else
                        <button wire:click="gotoPage({{ $page }})" wire:loading.attr="disabled"
                            class="px-3 py-1.5 text-sm rounded-lg bg-white border border-gray-300 hover:bg-blue-50 transition">
                            {{ $page }}
                        </button>
                    @endif
                @endforeach
            @endif
        @endforeach


        {{-- Next --}}
        @if ($paginator->hasMorePages())
            <button wire:click="nextPage" wire:loading.attr="disabled"
                class="px-3 py-1.5 text-sm rounded-lg bg-white border border-gray-300 hover:bg-blue-50 transition">
                ›
            </button>
        @else
            <span class="px-3 py-1.5 text-sm rounded-lg bg-gray-100 text-gray-400">
                ›
            </span>
        @endif

    </nav>
@endif
