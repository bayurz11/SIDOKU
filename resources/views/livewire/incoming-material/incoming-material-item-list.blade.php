<div class="bg-white shadow-xl rounded-2xl border border-gray-200 overflow-hidden">
    <div class="bg-gradient-to-r from-emerald-50 to-teal-50 px-6 py-6 border-b border-gray-200">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-gray-900">Master Nama Barang</h2>
                <p class="text-sm text-gray-600 mt-1">Sumber pilihan nama barang untuk Incoming Material Tahap 1.</p>
            </div>

            @permission('incoming_material.create')
                <button wire:click="$dispatch('openIncomingMaterialItemForm')"
                    class="px-5 py-3 rounded-xl bg-emerald-600 text-white text-sm font-semibold hover:bg-emerald-700">
                    + Tambah Barang
                </button>
            @endpermission
        </div>

        <div class="mt-5 grid grid-cols-1 md:grid-cols-3 gap-3">
            <input wire:model.live.debounce.400ms="search" type="text"
                class="md:col-span-2 border border-gray-300 rounded-xl px-4 py-3 text-sm"
                placeholder="Cari nama, kategori, satuan, atau deskripsi...">
            <label class="flex items-center gap-2 bg-white border border-gray-300 rounded-xl px-4 py-3 text-sm">
                <input wire:model.live="showInactive" type="checkbox" class="rounded border-gray-300">
                Tampilkan nonaktif
            </label>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="bg-gray-50 text-xs uppercase text-gray-600">
                <tr>
                    <th class="px-6 py-4 text-left">Nama Barang</th>
                    <th class="px-6 py-4 text-left">Kategori</th>
                    <th class="px-6 py-4 text-left">Uji Mikro</th>
                    <th class="px-6 py-4 text-left">Form Tahap 2</th>
                    <th class="px-6 py-4 text-left">Status</th>
                    <th class="px-6 py-4 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($data as $item)
                    <tr class="hover:bg-emerald-50/40">
                        <td class="px-6 py-4 font-semibold text-gray-900">
                            {{ $item->name }}
                            <div class="text-xs text-gray-500">{{ $item->description ?: '-' }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-700">
                                {{ $item->category === 'tea' ? 'Bahan Baku Teh' : ucfirst($item->category) }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            {{ $item->requires_microbiology ? 'Wajib' : 'Tidak wajib' }}
                        </td>
                        <td class="px-6 py-4 text-xs text-gray-600">
                            {{ count($item->stage2FieldList()) }} parameter
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-2.5 py-1 rounded-full text-xs font-semibold {{ $item->is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                {{ $item->is_active ? 'Aktif' : 'Nonaktif' }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex justify-end gap-2">
                                @permission('incoming_material.edit')
                                    <button wire:click="$dispatch('openIncomingMaterialItemForm', { id: {{ $item->id }} })"
                                        class="px-3 py-2 rounded-lg bg-blue-50 text-blue-700 text-xs font-semibold">Edit</button>
                                    <button wire:click="toggleStatus({{ $item->id }})"
                                        class="px-3 py-2 rounded-lg bg-yellow-50 text-yellow-700 text-xs font-semibold">
                                        {{ $item->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                    </button>
                                @endpermission
                                @permission('incoming_material.delete')
                                    <button wire:click="delete({{ $item->id }})"
                                        class="px-3 py-2 rounded-lg bg-red-50 text-red-700 text-xs font-semibold">Hapus</button>
                                @endpermission
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-14 text-center text-gray-500">
                            Belum ada master nama barang.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="px-6 py-4 bg-gray-50 border-t">
        {{ $data->links() }}
    </div>
</div>
