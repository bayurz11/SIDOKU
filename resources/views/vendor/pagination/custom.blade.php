@if ($paginator->hasPages())
    <div class="w-full">
        <nav class="flex items-center justify-center md:justify-end">

            {{-- Wrapper supaya bisa scroll di mobile --}}
            <div class="flex items-center gap-1 overflow-x-auto scrollbar-hide py-1">

                {{-- Previous --}}
                @if ($paginator->onFirstPage())
                    <span class="min-w-[36px] text-center px-3 py-1.5 text-sm rounded-lg bg-gray-100 text-gray-400">
                        ‹
                    </span>
                @else
                    <button wire:click="previousPage" wire:loading.attr="disabled"
                        class="min-w-[36px] px-3 py-1.5 text-sm rounded-lg bg-white border border-gray-300 hover:bg-blue-50 transition">
                        ‹
                    </button>
                @endif


                {{-- Page Numbers --}}
                @foreach ($elements as $element)
                    {{-- Dots --}}
                    @if (is_string($element))
                        <span class="px-2 py-1.5 text-sm text-gray-400">
                            {{ $element }}
                        </span>
                    @endif

                    {{-- Pages --}}
                    @if (is_array($element))
                        @foreach ($element as $page => $url)
                            @if ($page == $paginator->currentPage())
                                <span
                                    class="min-w-[36px] text-center px-3 py-1.5 text-sm rounded-lg bg-blue-600 text-white shadow font-medium">
                                    {{ $page }}
                                </span>
                            @else
                                <button wire:click="gotoPage({{ $page }})" wire:loading.attr="disabled"
                                    class="min-w-[36px] px-3 py-1.5 text-sm rounded-lg bg-white border border-gray-300 hover:bg-blue-50 transition">
                                    {{ $page }}
                                </button>
                            @endif
                        @endforeach
                    @endif
                @endforeach


                {{-- Next --}}
                @if ($paginator->hasMorePages())
                    <button wire:click="nextPage" wire:loading.attr="disabled"
                        class="min-w-[36px] px-3 py-1.5 text-sm rounded-lg bg-white border border-gray-300 hover:bg-blue-50 transition">
                        ›
                    </button>
                @else
                    <span class="min-w-[36px] text-center px-3 py-1.5 text-sm rounded-lg bg-gray-100 text-gray-400">
                        ›
                    </span>
                @endif

            </div>

        </nav>
    </div>
@endif
