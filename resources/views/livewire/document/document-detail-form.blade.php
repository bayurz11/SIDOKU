<div>
    @if ($showModal && $document)
        {{-- OVERLAY --}}
        <div class="fixed inset-0 z-50 bg-gray-600 bg-opacity-50 overflow-y-auto flex items-center justify-center bg-black/40 backdrop-blur-sm px-3 sm:px-4"
            wire:click.self="closeModal">

            {{-- WRAPPER KOTAK --}}
            <div
                class="relative w-full max-w-xl bg-white rounded-2xl shadow-2xl border border-gray-100
                       max-h-[90vh] overflow-y-auto transform transition-all duration-200 ease-out">

                {{-- HEADER --}}
                <div class="px-5 py-4 sm:px-6 sm:py-5 border-b border-gray-100">
                    <div class="flex items-start justify-between gap-3">
                        {{-- KIRI: ICON + TEKS --}}
                        <div class="flex items-start gap-3">
                            <div
                                class="w-11 h-11 rounded-2xl bg-gradient-to-br from-indigo-50 to-indigo-100
                                       flex items-center justify-center shadow-sm border border-indigo-100 flex-shrink-0">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke-width="1.5" stroke="currentColor" class="w-6 h-6 text-indigo-600">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5A3.375 3.375 0 0 0 10.125 2.25H5.625C4.839 2.25 4.5 2.964 4.5 3.75v16.5c0 .786.339 1.5 1.125 1.5h12.75c.786 0 1.125-.714 1.125-1.5V11.25a9 9 0 0 0-9-9" />
                                </svg>
                            </div>

                            <div class="space-y-0.5">
                                <h2 class="text-lg font-bold text-gray-900 leading-tight">
                                    Detail Dokumen
                                </h2>
                                <p class="text-xs text-gray-600">
                                    Informasi lengkap dokumen beserta status dan riwayat revisi.
                                </p>
                            </div>
                        </div>

                        {{-- TOMBOL CLOSE --}}
                        <button type="button" wire:click="closeModal"
                            class="text-gray-400 hover:text-gray-600 transition-colors">
                            <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>

                {{-- BODY --}}
                <div class="px-5 py-4 sm:px-6 sm:py-5 space-y-4 text-xs">
                    {{-- Judul + badge --}}
                    <div class="space-y-1">
                        <p class="text-sm font-semibold text-gray-900">
                            {{ $document->title }}
                        </p>

                        <div class="flex flex-wrap items-center gap-1.5">
                            {{-- No dokumen --}}
                            <span
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-semibold
                                       bg-green-50 text-green-700 border border-green-100">
                                {{ $document->document_code ?? 'No. dokumen belum diatur' }}
                            </span>

                            {{-- Type --}}
                            @if ($document->documentType)
                                <span
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-semibold
                                           bg-blue-50 text-blue-700 border border-blue-100">
                                    {{ $document->documentType->name }}
                                </span>
                            @endif

                            {{-- Dept --}}
                            @if ($document->department)
                                <span
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-semibold
                                           bg-purple-50 text-purple-700 border border-purple-100">
                                    {{ $document->department->name }}
                                </span>
                            @endif

                            {{-- Level --}}
                            @if ($document->level)
                                <span
                                    class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold
                                           bg-gray-50 text-gray-700 border border-gray-200">
                                    Level {{ $document->level }}
                                </span>
                            @endif

                            {{-- Status --}}
                            @php
                                $status = $document->status;
                                $statusMap = [
                                    'draft' => 'bg-gray-50 text-gray-700 border-gray-200',
                                    'in_review' => 'bg-amber-50 text-amber-700 border-amber-200',
                                    'approved' => 'bg-green-50 text-green-700 border-green-200',
                                    'obsolete' => 'bg-red-50 text-red-700 border-red-200',
                                ];
                                $statusClass = $statusMap[$status] ?? 'bg-gray-50 text-gray-700 border-gray-200';
                            @endphp
                            <span
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-semibold border {{ $statusClass }}">
                                <span class="w-1.5 h-1.5 rounded-full bg-current mr-1.5 opacity-70"></span>
                                {{ ucfirst(str_replace('_', ' ', $status)) }}
                            </span>
                        </div>
                    </div>

                    {{-- Info grid --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="space-y-1.5">
                            <h3 class="text-[11px] font-semibold text-gray-500 uppercase">Informasi Umum</h3>
                            <dl class="space-y-1">
                                <div class="flex justify-between">
                                    <dt class="text-gray-500">Document Type</dt>
                                    <dd class="text-gray-900 font-medium">
                                        {{ $document->documentType->name ?? '-' }}
                                    </dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-gray-500">Department</dt>
                                    <dd class="text-gray-900 font-medium">
                                        {{ $document->department->name ?? '-' }}
                                    </dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-gray-500">Revision</dt>
                                    <dd class="text-gray-900 font-medium">
                                        {{ $document->revision_no ?? 0 }}
                                    </dd>
                                </div>
                            </dl>
                        </div>

                        <div class="space-y-1.5">
                            <h3 class="text-[11px] font-semibold text-gray-500 uppercase">Lifecycle</h3>
                            <dl class="space-y-1">
                                <div class="flex justify-between">
                                    <dt class="text-gray-500">Effective Date</dt>
                                    <dd class="text-gray-900 font-medium">
                                        {{ optional($document->effective_date)->format('d M Y') ?? '-' }}
                                    </dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-gray-500">Expired Date</dt>
                                    <dd class="text-gray-900 font-medium">
                                        {{ optional($document->expired_date)->format('d M Y') ?? '-' }}
                                    </dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-gray-500">Active</dt>
                                    <dd class="text-gray-900 font-medium">
                                        {{ $document->is_active ? 'Yes' : 'No' }}
                                    </dd>
                                </div>
                            </dl>
                        </div>
                    </div>

                    {{-- Summary --}}
                    <div class="space-y-1.5">
                        <h3 class="text-[11px] font-semibold text-gray-500 uppercase">Ringkasan</h3>
                        <div class="rounded-xl border border-gray-100 bg-gray-50 px-3 py-2.5 text-[11px] text-gray-700">
                            {{ $document->summary ?: 'Belum ada ringkasan dokumen.' }}
                        </div>
                    </div>

                    {{-- Revisi --}}
                    <div class="space-y-1.5">
                        <div class="flex items-center justify-between">
                            <h3 class="text-[11px] font-semibold text-gray-500 uppercase">Riwayat Revisi</h3>
                            @if ($document->revisions->isNotEmpty())
                                <span class="text-[11px] text-gray-500">
                                    {{ $document->revisions->count() }} revisi
                                </span>
                            @endif
                        </div>

                        @if ($document->revisions->isEmpty())
                            <p class="text-[11px] text-gray-500">
                                Belum ada riwayat revisi untuk dokumen ini.
                            </p>
                        @else
                            <div class="space-y-2 max-h-40 overflow-y-auto pr-1">
                                @foreach ($document->revisions->sortByDesc('revision_no') as $rev)
                                    <div
                                        class="flex items-start justify-between rounded-xl border border-gray-100 bg-white px-3 py-2 text-[11px]">
                                        <div class="space-y-0.5">
                                            <div class="flex items-center gap-2">
                                                <span
                                                    class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold
                                                           bg-indigo-50 text-indigo-700 border border-indigo-100">
                                                    Rev. {{ $rev->revision_no }}
                                                </span>
                                                <span class="text-gray-500">
                                                    {{ optional($rev->changed_at)->format('d M Y H:i') ?? '-' }}
                                                </span>
                                            </div>
                                            <p class="text-gray-700">
                                                {{ $rev->change_note ?: 'Tidak ada catatan perubahan.' }}
                                            </p>

                                            @if ($rev->file_path)
                                                <a href="{{ asset('public/storage/' . ltrim($rev->file_path, '/')) }}"
                                                    class="inline-flex items-center mt-1 text-[11px] font-semibold text-green-700 hover:text-green-900">
                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                                                        viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                                                        class="w-3.5 h-3.5 mr-1">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5 7.5 12M12 4.5v12" />
                                                    </svg>
                                                    Download file revisi ini
                                                </a>
                                            @endif
                                        </div>

                                        @if ($rev->changedBy)
                                            <div class="pl-3 text-right text-gray-500">
                                                <div class="font-semibold text-gray-800">
                                                    {{ $rev->changedBy->name }}
                                                </div>
                                                <div>{{ $rev->changedBy->email }}</div>
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>

                {{-- FOOTER BUTTONS --}}
                <div class="px-5 py-3 sm:px-6 sm:py-4 border-t border-gray-100 bg-gray-50/60">
                    <div class="flex flex-col-reverse sm:flex-row sm:justify-end sm:space-x-3 gap-2">

                        {{-- BUTTON TUTUP --}}
                        <button wire:click="closeModal" type="button"
                            class="inline-flex items-center justify-center px-4 py-2 rounded-xl text-xs font-medium
                   text-gray-700 bg-gray-100 border border-gray-300
                   hover:bg-gray-200 transition-colors">
                            Tutup
                        </button>

                        {{-- BUTTON BUKA FILE --}}
                        @if ($document->file_path)
                            <a href="{{ asset('public/storage/' . ltrim($document->file_path, '/')) }}"
                                class="inline-flex items-center justify-center px-4 py-2 rounded-xl text-xs font-semibold text-white bg-gradient-to-r from-green-500 via-green-500 to-green-500 hover:from-green-600 hover:via-green-600 hover:to-green-600 shadow-sm hover:shadow-md active:scale-[0.97] transition-all duration-300">
                                <svg class="w-4 h-4 mr-1.5" xmlns="http://www.w3.org/2000/svg" fill="none"
                                    viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="m9 12.75 3 3m0 0 3-3m-3 3v-7.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                </svg>

                                Buka Dokumen
                            </a>
                        @endif

                        {{-- BUTTON AJUKAN APPROVAL — HANYA MUNCUL JIKA STATUS DRAFT --}}
                        @if ($document->status === 'draft')
                            <button wire:click="requestApproval({{ $document->id }})"
                                class="inline-flex items-center justify-center px-4 py-2 rounded-xl text-xs font-semibold text-white
                       bg-gradient-to-r from-indigo-500 via-indigo-500 to-indigo-600
                       hover:from-indigo-600 hover:via-indigo-600 hover:to-indigo-700
                       shadow-sm hover:shadow-md active:scale-[0.97] transition-all duration-300">

                                <svg class="w-4 h-4 mr-1.5" xmlns="http://www.w3.org/2000/svg" fill="none"
                                    viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                </svg>

                                Ajukan Dokumen
                            </button>
                        @endif

                    </div>
                </div>

            </div>
        </div>
    @endif
</div>
