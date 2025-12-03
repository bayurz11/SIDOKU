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
                                    Detail Kadar Air Produk
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
                <div class="px-5 py-4 sm:px-6 sm:py-5 space-y-4 text-xs">
                    @php
                        $lineLabel =
                            \App\Domains\Ipc\Models\IpcProductCheck::LINE_GROUPS[$ipc->line_group] ?? $ipc->line_group;
                        $subLineLabel = $ipc->sub_line
                            ? \App\Domains\Ipc\Models\IpcProductCheck::SUB_LINES_TEH[$ipc->sub_line] ?? $ipc->sub_line
                            : null;

                        $shiftLabel = $ipc->shift ? 'Shift ' . $ipc->shift : '-';

                        $lineClass = match ($ipc->line_group) {
                            'LINE_TEH' => 'bg-green-50 text-green-700 border-green-100',
                            'LINE_POWDER' => 'bg-amber-50 text-amber-700 border-amber-100',
                            default => 'bg-blue-50 text-blue-700 border-blue-100',
                        };
                    @endphp

                    {{-- Judul + badge --}}
                    <div class="space-y-1">
                        <p class="text-sm font-semibold text-gray-900">
                            {{ $ipc->product_name }}
                        </p>

                        {{-- BADGES --}}
                        <div class="flex flex-wrap items-center gap-1.5">

                            {{-- Line Group --}}
                            <span
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-semibold border {{ $lineClass }}">
                                {{ $lineLabel }}
                            </span>

                            {{-- Sub Line (khusus LINE_TEH) --}}
                            @if ($subLineLabel)
                                <span
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-semibold bg-purple-50 text-purple-700 border border-purple-100">
                                    {{ $subLineLabel }}
                                </span>
                            @endif

                            {{-- Shift --}}
                            <span
                                class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-gray-50 text-gray-700 border border-gray-200">
                                {{ $shiftLabel }}
                            </span>

                            {{-- Tanggal Uji --}}
                            <span
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-semibold bg-sky-50 text-sky-700 border border-sky-100">
                                {{ optional($ipc->test_date)->format('d M Y') ?? '-' }}
                            </span>

                            {{-- Status moisture "ada / tidak" --}}
                            <span
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-semibold
                                       border {{ $ipc->avg_moisture_percent !== null ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-gray-50 text-gray-500 border-gray-200' }}">
                                <span class="w-1.5 h-1.5 rounded-full bg-current mr-1.5 opacity-70"></span>
                                {{ $ipc->avg_moisture_percent !== null ? 'Moisture terisi' : 'Belum ada nilai moisture' }}
                            </span>
                        </div>
                    </div>

                    {{-- Info grid --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        {{-- Kolom kiri: Informasi Umum --}}
                        <div class="space-y-1.5">
                            <h3 class="text-[11px] font-semibold text-gray-500 uppercase">Informasi Umum</h3>
                            <dl class="space-y-1">
                                <div class="flex justify-between">
                                    <dt class="text-gray-500">Produk</dt>
                                    <dd class="text-gray-900 font-medium text-right">
                                        {{ $ipc->product_name }}
                                    </dd>
                                </div>

                                <div class="flex justify-between">
                                    <dt class="text-gray-500">Line Group</dt>
                                    <dd class="text-gray-900 font-medium text-right">
                                        {{ $lineLabel }}
                                    </dd>
                                </div>

                                <div class="flex justify-between">
                                    <dt class="text-gray-500">Sub Line</dt>
                                    <dd class="text-gray-900 font-medium text-right">
                                        {{ $subLineLabel ?? '-' }}
                                    </dd>
                                </div>

                                <div class="flex justify-between">
                                    <dt class="text-gray-500">Shift</dt>
                                    <dd class="text-gray-900 font-medium text-right">
                                        {{ $ipc->shift ?? '-' }}
                                    </dd>
                                </div>

                                <div class="flex justify-between">
                                    <dt class="text-gray-500">Tanggal Uji</dt>
                                    <dd class="text-gray-900 font-medium text-right">
                                        {{ optional($ipc->test_date)->format('d M Y') ?? '-' }}
                                    </dd>
                                </div>
                            </dl>
                        </div>

                        {{-- Kolom kanan: Hasil Ringkas --}}
                        <div class="space-y-1.5">
                            <h3 class="text-[11px] font-semibold text-gray-500 uppercase">Hasil Ringkas</h3>
                            <dl class="space-y-1">
                                <div class="flex justify-between">
                                    <dt class="text-gray-500">Moisture (%)</dt>
                                    <dd class="text-gray-900 font-medium text-right">
                                        {{ $ipc->avg_moisture_percent !== null ? number_format($ipc->avg_moisture_percent, 2) : '-' }}
                                    </dd>
                                </div>

                                <div class="flex justify-between">
                                    <dt class="text-gray-500">Berat (g)</dt>
                                    <dd class="text-gray-900 font-medium text-right">
                                        {{ $ipc->avg_weight_g !== null ? number_format($ipc->avg_weight_g, 3) : '-' }}
                                    </dd>
                                </div>

                                <div class="flex justify-between">
                                    <dt class="text-gray-500">Dibuat oleh</dt>
                                    <dd class="text-gray-900 font-medium text-right">
                                        @if (method_exists($ipc, 'createdBy') && $ipc->createdBy)
                                            {{ $ipc->createdBy->name }}
                                        @else
                                            {{ $ipc->created_by ?? '-' }}
                                        @endif
                                    </dd>
                                </div>

                                <div class="flex justify-between">
                                    <dt class="text-gray-500">Tanggal Input</dt>
                                    <dd class="text-gray-900 font-medium text-right">
                                        {{ optional($ipc->created_at)->format('d M Y H:i') ?? '-' }}
                                    </dd>
                                </div>
                            </dl>
                        </div>
                    </div>

                    {{-- Detail Perhitungan Moisture --}}
                    <div class="space-y-1.5">
                        <h3 class="text-[11px] font-semibold text-gray-500 uppercase">
                            Detail Perhitungan Kadar Air (Moisture)
                        </h3>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-[11px]">
                            <div class="rounded-xl border border-gray-100 bg-gray-50 px-3 py-2.5 space-y-1">
                                <div class="flex justify-between">
                                    <span class="text-gray-500">Berat Cawan (g)</span>
                                    <span class="font-semibold text-gray-900">
                                        {{ $ipc->cup_weight !== null ? number_format($ipc->cup_weight, 3) : '-' }}
                                    </span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-500">Berat Produk (g)</span>
                                    <span class="font-semibold text-gray-900">
                                        {{ $ipc->product_weight !== null ? number_format($ipc->product_weight, 3) : '-' }}
                                    </span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-500">Total Cawan + Produk (g)</span>
                                    <span class="font-semibold text-gray-900">
                                        {{ $ipc->total_cup_plus_product !== null ? number_format($ipc->total_cup_plus_product, 3) : '-' }}
                                    </span>
                                </div>
                            </div>

                            <div class="rounded-xl border border-gray-100 bg-gray-50 px-3 py-2.5 space-y-1">
                                <div class="flex justify-between">
                                    <span class="text-gray-500">Penimbangan 1 (P1)</span>
                                    <span class="font-semibold text-gray-900">
                                        {{ $ipc->weighing_1 !== null ? number_format($ipc->weighing_1, 3) : '-' }}
                                    </span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-500">Penimbangan 2 (P2)</span>
                                    <span class="font-semibold text-gray-900">
                                        {{ $ipc->weighing_2 !== null ? number_format($ipc->weighing_2, 3) : '-' }}
                                    </span>
                                </div>
                                @php
                                    $avgWeighing =
                                        $ipc->weighing_1 !== null && $ipc->weighing_2 !== null
                                            ? ($ipc->weighing_1 + $ipc->weighing_2) / 2
                                            : null;
                                @endphp

                            </div>
                        </div>

                        <p class="text-[10px] text-gray-500">
                            Rumus: <span class="font-mono">(Berat Cawan + Berat Produk − (P1 + P2) / 2) ÷ Berat Produk
                                ×
                                100</span>
                        </p>
                    </div>

                    {{-- Catatan --}}
                    <div class="space-y-1.5">
                        <h3 class="text-[11px] font-semibold text-gray-500 uppercase">Catatan</h3>
                        <div class="rounded-xl border border-gray-100 bg-gray-50 px-3 py-2.5 text-[11px] text-gray-700">
                            {{ $ipc->notes ?: 'Tidak ada catatan tambahan.' }}
                        </div>
                    </div>
                </div>

                {{-- FOOTER BUTTONS --}}
                <div class="px-5 py-3 sm:px-6 sm:py-4 border-t border-gray-100 bg-gray-50/60">
                    <div class="flex flex-col-reverse sm:flex-row sm:justify-end sm:space-x-3 gap-2">

                        {{-- BUTTON TUTUP --}}
                        <button wire:click="closeModal" type="button"
                            class="inline-flex items-center justify-center px-4 py-2 rounded-xl text-xs font-medium
                                       text-gray-700 bg-gray-100 border border-gray-300
                                       hover:bg-gray-200 transition-colors">
                            Tutup
                        </button>

                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
