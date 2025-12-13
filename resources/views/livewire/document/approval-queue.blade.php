<div class="space-y-5">
    <div class="bg-white rounded-2xl shadow border border-gray-200 overflow-hidden">
        <div
            class="px-5 py-4 border-b border-gray-100 flex flex-col sm:flex-row gap-3 sm:items-center sm:justify-between">
            <div>
                <h2 class="text-lg font-bold text-gray-900">Approval Queue</h2>
                <p class="text-xs text-gray-500">Daftar dokumen yang menunggu tindakan kamu (approve/reject).</p>
            </div>

            <div class="flex gap-2">
                <input type="text" wire:model.debounce.400ms="search"
                    class="w-full sm:w-72 rounded-xl border-gray-200 text-sm focus:ring-indigo-500 focus:border-indigo-500"
                    placeholder="Cari no dokumen / judul...">

                <select wire:model="perPage"
                    class="rounded-xl border-gray-200 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="10">10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                </select>
            </div>
        </div>

        <div class="px-5 py-3 border-b border-gray-100 flex flex-wrap gap-2 items-center">
            <select wire:model="status"
                class="rounded-xl border-gray-200 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                <option value="pending">Pending</option>
                <option value="approved">Approved</option>
                <option value="rejected">Rejected</option>
            </select>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50 text-gray-600">
                    <tr>
                        <th class="text-left px-5 py-3 font-semibold">No Dokumen</th>
                        <th class="text-left px-5 py-3 font-semibold">Judul</th>
                        <th class="text-left px-5 py-3 font-semibold">Dept</th>
                        <th class="text-left px-5 py-3 font-semibold">Step</th>
                        <th class="text-left px-5 py-3 font-semibold">Status</th>
                        <th class="text-right px-5 py-3 font-semibold">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($data as $step)
                        @php
                            $doc = $step->approvalRequest?->document;
                        @endphp
                        <tr class="hover:bg-gray-50/60">
                            <td class="px-5 py-3 font-semibold text-gray-900">
                                {{ $doc->document_code ?? '-' }}
                            </td>
                            <td class="px-5 py-3 text-gray-800">
                                {{ $doc->title ?? '-' }}
                                <div class="text-xs text-gray-500">
                                    {{ $doc->documentType->name ?? '' }}
                                </div>
                            </td>
                            <td class="px-5 py-3">
                                <span
                                    class="text-xs px-2 py-1 rounded-full bg-purple-50 text-purple-700 border border-purple-100">
                                    {{ $doc->department->name ?? '-' }}
                                </span>
                            </td>
                            <td class="px-5 py-3">
                                <span
                                    class="text-xs px-2 py-1 rounded-full bg-indigo-50 text-indigo-700 border border-indigo-100">
                                    Step {{ $step->step_order }}
                                </span>
                            </td>
                            <td class="px-5 py-3">
                                <span
                                    class="text-xs px-2 py-1 rounded-full bg-amber-50 text-amber-700 border border-amber-100">
                                    {{ ucfirst($step->status) }}
                                </span>
                            </td>
                            <td class="px-5 py-3 text-right space-x-2">
                                @if ($step->status === 'pending')
                                    @permission('documents.approve')
                                        <button wire:click="openActionModal({{ $step->id }}, 'approve')"
                                            class="inline-flex items-center px-3 py-2 text-xs font-semibold text-green-700 bg-green-50 border border-green-100 rounded-lg hover:bg-green-100">
                                            Approve
                                        </button>
                                    @endpermission

                                    @permission('documents.review')
                                        <button wire:click="openActionModal({{ $step->id }}, 'reject')"
                                            class="inline-flex items-center px-3 py-2 text-xs font-semibold text-red-700 bg-red-50 border border-red-100 rounded-lg hover:bg-red-100">
                                            Reject
                                        </button>
                                    @endpermission
                                @else
                                    <span class="text-xs text-gray-400">-</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-8 text-center text-gray-500">
                                Tidak ada approval yang menunggu tindakan kamu.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="px-5 py-4">
            {{ $data->links() }}
        </div>
    </div>

    {{-- MODAL APPROVE/REJECT --}}
    @if ($showActionModal)
        <div class="fixed inset-0 z-50 bg-black/40 backdrop-blur-sm flex items-center justify-center px-3"
            wire:click.self="closeActionModal">
            <div class="w-full max-w-lg bg-white rounded-2xl shadow-2xl border border-gray-100 overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-bold text-gray-900">
                            {{ $actionType === 'approve' ? 'Approve Dokumen' : 'Reject Dokumen' }}
                        </h3>
                        <p class="text-xs text-gray-500">
                            {{ $actionType === 'approve' ? 'Catatan opsional.' : 'Catatan reject wajib diisi.' }}
                        </p>
                    </div>
                    <button wire:click="closeActionModal" class="text-gray-400 hover:text-gray-600">✕</button>
                </div>

                <div class="px-5 py-4 space-y-2">
                    <label class="text-xs font-semibold text-gray-700">Catatan</label>
                    <textarea wire:model.defer="note"
                        class="w-full rounded-xl border-gray-200 text-sm focus:ring-indigo-500 focus:border-indigo-500" rows="4"
                        placeholder="Tulis catatan..."></textarea>
                </div>

                <div class="px-5 py-4 border-t border-gray-100 bg-gray-50 flex justify-end gap-2">
                    <button wire:click="closeActionModal"
                        class="px-4 py-2 rounded-xl text-xs font-semibold bg-gray-100 border border-gray-200 hover:bg-gray-200">
                        Batal
                    </button>

                    <button wire:click="submitAction"
                        class="px-4 py-2 rounded-xl text-xs font-semibold text-white
                        {{ $actionType === 'approve' ? 'bg-green-600 hover:bg-green-700' : 'bg-red-600 hover:bg-red-700' }}">
                        {{ $actionType === 'approve' ? 'Approve' : 'Reject' }}
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
