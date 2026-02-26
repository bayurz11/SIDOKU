@php
    use Illuminate\Support\Str;
@endphp

<div>
    @if ($showModal)
        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50 flex items-start justify-center"
            wire:click.self="closeModal">

            <div class="relative top-8 mx-auto p-6 border w-full max-w-6xl shadow-lg rounded-2xl bg-white"
                x-data="{}">

                {{-- ================= HEADER ================= --}}
                <div class="flex justify-between items-start mb-6">
                    <div>
                        <h3 class="text-xl font-semibold text-gray-900">
                            {{ $isEditing ? 'Edit Incoming Material' : 'Tambah Incoming Material' }}
                        </h3>
                        <p class="text-sm text-gray-500 mt-1">
                            Form Penerimaan Bahan Tahap 1 - QC Incoming Inspection
                        </p>
                    </div>

                    <button type="button" wire:click="closeModal" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form wire:submit.prevent="save" class="space-y-8">

                    {{-- ================= INFORMASI UMUM ================= --}}
                    <div class="border border-gray-200 rounded-xl p-6 bg-gray-50">
                        <h4 class="text-sm font-semibold text-gray-800 mb-4">
                            Informasi Umum
                        </h4>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

                            <div>
                                <label class="text-sm font-medium">Nama Barang <span
                                        class="text-red-500">*</span></label>
                                <input type="text" wire:model.defer="name_of_goods"
                                    class="w-full mt-1 border rounded-md p-2 text-sm">
                                @error('name_of_goods')
                                    <span class="text-red-500 text-xs">{{ $message }}</span>
                                @enderror
                            </div>

                            <div>
                                <label class="text-sm font-medium">Supplier <span class="text-red-500">*</span></label>
                                <input type="text" wire:model.defer="supplier_name"
                                    class="w-full mt-1 border rounded-md p-2 text-sm">
                                @error('supplier_name')
                                    <span class="text-red-500 text-xs">{{ $message }}</span>
                                @enderror
                            </div>

                            <div>
                                <label class="text-sm font-medium">Tanggal Terima <span
                                        class="text-red-500">*</span></label>
                                <input type="date" wire:model.defer="receipt_date"
                                    class="w-full mt-1 border rounded-md p-2 text-sm">
                            </div>

                            <div>
                                <label class="text-sm font-medium">Batch Number</label>
                                <input type="text" wire:model.defer="batch_number"
                                    class="w-full mt-1 border rounded-md p-2 text-sm">
                            </div>

                            <div>
                                <label class="text-sm font-medium">Expired Date</label>
                                <input type="date" wire:model.defer="expired_date"
                                    class="w-full mt-1 border rounded-md p-2 text-sm">
                            </div>

                            <div>
                                <label class="text-sm font-medium">Quantity</label>
                                <input type="number" wire:model.defer="quantity" min="0" step="any"
                                    class="w-full mt-1 border rounded-md p-2 text-sm">
                                @error('quantity')
                                    <span class="text-red-500 text-xs">{{ $message }}</span>
                                @enderror
                            </div>

                        </div>
                    </div>
                    {{-- ================= SPESIFIKASI YANG DIPERIKSA ================= --}}
                    <div class="bg-white shadow rounded-xl border mt-6">

                        <div class="px-6 py-4 border-b bg-gray-50 flex justify-between items-center">
                            <h3 class="text-sm font-semibold text-gray-700">
                                Spesifikasi yang Diperiksa
                            </h3>

                            <button type="button" wire:click="addInspectionItem"
                                class="bg-green-600 hover:bg-green-700 text-white px-3 py-1 rounded-md text-xs">
                                + Tambah Parameter
                            </button>
                        </div>

                        <div class="p-6 overflow-x-auto">
                            <table class="min-w-full text-sm border border-gray-200">
                                <thead class="bg-gray-100">
                                    <tr class="text-left">
                                        <th class="px-3 py-2 border">No</th>
                                        <th class="px-3 py-2 border">Parameter Uji</th>
                                        <th class="px-3 py-2 border">Standar (Kondisi Fisik)</th>
                                        <th class="px-3 py-2 border">Hasil Uji</th>
                                        <th class="px-3 py-2 border">Hasil Inspeksi</th>
                                        <th class="px-3 py-2 border text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($inspectionItems as $index => $item)
                                        <tr>
                                            <td class="px-3 py-2 border text-center">
                                                {{ $loop->iteration }}
                                            </td>

                                            {{-- Parameter --}}
                                            <td class="px-3 py-2 border">
                                                <input type="text"
                                                    wire:model.defer="inspectionItems.{{ $index }}.parameter"
                                                    class="w-full border rounded-md p-1 text-sm"
                                                    placeholder="Contoh: Warna, Bau, Tekstur">
                                            </td>

                                            {{-- Standard --}}
                                            <td class="px-3 py-2 border">
                                                <input type="text"
                                                    wire:model.defer="inspectionItems.{{ $index }}.standard"
                                                    class="w-full border rounded-md p-1 text-sm"
                                                    placeholder="Contoh: Putih bersih, Tidak berbau">
                                            </td>

                                            {{-- Test Result --}}
                                            <td class="px-3 py-2 border">
                                                <select wire:model="inspectionItems.{{ $index }}.test_result"
                                                    class="w-full border rounded-md p-1 text-sm">
                                                    <option value="">-- Pilih --</option>
                                                    <option value="ok">OK</option>
                                                    <option value="not ok">NOT OK</option>
                                                </select>
                                            </td>

                                            {{-- Inspection Result --}}
                                            <td class="px-3 py-2 border text-center font-semibold">
                                                @if ($item['inspection_result'] === 'OK')
                                                    <span class="text-green-600">OK</span>
                                                @elseif($item['inspection_result'] === 'NOT OK')
                                                    <span class="text-red-600">NOT OK</span>
                                                @endif
                                            </td>

                                            {{-- Delete --}}
                                            <td class="px-3 py-2 border text-center">
                                                <button type="button"
                                                    wire:click="removeInspectionItem({{ $index }})"
                                                    class="text-red-600 hover:text-red-800 text-xs">
                                                    Hapus
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                    </div>
                    {{-- ================= DOCUMENT SUITABILITY ================= --}}
                    <div class="border border-blue-200 rounded-xl p-6 bg-blue-50/30">
                        <h4 class="text-sm font-semibold text-gray-800 mb-4">
                            Kesesuaian Dokumen (Document Suitability)
                        </h4>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                            @foreach ($documentTypes as $key => $label)
                                <div class="bg-white border rounded-lg p-4 space-y-2">

                                    <label class="flex items-center gap-2">
                                        <input type="checkbox" wire:model="documents.{{ $key }}.is_checked"
                                            class="rounded border-gray-300">
                                        <span class="text-sm font-medium text-gray-700">
                                            {{ $label }}
                                        </span>
                                    </label>

                                    <input type="file" wire:model="documents.{{ $key }}.file"
                                        class="w-full text-xs border rounded-md p-2">

                                    @if (isset($documents[$key]['file']) && $documents[$key]['file'])
                                        <span class="text-xs text-green-600">
                                            File siap upload:
                                            {{ $documents[$key]['file']->getClientOriginalName() }}
                                        </span>
                                    @endif

                                </div>
                            @endforeach

                        </div>
                    </div>

                    {{-- ================= FOTO MATERIAL ================= --}}
                    <div class="border border-gray-200 rounded-xl p-6 bg-gray-50">
                        <h4 class="text-sm font-semibold text-gray-800 mb-4">
                            Upload Foto Material
                        </h4>

                        <input type="file" wire:model="photos" multiple
                            class="w-full border rounded-md p-2 text-sm">

                        @if ($photos)
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-4">
                                @foreach ($photos as $photo)
                                    <div class="border rounded-md overflow-hidden">
                                        <img src="{{ $photo->temporaryUrl() }}" class="w-full h-32 object-cover">
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    {{-- ================= KEPUTUSAN INSPEKSI ================= --}}
                    <div class="border border-green-200 rounded-xl p-6 bg-green-50/30">
                        <h4 class="text-sm font-semibold text-gray-800 mb-4">
                            Keputusan Inspeksi
                        </h4>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                            <div>
                                <label class="text-sm font-medium">Keputusan <span
                                        class="text-red-500">*</span></label>
                                <select wire:model.defer="inspection_decision"
                                    class="w-full mt-1 border rounded-md p-2 text-sm">
                                    <option value="">-- Pilih --</option>
                                    <option value="ACCEPTED">Accepted to Stage 2</option>
                                    <option value="HOLD">Hold</option>
                                    <option value="REJECTED">Rejected</option>
                                </select>
                            </div>

                            <div>
                                <label class="text-sm font-medium">Catatan</label>
                                <textarea wire:model.defer="inspection_notes" class="w-full mt-1 border rounded-md p-2 text-sm" rows="3"></textarea>
                            </div>

                        </div>
                    </div>

                    {{-- ================= BUTTON ================= --}}
                    <div class="flex justify-end space-x-3 pt-6 border-t border-gray-200">
                        <button type="button" wire:click="closeModal"
                            class="px-4 py-2 text-sm bg-gray-200 rounded-md hover:bg-gray-300">
                            Cancel
                        </button>

                        <button type="submit"
                            class="px-4 py-2 text-sm text-white bg-blue-600 rounded-md hover:bg-blue-700 flex items-center">
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
    @endif
</div>
