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

                            {{-- Nama Barang --}}
                            <div>
                                <label class="text-sm font-medium">
                                    Nama Barang <span class="text-red-500">*</span>
                                </label>
                                <input type="text" wire:model.defer="name_of_goods"
                                    class="w-full mt-1 border rounded-md p-2 text-sm">
                                @error('name_of_goods')
                                    <span class="text-red-500 text-xs">{{ $message }}</span>
                                @enderror
                            </div>

                            {{-- Supplier --}}
                            <div>
                                <label class="text-sm font-medium">
                                    Supplier <span class="text-red-500">*</span>
                                </label>
                                <input type="text" wire:model.defer="supplier_name"
                                    class="w-full mt-1 border rounded-md p-2 text-sm">
                                @error('supplier_name')
                                    <span class="text-red-500 text-xs">{{ $message }}</span>
                                @enderror
                            </div>

                            {{-- Tanggal Terima --}}
                            <div>
                                <label class="text-sm font-medium">
                                    Tanggal Terima <span class="text-red-500">*</span>
                                </label>
                                <input type="date" wire:model.defer="receipt_date"
                                    class="w-full mt-1 border rounded-md p-2 text-sm">
                            </div>

                            {{-- Waktu Penerimaan --}}
                            <div>
                                <label class="text-sm font-medium">
                                    Waktu Penerimaan
                                </label>
                                <input type="time" wire:model.defer="receipt_time"
                                    class="w-full mt-1 border rounded-md p-2 text-sm">
                            </div>

                            {{-- Batch Number --}}
                            <div>
                                <label class="text-sm font-medium">Batch Number</label>
                                <input type="text" wire:model.defer="batch_number"
                                    class="w-full mt-1 border rounded-md p-2 text-sm">
                            </div>

                            {{-- Expired Date --}}
                            <div>
                                <label class="text-sm font-medium">Expired Date</label>
                                <input type="date" wire:model.defer="expired_date"
                                    class="w-full mt-1 border rounded-md p-2 text-sm">
                            </div>

                            {{-- Quantity --}}
                            <div>
                                <label class="text-sm font-medium">Quantity</label>
                                <input type="number" wire:model.defer="quantity" min="0" step="any"
                                    class="w-full mt-1 border rounded-md p-2 text-sm">
                                @error('quantity')
                                    <span class="text-red-500 text-xs">{{ $message }}</span>
                                @enderror
                            </div>

                            {{-- Satuan Quantity --}}
                            <div>
                                <label class="text-sm font-medium">Satuan</label>
                                <select wire:model.defer="quantity_unit"
                                    class="w-full mt-1 border rounded-md p-2 text-sm bg-white">
                                    <option value="">-- Pilih Satuan --</option>
                                    <option value="PACK">Pack</option>
                                    <option value="BOX">Box</option>
                                    <option value="BOTOL">Botol</option>
                                    <option value="DUS">Dus</option>
                                    <option value="SAK">Sak</option>
                                    <option value="KG">Kg</option>
                                    <option value="LITER">Liter</option>
                                </select>
                            </div>

                            {{-- Jumlah Sampel --}}
                            <div>
                                <label class="text-sm font-medium">
                                    Jumlah Sampel
                                </label>
                                <input type="number" wire:model.defer="sample_quantity" min="0"
                                    class="w-full mt-1 border rounded-md p-2 text-sm">
                            </div>

                            {{-- Nomor Kendaraan --}}
                            <div>
                                <label class="text-sm font-medium">
                                    Nomor Kendaraan
                                </label>
                                <input type="text" wire:model.defer="vehicle_number" placeholder="Contoh: BG 1234 XX"
                                    class="w-full mt-1 border rounded-md p-2 text-sm uppercase">
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

                        <h4 class="text-sm font-semibold text-gray-800 mb-6">
                            Kesesuaian Dokumen (Document Suitability)
                        </h4>

                        <div class="space-y-8">

                            {{-- ================= A & B ================= --}}
                            @php
                                $mainDocuments = [
                                    'coa' => 'COA*',
                                    'halal_certificate' => 'Sertifikat Halal',
                                ];
                            @endphp

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                @foreach ($mainDocuments as $key => $label)
                                    <div class="bg-white border rounded-lg p-4 space-y-3">

                                        {{-- Checkbox --}}
                                        <label class="flex items-center gap-2">
                                            <input type="checkbox"
                                                wire:model="documents.{{ $key }}.is_checked"
                                                class="rounded border-gray-300">

                                            <span class="text-sm font-medium text-gray-700">
                                                {{ $label }}
                                            </span>
                                        </label>


                                        {{-- Drag & Drop Area --}}
                                        <div x-data="{ drag: false }" x-on:dragover.prevent="drag=true"
                                            x-on:dragleave.prevent="drag=false"
                                            x-on:drop.prevent=" drag=false;  let files = $event.dataTransfer.files; if(files.length){$wire.upload('documents.{{ $key }}.file', files[0])} "
                                            :class="drag ? 'border-blue-500 bg-blue-50' : ''"
                                            class="border-2 border-dashed rounded-lg p-4 text-center transition">

                                            {{-- Hidden File Input --}}
                                            <input type="file" wire:model="documents.{{ $key }}.file"
                                                id="doc_{{ $key }}" class="hidden">

                                            <label for="doc_{{ $key }}" class="cursor-pointer block">

                                                <p class="text-xs text-gray-500">
                                                    Drag & drop file atau klik upload
                                                </p>

                                            </label>

                                        </div>


                                        {{-- Preview File Lama --}}
                                        @if (!empty($documents[$key]['existing_path']))
                                            <div
                                                class="flex items-center justify-between bg-gray-100 rounded p-2 text-xs">

                                                <a href="{{ Storage::url($documents[$key]['existing_path']) }}"
                                                    target="_blank" class="text-blue-600 underline">

                                                    Lihat file

                                                </a>

                                                <button type="button"
                                                    wire:click="removeExistingDocument('{{ $key }}')"
                                                    class="text-red-500">

                                                    Hapus

                                                </button>

                                            </div>
                                        @endif


                                        {{-- Preview File Baru --}}
                                        @if (isset($documents[$key]['file']) && $documents[$key]['file'])
                                            <div class="text-xs text-green-600">

                                                File baru:
                                                {{ $documents[$key]['file']->getClientOriginalName() }}

                                            </div>
                                        @endif

                                    </div>
                                @endforeach
                            </div>

                            {{-- ================= C. PACKAGING ================= --}}
                            <div>
                                <h5 class="text-sm font-semibold text-gray-700 mb-3">
                                    Pengemasan (Packaging)
                                </h5>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                                    @php
                                        $packagingDocuments = [
                                            'original_packaging' => 'Kemasan asli',
                                            'repacking' => 'Pengemasan ulang',
                                        ];
                                    @endphp

                                    @foreach ($packagingDocuments as $key => $label)
                                        <div class="bg-white border rounded-xl p-4 space-y-3">

                                            <label class="flex items-center gap-2">
                                                <input type="checkbox"
                                                    wire:model="documents.{{ $key }}.is_checked"
                                                    class="rounded border-gray-300">

                                                <span class="text-sm font-medium text-gray-700">
                                                    {{ $label }}
                                                </span>
                                            </label>

                                            {{-- Drag Upload --}}
                                            <div
                                                class="border-2 border-dashed rounded-lg p-3 text-center hover:border-blue-400 transition">

                                                <input type="file" wire:model="documents.{{ $key }}.file"
                                                    id="upload_{{ $key }}" class="hidden">

                                                <label for="upload_{{ $key }}"
                                                    class="cursor-pointer text-xs text-gray-500">
                                                    Drag & Drop atau Klik Upload
                                                </label>

                                            </div>

                                            {{-- FILE LAMA --}}
                                            @if (!empty($documents[$key]['existing_path']))
                                                <div
                                                    class="flex justify-between items-center bg-gray-100 rounded p-2 text-xs">

                                                    <a href="{{ Storage::url($documents[$key]['existing_path']) }}"
                                                        target="_blank" class="text-blue-600 underline">

                                                        Lihat File

                                                    </a>

                                                    <button type="button"
                                                        wire:click="removeExistingDocument('{{ $key }}')"
                                                        class="text-red-500">

                                                        Hapus

                                                    </button>

                                                </div>
                                            @endif

                                            {{-- FILE BARU --}}
                                            @if (isset($documents[$key]['file']) && $documents[$key]['file'])
                                                <div class="text-xs text-green-600">
                                                    File baru:
                                                    {{ $documents[$key]['file']->getClientOriginalName() }}
                                                </div>
                                            @endif

                                        </div>
                                    @endforeach

                                </div>
                            </div>


                            {{-- ================= D. DATA PENDUKUNG ================= --}}
                            <div>

                                <h5 class="text-sm font-semibold text-gray-700 mb-3">
                                    Data pendukung lain (Other supporting data)
                                </h5>

                                @php
                                    $supportingDocuments = [
                                        'flow_chart' => 'Diagram alir',
                                        'no_animal_use' => 'Tanpa penggunaan hewan',
                                        'msds' => 'MSDS',
                                        'allergen' => 'Alergen statement',
                                        'food_grade' => 'Food Grade',
                                        'non_gmo' => 'Non GMO statement',
                                        'bse_tse' => 'BSE / TSE statement',
                                        'porcine_free' => 'Porcine free statement',
                                        'breakdown_composition' => 'Breakdown Composition',
                                    ];
                                @endphp

                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

                                    @foreach ($supportingDocuments as $key => $label)
                                        <div class="bg-white border rounded-xl p-4 space-y-3">

                                            <label class="flex items-center gap-2">

                                                <input type="checkbox"
                                                    wire:model="documents.{{ $key }}.is_checked"
                                                    class="rounded border-gray-300">

                                                <span class="text-sm font-medium text-gray-700">
                                                    {{ $label }}
                                                </span>

                                            </label>

                                            {{-- Drag Upload --}}
                                            <div
                                                class="border-2 border-dashed rounded-lg p-3 text-center hover:border-blue-400 transition">

                                                <input type="file" wire:model="documents.{{ $key }}.file"
                                                    id="doc_{{ $key }}" class="hidden">

                                                <label for="doc_{{ $key }}"
                                                    class="cursor-pointer text-xs text-gray-500">

                                                    Drag & Drop atau Klik Upload

                                                </label>

                                            </div>

                                            {{-- FILE LAMA --}}
                                            @if (!empty($documents[$key]['existing_path']))
                                                <div
                                                    class="flex justify-between items-center bg-gray-100 rounded p-2 text-xs">

                                                    <a href="{{ Storage::url($documents[$key]['existing_path']) }}"
                                                        target="_blank" class="text-blue-600 underline">

                                                        Lihat File

                                                    </a>

                                                    <button type="button"
                                                        wire:click="removeExistingDocument('{{ $key }}')"
                                                        class="text-red-500">

                                                        Hapus

                                                    </button>

                                                </div>
                                            @endif

                                            {{-- FILE BARU --}}
                                            @if (isset($documents[$key]['file']) && $documents[$key]['file'])
                                                <div class="text-xs text-green-600">

                                                    File baru:
                                                    {{ $documents[$key]['file']->getClientOriginalName() }}

                                                </div>
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

                                {{-- Drag Upload --}}
                                <div
                                    class="border-2 border-dashed rounded-xl p-6 text-center hover:border-purple-400 transition">

                                    <input type="file" wire:model="photos" multiple id="uploadPhoto"
                                        class="hidden">

                                    <label for="uploadPhoto" class="cursor-pointer text-sm text-gray-500">

                                        Drag & Drop Foto atau Klik Upload

                                    </label>

                                </div>

                                {{-- Loading --}}
                                <div wire:loading wire:target="photos" class="text-xs text-gray-500 mt-2">
                                    Uploading...
                                </div>

                                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-4">

                                    {{-- FOTO LAMA --}}
                                    @foreach ($existingPhotos as $photo)
                                        <div class="relative group border rounded-lg overflow-hidden">

                                            <img src="{{ Storage::url($photo) }}" class="w-full h-32 object-cover">

                                            <button type="button"
                                                wire:click="removeExistingPhoto('{{ $photo }}')"
                                                class="absolute top-1 right-1 bg-red-600 text-white text-xs px-2 py-1 rounded opacity-0 group-hover:opacity-100">

                                                Hapus

                                            </button>

                                        </div>
                                    @endforeach

                                    {{-- FOTO BARU --}}
                                    @foreach ($photos as $photo)
                                        <div class="border rounded-lg overflow-hidden">

                                            <img src="{{ $photo->temporaryUrl() }}" class="w-full h-32 object-cover">

                                        </div>
                                    @endforeach

                                </div>

                            </div>

                        </div>
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
                    {{-- ================= PARAMETER PENGUJIAN LAB ================= --}}
                    <div class="border border-purple-200 rounded-xl p-6 bg-purple-50/30">

                        <h4 class="text-sm font-semibold text-gray-800 mb-4">
                            Parameter Pengujian Laboratorium
                        </h4>

                        <p class="text-xs text-gray-500 mb-4">
                            Pilih parameter pengujian yang diperlukan untuk material ini.
                        </p>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

                            {{-- Kadar Air --}}
                            <label
                                class="flex items-center gap-3 bg-white border rounded-lg p-4 cursor-pointer hover:bg-gray-50">
                                <input type="checkbox" wire:model="test_moisture" class="rounded border-gray-300">

                                <div>
                                    <p class="text-sm font-medium text-gray-700">
                                        Kadar Air
                                    </p>
                                    <p class="text-xs text-gray-500">
                                        Pengujian Moisture Content
                                    </p>
                                </div>
                            </label>

                            {{-- Mikrobiologi --}}
                            <label
                                class="flex items-center gap-3 bg-white border rounded-lg p-4 cursor-pointer hover:bg-gray-50">
                                <input type="checkbox" wire:model="test_microbiology"
                                    class="rounded border-gray-300">

                                <div>
                                    <p class="text-sm font-medium text-gray-700">
                                        Mikrobiologi
                                    </p>
                                    <p class="text-xs text-gray-500">
                                        Uji TPC, YM, Coliform
                                    </p>
                                </div>
                            </label>

                            {{-- Kimia --}}
                            <label
                                class="flex items-center gap-3 bg-white border rounded-lg p-4 cursor-pointer hover:bg-gray-50">
                                <input type="checkbox" wire:model="test_chemical" class="rounded border-gray-300">

                                <div>
                                    <p class="text-sm font-medium text-gray-700">
                                        Kimia
                                    </p>
                                    <p class="text-xs text-gray-500">
                                        Parameter kimia tambahan
                                    </p>
                                </div>
                            </label>

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
