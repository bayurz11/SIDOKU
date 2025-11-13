<div>
    @if ($showModal)
        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50 flex items-start justify-center"
            wire:click.self="closeModal">
            <div class="relative top-8 mx-auto p-6 border w-full max-w-3xl shadow-lg rounded-md bg-white">
                <div class="mt-3">
                    <!-- HEADER -->
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-xl font-semibold text-gray-900">
                            {{ $isEditing ? 'Edit Document Prefix Setting' : 'Add Document Prefix Setting' }}
                        </h3>
                        <button type="button" wire:click="closeModal" class="text-gray-400 hover:text-gray-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <!-- FORM -->
                    <form wire:submit.prevent="save" class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- Company Prefix -->
                            <div>
                                <label for="company_prefix" class="block text-sm font-medium text-gray-700 mb-2">
                                    Company Prefix
                                </label>
                                <input wire:model.defer="company_prefix" type="text" id="company_prefix"
                                    class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm
                                           focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                    placeholder="Contoh: PRP">
                                @error('company_prefix')
                                    <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                                @enderror
                            </div>

                            <!-- Reset Interval -->
                            <div>
                                <label for="reset_interval" class="block text-sm font-medium text-gray-700 mb-2">
                                    Reset Interval
                                </label>
                                <select wire:model.defer="reset_interval" id="reset_interval"
                                    class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm
                                           focus:outline-none focus:ring-blue-500 focus:border-blue-500 text-sm">
                                    <option value="0">Tidak pernah reset</option>
                                    <option value="1">Reset per tahun</option>
                                    <option value="2">Reset per bulan</option>
                                </select>
                                @error('reset_interval')
                                    <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <!-- Document Type & Department -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="document_type_id" class="block text-sm font-medium text-gray-700 mb-2">
                                    Document Type
                                </label>
                                <select wire:model.defer="document_type_id" id="document_type_id"
                                    class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm
                                           focus:outline-none focus:ring-blue-500 focus:border-blue-500 text-sm">
                                    <option value="">-- Pilih Type --</option>
                                    @foreach ($documentTypes as $type)
                                        <option value="{{ $type->id }}">{{ $type->name }}</option>
                                    @endforeach
                                </select>
                                @error('document_type_id')
                                    <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                                @enderror
                            </div>

                            <div>
                                <label for="department_id" class="block text-sm font-medium text-gray-700 mb-2">
                                    Department
                                </label>
                                <select wire:model.defer="department_id" id="department_id"
                                    class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm
                                           focus:outline-none focus:ring-blue-500 focus:border-blue-500 text-sm">
                                    <option value="">-- Semua Department --</option>
                                    @foreach ($departments as $dept)
                                        <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                                    @endforeach
                                </select>
                                @error('department_id')
                                    <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <!-- Format Nomor -->
                        <div>
                            <label for="format_nomor" class="block text-sm font-medium text-gray-700 mb-2">
                                Format Nomor
                            </label>
                            <textarea wire:model.defer="format_nomor" id="format_nomor" rows="3"
                                class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm
                                       focus:outline-none focus:ring-blue-500 focus:border-blue-500 font-mono text-xs"
                                placeholder="{{ '{{COMP ?>' }}/{{ MAIN }}/{{ DEPT }}/{{ SEQ }}' }}"></textarea>
                            <p class="mt-1 text-xs text-gray-500">
                                Placeholder yang tersedia:
                                <span class="font-mono bg-gray-50 px-1 py-0.5 rounded border"> {{ '{COMP}' }}
                                </span>,
                                <span class="font-mono bg-gray-50 px-1 py-0.5 rounded border"> {{ '{MAIN}' }}
                                </span>,
                                <span class="font-mono bg-gray-50 px-1 py-0.5 rounded border"> {{ '{DEPT}' }}
                                </span>,
                                <span class="font-mono bg-gray-50 px-1 py-0.5 rounded border"> {{ '{SEQ}' }}
                                </span>,
                                <span class="font-mono bg-gray-50 px-1 py-0.5 rounded border"> {{ '{SUBSEQ}' }}
                                </span>,
                                <span class="font-mono bg-gray-50 px-1 py-0.5 rounded border"> {{ '{PARENT_REF}' }}
                                </span>.
                            </p>
                            @error('format_nomor')
                                <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Sub reference format -->
                        <div>
                            <label for="sub_reference_format" class="block text-sm font-medium text-gray-700 mb-2">
                                Sub Reference Format (opsional)
                            </label>
                            <input wire:model.defer="sub_reference_format" type="text" id="sub_reference_format"
                                class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm
                                       focus:outline-none focus:ring-blue-500 focus:border-blue-500 font-mono text-xs"
                                placeholder="Contoh: SOP{{ '{SEQ}' }} atau WI.SOP{{ '{SEQ}' }}.{{ '{SUBSEQ}' }}">
                            @error('sub_reference_format')
                                <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Example Output -->
                        <div>
                            <label for="example_output" class="block text-sm font-medium text-gray-700 mb-2">
                                Contoh Output (opsional)
                            </label>
                            <input wire:model.defer="example_output" type="text" id="example_output"
                                class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm
                                       focus:outline-none focus:ring-blue-500 focus:border-blue-500 font-mono text-xs"
                                placeholder="Contoh: PRP/DOC.QMS/QS/001">
                            @error('example_output')
                                <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Status aktif -->
                        <div>
                            <label class="flex items-center">
                                <input wire:model="is_active" type="checkbox"
                                    class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                <span class="ml-2 text-sm text-gray-700 font-medium">
                                    Aktif
                                </span>
                            </label>
                        </div>

                        <!-- FOOTER BUTTONS -->
                        <div class="flex justify-end space-x-3 pt-6 border-t border-gray-200">
                            <button type="button" wire:click="closeModal"
                                class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 rounded-md
                                       hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-500">
                                Cancel
                            </button>
                            <button type="submit"
                                class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md
                                       hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500
                                       flex items-center">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M5 13l4 4L19 7" />
                                </svg>
                                {{ $isEditing ? 'Update Prefix Setting' : 'Save Prefix Setting' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>
