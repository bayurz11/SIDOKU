@php
    use Illuminate\Support\Str;
@endphp

<div>
    @if ($showModal)
        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50 flex items-start justify-center"
            wire:click.self="closeModal">
            <div class="relative top-8 mx-auto p-6 border w-full max-w-4xl shadow-lg rounded-2xl bg-white">
                <div class="mt-1">
                    {{-- HEADER --}}
                    <div class="flex justify-between items-start mb-6">
                        <div>
                            <h3 class="text-xl font-semibold text-gray-900">
                                {{ $isEditing ? 'Edit IPC Product Check' : 'Tambah IPC Product Check' }}
                            </h3>
                            <p class="text-sm text-gray-500 mt-1">
                                Input hasil pemeriksaan IPC (kadar air, berat, pH, Brix, TDS, dll) per Line dan Produk.
                            </p>
                        </div>

                        <div class="flex flex-col items-end space-y-2">
                            {{-- Bisa dipakai untuk info Line/Produk ringkas kalau mau --}}
                            @if ($line_group && $product_name)
                                <span
                                    class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-200">
                                    {{ \App\Domains\Ipc\Models\IpcProductCheck::LINE_GROUPS[$line_group] ?? $line_group }}:
                                    <span class="ml-1">{{ Str::limit($product_name, 30) }}</span>
                                </span>
                            @endif

                            <button type="button" wire:click="closeModal" class="text-gray-400 hover:text-gray-600">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    {{-- FORM --}}
                    <form wire:submit.prevent="save" class="space-y-6">
                        {{-- Row 1: Line Group, Sub Line, Tanggal --}}
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            {{-- Line Group --}}
                            <div>
                                <label for="line_group" class="block text-sm font-medium text-gray-700 mb-2">
                                    Line Group <span class="text-red-500">*</span>
                                </label>
                                <select wire:model.defer="line_group" id="line_group"
                                    class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm
                                           focus:outline-none focus:ring-blue-500 focus:border-blue-500 text-sm">
                                    <option value="">-- Pilih Line --</option>
                                    @foreach ($lineGroups as $key => $label)
                                        <option value="{{ $key }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('line_group')
                                    <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                                @enderror
                            </div>

                            {{-- Sub Line (khusus Line Teh) --}}
                            <div>
                                <label for="sub_line" class="block text-sm font-medium text-gray-700 mb-2">
                                    Sub Line (Line Teh)
                                </label>
                                <select wire:model.defer="sub_line" id="sub_line"
                                    class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm
                                           focus:outline-none focus:ring-blue-500 focus:border-blue-500 text-sm
                                           @if ($line_group !== 'LINE_TEH') bg-gray-50 @endif"
                                    @if ($line_group !== 'LINE_TEH') disabled @endif>
                                    <option value="">-- Semua Sub Line --</option>
                                    @foreach ($subLinesTeh as $key => $label)
                                        <option value="{{ $key }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                                <p class="mt-1 text-xs text-gray-500">
                                    Aktif jika Line Group = <span class="font-semibold">Line Teh</span>.
                                </p>
                                @error('sub_line')
                                    <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                                @enderror
                            </div>

                            {{-- Tanggal --}}
                            <div>
                                <label for="test_date" class="block text-sm font-medium text-gray-700 mb-2">
                                    Hari, Tanggal <span class="text-red-500">*</span>
                                </label>
                                <input wire:model.defer="test_date" type="date" id="test_date"
                                    class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm
                                           focus:outline-none focus:ring-blue-500 focus:border-blue-500 text-sm">
                                @error('test_date')
                                    <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        {{-- Row 2: Nama Produk & Shift --}}
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            {{-- Nama Produk --}}
                            <div class="md:col-span-2">
                                <label for="product_name" class="block text-sm font-medium text-gray-700 mb-2">
                                    Nama Produk / Nama Botol <span class="text-red-500">*</span>
                                </label>
                                <input wire:model.defer="product_name" type="text" id="product_name"
                                    class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm
                                           focus:outline-none focus:ring-blue-500 focus:border-blue-500 text-sm"
                                    placeholder="Contoh: Teh Ori 350 ml, Powder Instan 1 kg, AMDK 600 ml">
                                @error('product_name')
                                    <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                                @enderror
                            </div>

                            {{-- Shift --}}
                            <div>
                                <label for="shift" class="block text-sm font-medium text-gray-700 mb-2">
                                    Shift (opsional)
                                </label>
                                <select wire:model.defer="shift" id="shift"
                                    class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm
                                           focus:outline-none focus:ring-blue-500 focus:border-blue-500 text-sm">
                                    <option value="">-- Tidak ditentukan --</option>
                                    <option value="1">Shift 1</option>
                                    <option value="2">Shift 2</option>
                                    <option value="3">Shift 3</option>
                                </select>
                                @error('shift')
                                    <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        {{-- Row 3: Kadar Air & Berat --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            {{-- Kadar Air --}}
                            <div>
                                <label for="avg_moisture_percent" class="block text-sm font-medium text-gray-700 mb-2">
                                    Rata-rata Kadar Air (%) <span class="text-xs text-gray-400">(opsional)</span>
                                </label>
                                <input wire:model.defer="avg_moisture_percent" type="number" step="0.01"
                                    min="0" id="avg_moisture_percent"
                                    class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm
                                           focus:outline-none focus:ring-blue-500 focus:border-blue-500 text-sm"
                                    placeholder="Contoh: 3.50">
                                @error('avg_moisture_percent')
                                    <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                                @enderror
                            </div>

                            {{-- Berat --}}
                            <div>
                                <label for="avg_weight_g" class="block text-sm font-medium text-gray-700 mb-2">
                                    Rata-rata Berat (g) <span class="text-xs text-gray-400">(opsional)</span>
                                </label>
                                <input wire:model.defer="avg_weight_g" type="number" step="0.001" min="0"
                                    id="avg_weight_g"
                                    class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm
                                           focus:outline-none focus:ring-blue-500 focus:border-blue-500 text-sm"
                                    placeholder="Contoh: 3.200">
                                @error('avg_weight_g')
                                    <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        {{-- Row 4: pH & Brix --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            {{-- pH --}}
                            <div>
                                <label for="avg_ph" class="block text-sm font-medium text-gray-700 mb-2">
                                    Rata-rata pH <span class="text-xs text-gray-400">(opsional)</span>
                                </label>
                                <input wire:model.defer="avg_ph" type="number" step="0.01" min="0"
                                    max="14" id="avg_ph"
                                    class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm
                                           focus:outline-none focus:ring-blue-500 focus:border-blue-500 text-sm"
                                    placeholder="Contoh: 6.80">
                                @error('avg_ph')
                                    <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                                @enderror
                            </div>

                            {{-- Brix --}}
                            <div>
                                <label for="avg_brix" class="block text-sm font-medium text-gray-700 mb-2">
                                    Rata-rata Brix (°Brix) <span class="text-xs text-gray-400">(opsional)</span>
                                </label>
                                <input wire:model.defer="avg_brix" type="number" step="0.01" min="0"
                                    id="avg_brix"
                                    class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm
                                           focus:outline-none focus:ring-blue-500 focus:border-blue-500 text-sm"
                                    placeholder="Contoh: 10.50">
                                @error('avg_brix')
                                    <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        {{-- Row 5: TDS & Klorin --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            {{-- TDS --}}
                            <div>
                                <label for="avg_tds_ppm" class="block text-sm font-medium text-gray-700 mb-2">
                                    Rata-rata TDS (ppm) <span class="text-xs text-gray-400">(opsional)</span>
                                </label>
                                <input wire:model.defer="avg_tds_ppm" type="number" step="0.01" min="0"
                                    id="avg_tds_ppm"
                                    class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm
                                           focus:outline-none focus:ring-blue-500 focus:border-blue-500 text-sm"
                                    placeholder="Contoh: 120.00">
                                @error('avg_tds_ppm')
                                    <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                                @enderror
                            </div>

                            {{-- Klorin --}}
                            <div>
                                <label for="avg_chlorine" class="block text-sm font-medium text-gray-700 mb-2">
                                    Rata-rata Klorin <span class="text-xs text-gray-400">(opsional)</span>
                                </label>
                                <input wire:model.defer="avg_chlorine" type="number" step="0.001" min="0"
                                    id="avg_chlorine"
                                    class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm
                                           focus:outline-none focus:ring-blue-500 focus:border-blue-500 text-sm"
                                    placeholder="Contoh: 0.300">
                                @error('avg_chlorine')
                                    <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        {{-- Row 6: Ozon & Kekeruhan --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            {{-- Ozon --}}
                            <div>
                                <label for="avg_ozone" class="block text-sm font-medium text-gray-700 mb-2">
                                    Rata-rata Ozon <span class="text-xs text-gray-400">(opsional)</span>
                                </label>
                                <input wire:model.defer="avg_ozone" type="number" step="0.001" min="0"
                                    id="avg_ozone"
                                    class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm
                                           focus:outline-none focus:ring-blue-500 focus:border-blue-500 text-sm"
                                    placeholder="Contoh: 0.200">
                                @error('avg_ozone')
                                    <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                                @enderror
                            </div>

                            {{-- Kekeruhan --}}
                            <div>
                                <label for="avg_turbidity_ntu" class="block text-sm font-medium text-gray-700 mb-2">
                                    Rata-rata Kekeruhan (NTU) <span class="text-xs text-gray-400">(opsional)</span>
                                </label>
                                <input wire:model.defer="avg_turbidity_ntu" type="number" step="0.001"
                                    min="0" id="avg_turbidity_ntu"
                                    class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm
                                           focus:outline-none focus:ring-blue-500 focus:border-blue-500 text-sm"
                                    placeholder="Contoh: 0.500">
                                @error('avg_turbidity_ntu')
                                    <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        {{-- Row 7: Salinitas --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="avg_salinity" class="block text-sm font-medium text-gray-700 mb-2">
                                    Rata-rata Salinitas <span class="text-xs text-gray-400">(opsional)</span>
                                </label>
                                <input wire:model.defer="avg_salinity" type="number" step="0.001" min="0"
                                    id="avg_salinity"
                                    class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm
                                           focus:outline-none focus:ring-blue-500 focus:border-blue-500 text-sm"
                                    placeholder="Contoh: 0.800">
                                @error('avg_salinity')
                                    <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        {{-- Notes --}}
                        <div>
                            <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">
                                Catatan (opsional)
                            </label>
                            <textarea wire:model.defer="notes" id="notes" rows="3"
                                class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm
                                       focus:outline-none focus:ring-blue-500 focus:border-blue-500 text-sm"
                                placeholder="Catatan tambahan, misal kondisi alat ukur, koreksi sampling, atau observasi khusus..."></textarea>
                            @error('notes')
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
                                {{ $isEditing ? 'Update IPC' : 'Simpan IPC' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>
