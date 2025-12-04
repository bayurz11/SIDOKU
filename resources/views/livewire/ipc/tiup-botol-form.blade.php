@php
    use Illuminate\Support\Str;
    use App\Domains\Ipc\Models\TiupBotolCheck;
@endphp

<div>
    @if ($showModal)
        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50 flex items-start justify-center"
            wire:click.self="closeModal">
            <div class="relative top-8 mx-auto p-6 border w-full max-w-4xl shadow-lg rounded-2xl bg-white">
                <div class="mt-1">
                    {{-- HEADER --}}
                    {{-- ... (bagian header & row tanggal + nama botol tetap) ... --}}

                    <form wire:submit.prevent="save" class="space-y-6">
                        {{-- Row tanggal & nama botol tetap --}}

                        {{-- KARTU: Drop Test --}}
                        <div class="border border-emerald-100 rounded-lg p-4 bg-emerald-50/40 space-y-3">
                            <div class="flex items-center justify-between gap-2">
                                <div>
                                    <h4 class="text-sm font-semibold text-gray-800">
                                        Drop Test (Botol Dijatuhkan)
                                    </h4>
                                    <p class="text-xs text-gray-500 mt-0.5">
                                        Pilih hasil drop test dan (opsional) lampirkan foto kondisi botol setelah uji.
                                    </p>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                {{-- Hasil Drop Test --}}
                                <div>
                                    <label for="drop_test" class="block text-xs font-medium text-gray-700 mb-1.5">
                                        Hasil Drop Test <span class="text-red-500">*</span>
                                    </label>
                                    <select wire:model.defer="drop_test" id="drop_test"
                                        class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm
                                               focus:outline-none focus:ring-blue-500 focus:border-blue-500 text-sm">
                                        <option value="">-- Pilih Hasil --</option>
                                        @foreach (TiupBotolCheck::DROP_TEST as $key => $label)
                                            <option value="{{ $key }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    @error('drop_test')
                                        <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                                    @enderror
                                </div>

                                {{-- Upload Gambar Drop Test --}}
                                <div class="md:col-span-2">
                                    <label for="drop_test_image" class="block text-xs font-medium text-gray-700 mb-1.5">
                                        Foto Kondisi Botol (Drop Test)
                                    </label>
                                    <input wire:model="drop_test_image" type="file" id="drop_test_image"
                                        accept="image/*" capture="environment"
                                        class="block w-full text-xs text-gray-700
                                               file:mr-3 file:py-2 file:px-3
                                               file:rounded-md file:border-0
                                               file:text-xs file:font-semibold
                                               file:bg-emerald-50 file:text-emerald-700
                                               hover:file:bg-emerald-100">
                                    @error('drop_test_image')
                                        <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                                    @enderror

                                    {{-- PREVIEW: file baru atau gambar lama --}}
                                    <div class="mt-2 flex items-center gap-3">
                                        @if ($drop_test_image instanceof \Livewire\Features\SupportFileUploads\TemporaryUploadedFile)
                                            {{-- preview thumbnail file baru --}}
                                            <img src="{{ $drop_test_image->temporaryUrl() }}" alt="Preview Drop Test"
                                                class="w-16 h-16 rounded-lg object-cover border border-gray-200">
                                            <span class="text-[11px] text-gray-600">
                                                File baru:
                                                {{ Str::limit($drop_test_image->getClientOriginalName(), 40) }}
                                            </span>
                                        @elseif (!empty($existing_drop_test_image_url))
                                            {{-- preview gambar lama --}}
                                            <img src="{{ $existing_drop_test_image_url }}" alt="Drop Test"
                                                class="w-16 h-16 rounded-lg object-cover border border-gray-200">
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- KARTU: Kondisi Visual Botol --}}
                        <div class="border border-blue-100 rounded-lg p-4 bg-blue-50/40 space-y-4">
                            <div>
                                <h4 class="text-sm font-semibold text-gray-800">
                                    Kondisi Visual Botol
                                </h4>
                                <p class="text-xs text-gray-500 mt-0.5">
                                    Pilih hasil pemeriksaan visual dan (opsional) lampirkan foto masing-masing kondisi.
                                </p>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                {{-- Penyebaran Rata --}}
                                <div class="space-y-2">
                                    <div>
                                        <label for="penyebaran_rata"
                                            class="block text-xs font-medium text-gray-700 mb-1.5">
                                            Penyebaran Rata
                                        </label>
                                        <select wire:model.defer="penyebaran_rata" id="penyebaran_rata"
                                            class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm
                                                   focus:outline-none focus:ring-blue-500 focus:border-blue-500 text-sm">
                                            <option value="">-- Pilih --</option>
                                            @foreach (TiupBotolCheck::OK_NOK as $key => $label)
                                                <option value="{{ $key }}">{{ $label }}</option>
                                            @endforeach
                                        </select>
                                        @error('penyebaran_rata')
                                            <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div>
                                        <label for="penyebaran_rata_image"
                                            class="block text-xs font-medium text-gray-700 mb-1.5">
                                            Foto Penyebaran Rata
                                        </label>
                                        <input wire:model="penyebaran_rata_image" type="file"
                                            id="penyebaran_rata_image" accept="image/*" capture="environment"
                                            class="block w-full text-xs text-gray-700
                                                   file:mr-3 file:py-2 file:px-3
                                                   file:rounded-md file:border-0
                                                   file:text-xs file:font-semibold
                                                   file:bg-blue-50 file:text-blue-700
                                                   hover:file:bg-blue-100">
                                        @error('penyebaran_rata_image')
                                            <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                                        @enderror

                                        <div class="mt-2 flex items-center gap-3">
                                            @if ($penyebaran_rata_image instanceof \Livewire\Features\SupportFileUploads\TemporaryUploadedFile)
                                                <img src="{{ $penyebaran_rata_image->temporaryUrl() }}"
                                                    alt="Preview Penyebaran Rata"
                                                    class="w-16 h-16 rounded-lg object-cover border border-gray-200">
                                                <span class="text-[11px] text-gray-600">
                                                    File baru:
                                                    {{ Str::limit($penyebaran_rata_image->getClientOriginalName(), 40) }}
                                                </span>
                                            @elseif (!empty($existing_penyebaran_rata_image_url))
                                                <img src="{{ $existing_penyebaran_rata_image_url }}"
                                                    alt="Penyebaran Rata"
                                                    class="w-16 h-16 rounded-lg object-cover border border-gray-200">
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                {{-- Bottom Tidak Menonjol --}}
                                <div class="space-y-2">
                                    <div>
                                        <label for="bottom_tidak_menonjol"
                                            class="block text-xs font-medium text-gray-700 mb-1.5">
                                            Bottom Tidak Menonjol
                                        </label>
                                        <select wire:model.defer="bottom_tidak_menonjol" id="bottom_tidak_menonjol"
                                            class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm
                                                   focus:outline-none focus:ring-blue-500 focus:border-blue-500 text-sm">
                                            <option value="">-- Pilih --</option>
                                            @foreach (TiupBotolCheck::OK_NOK as $key => $label)
                                                <option value="{{ $key }}">{{ $label }}</option>
                                            @endforeach
                                        </select>
                                        @error('bottom_tidak_menonjol')
                                            <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div>
                                        <label for="bottom_tidak_menonjol_image"
                                            class="block text-xs font-medium text-gray-700 mb-1.5">
                                            Foto Bottom
                                        </label>
                                        <input wire:model="bottom_tidak_menonjol_image" type="file"
                                            id="bottom_tidak_menonjol_image" accept="image/*" capture="environment"
                                            class="block w-full text-xs text-gray-700
                                                   file:mr-3 file:py-2 file:px-3
                                                   file:rounded-md file:border-0
                                                   file:text-xs file:font-semibold
                                                   file:bg-blue-50 file:text-blue-700
                                                   hover:file:bg-blue-100">
                                        @error('bottom_tidak_menonjol_image')
                                            <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                                        @enderror

                                        <div class="mt-2 flex items-center gap-3">
                                            @if ($bottom_tidak_menonjol_image instanceof \Livewire\Features\SupportFileUploads\TemporaryUploadedFile)
                                                <img src="{{ $bottom_tidak_menonjol_image->temporaryUrl() }}"
                                                    alt="Preview Bottom"
                                                    class="w-16 h-16 rounded-lg object-cover border border-gray-200">
                                                <span class="text-[11px] text-gray-600">
                                                    File baru:
                                                    {{ Str::limit($bottom_tidak_menonjol_image->getClientOriginalName(), 40) }}
                                                </span>
                                            @elseif (!empty($existing_bottom_tidak_menonjol_image_url))
                                                <img src="{{ $existing_bottom_tidak_menonjol_image_url }}"
                                                    alt="Bottom Tidak Menonjol"
                                                    class="w-16 h-16 rounded-lg object-cover border border-gray-200">
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                {{-- Tidak Ada Material --}}
                                <div class="space-y-2">
                                    <div>
                                        <label for="tidak_ada_material"
                                            class="block text-xs font-medium text-gray-700 mb-1.5">
                                            Tidak Ada Material Asing
                                        </label>
                                        <select wire:model.defer="tidak_ada_material" id="tidak_ada_material"
                                            class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm
                                                   focus:outline-none focus:ring-blue-500 focus:border-blue-500 text-sm">
                                            <option value="">-- Pilih --</option>
                                            @foreach (TiupBotolCheck::OK_NOK as $key => $label)
                                                <option value="{{ $key }}">{{ $label }}</option>
                                            @endforeach
                                        </select>
                                        @error('tidak_ada_material')
                                            <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div>
                                        <label for="tidak_ada_material_image"
                                            class="block text-xs font-medium text-gray-700 mb-1.5">
                                            Foto Kondisi Dalam Botol
                                        </label>
                                        <input wire:model="tidak_ada_material_image" type="file"
                                            id="tidak_ada_material_image" accept="image/*" capture="environment"
                                            class="block w-full text-xs text-gray-700
                                                   file:mr-3 file:py-2 file:px-3
                                                   file:rounded-md file:border-0
                                                   file:text-xs file:font-semibold
                                                   file:bg-blue-50 file:text-blue-700
                                                   hover:file:bg-blue-100">
                                        @error('tidak_ada_material_image')
                                            <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                                        @enderror

                                        <div class="mt-2 flex items-center gap-3">
                                            @if ($tidak_ada_material_image instanceof \Livewire\Features\SupportFileUploads\TemporaryUploadedFile)
                                                <img src="{{ $tidak_ada_material_image->temporaryUrl() }}"
                                                    alt="Preview Dalam Botol"
                                                    class="w-16 h-16 rounded-lg object-cover border border-gray-200">
                                                <span class="text-[11px] text-gray-600">
                                                    File baru:
                                                    {{ Str::limit($tidak_ada_material_image->getClientOriginalName(), 40) }}
                                                </span>
                                            @elseif (!empty($existing_tidak_ada_material_image_url))
                                                <img src="{{ $existing_tidak_ada_material_image_url }}"
                                                    alt="Tidak Ada Material"
                                                    class="w-16 h-16 rounded-lg object-cover border border-gray-200">
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Catatan --}}
                        <div>
                            <label for="catatan" class="block text-sm font-medium text-gray-700 mb-2">
                                Catatan (opsional)
                            </label>
                            <textarea wire:model.defer="catatan" id="catatan" rows="3"
                                class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm
                                       focus:outline-none focus:ring-blue-500 focus:border-blue-500 text-sm"
                                placeholder="Catatan tambahan, misal kondisi line, kejadian khusus, atau tindakan perbaikan..."></textarea>
                            @error('catatan')
                                <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                            @enderror
                        </div>

                        {{-- FOOTER BUTTONS --}}
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
                                {{ $isEditing ? 'Update' : 'Simpan' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>
