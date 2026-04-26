<div class="bg-white shadow-xl rounded-2xl border border-gray-200 overflow-hidden">
    <div class="bg-gradient-to-r from-cyan-50 to-emerald-50 px-6 py-6 border-b border-gray-200">
        <h2 class="text-2xl font-bold text-gray-900">Incoming Material Tahap 2</h2>
        <p class="text-sm text-gray-600 mt-1">
            Data tahap 2 otomatis mengambil Incoming Material Tahap 1 yang sudah ACCEPTED.
        </p>

        <div class="mt-5 grid grid-cols-1 md:grid-cols-4 gap-3">
            <input wire:model.live.debounce.400ms="search" type="text"
                class="md:col-span-2 border border-gray-300 rounded-xl px-4 py-3 text-sm"
                placeholder="Cari material, supplier, batch...">
            <select wire:model.live="status" class="border border-gray-300 rounded-xl px-4 py-3 text-sm bg-white">
                <option value="ready">Belum Tahap 2</option>
                <option value="checked">Sudah Tahap 2</option>
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
                    <th class="px-6 py-4 text-left">Mikrobiologi</th>
                    <th class="px-6 py-4 text-left">Tahap 2</th>
                    <th class="px-6 py-4 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($data as $row)
                    @php
                        $micro = $row->microbiologyTest;
                        $stage2 = $row->stage2Check;
                        $needsMicro = $row->needsMicroTest();
                    @endphp
                    <tr class="hover:bg-cyan-50/40">
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
                            @if (! $needsMicro)
                                <span class="px-2.5 py-1 rounded-full bg-gray-100 text-gray-700 text-xs font-semibold">Tidak wajib</span>
                            @elseif ($micro?->status === 'COMPLETED')
                                <span class="px-2.5 py-1 rounded-full {{ $micro->result === 'PASS' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }} text-xs font-semibold">
                                    {{ $micro->result }}
                                </span>
                            @else
                                <span class="px-2.5 py-1 rounded-full bg-yellow-100 text-yellow-700 text-xs font-semibold">Menunggu mikro</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            @if ($stage2)
                                <span class="px-2.5 py-1 rounded-full {{ $stage2->final_decision === 'ACCEPTED' ? 'bg-green-100 text-green-700' : ($stage2->final_decision === 'REJECTED' ? 'bg-red-100 text-red-700' : 'bg-yellow-100 text-yellow-700') }} text-xs font-semibold">
                                    {{ $stage2->final_decision }}
                                </span>
                            @else
                                <span class="px-2.5 py-1 rounded-full bg-slate-100 text-slate-700 text-xs font-semibold">Belum diisi</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right">
                            @permission('incoming_material.edit')
                                <button wire:click="openForm({{ $row->id }})"
                                    class="px-3 py-2 rounded-lg bg-emerald-50 text-emerald-700 text-xs font-semibold">
                                    {{ $stage2 ? 'Edit Tahap 2' : 'Isi Tahap 2' }}
                                </button>
                            @endpermission
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-14 text-center text-gray-500">
                            Tidak ada data tahap 1 yang siap untuk tahap 2.
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
