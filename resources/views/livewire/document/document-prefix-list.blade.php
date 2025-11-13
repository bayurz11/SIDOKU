@php
    use Illuminate\Support\Str;
@endphp

<div class="bg-white shadow-xl rounded-2xl border border-gray-200 overflow-hidden">
    <!-- HEADER -->
    <div class="bg-gradient-to-r from-blue-50 via-emerald-50 to-lime-50 px-6 py-6 border-b border-gray-200">
        <div class="flex justify-between items-center">
            <div class="flex items-center space-x-4">
                <div
                    class="w-12 h-12 bg-gradient-to-br from-blue-500 to-purple-600 rounded-xl flex items-center justify-center shadow-lg">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 7.5A2.25 2.25 0 015.25 5.25h13.5A2.25 2.25 0 0121 7.5v9a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 16.5v-9zM7.5 9h9m-9 3.75h4.5" />
                    </svg>
                </div>
                <div>
                    <h2 class="text-2xl font-bold text-gray-900">Document Prefix Settings</h2>
                    <p class="text-sm text-gray-600 mt-1">
                        Kelola format dan aturan penomoran dokumen (DOC, SOP, WI, FORM, dll.)
                    </p>
                </div>
            </div>

            @permission('document_prefix_settings.create')
                <button wire:click="$dispatch('openDocumentPrefixForm')"
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

        <!-- FILTERS -->
        <div
            class="mt-6 flex flex-col lg:flex-row lg:items-center lg:justify-between space-y-4 lg:space-y-0 lg:space-x-6">
            <div class="flex-1">
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>
                    <input wire:model.live="search" type="text"
                        placeholder="Cari prefix, format, atau contoh output…"
                        class="block w-full pl-12 pr-4 py-3 border border-gray-300 rounded-xl bg-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200">
                </div>
            </div>

            <div class="flex flex-col sm:flex-row space-y-3 sm:space-y-0 sm:space-x-4">
                <label
                    class="flex items-center px-4 py-3 bg-white border border-gray-300 rounded-xl hover:bg-gray-50 transition-colors duration-200">
                    <input wire:model.live="showInactive" type="checkbox"
                        class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 focus:ring-offset-0">
                    <span class="ml-3 text-sm font-medium text-gray-700">Tampilkan Nonaktif</span>
                </label>

                <select wire:model.live="perPage"
                    class="px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white text-sm font-medium transition-all duration-200">
                    <option value="10">10 per halaman</option>
                    <option value="25">25 per halaman</option>
                    <option value="50">50 per halaman</option>
                </select>
            </div>
        </div>
    </div>

    <!-- TABLE -->
    <div class="overflow-x-auto">
        <table class="min-w-full">
            <thead class="bg-gradient-to-r from-gray-50 to-gray-100">
                <tr>
                    <th wire:click="sortBy('company_prefix')"
                        class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider cursor-pointer hover:bg-gray-200 rounded-tl-xl">
                        Prefix
                    </th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                        Tipe Dokumen
                    </th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                        Departemen
                    </th>
                    <th wire:click="sortBy('format_nomor')"
                        class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider cursor-pointer hover:bg-gray-200">
                        Format Nomor
                    </th>
                    <th wire:click="sortBy('is_active')"
                        class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider cursor-pointer hover:bg-gray-200">
                        Status
                    </th>
                    <th wire:click="sortBy('created_at')"
                        class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider cursor-pointer hover:bg-gray-200">
                        Dibuat
                    </th>
                    <th
                        class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider rounded-tr-xl">
                        Aksi
                    </th>
                </tr>
            </thead>

            <tbody class="bg-white divide-y divide-gray-100">
                @forelse($data as $prefix)
                    <tr
                        class="hover:bg-gradient-to-r hover:from-blue-50 hover:to-emerald-50 transition-all duration-300">
                        <td class="px-6 py-5 whitespace-nowrap font-semibold text-gray-800">
                            {{ $prefix->company_prefix ?? 'PRP' }}
                        </td>
                        <td class="px-6 py-5 whitespace-nowrap text-sm text-gray-700">
                            {{ $prefix->documentType->name ?? '-' }}
                        </td>
                        <td class="px-6 py-5 whitespace-nowrap text-sm text-gray-700">
                            {{ $prefix->department->name ?? '-' }}
                        </td>
                        <td class="px-6 py-5 whitespace-nowrap text-sm text-gray-700">
                            <div
                                class="font-mono text-xs bg-gray-50 px-3 py-2 rounded-lg border border-gray-200 inline-block">
                                {{ Str::limit($prefix->format_nomor, 60) }}
                            </div>
                            @if ($prefix->example_output)
                                <div class="mt-1 text-xs text-gray-500">
                                    Contoh: <span class="font-mono">{{ $prefix->example_output }}</span>
                                </div>
                            @endif
                        </td>
                        <td class="px-6 py-5 whitespace-nowrap">
                            <span
                                class="inline-flex items-center px-3 py-1.5 rounded-xl text-xs font-semibold shadow-sm
                                {{ $prefix->is_active ? 'bg-gradient-to-r from-green-100 to-emerald-100 text-green-800' : 'bg-gradient-to-r from-red-100 to-pink-100 text-red-800' }}">
                                <div
                                    class="w-2 h-2 rounded-full mr-2 {{ $prefix->is_active ? 'bg-green-500 animate-pulse' : 'bg-red-500' }}">
                                </div>
                                {{ $prefix->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td class="px-6 py-5 whitespace-nowrap text-sm text-gray-600">
                            <div class="font-medium">{{ optional($prefix->created_at)->format('d M Y') }}</div>
                            <div class="text-xs">{{ optional($prefix->created_at)->diffForHumans() }}</div>
                        </td>

                        <td class="px-6 py-5 whitespace-nowrap text-sm font-medium">
                            <div class="flex space-x-2">
                                @permission('document_prefix_settings.edit')
                                    <button wire:click="$dispatch('openDocumentPrefixForm', { id: {{ $prefix->id }} })"
                                        class="group/btn inline-flex items-center px-3 py-2 text-xs font-semibold text-blue-600 bg-blue-50 rounded-lg hover:bg-blue-100 hover:text-blue-700 transition-all duration-200 transform hover:scale-105">
                                        <svg class="w-4 h-4 mr-1.5 group-hover/btn:rotate-12 transition-transform duration-200"
                                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                                            </path>
                                        </svg>
                                        Edit
                                    </button>
                                @endpermission

                                @permission('document_prefix_settings.edit')
                                    <button wire:click="toggleStatus({{ $prefix->id }})"
                                        class="group/btn inline-flex items-center px-3 py-2 text-xs font-semibold text-yellow-600 bg-yellow-50 rounded-lg hover:bg-yellow-100 hover:text-yellow-700 transition-all duration-200 transform hover:scale-105">
                                        <svg class="w-4 h-4 mr-1.5 group-hover/btn:rotate-180 transition-transform duration-300"
                                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            @if ($prefix->is_active)
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728L5.636 5.636M5.636 18.364l12.728-12.728" />
                                            @else
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            @endif
                                        </svg>
                                        {{ $prefix->is_active ? 'Deactivate' : 'Activate' }}
                                    </button>
                                @endpermission

                                @permission('document_prefix_settings.delete')
                                    <button wire:click="delete({{ $prefix->id }})"
                                        class="group/btn inline-flex items-center px-3 py-2 text-xs font-semibold text-red-600 bg-red-50 rounded-lg hover:bg-red-100 hover:text-red-700 transition-all duration-200 transform hover:scale-105">
                                        <svg class="w-4 h-4 mr-1.5 group-hover/btn:animate-bounce" fill="none"
                                            stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
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
                                <h3 class="text-xl font-semibold text-gray-900 mb-2">Belum ada prefix dokumen</h3>
                                <p class="text-gray-500 mb-6 max-w-sm text-center">
                                    @if ($search || !$showInactive)
                                        Coba ubah kata kunci atau filter pencarian.
                                    @else
                                        Tambahkan pengaturan prefix pertama untuk dokumen Anda.
                                    @endif
                                </p>

                                @if (!$search)
                                    @permission('document_prefix_settings.create')
                                        <button wire:click="$dispatch('openDocumentPrefixForm')"
                                            class="group bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white px-6 py-3 rounded-xl text-sm font-semibold flex items-center shadow-lg hover:shadow-xl transition-all duration-300 transform hover:scale-105">
                                            <svg class="w-5 h-5 mr-2 group-hover:rotate-90 transition-transform duration-300"
                                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                            </svg>
                                            Tambah Prefix Setting
                                        </button>
                                    @endpermission
                                @else
                                    <button wire:click="$set('search', ''); $set('showInactive', false)"
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

    <!-- FOOTER -->
    <div class="px-6 py-4 bg-gradient-to-r from-gray-50 to-blue-50 border-t border-gray-200 rounded-b-2xl">
        <div class="flex items-center justify-between">
            <div class="text-sm text-gray-600">
                Menampilkan <span class="font-medium">{{ $data->firstItem() ?? 0 }}</span>
                sampai <span class="font-medium">{{ $data->lastItem() ?? 0 }}</span>
                dari <span class="font-medium">{{ $data->total() }}</span> prefix dokumen
            </div>
            <div class="flex-1 flex justify-center">
                {{ $data->links() }}
            </div>
        </div>
    </div>
</div>
