<div>
    @if ($showModal && $material)
        <div class="fixed inset-0 z-50 bg-black/40 overflow-y-auto flex items-start justify-center px-4 py-8"
            wire:click.self="closeModal">
            <div class="w-full max-w-3xl bg-white rounded-2xl shadow-xl border border-gray-100">
                <div class="px-6 py-5 border-b bg-amber-50 rounded-t-2xl">
                    <h3 class="text-lg font-bold text-gray-900">Hasil Uji Mikrobiologi Incoming Material</h3>
                    <p class="text-xs text-gray-600 mt-1">
                        {{ $material->material_name }} / {{ $material->supplier }} / Batch {{ $material->batch_number ?: '-' }}
                    </p>
                </div>

                <form wire:submit.prevent="save" class="p-6 space-y-5">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="text-sm font-medium">TPC</label>
                            <input wire:model.defer="tpc" type="number" min="0" step="0.01"
                                class="w-full mt-1 border rounded-lg p-2 text-sm">
                        </div>
                        <div>
                            <label class="text-sm font-medium">Yeast & Mold</label>
                            <input wire:model.defer="yeast_mold" type="number" min="0" step="0.01"
                                class="w-full mt-1 border rounded-lg p-2 text-sm">
                        </div>
                        <div>
                            <label class="text-sm font-medium">Coliform</label>
                            <input wire:model.defer="coliform" type="number" min="0" step="0.01"
                                class="w-full mt-1 border rounded-lg p-2 text-sm">
                        </div>
                        <div>
                            <label class="text-sm font-medium">E. coli</label>
                            <input wire:model.defer="e_coli" type="text" placeholder="Negatif / Positif"
                                class="w-full mt-1 border rounded-lg p-2 text-sm">
                        </div>
                        <div>
                            <label class="text-sm font-medium">Salmonella</label>
                            <input wire:model.defer="salmonella" type="text" placeholder="Negatif / Positif"
                                class="w-full mt-1 border rounded-lg p-2 text-sm">
                        </div>
                        <div>
                            <label class="text-sm font-medium">Kesimpulan <span class="text-red-500">*</span></label>
                            <select wire:model.defer="result" class="w-full mt-1 border rounded-lg p-2 text-sm bg-white">
                                <option value="PENDING">Pending</option>
                                <option value="PASS">Pass</option>
                                <option value="FAIL">Fail</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="text-sm font-medium">Catatan Mikro</label>
                        <textarea wire:model.defer="notes" rows="3" class="w-full mt-1 border rounded-lg p-2 text-sm"></textarea>
                    </div>

                    <div class="flex justify-end gap-3 border-t pt-5">
                        <button type="button" wire:click="closeModal"
                            class="px-4 py-2 rounded-lg bg-gray-100 text-sm font-semibold text-gray-700">Batal</button>
                        <button type="submit"
                            class="px-4 py-2 rounded-lg bg-amber-600 text-sm font-semibold text-white hover:bg-amber-700">
                            Simpan Hasil Mikro
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
