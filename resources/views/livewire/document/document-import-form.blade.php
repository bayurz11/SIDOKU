<div>
    @if ($showModal)
        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50 flex items-start justify-center"
            wire:click.self="closeModal">

            {{-- WRAPPER KOTAK (mirip DocumentForm) --}}
            <div class="relative top-8 mx-auto p-6 border w-full max-w-xl shadow-lg rounded-md bg-white">
                {{-- HEADER --}}
                <div class="flex justify-between items-start mb-4">
                    <div class="flex items-start gap-3">
                        <div
                            class="w-10 h-10 rounded-xl bg-gradient-to-br from-emerald-500 to-green-600 flex items-center justify-center shadow-md">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                stroke="currentColor" class="w-5 h-5 text-white">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M12 16.5V9.75m0 6.75 2.25-2.25M12 16.5l-2.25-2.25M4.5 19.5h15a2.25 2.25 0 0 0 2.25-2.25v-6.63a2.25 2.25 0 0 0-.659-1.591l-6.87-6.87A2.25 2.25 0 0 0 12.63 1.5H6.75A2.25 2.25 0 0 0 4.5 3.75v13.5z" />
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-lg font-bold text-gray-900">
                                Import Daftar Induk Dokumen
                            </h2>
                            <p class="text-xs text-gray-600 mt-1">
                                Upload file Excel (.xlsx / .xls) sesuai template untuk menambahkan banyak dokumen
                                sekaligus.
                            </p>
                        </div>
                    </div>

                    <button type="button" wire:click="closeModal" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                {{-- INFO TEMPLATE + DOWNLOAD --}}
                <div
                    class="rounded-xl border border-dashed border-emerald-200 bg-emerald-50/60 px-4 py-3 text-xs text-emerald-900 mb-4">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="font-semibold mb-1">Format kolom yang didukung:</p>
                            <ul class="list-disc list-inside space-y-0.5">
                                <li><code class="font-mono text-[11px]">document_code</code> (opsional)</li>
                                <li><code class="font-mono text-[11px]">title</code> (wajib)</li>
                                <li><code class="font-mono text-[11px]">document_type</code> (nama di Master Document
                                    Type)</li>
                                <li><code class="font-mono text-[11px]">department</code> (nama di Master Department)
                                </li>
                                <li><code class="font-mono text-[11px]">level</code>, <code
                                        class="font-mono text-[11px]">status</code></li>
                                <li><code class="font-mono text-[11px]">effective_date</code>, <code
                                        class="font-mono text-[11px]">expired_date</code></li>
                                <li><code class="font-mono text-[11px]">summary</code> (opsional)</li>
                            </ul>
                        </div>

                        {{-- Tombol download template --}}
                        <div class="shrink-0">
                            <a href="#"
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
                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">
                            File Excel <span class="text-red-500">*</span>
                        </label>

                        <input type="file" wire:model="excel_file" accept=".xlsx,.xls"
                            class="block w-full text-sm text-gray-700
                                      file:mr-4 file:py-2.5 file:px-4
                                      file:rounded-lg file:border-0
                                      file:text-xs file:font-semibold
                                      file:bg-emerald-50 file:text-emerald-700
                                      hover:file:bg-emerald-100
                                      border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">

                        @error('excel_file')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror

                        @if ($excel_file)
                            <p class="mt-1 text-xs text-gray-500">
                                File terpilih:
                                <span class="font-medium">{{ $excel_file->getClientOriginalName() }}</span>
                            </p>
                        @endif
                    </div>

                    {{-- HASIL / ERROR --}}
                    @if ($importedCount || $skippedCount || $importErrors)
                        <div class="mt-2 space-y-2">
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
                <div class="flex justify-end space-x-3 pt-6 mt-4 border-t border-gray-200">
                    <button wire:click="closeModal" type="button"
                        class="inline-flex items-center px-4 py-2 rounded-xl text-xs font-medium text-gray-700 bg-gray-100 border border-gray-300 hover:bg-gray-200 transition-colors">
                        Batal
                    </button>

                    <button wire:click="import" wire:loading.attr="disabled" wire:target="import,excel_file"
                        class="inline-flex items-center px-5 py-2.5 rounded-xl text-xs font-semibold text-white
                               bg-gradient-to-r from-emerald-500 via-green-500 to-lime-500
                               hover:from-emerald-600 hover:via-green-600 hover:to-lime-600
                               shadow-sm hover:shadow-md active:scale-[0.97] transition-all duration-300">
                        <svg wire:loading wire:target="import" class="animate-spin w-4 h-4 mr-2" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                        </svg>
                        <span wire:loading.remove wire:target="import">Import</span>
                        <span wire:loading wire:target="import">Memproses...</span>
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
