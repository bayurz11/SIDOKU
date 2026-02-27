<div>
    @if ($showDetail && $material)

        <div class="fixed inset-0 z-50 bg-black/40 backdrop-blur-sm flex items-center justify-center px-4"
            wire:click.self="closeDetail">

            <div
                class="relative w-full max-w-4xl bg-white rounded-2xl shadow-2xl
                        max-h-[90vh] overflow-y-auto border border-gray-100">

                {{-- HEADER --}}
                <div class="px-6 py-5 border-b border-gray-100">
                    <div class="flex justify-between items-start gap-4">

                        <div class="flex items-start gap-3">
                            <div class="w-12 h-12 rounded-2xl bg-blue-50 flex items-center justify-center">
                                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                        d="M3 7h18M3 12h18M3 17h18" />
                                </svg>
                            </div>

                            <div>
                                <h2 class="text-lg font-bold text-gray-900">
                                    Detail Incoming Material
                                </h2>
                                <p class="text-xs text-gray-500">
                                    Informasi lengkap penerimaan bahan baku
                                </p>
                            </div>
                        </div>

                        <button wire:click="closeDetail" class="text-gray-400 hover:text-gray-600">
                            ✕
                        </button>
                    </div>
                </div>

                {{-- BODY --}}
                <div class="px-6 py-5 space-y-6 text-sm">

                    {{-- STATUS BADGE --}}
                    @php
                        $statusClass = match ($material->status) {
                            'APPROVED', 'ACCEPTED' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                            'HOLD' => 'bg-amber-50 text-amber-700 border-amber-200',
                            'REJECTED' => 'bg-red-50 text-red-700 border-red-200',
                            default => 'bg-gray-50 text-gray-700 border-gray-200',
                        };
                    @endphp

                    <span
                        class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold border {{ $statusClass }}">
                        {{ $material->status ?? '-' }}
                    </span>

                    {{-- INFORMASI GRID --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                        {{-- INFORMASI UMUM --}}
                        <div class="space-y-2">
                            <h3 class="text-xs font-semibold uppercase text-gray-500">
                                Informasi Umum
                            </h3>

                            <dl class="space-y-2">

                                <div class="flex justify-between">
                                    <dt class="text-gray-500">Nama Barang</dt>
                                    <dd class="font-medium text-gray-900 text-right">
                                        {{ $material->material_name ?? '-' }}
                                    </dd>
                                </div>

                                <div class="flex justify-between">
                                    <dt class="text-gray-500">Supplier</dt>
                                    <dd class="font-medium text-gray-900 text-right">
                                        {{ $material->supplier ?? '-' }}
                                    </dd>
                                </div>

                                <div class="flex justify-between">
                                    <dt class="text-gray-500">Tanggal Terima</dt>
                                    <dd class="font-medium text-gray-900 text-right">
                                        {{ $material->date?->format('d M Y') ?? '-' }}
                                    </dd>
                                </div>

                                <div class="flex justify-between">
                                    <dt class="text-gray-500">Waktu Terima</dt>
                                    <dd class="font-medium text-gray-900 text-right">
                                        {{ $material->receipt_time ?? '-' }}
                                    </dd>
                                </div>

                                <div class="flex justify-between">
                                    <dt class="text-gray-500">No Kendaraan</dt>
                                    <dd class="font-medium text-gray-900 text-right">
                                        {{ $material->vehicle_number ?? '-' }}
                                    </dd>
                                </div>

                            </dl>
                        </div>

                        {{-- INFORMASI KUANTITAS --}}
                        <div class="space-y-2">
                            <h3 class="text-xs font-semibold uppercase text-gray-500">
                                Informasi Kuantitas
                            </h3>

                            <dl class="space-y-2">

                                <div class="flex justify-between">
                                    <dt class="text-gray-500">Batch Number</dt>
                                    <dd class="font-medium text-gray-900 text-right">
                                        {{ $material->batch_number ?? '-' }}
                                    </dd>
                                </div>

                                <div class="flex justify-between">
                                    <dt class="text-gray-500">Quantity</dt>
                                    <dd class="font-medium text-gray-900 text-right">
                                        {{ $material->quantity ?? 0 }}
                                        {{ $material->quantity_unit ?? '' }}
                                    </dd>
                                </div>

                                <div class="flex justify-between">
                                    <dt class="text-gray-500">Jumlah Sample</dt>
                                    <dd class="font-medium text-gray-900 text-right">
                                        {{ $material->sample_quantity ?? '-' }}
                                    </dd>
                                </div>

                                <div class="flex justify-between">
                                    <dt class="text-gray-500">Tanggal Input</dt>
                                    <dd class="font-medium text-gray-900 text-right">
                                        {{ $material->created_at?->format('d M Y') ?? '-' }}
                                    </dd>
                                </div>

                            </dl>
                        </div>

                    </div>

                    {{-- DOKUMEN --}}
                    <div>
                        <h3 class="text-xs font-semibold uppercase text-gray-500 mb-2">
                            Dokumen & Foto
                        </h3>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">

                            @forelse($material->files ?? [] as $file)
                                <div class="border rounded-lg p-3 bg-gray-50 text-xs">
                                    <div class="font-medium text-gray-800">
                                        {{ $file->file_name ?? '-' }}
                                    </div>

                                    <div class="text-gray-500">
                                        Kategori: {{ $file->category ?? '-' }}
                                    </div>

                                    <a href="{{ asset('storage/' . $file->file_path) }}" target="_blank"
                                        class="text-blue-600 hover:underline text-xs">
                                        Lihat File
                                    </a>
                                </div>
                            @empty
                                <div class="text-gray-500 text-xs">
                                    Tidak ada dokumen.
                                </div>
                            @endforelse

                        </div>
                    </div>

                    {{-- CATATAN --}}
                    <div>
                        <h3 class="text-xs font-semibold uppercase text-gray-500 mb-2">
                            Catatan
                        </h3>
                        <div class="border rounded-lg p-3 bg-gray-50 text-xs text-gray-700">
                            {{ $material->notes ?? 'Tidak ada catatan.' }}
                        </div>
                    </div>

                </div>

                {{-- FOOTER --}}
                <div class="px-6 py-4 border-t bg-gray-50 flex justify-end">
                    <button wire:click="closeDetail" class="px-4 py-2 text-xs rounded-xl bg-gray-100 hover:bg-gray-200">
                        Tutup
                    </button>
                </div>

            </div>
        </div>

    @endif
</div>
