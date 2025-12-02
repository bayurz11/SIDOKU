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
                                Input hasil pemeriksaan IPC (kadar air dan berat) per Line dan Produk.
                            </p>
                        </div>

                        <div class="flex flex-col items-end space-y-2">
                            {{-- Info Line/Produk ringkas --}}
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

                            {{-- Sub Line (khusus LINE_TEH) --}}
                            <div>
                                <label for="sub_line" class="block text-sm font-medium text-gray-700 mb-2">
                                    Sub Line (Teh)
                                </label>
                                <select wire:model.defer="sub_line" id="sub_line"
                                    class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm
                                           focus:outline-none focus:ring-blue-500 focus:border-blue-500 text-sm
                                           @if ($line_group !== 'LINE_TEH') bg-gray-100 @endif"
                                    @if ($line_group !== 'LINE_TEH') disabled @endif>
                                    <option value="">-- Pilih Sub Line --</option>
                                    @foreach ($subLinesTeh as $key => $label)
                                        <option value="{{ $key }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                                @if ($line_group !== 'LINE_TEH')
                                    <p class="mt-1 text-[10px] text-gray-400 italic">
                                        Sub line aktif jika Line Group = Line Teh.
                                    </p>
                                @endif
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

                        {{-- Row 3: Ringkasan Kadar Air & Berat --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            {{-- Kadar Air --}}
                            <div>
                                <label for="avg_moisture_percent" class="block text-sm font-medium text-gray-700 mb-2">
                                    Rata-rata Kadar Air (%) <span class="text-xs text-gray-400">(otomatis / bisa
                                        diubah)</span>
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
                                    Rata-rata Berat (g) <span class="text-xs text-gray-400">(otomatis / bisa
                                        diubah)</span>
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

                        {{-- Row 4: Perhitungan Kadar Air Otomatis (Line Teh & Line Powder) --}}
                        <div class="border border-blue-100 rounded-lg p-4 bg-blue-50/40 space-y-4">
                            <div class="flex items-center justify-between gap-2">
                                <div>
                                    <h4 class="text-sm font-semibold text-gray-800">
                                        Perhitungan Kadar Air Otomatis
                                    </h4>
                                    <p class="text-xs text-gray-500 mt-0.5">
                                        Untuk <span class="font-semibold">Line Teh & Line Powder</span>,
                                        isi data berikut. Sistem akan menghitung kadar air otomatis
                                        sesuai rumus: <span class="font-mono text-[11px]">
                                            (Total − rata-rata P1,P2) / Berat produk × 100
                                        </span>.
                                    </p>
                                </div>
                            </div>

                            {{-- Baris 1: Berat cawan, produk, total --}}
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                {{-- Berat cawan --}}
                                <div>
                                    <label for="cup_weight" class="block text-xs font-medium text-gray-700 mb-1.5">
                                        Berat Cawan (g)
                                    </label>
                                    <input wire:model.live="cup_weight" type="number" step="0.001" min="0"
                                        id="cup_weight"
                                        class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm
                                               focus:outline-none focus:ring-blue-500 focus:border-blue-500 text-sm"
                                        placeholder="Berat cawan kosong">
                                    @error('cup_weight')
                                        <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                                    @enderror
                                </div>

                                {{-- Berat produk --}}
                                <div>
                                    <label for="product_weight"
                                        class="block text-xs font-medium text-gray-700 mb-1.5">
                                        Berat Produk (g)
                                    </label>
                                    <input wire:model.live="product_weight" type="number" step="0.001"
                                        min="0" id="product_weight"
                                        class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm
                                               focus:outline-none focus:ring-blue-500 focus:border-blue-500 text-sm"
                                        placeholder="Berat produk">
                                    @error('product_weight')
                                        <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                                    @enderror
                                </div>

                                {{-- Total cawan + produk (otomatis) --}}
                                <div>
                                    <label for="total_cup_plus_product"
                                        class="block text-xs font-medium text-gray-700 mb-1.5">
                                        Total Cawan + Produk (g)
                                    </label>
                                    <input wire:model.defer="total_cup_plus_product" type="number" step="0.001"
                                        min="0" id="total_cup_plus_product" readonly
                                        class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm
                                               bg-gray-100 text-gray-700 text-sm"
                                        placeholder="Otomatis dari cawan + produk">
                                    @error('total_cup_plus_product')
                                        <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            {{-- Baris 2: Penimbangan 1 & 2 --}}
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                {{-- Penimbangan 1 --}}
                                <div>
                                    <label for="weighing_1" class="block text-xs font-medium text-gray-700 mb-1.5">
                                        Penimbangan 1 (g)
                                    </label>
                                    <input wire:model.live="weighing_1" type="number" step="0.001" min="0"
                                        id="weighing_1"
                                        class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm
                                               focus:outline-none focus:ring-blue-500 focus:border-blue-500 text-sm"
                                        placeholder="Setelah oven 1">
                                    @error('weighing_1')
                                        <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                                    @enderror
                                </div>

                                {{-- Penimbangan 2 --}}
                                <div>
                                    <label for="weighing_2" class="block text-xs font-medium text-gray-700 mb-1.5">
                                        Penimbangan 2 (g)
                                    </label>
                                    <input wire:model.live="weighing_2" type="number" step="0.001" min="0"
                                        id="weighing_2"
                                        class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm
                                               focus:outline-none focus:ring-blue-500 focus:border-blue-500 text-sm"
                                        placeholder="Setelah oven 2">
                                    @error('weighing_2')
                                        <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                                    @enderror
                                </div>

                                {{-- Info hasil (opsional kecil) --}}
                                <div class="flex flex-col justify-center text-xs text-gray-500">
                                    <span>
                                        Nilai <span class="font-semibold">Rata-rata Kadar Air</span> di atas akan
                                        diupdate otomatis jika semua data terisi dan Line termasuk
                                        <span class="font-semibold">Teh / Powder</span>.
                                    </span>
                                </div>
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
                                {{ $isEditing ? 'Update' : 'Simpan' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>
