@php
    use Illuminate\Support\Str;
@endphp

<div class="bg-white shadow-xl rounded-2xl border border-gray-200 overflow-hidden">

    {{-- HEADER --}}
    <div class="bg-gradient-to-r from-blue-50 via-green-50 to-lime-50 px-6 py-6 border-b border-gray-200">
        <div class="flex justify-between items-center gap-4">
            <div class="flex items-center space-x-4">
                <div
                    class="w-12 h-12 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-xl flex items-center justify-center shadow-lg">
                    {{-- icon: checklist/approval --}}
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12h6m-6 4h6M7 8h10M5 6.5A2.5 2.5 0 017.5 4h9A2.5 2.5 0 0119 6.5v13A2.5 2.5 0 0116.5 22h-9A2.5 2.5 0 015 19.5v-13z" />
                    </svg>
                </div>

                <div>
                    <h2 class="text-2xl font-bold text-gray-900">Approval Queue</h2>
                    <p class="text-sm text-gray-600 mt-1">
                        Daftar dokumen yang menunggu tindakan kamu (approve / reject) sesuai workflow QMS.
                    </p>
                </div>
            </div>

            {{-- badge info kecil --}}
            <div class="hidden md:flex items-center gap-2">
                <span
                    class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-semibold bg-white text-gray-700 border border-gray-200 shadow-sm">
                    Total: <span class="ml-1 font-mono">{{ $data->total() }}</span>
                </span>
                <span
                    class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-semibold bg-indigo-50 text-indigo-700 border border-indigo-200">
                    Status: <span class="ml-1 font-mono">{{ strtoupper($status ?? 'pending') }}</span>
                </span>
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
                        <input wire:model.debounce.400ms="search" type="text"
                            placeholder="Cari nomor dokumen atau judul..."
                            class="block w-full pl-12 pr-4 py-3 border border-gray-300 rounded-xl bg-white placeholder-gray-500
                                   focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200">
                    </div>
                </div>

                {{-- Per page --}}
                <div>
                    <select wire:model="perPage"
                        class="px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white text-sm font-medium transition-all duration-200">
                        <option value="10">10 per halaman</option>
                        <option value="25">25 per halaman</option>
                        <option value="50">50 per halaman</option>
                        <option value="100">100 per halaman</option>
                    </select>
                </div>
            </div>

            {{-- Status --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Status Step</label>
                    <select wire:model="status"
                        class="block w-full px-3 py-2 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                        <option value="pending">Pending</option>
                        <option value="approved">Approved</option>
                        <option value="rejected">Rejected</option>
                    </select>
                </div>

                <div class="md:col-span-2 flex items-end justify-end gap-2">
                    {{-- optional: tombol refresh / reset --}}
                    <button type="button" wire:click="$set('search',''); $set('status','pending');"
                        class="inline-flex items-center px-4 py-2 bg-white text-gray-700 font-semibold rounded-xl border border-gray-200 hover:bg-gray-50 transition-all duration-200">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        Reset Filter
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- TABLE --}}
    <div class="overflow-x-auto">
        <table class="min-w-full">
            <thead class="bg-gradient-to-r from-gray-50 to-gray-100">
                <tr>
                    <th
                        class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider rounded-tl-xl">
                        No. Dokumen
                    </th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                        Judul
                    </th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                        Dept
                    </th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                        Step
                    </th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                        Status
                    </th>
                    <th
                        class="px-6 py-4 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider rounded-tr-xl">
                        Aksi
                    </th>
                </tr>
            </thead>

            <tbody class="bg-white divide-y divide-gray-100">
                @forelse($data as $step)
                    @php
                        $doc = $step->approvalRequest?->document;

                        $statusMap = [
                            'pending' => 'bg-yellow-100 text-yellow-800 border-yellow-200',
                            'approved' => 'bg-green-100 text-green-800 border-green-200',
                            'rejected' => 'bg-red-100 text-red-800 border-red-200',
                        ];
                        $statusCls = $statusMap[$step->status] ?? 'bg-gray-100 text-gray-800 border-gray-200';
                    @endphp

                    <tr class="hover:bg-gradient-to-r hover:from-blue-50 hover:to-green-50 transition-all duration-300">
                        {{-- No Dok --}}
                        <td class="px-6 py-5 whitespace-nowrap font-mono text-sm text-gray-900">
                            {{ $doc->document_code ?? '-' }}
                        </td>

                        {{-- Judul --}}
                        <td class="px-6 py-5 whitespace-nowrap text-sm text-gray-800">
                            <div class="font-semibold">
                                {{ Str::limit($doc->title ?? '-', 55) }}
                            </div>
                            <div class="text-xs text-gray-500">
                                {{ $doc->documentType->name ?? '' }}
                            </div>
                        </td>

                        {{-- Dept --}}
                        <td class="px-6 py-5 whitespace-nowrap text-sm text-gray-700">
                            <span
                                class="inline-flex items-center px-3 py-1.5 rounded-xl text-xs font-semibold border bg-purple-50 text-purple-700 border-purple-100">
                                {{ $doc->department->name ?? '-' }}
                            </span>
                        </td>

                        {{-- Step --}}
                        <td class="px-6 py-5 whitespace-nowrap text-sm">
                            <span
                                class="inline-flex items-center px-3 py-1.5 rounded-xl text-xs font-semibold border bg-indigo-50 text-indigo-700 border-indigo-100">
                                Step {{ $step->step_order }}
                            </span>
                        </td>

                        {{-- Status --}}
                        <td class="px-6 py-5 whitespace-nowrap">
                            <span
                                class="inline-flex items-center px-3 py-1.5 rounded-xl text-xs font-semibold border {{ $statusCls }}">
                                <span
                                    class="w-2 h-2 rounded-full mr-2
                                    {{ $step->status === 'approved' ? 'bg-green-500' : ($step->status === 'rejected' ? 'bg-red-500' : 'bg-yellow-500') }}">
                                </span>
                                {{ ucfirst($step->status) }}
                            </span>
                        </td>

                        {{-- Actions --}}
                        <td class="px-6 py-5 whitespace-nowrap text-sm font-medium">
                            <div class="flex items-center justify-end gap-2">
                                @if ($step->status === 'pending')
                                    @permission('documents.approve')
                                        <button wire:click="openActionModal({{ $step->id }}, 'approve')"
                                            class="inline-flex items-center px-3 py-2 text-xs font-semibold text-green-700 bg-green-50 rounded-lg hover:bg-green-100 hover:text-green-800 transition-all duration-200">
                                            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M5 13l4 4L19 7" />
                                            </svg>
                                            Approve
                                        </button>
                                    @endpermission

                                    @permission('documents.review')
                                        <button wire:click="openActionModal({{ $step->id }}, 'reject')"
                                            class="inline-flex items-center px-3 py-2 text-xs font-semibold text-red-700 bg-red-50 rounded-lg hover:bg-red-100 hover:text-red-800 transition-all duration-200">
                                            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                            Reject
                                        </button>
                                    @endpermission
                                @else
                                    <span class="text-xs text-gray-400">-</span>
                                @endif
                            </div>
                        </td>
                    </tr>

                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-16 text-center">
                            <div class="flex flex-col items-center justify-center">
                                <div
                                    class="w-20 h-20 bg-gradient-to-br from-gray-100 to-gray-200 rounded-2xl flex items-center justify-center mb-6 shadow-inner">
                                    <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M3 7.5A2.25 2.25 0 015.25 5.25h13.5A2.25 2.25 0 0121 7.5v9a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 16.5v-9zM7.5 9h9m-9 3.75h4.5" />
                                    </svg>
                                </div>

                                <h3 class="text-xl font-semibold text-gray-900 mb-2">Tidak ada antrian approval</h3>
                                <p class="text-gray-500 mb-6 max-w-sm text-center">
                                    Coba ubah status atau kata kunci pencarian.
                                </p>

                                <button wire:click="$set('search',''); $set('status','pending');"
                                    class="inline-flex items-center px-6 py-3 bg-gray-100 text-gray-700 font-semibold rounded-xl hover:bg-gray-200 transition-all duration-300">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                    </svg>
                                    Reset Filter
                                </button>
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
                approval
            </div>
            <div class="flex-1 flex justify-center md:justify-end">
                {{ $data->links() }}
            </div>
        </div>
    </div>

    {{-- MODAL APPROVE/REJECT (style seperti modal dokumen) --}}
    @if ($showActionModal)
        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50 flex items-start justify-center"
            wire:click.self="closeActionModal">
            <div class="relative top-8 mx-auto p-6 border w-full max-w-2xl shadow-lg rounded-md bg-white">
                <div class="mt-3">

                    {{-- HEADER --}}
                    <div class="flex justify-between items-start mb-6">
                        <div>
                            <h3 class="text-xl font-semibold text-gray-900">
                                {{ $actionType === 'approve' ? 'Approve Dokumen' : 'Reject Dokumen' }}
                            </h3>
                            <p class="text-sm text-gray-500 mt-1">
                                {{ $actionType === 'approve' ? 'Catatan opsional.' : 'Catatan reject wajib diisi.' }}
                            </p>
                        </div>

                        <button type="button" wire:click="closeActionModal"
                            class="text-gray-400 hover:text-gray-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    {{-- BODY --}}
                    <div class="space-y-3">
                        <label class="block text-sm font-medium text-gray-700">
                            Catatan
                            @if ($actionType === 'reject')
                                <span class="text-red-500">*</span>
                            @endif
                        </label>
                        {{-- DOCUMENT INFO --}}
                        <div class="bg-gray-50 border border-gray-200 rounded-lg p-4 space-y-2 mb-4">
                            <div class="text-sm">
                                <span class="font-semibold text-gray-700">No Dokumen:</span>
                                <span
                                    class="font-mono">{{ $step->approvalRequest->document->document_code ?? '-' }}</span>
                            </div>

                            <div class="text-sm">
                                <span class="font-semibold text-gray-700">Judul:</span>
                                {{ $step->approvalRequest->document->title ?? '-' }}
                            </div>

                            <div class="grid grid-cols-2 gap-2 text-sm">
                                <div>
                                    <span class="font-semibold text-gray-700">Department:</span>
                                    {{ $step->approvalRequest->document->department->name ?? '-' }}
                                </div>
                                <div>
                                    <span class="font-semibold text-gray-700">Level:</span>
                                    Level {{ $step->approvalRequest->document->level ?? '-' }}
                                </div>
                            </div>

                            @if ($step->approvalRequest->document->summary)
                                <div class="text-sm text-gray-600 border-t pt-2">
                                    <span class="font-semibold text-gray-700">Ringkasan:</span><br>
                                    {{ $step->approvalRequest->document->summary }}
                                </div>
                            @endif

                            @if ($step->approvalRequest->document->file_path)
                                <div class="pt-2">
                                    <a href="{{ asset('storage/' . $step->approvalRequest->document->file_path) }}"
                                        target="_blank"
                                        class="inline-flex items-center px-3 py-2 text-xs font-semibold
                      bg-blue-50 text-blue-700 rounded-md hover:bg-blue-100 border border-blue-200">
                                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 4v16m8-8H4" />
                                        </svg>
                                        Lihat / Download Dokumen
                                    </a>
                                </div>
                            @endif
                        </div>

                        <textarea wire:model.defer="note" rows="4"
                            class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm
                                   focus:outline-none focus:ring-blue-500 focus:border-blue-500 text-sm"
                            placeholder="Tulis catatan..."></textarea>
                    </div>

                    {{-- FOOTER --}}
                    <div class="flex justify-end space-x-3 pt-6 border-t border-gray-200 mt-6">
                        <button type="button" wire:click="closeActionModal"
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 rounded-md
                                   hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-500">
                            Cancel
                        </button>

                        <button type="button" wire:click="submitAction"
                            class="px-4 py-2 text-sm font-medium text-white rounded-md focus:outline-none focus:ring-2
                                {{ $actionType === 'approve' ? 'bg-green-600 hover:bg-green-700 focus:ring-green-500' : 'bg-red-600 hover:bg-red-700 focus:ring-red-500' }}
                                flex items-center">
                            @if ($actionType === 'approve')
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M5 13l4 4L19 7" />
                                </svg>
                                Approve
                            @else
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12" />
                                </svg>
                                Reject
                            @endif
                        </button>
                    </div>

                </div>
            </div>
        </div>
    @endif
</div>
