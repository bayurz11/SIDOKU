@php
    use Illuminate\Support\Str;
@endphp

<div class="bg-white shadow-xl rounded-2xl border border-gray-200 overflow-hidden">
    <div class="bg-gradient-to-r from-slate-50 via-emerald-50 to-cyan-50 px-6 py-6 border-b border-gray-200">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            <div class="flex items-center space-x-4">
                <div class="w-12 h-12 bg-gradient-to-br from-emerald-500 to-cyan-600 rounded-xl flex items-center justify-center shadow-lg">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16.862 4.487 18.55 2.8a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                    </svg>
                </div>
                <div>
                    <h2 class="text-2xl font-bold text-gray-900">Document Revisions</h2>
                    <p class="text-sm text-gray-600 mt-1">
                        Histori revisi yang sudah disahkan dari workflow approval dokumen.
                    </p>
                </div>
            </div>

            <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-semibold bg-white text-gray-700 border border-gray-200 shadow-sm">
                Total: <span class="ml-1 font-mono">{{ $data->total() }}</span>
            </span>
        </div>

        <div class="mt-6 space-y-4">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                <div class="flex-1">
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 1 1-14 0 7 7 0 0 1 14 0z" />
                            </svg>
                        </div>
                        <input wire:model.live="search" type="text" placeholder="Cari nomor dokumen, judul, atau catatan revisi..."
                            class="block w-full pl-12 pr-4 py-3 border border-gray-300 rounded-xl bg-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-all duration-200">
                    </div>
                </div>

                <select wire:model.live="perPage"
                    class="px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500 bg-white text-sm font-medium transition-all duration-200">
                    <option value="10">10 per halaman</option>
                    <option value="25">25 per halaman</option>
                    <option value="50">50 per halaman</option>
                    <option value="100">100 per halaman</option>
                </select>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Document Type</label>
                    <select wire:model.live="filterDocumentType"
                        class="block w-full px-3 py-2 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 bg-white">
                        <option value="">Semua Type</option>
                        @foreach ($documentTypes as $type)
                            <option value="{{ $type->id }}">{{ $type->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Department</label>
                    <select wire:model.live="filterDepartment"
                        class="block w-full px-3 py-2 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 bg-white">
                        <option value="">Semua Department</option>
                        @foreach ($departments as $dept)
                            <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex items-end justify-end">
                    <button type="button"
                        wire:click="$set('search', ''); $set('filterDocumentType', null); $set('filterDepartment', null)"
                        class="inline-flex items-center px-4 py-2 bg-white text-gray-700 font-semibold rounded-xl border border-gray-200 hover:bg-gray-50 transition-all duration-200">
                        Reset Filter
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full">
            <thead class="bg-gradient-to-r from-gray-50 to-gray-100">
                <tr>
                    <th wire:click="sortBy('document_code')" class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider cursor-pointer hover:bg-gray-200 rounded-tl-xl">No. Dokumen</th>
                    <th wire:click="sortBy('title')" class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider cursor-pointer hover:bg-gray-200">Judul</th>
                    <th wire:click="sortBy('document_type_id')" class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider cursor-pointer hover:bg-gray-200">Type</th>
                    <th wire:click="sortBy('department_id')" class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider cursor-pointer hover:bg-gray-200">Dept</th>
                    <th wire:click="sortBy('revision_no')" class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider cursor-pointer hover:bg-gray-200">Rev</th>
                    <th wire:click="sortBy('changed_at')" class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider cursor-pointer hover:bg-gray-200">Tanggal</th>
                    <th class="px-6 py-4 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider rounded-tr-xl">Aksi</th>
                </tr>
            </thead>

            <tbody class="bg-white divide-y divide-gray-100">
                @forelse($data as $revision)
                    @php
                        $doc = $revision->document;
                    @endphp
                    <tr class="hover:bg-gradient-to-r hover:from-emerald-50 hover:to-cyan-50 transition-all duration-300">
                        <td class="px-6 py-5 whitespace-nowrap font-mono text-sm text-gray-900">{{ $doc->document_code ?? '-' }}</td>
                        <td class="px-6 py-5 text-sm text-gray-800">
                            <div class="font-semibold">{{ Str::limit($doc->title ?? '-', 48) }}</div>
                            @if ($revision->change_note)
                                <div class="text-xs text-gray-500">{{ Str::limit($revision->change_note, 70) }}</div>
                            @endif
                        </td>
                        <td class="px-6 py-5 whitespace-nowrap text-sm text-gray-700">{{ $doc->documentType->name ?? '-' }}</td>
                        <td class="px-6 py-5 whitespace-nowrap text-sm text-gray-700">{{ $doc->department->name ?? '-' }}</td>
                        <td class="px-6 py-5 whitespace-nowrap">
                            <span class="inline-flex items-center px-3 py-1.5 rounded-xl text-xs font-semibold border bg-emerald-50 text-emerald-700 border-emerald-100">
                                Rev {{ $revision->revision_no }}
                            </span>
                        </td>
                        <td class="px-6 py-5 whitespace-nowrap text-sm text-gray-600">
                            <div class="font-medium">{{ optional($revision->changed_at)->format('d M Y H:i') ?? '-' }}</div>
                            <div class="text-xs text-gray-500">{{ $revision->changedBy->name ?? '-' }}</div>
                        </td>
                        <td class="px-6 py-5 whitespace-nowrap text-right text-sm font-medium">
                            @if ($revision->file_path)
                                <a href="{{ asset('storage/' . ltrim($revision->file_path, '/')) }}" target="_blank"
                                    class="inline-flex items-center px-3 py-2 text-xs font-semibold text-emerald-700 bg-emerald-50 rounded-lg hover:bg-emerald-100 hover:text-emerald-800 transition-all duration-200">
                                    Lihat File
                                </a>
                            @else
                                <span class="text-xs text-gray-400">Tidak ada file</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-16 text-center">
                            <div class="flex flex-col items-center justify-center">
                                <div class="w-20 h-20 bg-gradient-to-br from-gray-100 to-gray-200 rounded-2xl flex items-center justify-center mb-6 shadow-inner">
                                    <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.5 14.25v-2.625A3.375 3.375 0 0 0 16.125 8.25h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5A3.375 3.375 0 0 0 10.125 2.25H8.25m0 12.75h7.5m-7.5 3H12m-6.75 3h13.5A2.25 2.25 0 0 0 21 18.75V11.25a9 9 0 0 0-9-9H5.25A2.25 2.25 0 0 0 3 4.5v14.25A2.25 2.25 0 0 0 5.25 21Z" />
                                    </svg>
                                </div>
                                <h3 class="text-xl font-semibold text-gray-900 mb-2">Belum ada histori revisi</h3>
                                <p class="text-gray-500 max-w-sm text-center">
                                    Histori akan muncul setelah dokumen disetujui penuh dari Approval Queue.
                                </p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="px-6 py-4 bg-gradient-to-r from-gray-50 to-emerald-50 border-t border-gray-200 rounded-b-2xl">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
            <div class="text-sm text-gray-600">
                Menampilkan <span class="font-medium">{{ $data->firstItem() ?? 0 }}</span>
                sampai <span class="font-medium">{{ $data->lastItem() ?? 0 }}</span>
                dari <span class="font-medium">{{ $data->total() }}</span> revisi
            </div>
            <div class="flex-1 flex justify-center md:justify-end">
                {{ $data->links('vendor.pagination.custom') }}
            </div>
        </div>
    </div>
</div>
