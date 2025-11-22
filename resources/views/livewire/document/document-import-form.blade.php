<div>
    @if ($showModal)
        {{-- OVERLAY --}}
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm px-3 sm:px-4"
            wire:click.self="closeModal">

            {{-- WRAPPER KOTAK --}}
            <div
                class="relative w-full max-w-xl bg-white rounded-2xl shadow-2xl border border-emerald-100
                       transform transition-all duration-200 ease-out
                       max-h-[90vh] overflow-y-auto">

                {{-- HEADER --}}
                <div class="px-5 py-4 sm:px-6 sm:py-5 border-b border-emerald-50">
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex items-start gap-3">
                            <div
                                class="w-10 h-10 rounded-xl bg-gradient-to-br from-emerald-500 to-green-600
                                       flex items-center justify-center shadow-md flex-shrink-0">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-white">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M12 16.5V9.75m0 6.75 2.25-2.25M12 16.5l-2.25-2.25M4.5 19.5h15a2.25 2.25 0 0 0 2.25-2.25v-6.63a2.25 2.25 0 0 0-.659-1.591l-6.87-6.87A2.25 2.25 0 0 0 12.63 1.5H6.75A2.25 2.25 0 0 0 4.5 3.75v13.5z" />
                                </svg>
                            </div>
                            <div class="space-y-1">
                                <h2 class="text-base sm:text-lg font-bold text-gray-900">
                                    Import Daftar Induk Dokumen
                                </h2>
                                <p class="text-[11px] sm:text-xs text-gray-600 leading-relaxed">
                                    Upload file Excel (.xlsx / .xls) sesuai template untuk menambahkan banyak dokumen
                                    sekaligus.
                                </p>
                            </div>
                        </div>

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
                                           text-emerald-700 bg-white border border-emerald-200
                                           hover:bg-emerald-600 hover:text-white hover:border-emerald-600
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

                        <input type="file" wire:model="excel_file" accept=".xlsx,.xls"
                            class="block w-full text-sm text-gray-700
                                   file:mr-4 file:py-2.5 file:px-4
                                   file:rounded-lg file:border-0
                                   file:text-xs file:font-semibold
                                   file:bg-emerald-50 file:text-emerald-700
                                   hover:file:bg-emerald-100
                                   border border-gray-300 rounded-xl
                                   focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">

                        @error('excel_file')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror

                        @if ($excel_file)
                            <p class="mt-1 text-[11px] text-gray-500">
                                File terpilih:
                                <span class="font-medium break-all">{{ $excel_file->getClientOriginalName() }}</span>
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
                                   bg-gradient-to-r from-emerald-500 via-green-500 to-lime-500
                                   hover:from-emerald-600 hover:via-green-600 hover:to-lime-600
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
