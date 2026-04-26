<div>
    @if ($showModal && $material)
        <div class="fixed inset-0 z-50 bg-black/40 overflow-y-auto flex items-start justify-center px-4 py-8"
            wire:click.self="closeModal">
            <div class="w-full max-w-5xl bg-white rounded-2xl shadow-xl border border-gray-100">
                <div class="px-6 py-5 border-b bg-cyan-50 rounded-t-2xl">
                    <h3 class="text-lg font-bold text-gray-900">Form Incoming Material Tahap 2</h3>
                    <p class="text-xs text-gray-600 mt-1">
                        {{ $material->material_name }} / {{ $material->supplier }} / Batch {{ $material->batch_number ?: '-' }}
                    </p>
                </div>

                <form wire:submit.prevent="save" class="p-6 space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 text-sm">
                        <div class="rounded-xl border p-4 bg-gray-50">
                            <div class="text-xs text-gray-500">Kategori</div>
                            <div class="font-semibold">{{ $material->item?->category === 'tea' ? 'Bahan Baku Teh' : ($material->item?->category ?? '-') }}</div>
                        </div>
                        <div class="rounded-xl border p-4 bg-gray-50">
                            <div class="text-xs text-gray-500">Qty</div>
                            <div class="font-semibold">{{ $material->quantity }} {{ $material->quantity_unit }}</div>
                        </div>
                        <div class="rounded-xl border p-4 bg-gray-50">
                            <div class="text-xs text-gray-500">Mikrobiologi</div>
                            <div class="font-semibold">{{ str_replace('_', ' ', $microbiology_result) }}</div>
                        </div>
                        <div class="rounded-xl border p-4 bg-gray-50">
                            <div class="text-xs text-gray-500">Keputusan Akhir</div>
                            <div class="font-semibold">{{ $final_decision }}</div>
                        </div>
                    </div>

                    <div class="overflow-x-auto border rounded-xl">
                        <table class="min-w-full text-sm">
                            <thead class="bg-gray-50 text-xs uppercase text-gray-600">
                                <tr>
                                    <th class="px-4 py-3 text-left">Parameter Tahap 2</th>
                                    <th class="px-4 py-3 text-left">Hasil</th>
                                    <th class="px-4 py-3 text-left">Catatan</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @forelse ($fieldResults as $index => $value)
                                    <tr>
                                        <td class="px-4 py-3 font-medium text-gray-900">
                                            {{ $value['field'] ?? '-' }}
                                        </td>
                                        <td class="px-4 py-3">
                                            <select wire:model.live="fieldResults.{{ $index }}.result"
                                                class="border rounded-lg p-2 text-sm bg-white">
                                                <option value="">-- Pilih --</option>
                                                <option value="OK">OK</option>
                                                <option value="NOT_OK">NOT OK</option>
                                            </select>
                                        </td>
                                        <td class="px-4 py-3">
                                            <input wire:model.defer="fieldResults.{{ $index }}.note" type="text"
                                                class="w-full border rounded-lg p-2 text-sm"
                                                placeholder="Catatan opsional">
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="px-4 py-8 text-center text-gray-500">
                                            Parameter Tahap 2 belum diatur di master barang.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div>
                        <label class="text-sm font-medium">Catatan Tahap 2</label>
                        <textarea wire:model.defer="notes" rows="3" class="w-full mt-1 border rounded-lg p-2 text-sm"></textarea>
                    </div>

                    <div class="flex justify-end gap-3 border-t pt-5">
                        <button type="button" wire:click="closeModal"
                            class="px-4 py-2 rounded-lg bg-gray-100 text-sm font-semibold text-gray-700">Batal</button>
                        <button type="submit"
                            class="px-4 py-2 rounded-lg bg-cyan-600 text-sm font-semibold text-white hover:bg-cyan-700">
                            Simpan Tahap 2
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
