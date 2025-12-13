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


    @if ($showActionModal && $selectedStep)
        @php
            $doc = $selectedStep->approvalRequest?->document;
            $status = $doc?->status ?? 'draft';

            $statusClass =
                [
                    'draft' => 'bg-gray-50 text-gray-700 border-gray-200',
                    'in_review' => 'bg-amber-50 text-amber-700 border-amber-200',
                    'approved' => 'bg-green-50 text-green-700 border-green-200',
                    'obsolete' => 'bg-red-50 text-red-700 border-red-200',
                ][$status] ?? 'bg-gray-50 text-gray-700 border-gray-200';
        @endphp

        <div class="fixed inset-0 z-50 bg-black/40 backdrop-blur-sm px-3 sm:px-4
                overflow-y-auto flex items-center justify-center"
            wire:click.self="closeActionModal">

            <div
                class="relative w-full max-w-2xl bg-white rounded-2xl shadow-2xl border border-gray-100
                    max-h-[90vh] overflow-y-auto transform transition-all duration-200 ease-out">

                {{-- HEADER --}}
                <div class="px-5 py-4 sm:px-6 sm:py-5 border-b border-gray-100">
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex items-start gap-3">
                            <div
                                class="w-11 h-11 rounded-2xl
                                    {{ $actionType === 'approve' ? 'bg-gradient-to-br from-green-50 to-green-100 border border-green-100' : 'bg-gradient-to-br from-red-50 to-red-100 border border-red-100' }}
                                    flex items-center justify-center shadow-sm flex-shrink-0">

                                @if ($actionType === 'approve')
                                    <svg class="w-6 h-6 text-green-700" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24" stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                    </svg>
                                @else
                                    <svg class="w-6 h-6 text-red-700" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24" stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M6 18 18 6M6 6l12 12" />
                                    </svg>
                                @endif
                            </div>

                            <div class="space-y-0.5">
                                <h3 class="text-lg font-bold text-gray-900 leading-tight">
                                    {{ $actionType === 'approve' ? 'Approve Dokumen' : 'Reject Dokumen' }}
                                </h3>
                                <p class="text-xs text-gray-600">
                                    {{ $actionType === 'approve' ? 'Catatan opsional.' : 'Catatan reject wajib diisi.' }}
                                </p>
                            </div>
                        </div>

                        <button type="button" wire:click="closeActionModal"
                            class="text-gray-400 hover:text-gray-600 transition-colors">
                            <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>

                {{-- BODY --}}
                <div class="px-5 py-4 sm:px-6 sm:py-5 space-y-4 text-xs">

                    {{-- INFO DOKUMEN (READ ONLY) --}}
                    <div class="rounded-2xl border border-gray-100 bg-gray-50/70 p-4 space-y-3">
                        <div class="flex flex-wrap items-center gap-1.5">
                            <span
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-semibold
                                     bg-green-50 text-green-700 border border-green-100">
                                {{ $doc->document_code ?? '-' }}
                            </span>

                            @if ($doc?->documentType)
                                <span
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-semibold
                                         bg-blue-50 text-blue-700 border border-blue-100">
                                    {{ $doc->documentType->name }}
                                </span>
                            @endif

                            @if ($doc?->department)
                                <span
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-semibold
                                         bg-purple-50 text-purple-700 border border-purple-100">
                                    {{ $doc->department->name }}
                                </span>
                            @endif

                            @if ($doc?->level)
                                <span
                                    class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold
                                         bg-gray-50 text-gray-700 border border-gray-200">
                                    Level {{ $doc->level }}
                                </span>
                            @endif

                            <span
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-semibold border {{ $statusClass }}">
                                <span class="w-1.5 h-1.5 rounded-full bg-current mr-1.5 opacity-70"></span>
                                {{ ucfirst(str_replace('_', ' ', $status)) }}
                            </span>

                            <span
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-semibold
                                     bg-indigo-50 text-indigo-700 border border-indigo-100">
                                Step {{ $selectedStep->step_order }}
                            </span>
                        </div>

                        <div class="space-y-1">
                            <div class="text-sm font-semibold text-gray-900">
                                {{ $doc->title ?? '-' }}
                            </div>

                            @if ($doc?->summary)
                                <div class="text-[11px] text-gray-700 border-t border-gray-200 pt-2">
                                    <span class="font-semibold text-gray-700">Ringkasan:</span><br>
                                    {{ $doc->summary }}
                                </div>
                            @endif
                        </div>

                        <div
                            class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 pt-2 border-t border-gray-200">
                            <div class="text-[11px] text-gray-600">
                                <span class="font-semibold text-gray-700">Effective:</span>
                                {{ optional($doc?->effective_date)->format('d M Y') ?? '-' }}
                                <span class="mx-1">•</span>
                                <span class="font-semibold text-gray-700">Revision:</span>
                                {{ $doc->revision_no ?? 0 }}
                            </div>

                            @if ($doc?->file_path)
                                <a href="{{ asset('public/storage/' . ltrim($doc->file_path, '/')) }}"
                                    target="_blank"
                                    class="inline-flex items-center justify-center px-3 py-2 rounded-xl text-xs font-semibold
                                      text-white bg-gradient-to-r from-green-500 via-green-500 to-green-500
                                      hover:from-green-600 hover:via-green-600 hover:to-green-600
                                      shadow-sm hover:shadow-md active:scale-[0.97] transition-all duration-300">
                                    <svg class="w-4 h-4 mr-1.5" xmlns="http://www.w3.org/2000/svg" fill="none"
                                        viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="m9 12.75 3 3m0 0 3-3m-3 3v-7.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                    </svg>
                                    Lihat / Download
                                </a>
                            @endif
                        </div>
                    </div>

                    {{-- CATATAN --}}
                    <div class="space-y-2">
                        <label class="block text-xs font-semibold text-gray-700">
                            Catatan
                            @if ($actionType === 'reject')
                                <span class="text-red-500">*</span>
                            @endif
                        </label>

                        <textarea wire:model.defer="note" rows="4"
                            class="block w-full rounded-xl border-gray-200 text-sm
                               focus:ring-indigo-500 focus:border-indigo-500"
                            placeholder="Tulis catatan..."></textarea>

                        @if ($actionType === 'reject')
                            <p class="text-[11px] text-gray-500">
                                Tips: tulis alasan reject + poin yang harus diperbaiki (audit-friendly).
                            </p>
                        @endif
                    </div>

                </div>

                {{-- FOOTER --}}
                <div class="px-5 py-3 sm:px-6 sm:py-4 border-t border-gray-100 bg-gray-50/60">
                    <div class="flex flex-col-reverse sm:flex-row sm:justify-end sm:space-x-3 gap-2">

                        <button type="button" wire:click="closeActionModal"
                            class="inline-flex items-center justify-center px-4 py-2 rounded-xl text-xs font-medium
                               text-gray-700 bg-gray-100 border border-gray-300 hover:bg-gray-200 transition-colors">
                            Cancel
                        </button>

                        <button type="button" wire:click="submitAction"
                            class="inline-flex items-center justify-center px-4 py-2 rounded-xl text-xs font-semibold text-white
                               {{ $actionType === 'approve'
                                   ? 'bg-gradient-to-r from-green-500 via-green-500 to-green-600 hover:from-green-600 hover:via-green-600 hover:to-green-700'
                                   : 'bg-gradient-to-r from-red-500 via-red-500 to-red-600 hover:from-red-600 hover:via-red-600 hover:to-red-700' }}
                               shadow-sm hover:shadow-md active:scale-[0.97] transition-all duration-300">

                            @if ($actionType === 'approve')
                                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                    stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                </svg>
                                Approve
                            @else
                                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                    stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
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
