<div>
    @if ($showModal && $ipc)
        <div class="fixed inset-0 z-50 bg-black/40 backdrop-blur-sm flex items-center justify-center px-4"
            wire:click.self="closeModal">

            <div class="relative w-full max-w-xl bg-white rounded-2xl shadow-2xl border max-h-[90vh] overflow-y-auto">

                {{-- HEADER --}}
                <div class="px-6 py-4 border-b flex justify-between items-center">
                    <div>
                        <h2 class="text-lg font-bold text-gray-900">Detail IPC Product</h2>
                        <p class="text-xs text-gray-500">Informasi lengkap hasil pemeriksaan</p>
                    </div>
                    <button wire:click="closeModal" class="text-gray-400 hover:text-gray-600">
                        ✕
                    </button>
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
