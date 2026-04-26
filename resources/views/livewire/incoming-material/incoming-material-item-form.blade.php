<div>
    @if ($showModal)
        <div class="fixed inset-0 z-50 bg-black/40 overflow-y-auto flex items-start justify-center px-4 py-8"
            wire:click.self="closeModal">
            <div class="w-full max-w-3xl bg-white rounded-2xl shadow-xl border border-gray-100">
                <div class="px-6 py-5 border-b">
                    <h3 class="text-lg font-bold text-gray-900">
                        {{ $isEditing ? 'Edit Master Nama Barang' : 'Tambah Master Nama Barang' }}
                    </h3>
                    <p class="text-xs text-gray-500 mt-1">
                        Field Tahap 2 diisi satu parameter per baris. Kategori teh otomatis wajib uji mikrobiologi.
                    </p>
                </div>

                <form wire:submit.prevent="save" class="p-6 space-y-5">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="text-sm font-medium">Nama Barang <span class="text-red-500">*</span></label>
                            <input wire:model.defer="name" type="text" class="w-full mt-1 border rounded-lg p-2 text-sm">
                            @error('name')
                                <div class="text-xs text-red-500 mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div>
                            <label class="text-sm font-medium">Kategori <span class="text-red-500">*</span></label>
                            <select wire:model.live="category" class="w-full mt-1 border rounded-lg p-2 text-sm bg-white">
                                <option value="general">General</option>
                                <option value="tea">Bahan Baku Teh</option>
                            </select>
                        </div>

                        <div>
                            <label class="text-sm font-medium">Satuan Default</label>
                            <input wire:model.defer="default_unit" type="text" placeholder="KG, SAK, LITER..."
                                class="w-full mt-1 border rounded-lg p-2 text-sm">
                        </div>

                        <div class="flex items-center gap-6 pt-6">
                            <label class="inline-flex items-center gap-2 text-sm">
                                <input wire:model="requires_microbiology" type="checkbox" class="rounded border-gray-300">
                                Wajib uji mikrobiologi
                            </label>
                            <label class="inline-flex items-center gap-2 text-sm">
                                <input wire:model="is_active" type="checkbox" class="rounded border-gray-300">
                                Aktif
                            </label>
                        </div>
                    </div>

                    <div>
                        <label class="text-sm font-medium">Parameter Form Tahap 2</label>
                        <textarea wire:model.defer="stage2_fields_text" rows="6" class="w-full mt-1 border rounded-lg p-2 text-sm"
                            placeholder="Contoh:&#10;Kondisi kemasan&#10;Warna daun teh&#10;Aroma&#10;Benda asing&#10;Kesesuaian COA"></textarea>
                    </div>

                    <div>
                        <label class="text-sm font-medium">Deskripsi</label>
                        <textarea wire:model.defer="description" rows="3" class="w-full mt-1 border rounded-lg p-2 text-sm"></textarea>
                    </div>

                    <div class="flex justify-end gap-3 border-t pt-5">
                        <button type="button" wire:click="closeModal"
                            class="px-4 py-2 rounded-lg bg-gray-100 text-sm font-semibold text-gray-700">Batal</button>
                        <button type="submit"
                            class="px-4 py-2 rounded-lg bg-emerald-600 text-sm font-semibold text-white hover:bg-emerald-700">
                            Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
