<div>
    @if ($showDetail && $material)

        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50 flex items-start justify-center"
            wire:click.self="closeDetail">

            <div class="relative top-8 mx-auto p-6 border w-full max-w-6xl shadow-lg rounded-2xl bg-white"
                x-data="{}">

                {{-- HEADER --}}
                <div class="px-6 py-5 border-b bg-gray-50 rounded-t-2xl">
                    <div class="flex justify-between items-start">

                        <div>
                            <h2 class="text-lg font-bold text-gray-900">
                                Detail Incoming Material
                            </h2>
                            <p class="text-xs text-gray-500">
                                Informasi lengkap penerimaan bahan baku
                            </p>
                        </div>

                        <button wire:click="closeDetail" class="text-gray-400 hover:text-gray-600 text-lg">
                            ✕
                        </button>
                    </div>
                </div>

                {{-- BODY --}}
                <div class="px-6 py-6 space-y-6 text-sm">

                    {{-- STATUS --}}
                    @php
                        $statusClass = match ($material->status) {
                            'ACCEPTED' => 'bg-green-100 text-green-700',
                            'HOLD' => 'bg-yellow-100 text-yellow-700',
                            'REJECTED' => 'bg-red-100 text-red-700',
                            default => 'bg-gray-100 text-gray-700',
                        };
                    @endphp

                    <span
                        class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold border {{ $statusClass }}">
                        {{ $material->status ?? '-' }}
                    </span>

                    {{-- INFORMASI GRID --}}
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">

                        {{-- INFORMASI UMUM --}}
                        <div class="space-y-3">
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



                            </dl>
                        </div>

                        {{-- INFORMASI KUANTITAS --}}
                        <div class="space-y-3">
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
                        {{-- INFORMASI Kendaraan --}}
                        <div class="space-y-3">
                            <h3 class="text-xs font-semibold uppercase text-gray-500">
                                Informasi Kendaraan
                            </h3>

                            <dl class="space-y-2">

                                <div class="flex justify-between">
                                    <dt class="text-gray-500">No Kendaraan</dt>
                                    <dd class="font-medium text-gray-900 text-right uppercase">
                                        {{ $material->vehicle_number ?? '-' }}
                                    </dd>
                                </div>

                            </dl>
                        </div>

                    </div>

                    {{-- DOKUMEN & FOTO --}}
                    <div>
                        <h3 class="text-xs font-semibold uppercase text-gray-500 mb-3">
                            Dokumen & Foto
                        </h3>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

                            @forelse($material->files ?? [] as $file)
                                <div class="border rounded-lg p-4 bg-gray-50 text-xs space-y-1">

                                    <div class="font-medium text-gray-800">
                                        {{ $file->file_name ?? '-' }}
                                    </div>

                                    <div class="text-gray-500">
                                        Kategori: {{ strtoupper($file->category ?? '-') }}
                                    </div>

                                    <a href="{{ asset('storage/' . $file->file_path) }}" target="_blank"
                                        class="inline-block text-blue-600 hover:underline">
                                        Lihat File
                                    </a>
                                </div>
                            @empty
                                <div class="text-gray-500 text-xs">
                                    Tidak ada dokumen atau foto.
                                </div>
                            @endforelse

                        </div>
                    </div>

                    {{-- CATATAN --}}
                    <div>
                        <h3 class="text-xs font-semibold uppercase text-gray-500 mb-2">
                            Catatan
                        </h3>

                        <div class="border rounded-lg p-4 bg-gray-50 text-xs text-gray-700">
                            {{ $material->notes ?? 'Tidak ada catatan.' }}
                        </div>
                    </div>

                </div>

                {{-- FOOTER --}}
                <div class="px-6 py-4 border-t bg-gray-50 flex justify-end rounded-b-2xl">
                    <button wire:click="closeDetail" class="px-4 py-2 text-xs rounded-xl bg-gray-100 hover:bg-gray-200">
                        Tutup
                    </button>
                </div>

            </div>
        </div>

    @endif
</div>
