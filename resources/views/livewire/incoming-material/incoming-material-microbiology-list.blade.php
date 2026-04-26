<div class="bg-white shadow-xl rounded-2xl border border-gray-200 overflow-hidden">
    <div class="bg-gradient-to-r from-amber-50 to-lime-50 px-6 py-6 border-b border-gray-200">
        <h2 class="text-2xl font-bold text-gray-900">Mikrobiologi Incoming Material</h2>
        <p class="text-sm text-gray-600 mt-1">
            Khusus uji mikro bahan incoming material. Hasilnya otomatis terbaca di Incoming Material Tahap 2.
        </p>

        <div class="mt-5 grid grid-cols-1 md:grid-cols-4 gap-3">
            <input wire:model.live.debounce.400ms="search" type="text"
                class="md:col-span-2 border border-gray-300 rounded-xl px-4 py-3 text-sm"
                placeholder="Cari material, supplier, batch...">
            <select wire:model.live="status" class="border border-gray-300 rounded-xl px-4 py-3 text-sm bg-white">
                <option value="pending">Belum selesai</option>
                <option value="completed">Selesai</option>
                <option value="all">Semua</option>
            </select>
            <select wire:model.live="perPage" class="border border-gray-300 rounded-xl px-4 py-3 text-sm bg-white">
                <option value="10">10 per halaman</option>
                <option value="25">25 per halaman</option>
                <option value="50">50 per halaman</option>
            </select>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="bg-gray-50 text-xs uppercase text-gray-600">
                <tr>
                    <th class="px-6 py-4 text-left">Tanggal</th>
                    <th class="px-6 py-4 text-left">Material</th>
                    <th class="px-6 py-4 text-left">Supplier / Batch</th>
                    <th class="px-6 py-4 text-left">Status Mikro</th>
                    <th class="px-6 py-4 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($data as $row)
                    @php $micro = $row->microbiologyTest; @endphp
                    <tr class="hover:bg-amber-50/40">
                        <td class="px-6 py-4">{{ $row->date?->format('d M Y') ?? '-' }}</td>
                        <td class="px-6 py-4">
                            <div class="font-semibold text-gray-900">{{ $row->material_name }}</div>
                            <div class="text-xs text-gray-500">{{ $row->item?->category === 'tea' ? 'Bahan Baku Teh' : ($row->item?->category ?? '-') }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <div>{{ $row->supplier }}</div>
                            <div class="font-mono text-xs text-gray-500">{{ $row->batch_number ?: '-' }}</div>
                        </td>
                        <td class="px-6 py-4">
                            @if ($micro?->status === 'COMPLETED')
                                <span class="px-2.5 py-1 rounded-full {{ $micro->result === 'PASS' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }} text-xs font-semibold">
                                    {{ $micro->result }}
                                </span>
                            @else
                                <span class="px-2.5 py-1 rounded-full bg-yellow-100 text-yellow-700 text-xs font-semibold">
                                    Menunggu hasil
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right">
                            @permission('microbiology.edit')
                                <button wire:click="openForm({{ $row->id }})"
                                    class="px-3 py-2 rounded-lg bg-amber-50 text-amber-700 text-xs font-semibold">
                                    {{ $micro ? 'Edit Hasil' : 'Isi Hasil' }}
                                </button>
                            @endpermission
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-14 text-center text-gray-500">
                            Tidak ada incoming material yang wajib uji mikro.
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
