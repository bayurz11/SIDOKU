@php
    use Illuminate\Support\Str;
@endphp

<div class="bg-white shadow-xl rounded-2xl border border-gray-200 overflow-hidden">
    {{-- HEADER --}}
    <div class="bg-gradient-to-r from-blue-50 via-green-50 to-lime-50 px-6 py-6 border-b border-gray-200">
        <div class="flex justify-between items-center">
            <div class="flex items-center space-x-4">
                <div
                    class="w-12 h-12 bg-gradient-to-br from-blue-500 to-purple-600 rounded-xl flex items-center justify-center shadow-lg">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25ZM6.75 12h.008v.008H6.75V12Zm0 3h.008v.008H6.75V15Zm0 3h.008v.008H6.75V18Z" />
                    </svg>

                </div>
                <div>
                    <h2 class="text-2xl font-bold text-gray-900">Document Management</h2>
                    <p class="text-sm text-gray-600 mt-1">
                        Kelola daftar dokumen (DOC, SOP, WI, FORM) beserta status, departemen, dan daftar induk yang
                        bisa di-import dari Excel.
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-2">
                @permission('documents.import')
                    <button wire:click="$dispatch('openDocumentImportForm')"
                        class="group bg-gradient-to-r from-green-600 to-green-600 hover:from-green-700 hover:to-green-700 text-white px-6 py-3 rounded-xl text-sm font-semibold inline-flex items-center shadow-lg hover:shadow-xl transition-all duration-300 transform hover:scale-105">
                        <svg class="w-5 h-5 mr-2 group-hover:rotate-90 transition-transform duration-300"
                            xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="m9 12.75 3 3m0 0 3-3m-3 3v-7.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                        </svg>
                        Import
                    </button>
                @endpermission


                {{-- Add Document --}}
                @permission('documents.create')
                    <button wire:click="$dispatch('openDocumentForm')"
                        class="group bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white px-6 py-3 rounded-xl text-sm font-semibold flex items-center shadow-lg hover:shadow-xl transition-all duration-300 transform hover:scale-105">
                        <svg class="w-5 h-5 mr-2 group-hover:rotate-90 transition-transform duration-300" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        Add
                    </button>
                @endpermission
            </div>
        </div>

        {{-- FILTERS --}}
        <div class="mt-6 space-y-4">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                {{-- Search --}}
                <div class="flex-1">
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </div>
                        <input wire:model.live="search" type="text" placeholder="Cari nomor dokumen atau judul..."
                            class="block w-full pl-12 pr-4 py-3 border border-gray-300 rounded-xl bg-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200">
                    </div>
                </div>

                {{-- Per page --}}
                <div>
                    <select wire:model.live="perPage"
                        class="px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white text-sm font-medium transition-all duration-200">
                        <option value="10">10 per halaman</option>
                        <option value="25">25 per halaman</option>
                        <option value="50">50 per halaman</option>
                    </select>
                </div>
            </div>

            {{-- Dropdown filter type / dept / status --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Document Type</label>
                    <select wire:model.live="filterDocumentType"
                        class="block w-full px-3 py-2 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                        <option value="">Semua Type</option>
                        @foreach ($documentTypes as $type)
                            <option value="{{ $type->id }}">{{ $type->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Department</label>
                    <select wire:model.live="filterDepartment"
                        class="block w-full px-3 py-2 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                        <option value="">Semua Department</option>
                        @foreach ($departments as $dept)
                            <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Status</label>
                    <select wire:model.live="filterStatus"
                        class="block w-full px-3 py-2 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                        <option value="">Semua Status</option>
                        @foreach ($statuses as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
    </div>

    {{-- TABLE --}}
    <div class="overflow-x-auto">
        <table class="min-w-full">
            <thead class="bg-gradient-to-r from-gray-50 to-gray-100">
                <tr>
                    <th wire:click="sortBy('document_code')"
                        class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider cursor-pointer hover:bg-gray-200 rounded-tl-xl">
                        No. Dokumen
                    </th>
                    <th wire:click="sortBy('level')"
                        class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider cursor-pointer hover:bg-gray-200">
                        Level
                    </th>
                    <th wire:click="sortBy('title')"
                        class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider cursor-pointer hover:bg-gray-200">
                        Judul
                    </th>
                    <th wire:click="sortBy('document_type_id')"
                        class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider cursor-pointer hover:bg-gray-200">
                        Type
                    </th>
                    <th wire:click="sortBy('department_id')"
                        class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider cursor-pointer hover:bg-gray-200">
                        Dept
                    </th>
                    <th wire:click="sortBy('status')"
                        class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider cursor-pointer hover:bg-gray-200">
                        Status
                    </th>
                    <th wire:click="sortBy('effective_date')"
                        class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider cursor-pointer hover:bg-gray-200">
                        Efektif
                    </th>
                    <th
                        class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider rounded-tr-xl">
                        Aksi
                    </th>
                </tr>
            </thead>

            <tbody class="bg-white divide-y divide-gray-100">
                @forelse($data as $doc)
                    <tr class="hover:bg-gradient-to-r hover:from-blue-50 hover:to-green-50 transition-all duration-300">
                        {{-- No Dokumen --}}
                        <td class="px-6 py-5 whitespace-nowrap font-mono text-sm text-gray-900">
                            {{ $doc->document_code }}
                            @if (!$doc->is_active)
                                <span
                                    class="ml-2 inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-gray-200 text-gray-700">
                                    inactive
                                </span>
                            @endif
                        </td>

                        {{-- Level --}}
                        <td class="px-6 py-5 whitespace-nowrap font-mono text-sm text-gray-900">
                            {{ $doc->level }}
                        </td>

                        {{-- Judul --}}
                        <td class="px-6 py-5 whitespace-nowrap text-sm text-gray-800">
                            <div class="font-semibold">
                                {{ Str::limit($doc->title, 50) }}
                            </div>
                            @if ($doc->summary)
                                <div class="text-xs text-gray-500">
                                    {{ Str::limit($doc->summary, 50) }}
                                </div>
                            @endif
                        </td>

                        {{-- Tipe --}}
                        <td class="px-6 py-5 whitespace-nowrap text-sm text-gray-700">
                            {{ $doc->documentType->name ?? '-' }}
                        </td>

                        {{-- Dept --}}
                        <td class="px-6 py-5 whitespace-nowrap text-sm text-gray-700">
                            {{ $doc->department->name ?? '-' }}
                        </td>

                        {{-- Status --}}
                        <td class="px-6 py-5 whitespace-nowrap">
                            @php
                                $status = $doc->status;
                                $map = [
                                    'draft' => 'bg-gray-100 text-gray-800 border-gray-200',
                                    'in_review' => 'bg-yellow-100 text-yellow-800 border-yellow-200',
                                    'approved' => 'bg-green-100 text-green-800 border-green-200',
                                    'obsolete' => 'bg-red-100 text-red-800 border-red-200',
                                ];
                                $cls = $map[$status] ?? 'bg-gray-100 text-gray-800 border-gray-200';
                            @endphp
                            <span
                                class="inline-flex items-center px-3 py-1.5 rounded-xl text-xs font-semibold border {{ $cls }}">
                                <span
                                    class="w-2 h-2 rounded-full mr-2
                                    {{ $status === 'approved' ? 'bg-green-500' : ($status === 'obsolete' ? 'bg-red-500' : 'bg-yellow-500') }}">
                                </span>
                                {{ ucfirst(str_replace('_', ' ', $status)) }}
                            </span>
                        </td>

                        {{-- Efektif --}}
                        <td class="px-6 py-5 whitespace-nowrap text-sm text-gray-600">
                            @if ($doc->effective_date)
                                <div class="font-medium">
                                    {{ $doc->effective_date->format('d M Y') }}
                                </div>
                                <div class="text-xs text-gray-500">
                                    {{ $doc->effective_date->diffForHumans() }}
                                </div>
                            @else
                                <span class="text-xs text-gray-400 italic">Belum di-set</span>
                            @endif
                        </td>

                        {{-- Aksi --}}
                        <td class="px-6 py-5 whitespace-nowrap text-sm font-medium">
                            <div class="flex items-center gap-2">
                                @permission('documents.view')
                                    <button wire:click="$dispatch('openDocumentDetail', { id: {{ $doc->id }} })"
                                        class="group inline-flex items-center px-4 py-2.5 rounded-xl text-sm font-semibold
           text-white bg-gradient-to-r from-indigo-600 to-indigo-600
           hover:from-indigo-700 hover:to-indigo-700
           shadow-md hover:shadow-lg transition-all duration-300 hover:scale-[1.03]">

                                        <svg class="w-5 h-5 mr-2 group-hover:rotate-12 transition-transform duration-300"
                                            xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                            stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M4.5 12a7.5 7.5 0 0 1 15 0m-15 0a7.5 7.5 0 0 0 15 0M4.5 12H9m6 0h4.5" />
                                        </svg>

                                        Detail
                                    </button>
                                @endpermission

                                @permission('documents.edit')
                                    {{-- Edit --}}
                                    <button wire:click="$dispatch('openDocumentForm', { id: {{ $doc->id }} })"
                                        class="inline-flex items-center px-3 py-2 text-xs font-semibold text-blue-600 bg-blue-50 rounded-lg hover:bg-blue-100 hover:text-blue-700 transition-all duration-200">
                                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414
                                                                                                                                                                                                                                                                                                                            a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                                            </path>
                                        </svg>
                                        Edit
                                    </button>
                                @endpermission

                                @permission('documents.edit')
                                    {{-- Mark obsolete --}}
                                    @if ($doc->status !== 'obsolete')
                                        <button wire:click="markObsolete({{ $doc->id }})"
                                            class="inline-flex items-center px-3 py-2 text-xs font-semibold text-pink-700 bg-red-50 rounded-lg hover:bg-red-100 hover:text-pink-800 transition-all duration-200">
                                            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M12 8v4m0 4h.01M4.93 4.93l14.14 14.14M3 12a9 9 0 1118 0 9 9 0 01-18 0z" />
                                            </svg>
                                            Obsolete
                                        </button>
                                    @endif
                                @endpermission

                                @permission('documents.delete')
                                    {{-- Delete --}}
                                    <button wire:click="delete({{ $doc->id }})"
                                        class="inline-flex items-center px-3 py-2 text-xs font-semibold text-red-600 bg-red-50 rounded-lg hover:bg-red-100 hover:text-red-700 transition-all duration-200">
                                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6
                                                                                                                                                                                                                                                                                                                            m1-10V4a1 1 0 00-1-1H9a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                        Delete
                                    </button>
                                @endpermission
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-16 text-center">
                            <div class="flex flex-col items-center justify-center">
                                <div
                                    class="w-20 h-20 bg-gradient-to-br from-gray-100 to-gray-200 rounded-2xl flex items-center justify-center mb-6 shadow-inner">
                                    <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M3 7.5A2.25 2.25 0 015.25 5.25h13.5A2.25 2.25 0 0121 7.5v9a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 16.5v-9zM7.5 9h9m-9 3.75h4.5" />
                                    </svg>
                                </div>
                                <h3 class="text-xl font-semibold text-gray-900 mb-2">Belum ada dokumen</h3>
                                <p class="text-gray-500 mb-6 max-w-sm text-center">
                                    @if ($search || $filterDocumentType || $filterDepartment || $filterStatus)
                                        Coba ubah kata kunci atau filter pencarian.
                                    @else
                                        Tambahkan dokumen pertama untuk memulai pengelolaan QMS.
                                    @endif
                                </p>

                                @if (!$search && !$filterDocumentType && !$filterDepartment && !$filterStatus)
                                    @permission('documents.create')
                                        <button wire:click="$dispatch('openDocumentForm')"
                                            class="group bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white px-6 py-3 rounded-xl text-sm font-semibold flex items-center shadow-lg hover:shadow-xl transition-all duration-300 transform hover:scale-105">
                                            <svg class="w-5 h-5 mr-2 group-hover:rotate-90 transition-transform duration-300"
                                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                            </svg>
                                            Tambah Document
                                        </button>
                                    @endpermission
                                @else
                                    <button
                                        wire:click="$set('search', ''); $set('filterDocumentType', null); $set('filterDepartment', null); $set('filterStatus', null)"
                                        class="inline-flex items-center px-6 py-3 bg-gray-100 text-gray-700 font-semibold rounded-xl hover:bg-gray-200 transition-all duration-300">
                                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0
                                                 0a8.003 8.003 0 01-15.357-2m15.357
                                                 2H15" />
                                        </svg>
                                        Hapus Filter
                                    </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- FOOTER --}}
    <div class="px-6 py-4 bg-gradient-to-r from-gray-50 to-blue-50 border-t border-gray-200 rounded-b-2xl">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
            <div class="text-sm text-gray-600">
                Menampilkan
                <span class="font-medium">{{ $data->firstItem() ?? 0 }}</span>
                sampai
                <span class="font-medium">{{ $data->lastItem() ?? 0 }}</span>
                dari
                <span class="font-medium">{{ $data->total() }}</span>
                dokumen
            </div>
            <div class="flex-1 flex justify-center md:justify-end">
                {{ $data->links() }}
            </div>
        </div>
    </div>
</div>
