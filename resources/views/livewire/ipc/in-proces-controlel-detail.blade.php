<div>
    @if ($showModal && $ipc)
        {{-- OVERLAY --}}
        <div class="fixed inset-0 z-50 bg-gray-600 bg-opacity-50 overflow-y-auto flex items-center justify-center bg-black/40 backdrop-blur-sm px-3 sm:px-4"
            wire:click.self="closeModal">

            {{-- WRAPPER KOTAK --}}
            <div
                class="relative w-full max-w-xl bg-white rounded-2xl shadow-2xl border border-gray-100
                       max-h-[90vh] overflow-y-auto transform transition-all duration-200 ease-out">

                {{-- HEADER --}}
                <div class="px-5 py-4 sm:px-6 sm:py-5 border-b border-gray-100">
                    <div class="flex items-start justify-between gap-3">
                        {{-- KIRI: ICON + TEKS --}}
                        <div class="flex items-start gap-3">
                            <div
                                class="w-11 h-11 rounded-2xl bg-gradient-to-br from-emerald-50 to-emerald-100
                                       flex items-center justify-center shadow-sm border border-emerald-100 flex-shrink-0">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke-width="1.5" stroke="currentColor" class="w-6 h-6 text-emerald-600">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="m8.99 14.993 6-6m6 3.001c0 1.268-.63 2.39-1.593 3.069a3.746 3.746 0 0 1-1.043 3.296 3.745 3.745 0 0 1-3.296 1.043 3.745 3.745 0 0 1-3.068 1.593c-1.268 0-2.39-.63-3.068-1.593a3.745 3.745 0 0 1-3.296-1.043 3.746 3.746 0 0 1-1.043-3.297 3.746 3.746 0 0 1-1.593-3.068c0-1.268.63-2.39 1.593-3.068a3.746 3.746 0 0 1 1.043-3.297 3.745 3.745 0 0 1 3.296-1.042 3.745 3.745 0 0 1 3.068-1.594c1.268 0 2.39.63 3.068 1.593a3.745 3.745 0 0 1 3.296 1.043 3.746 3.746 0 0 1 1.043 3.297 3.746 3.746 0 0 1 1.593 3.068ZM9.74 9.743h.008v.007H9.74v-.007Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm4.125 4.5h.008v.008h-.008v-.008Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
                                </svg>
                            </div>

                            <div class="space-y-0.5">
                                <h2 class="text-lg font-bold text-gray-900 leading-tight">
                                    Detail Produk
                                </h2>
                                <p class="text-xs text-gray-600">
                                    Informasi lengkap hasil pengecekan IPC
                                </p>
                            </div>
                        </div>

                        {{-- TOMBOL CLOSE --}}
                        <button type="button" wire:click="closeModal"
                            class="text-gray-400 hover:text-gray-600 transition-colors">
                            <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>

                {{-- BODY --}}
                <div class="p-6 space-y-4 text-sm">

                    {{-- IDENTITAS --}}
                    <div class="grid grid-cols-2 gap-3 text-xs">
                        <div>
                            <p class="text-gray-500">Nama Produk</p>
                            <p class="font-semibold">{{ $ipc->product_name }}</p>
                        </div>

                        <div>
                            <p class="text-gray-500">Line Group</p>
                            <p class="font-semibold">{{ $ipc->line_group }}</p>
                        </div>

                        <div>
                            <p class="text-gray-500">Sub Line</p>
                            <p class="font-semibold">{{ $ipc->sub_line ?? '-' }}</p>
                        </div>

                        <div>
                            <p class="text-gray-500">Shift</p>
                            <p class="font-semibold">{{ $ipc->shift ?? '-' }}</p>
                        </div>

                        <div>
                            <p class="text-gray-500">Tanggal Uji</p>
                            <p class="font-semibold">
                                {{ optional($ipc->test_date)->format('d M Y') }}
                            </p>
                        </div>

                        <div>
                            <p class="text-gray-500">Dibuat Oleh</p>
                            <p class="font-semibold">
                                {{ optional($ipc->creator)->name ?? '-' }}
                            </p>
                        </div>
                    </div>

                    {{-- PARAMETER --}}
                    <div class="border-t pt-4 grid grid-cols-2 gap-3 text-xs">

                        <div>
                            <p class="text-gray-500">Rata-rata Berat (g)</p>
                            <p class="font-semibold">{{ $ipc->avg_weight_g ?? '-' }}</p>
                        </div>

                        <div>
                            <p class="text-gray-500">Rata-rata pH</p>
                            <p class="font-semibold">{{ $ipc->avg_ph ?? '-' }}</p>
                        </div>

                        <div>
                            <p class="text-gray-500">Rata-rata Brix</p>
                            <p class="font-semibold">{{ $ipc->avg_brix ?? '-' }}</p>
                        </div>

                        <div>
                            <p class="text-gray-500">Rata-rata TDS (ppm)</p>
                            <p class="font-semibold">{{ $ipc->avg_tds_ppm ?? '-' }}</p>
                        </div>

                        <div>
                            <p class="text-gray-500">Klorin</p>
                            <p class="font-semibold">{{ $ipc->avg_chlorine ?? '-' }}</p>
                        </div>

                        <div>
                            <p class="text-gray-500">Ozon</p>
                            <p class="font-semibold">{{ $ipc->avg_ozone ?? '-' }}</p>
                        </div>

                        <div>
                            <p class="text-gray-500">Kekeruhan (NTU)</p>
                            <p class="font-semibold">{{ $ipc->avg_turbidity_ntu ?? '-' }}</p>
                        </div>

                        <div>
                            <p class="text-gray-500">Salinitas</p>
                            <p class="font-semibold">{{ $ipc->avg_salinity ?? '-' }}</p>
                        </div>
                    </div>

                    {{-- CATATAN --}}
                    <div class="border-t pt-4">
                        <p class="text-gray-500 text-xs mb-1">Catatan</p>
                        <div class="bg-gray-50 border rounded-lg p-3 text-xs">
                            {{ $ipc->notes ?: 'Tidak ada catatan.' }}
                        </div>
                    </div>

                </div>

                {{-- FOOTER --}}
                <div class="px-6 py-4 border-t flex justify-end bg-gray-50">
                    <button wire:click="closeModal" class="px-4 py-2 bg-gray-200 hover:bg-gray-300 rounded-lg text-sm">
                        Tutup
                    </button>
                </div>

            </div>
        </div>
    @endif
</div>
