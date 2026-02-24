@if ($paginator->hasPages())
    <div class="w-full flex justify-center md:justify-end">

        <nav
            class="flex items-center gap-2 bg-white/70 backdrop-blur 
                    px-3 py-2 rounded-2xl shadow-sm border border-gray-200">

            {{-- Previous --}}
            @if ($paginator->onFirstPage())
                <span
                    class="w-9 h-9 flex items-center justify-center 
                            rounded-xl bg-gray-100 text-gray-400 cursor-not-allowed">
                    ‹
                </span>
            @else
                <button wire:click="previousPage" wire:loading.attr="disabled"
                    class="w-9 h-9 flex items-center justify-center 
                           rounded-xl bg-white border border-gray-200
                           hover:bg-blue-50 hover:border-blue-300 
                           transition-all duration-200">
                    ‹
                </button>
            @endif


            {{-- Pages --}}
            @foreach ($elements as $element)
                {{-- Dots --}}
                @if (is_string($element))
                    <span class="px-2 text-gray-400 text-sm">
                        {{ $element }}
                    </span>
                @endif

                {{-- Numbers --}}
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span
                                class="w-9 h-9 flex items-center justify-center
                                       rounded-xl bg-gradient-to-r from-blue-600 to-indigo-600
                                       text-white text-sm font-semibold shadow-md">
                                {{ $page }}
                            </span>
                        @else
                            <button wire:click="gotoPage({{ $page }})" wire:loading.attr="disabled"
                                class="w-9 h-9 flex items-center justify-center
                                       rounded-xl text-sm
                                       bg-white border border-gray-200
                                       hover:bg-blue-50 hover:border-blue-300
                                       hover:scale-105
                                       transition-all duration-200">
                                {{ $page }}
                            </button>
                        @endif
                    @endforeach
                @endif
            @endforeach


            {{-- Next --}}
            @if ($paginator->hasMorePages())
                <button wire:click="nextPage" wire:loading.attr="disabled"
                    class="w-9 h-9 flex items-center justify-center 
                           rounded-xl bg-white border border-gray-200
                           hover:bg-blue-50 hover:border-blue-300 
                           transition-all duration-200">
                    ›
                </button>
            @else
                <span
                    class="w-9 h-9 flex items-center justify-center 
                            rounded-xl bg-gray-100 text-gray-400 cursor-not-allowed">
                    ›
                </span>
            @endif

        </nav>
    </div>
@endif
