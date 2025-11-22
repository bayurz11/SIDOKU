<div>
    @if ($showModal)
        {{-- OVERLAY --}}
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm px-3 sm:px-4"
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
                            {{-- ICON BULAT LEMBUT --}}
                            <div
                                class="w-11 h-11 rounded-2xl bg-gradient-to-br from-emerald-50 to-emerald-100
                                       flex items-center justify-center shadow-sm border border-emerald-100 flex-shrink-0">
                                {{-- ICON IMPORT (VERSI BARU) --}}
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke-width="1.5" stroke="currentColor" class="w-6 h-6 text-emerald-600">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="m9 12.75 3 3m0 0 3-3m-3 3v-7.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                </svg>
                            </div>

                            <div class="space-y-0.5">
                                <h2 class="text-lg font-bold text-gray-900 leading-tight">
                                    Import Daftar Induk Dokumen
                                </h2>
                                <p class="text-xs text-gray-600">
                                    Upload file Excel (.xlsx / .xls) sesuai template untuk menambahkan banyak dokumen
                                    sekaligus.
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
                <div class="px-5 py-4 sm:px-6 sm:py-5 space-y-4">

                    {{-- INFO TEMPLATE + DOWNLOAD --}}
                    <div
                        class="rounded-xl border border-dashed border-emerald-200 bg-emerald-50/70 px-4 py-3 text-xs text-emerald-900">
                        <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3">
                            <div>
                                <p class="font-semibold mb-1.5">Format kolom yang didukung:</p>
                                <ul class="list-disc list-inside space-y-0.5">
                                    <li><code class="font-mono text-[11px]">document_code</code> (opsional)</li>
                                    <li><code class="font-mono text-[11px]">title</code> (wajib)</li>
                                    <li><code class="font-mono text-[11px]">document_type</code> (nama di Master
                                        Document Type)</li>
                                    <li><code class="font-mono text-[11px]">department</code> (nama di Master
                                        Department)</li>
                                    <li>
                                        <code class="font-mono text-[11px]">level</code>,
                                        <code class="font-mono text-[11px]">status</code>
                                    </li>
                                    <li>
                                        <code class="font-mono text-[11px]">effective_date</code>,
                                        <code class="font-mono text-[11px]">expired_date</code>
                                    </li>
                                    <li><code class="font-mono text-[11px]">summary</code> (opsional)</li>
                                </ul>
                            </div>

                            {{-- Tombol download template --}}
                            <div class="shrink-0">
                                <a href="{{ route('documents.import-template') }}"
                                    class="inline-flex items-center px-3 py-2 rounded-lg text-[11px] font-semibold
                                           text-green-700 bg-white border border-green-200
                                           hover:bg-green-600 hover:text-white hover:border-green-600
                                           transition-all duration-200 shadow-sm">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                        stroke-width="1.5" stroke="currentColor" class="w-4 h-4 mr-1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M12 16.5V9.75m0 6.75 2.25-2.25M12 16.5l-2.25-2.25M4.5 19.5h15a2.25 2.25 0 0 0 2.25-2.25v-6.63a2.25 2.25 0 0 0-.659-1.591l-6.87-6.87A2.25 2.25 0 0 0 12.63 1.5H6.75A2.25 2.25 0 0 0 4.5 3.75v13.5z" />
                                    </svg>
                                    Download Template
                                </a>
                            </div>
                        </div>
                    </div>

                    {{-- INPUT FILE --}}
                    <div class="space-y-2">
                        <label class="block text-xs font-semibold text-gray-700">
                            File Excel <span class="text-red-500">*</span>
                        </label>

                        {{-- DROPZONE AREA --}}
                        <label
                            class="flex flex-col items-center justify-center w-full bg-white border-2 border-dashed border-emerald-300 rounded-xl p-5 cursor-pointer transition hover:bg-emerald-50 hover:border-emerald-400">

                            {{-- ICON --}}
                            <div class="flex flex-col items-center text-emerald-600">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke-width="1.5" stroke="currentColor" class="w-10 h-10 mb-2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5" />
                                </svg>

                                <p class="text-sm font-medium">Klik untuk memilih file Excel</p>
                                <p class="text-[11px] text-gray-500 mt-1">Format yang didukung: .xlsx, .xls (maks 10 MB)
                                </p>
                            </div>

                            {{-- INPUT FILE ASLI (DISAMARKAN) --}}
                            <input type="file" wire:model="excel_file" accept=".xlsx,.xls" class="hidden" />
                        </label>

                        {{-- TAMPILKAN PESAN ERROR --}}
                        @error('excel_file')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror

                        {{-- TAMPILKAN NAMA FILE --}}
                        @if ($excel_file)
                            <p class="mt-2 text-[12px] text-gray-700 bg-gray-50 border rounded-lg px-3 py-2">
                                File terpilih:
                                <span class="font-medium break-all text-emerald-600">
                                    {{ $excel_file->getClientOriginalName() }}
                                </span>
                            </p>
                        @endif
                    </div>


                    {{-- HASIL / ERROR --}}
                    @if ($importedCount || $skippedCount || $importErrors)
                        <div class="mt-1 space-y-2">
                            <div class="flex items-center justify-between text-xs">
                                <span class="text-emerald-700 font-semibold">
                                    Berhasil: {{ $importedCount }}
                                </span>
                                <span class="text-orange-600 font-semibold">
                                    Dilewati: {{ $skippedCount }}
                                </span>
                            </div>

                            @if ($importErrors)
                                <div
                                    class="max-h-32 overflow-y-auto mt-1 rounded-lg bg-red-50 border border-red-100 px-3 py-2">
                                    <p class="text-xs font-semibold text-red-700 mb-1">Detail error:</p>
                                    <ul class="text-[11px] text-red-700 space-y-0.5 list-disc list-inside">
                                        @foreach ($importErrors as $err)
                                            <li>{{ $err }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                        </div>
                    @endif
                </div>

                {{-- FOOTER BUTTONS --}}
                <div class="px-5 py-3 sm:px-6 sm:py-4 border-t border-gray-100 bg-gray-50/60">
                    <div class="flex flex-col-reverse sm:flex-row sm:justify-end sm:space-x-3 gap-2">
                        <button wire:click="closeModal" type="button"
                            class="inline-flex items-center justify-center px-4 py-2 rounded-xl text-xs font-medium
                                   text-gray-700 bg-gray-100 border border-gray-300
                                   hover:bg-gray-200 transition-colors">
                            Batal
                        </button>

                        <button wire:click="import" wire:loading.attr="disabled" wire:target="import,excel_file"
                            class="inline-flex items-center justify-center px-5 py-2.5 rounded-xl text-xs font-semibold text-white
                                   bg-gradient-to-r from-green-500 via-green-500 to-green-500
                                   hover:from-green-600 hover:via-green-600 hover:to-green-600
                                   shadow-sm hover:shadow-md active:scale-[0.97] transition-all duration-300
                                   disabled:opacity-60 disabled:cursor-not-allowed">
                            <svg wire:loading wire:target="import" class="animate-spin w-4 h-4 mr-2"
                                viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                    stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z">
                                </path>
                            </svg>
                            <span wire:loading.remove wire:target="import">Import</span>
                            <span wire:loading wire:target="import">Memproses...</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
