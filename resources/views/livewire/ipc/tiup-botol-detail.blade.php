@php
    use App\Domains\Ipc\Models\TiupBotolCheck;
    use Illuminate\Support\Str;

    if ($record ?? null) {
        $dropTestLabel = $record->drop_test ? TiupBotolCheck::DROP_TEST[$record->drop_test] ?? $record->drop_test : '-';

        $penyebaranLabel = $record->penyebaran_rata
            ? TiupBotolCheck::OK_NOK[$record->penyebaran_rata] ?? $record->penyebaran_rata
            : '-';

        $bottomLabel = $record->bottom_tidak_menonjol
            ? TiupBotolCheck::OK_NOK[$record->bottom_tidak_menonjol] ?? $record->bottom_tidak_menonjol
            : '-';

        $materialLabel = $record->tidak_ada_material
            ? TiupBotolCheck::OK_NOK[$record->tidak_ada_material] ?? $record->tidak_ada_material
            : '-';

        // class badge berdasarkan hasil drop test
        $dropClass = match ($record->drop_test) {
            'OK' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            'NOK' => 'bg-red-50 text-red-700 border-red-200',
            default => 'bg-gray-50 text-gray-600 border-gray-200',
        };
    }
@endphp

<div>
    @if ($showModal && $record)
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
                                        d="M4.5 3.75h15m-13.5 0v13.5a3 3 0 003 3h6a3 3 0 003-3v-13.5m-10.5 6h7.5" />
                                </svg>
                            </div>

                            <div class="space-y-0.5">
                                <h2 class="text-lg font-bold text-gray-900 leading-tight">
                                    Detail Pemeriksaan Tiup Botol
                                </h2>
                                <p class="text-xs text-gray-600">
                                    Ringkasan hasil drop test & pemeriksaan visual botol.
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
                    {{-- Judul + badges --}}
                    <div class="space-y-1">
                        <p class="text-sm font-semibold text-gray-900">
                            {{ $record->nama_botol ?? '-' }}
                        </p>

                        <div class="flex flex-wrap items-center gap-1.5">
                            {{-- Tanggal --}}
                            <span
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-semibold bg-sky-50 text-sky-700 border border-sky-100">
                                {{ optional($record->tanggal)->format('d M Y') ?? '-' }}
                            </span>

                            {{-- Hasil Drop Test --}}
                            <span
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-semibold border {{ $dropClass }}">
                                {{ $dropTestLabel }}
                            </span>

                            {{-- Status Visual (singkat) --}}
                            <span
                                class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-gray-50 text-gray-700 border border-gray-200">
                                Visual:
                                <span class="ml-1">
                                    P.Rata {{ $penyebaranLabel }},
                                    Bottom {{ $bottomLabel }},
                                    Material {{ $materialLabel }}
                                </span>
                            </span>
                        </div>
                    </div>

                    {{-- Info grid --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        {{-- Kiri: Informasi Umum --}}
                        <div class="space-y-1.5">
                            <h3 class="text-[11px] font-semibold text-gray-500 uppercase">Informasi Umum</h3>
                            <dl class="space-y-1">
                                <div class="flex justify-between">
                                    <dt class="text-gray-500">Nama Botol</dt>
                                    <dd class="text-gray-900 font-medium text-right">
                                        {{ $record->nama_botol ?? '-' }}
                                    </dd>
                                </div>

                                <div class="flex justify-between">
                                    <dt class="text-gray-500">Tanggal Uji</dt>
                                    <dd class="text-gray-900 font-medium text-right">
                                        {{ optional($record->tanggal)->format('d M Y') ?? '-' }}
                                    </dd>
                                </div>

                                <div class="flex justify-between">
                                    <dt class="text-gray-500">Tanggal Input</dt>
                                    <dd class="text-gray-900 font-medium text-right">
                                        {{ optional($record->created_at)->format('d M Y H:i') ?? '-' }}
                                    </dd>
                                </div>
                            </dl>
                        </div>

                        {{-- Kanan: Hasil Ringkas --}}
                        <div class="space-y-1.5">
                            <h3 class="text-[11px] font-semibold text-gray-500 uppercase">Hasil Ringkas</h3>
                            <dl class="space-y-1">
                                <div class="flex justify-between">
                                    <dt class="text-gray-500">Drop Test</dt>
                                    <dd class="text-gray-900 font-medium text-right">
                                        {{ $dropTestLabel }}
                                    </dd>
                                </div>

                                <div class="flex justify-between">
                                    <dt class="text-gray-500">Penyebaran Rata</dt>
                                    <dd class="text-gray-900 font-medium text-right">
                                        {{ $penyebaranLabel }}
                                    </dd>
                                </div>

                                <div class="flex justify-between">
                                    <dt class="text-gray-500">Bottom Tidak Menonjol</dt>
                                    <dd class="text-gray-900 font-medium text-right">
                                        {{ $bottomLabel }}
                                    </dd>
                                </div>

                                <div class="flex justify-between">
                                    <dt class="text-gray-500">Tidak Ada Material Asing</dt>
                                    <dd class="text-gray-900 font-medium text-right">
                                        {{ $materialLabel }}
                                    </dd>
                                </div>
                            </dl>
                        </div>
                    </div>

                    {{-- Foto / Evidence (opsional, sesuaikan nama atribut path/url-nya di model) --}}
                    <div class="space-y-1.5">
                        <h3 class="text-[11px] font-semibold text-gray-500 uppercase">Dokumentasi Foto</h3>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-[11px]">
                            {{-- Drop Test --}}
                            <div class="space-y-1.5">
                                <p class="font-semibold text-gray-700">Drop Test</p>
                                @if (!empty($record->drop_test_image_url ?? null))
                                    <img src="{{ $record->drop_test_image_url }}" alt="Drop Test"
                                        class="w-full h-24 rounded-lg object-cover border border-gray-200">
                                @else
                                    <p class="text-gray-400 italic">Tidak ada foto.</p>
                                @endif
                            </div>

                            {{-- Penyebaran Rata --}}
                            <div class="space-y-1.5">
                                <p class="font-semibold text-gray-700">Penyebaran Rata</p>
                                @if (!empty($record->penyebaran_rata_image_url ?? null))
                                    <img src="{{ $record->penyebaran_rata_image_url }}" alt="Penyebaran Rata"
                                        class="w-full h-24 rounded-lg object-cover border border-gray-200">
                                @else
                                    <p class="text-gray-400 italic">Tidak ada foto.</p>
                                @endif
                            </div>

                            {{-- Bottom / Material --}}
                            <div class="space-y-1.5">
                                <p class="font-semibold text-gray-700">Bottom / Material</p>
                                @if (!empty($record->bottom_tidak_menonjol_image_url ?? null))
                                    <img src="{{ $record->bottom_tidak_menonjol_image_url }}" alt="Bottom"
                                        class="w-full h-24 rounded-lg object-cover border border-gray-200 mb-1.5">
                                @endif

                                @if (!empty($record->tidak_ada_material_image_url ?? null))
                                    <img src="{{ $record->tidak_ada_material_image_url }}" alt="Material Asing"
                                        class="w-full h-24 rounded-lg object-cover border border-gray-200">
                                @endif

                                @if (empty($record->bottom_tidak_menonjol_image_url ?? null) && empty($record->tidak_ada_material_image_url ?? null))
                                    <p class="text-gray-400 italic">Tidak ada foto.</p>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Catatan --}}
                    <div class="space-y-1.5">
                        <h3 class="text-[11px] font-semibold text-gray-500 uppercase">Catatan</h3>
                        <div class="rounded-xl border border-gray-100 bg-gray-50 px-3 py-2.5 text-[11px] text-gray-700">
                            {{ $record->catatan ?: 'Tidak ada catatan tambahan.' }}
                        </div>
                    </div>
                </div>

                {{-- FOOTER --}}
                <div class="px-5 py-3 sm:px-6 sm:py-4 border-t border-gray-100 bg-gray-50/60">
                    <div class="flex flex-col-reverse sm:flex-row sm:justify-end sm:space-x-3 gap-2">
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
