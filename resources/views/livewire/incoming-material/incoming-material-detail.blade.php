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

                    {{-- INFORMASI UMUM, KUANTITAS, KENDARAAN --}}
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                        {{-- Informasi Umum --}}
                        <div class="space-y-3">
                            <h3 class="text-xs font-semibold uppercase text-gray-500">Informasi Umum</h3>
                            <dl class="space-y-2">
                                <div class="flex justify-between">
                                    <dt class="text-gray-500">Nama Barang</dt>
                                    <dd class="font-medium text-gray-900 text-right">
                                        {{ $material->material_name ?? '-' }}</dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-gray-500">Supplier</dt>
                                    <dd class="font-medium text-gray-900 text-right">{{ $material->supplier ?? '-' }}
                                    </dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-gray-500">Tanggal Terima</dt>
                                    <dd class="font-medium text-gray-900 text-right">
                                        {{ $material->date?->format('d M Y') ?? '-' }}</dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-gray-500">Waktu Terima</dt>
                                    <dd class="font-medium text-gray-900 text-right">
                                        {{ $material->receipt_time ?? '-' }}</dd>
                                </div>
                            </dl>
                        </div>

                        {{-- Informasi Kuantitas --}}
                        <div class="space-y-3">
                            <h3 class="text-xs font-semibold uppercase text-gray-500">Informasi Kuantitas</h3>
                            <dl class="space-y-2">
                                <div class="flex justify-between">
                                    <dt class="text-gray-500">Batch Number</dt>
                                    <dd class="font-medium text-gray-900 text-right">
                                        {{ $material->batch_number ?? '-' }}</dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-gray-500">Quantity</dt>
                                    <dd class="font-medium text-gray-900 text-right">
                                        {{ rtrim(rtrim(number_format($material->quantity, 2), '0'), '.') }}
                                        {{ $material->quantity_unit ?? '' }}
                                    </dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-gray-500">Jumlah Sample</dt>
                                    <dd class="font-medium text-gray-900 text-right">
                                        {{ $material->sample_quantity ?? '-' }}</dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-gray-500">Tanggal Input</dt>
                                    <dd class="font-medium text-gray-900 text-right">
                                        {{ $material->created_at?->format('d M Y') ?? '-' }}</dd>
                                </div>
                            </dl>
                        </div>

                        {{-- Informasi Kendaraan & Staf --}}
                        <div class="space-y-3">
                            <h3 class="text-xs font-semibold uppercase text-gray-500">Informasi Kendaraan</h3>
                            <dl class="space-y-2">
                                <div class="flex justify-between">
                                    <dt class="text-gray-500">No Kendaraan</dt>
                                    <dd class="font-medium text-gray-900 text-right uppercase">
                                        {{ $material->vehicle_number ?? '-' }}</dd>
                                </div>
                            </dl>
                            <h3 class="text-xs font-semibold uppercase text-gray-500">Staf Penerima</h3>
                            <dl class="space-y-2">
                                <div class="flex justify-between">
                                    <dt class="text-gray-500">Nama Staf</dt>
                                    <dd class="font-medium text-gray-900 text-right uppercase">
                                        @if (method_exists($material, 'createdBy') && $material->createdBy)
                                            {{ $material->createdBy->name }}
                                        @else
                                            {{ $material->created_by ?? '-' }}
                                        @endif
                                    </dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-gray-500">Diedit oleh</dt>
                                    <dd class="text-gray-900 font-medium text-right">
                                        @if (method_exists($material, 'updatedBy') && $material->updatedBy)
                                            {{ $material->updatedBy->name }}
                                        @else
                                            {{ $material->updated_by ?? '-' }}
                                        @endif
                                    </dd>
                                </div>
                            </dl>
                        </div>
                    </div>
                    {{-- ================= SPESIFIKASI YANG DIPERIKSA ================= --}}
                    <div class="bg-white shadow rounded-xl border mt-6">
                        <div class="px-6 py-4 border-b bg-gray-50 flex justify-between items-center">
                            <h3 class="text-sm font-semibold text-gray-700">Spesifikasi yang Diperiksa</h3>

                        </div>
                        <div class="p-6 overflow-x-auto">
                            <table class="min-w-full text-sm border border-gray-200">
                                <thead class="bg-gray-100">
                                    <tr class="text-left">
                                        <th class="px-3 py-2 border">No</th>
                                        <th class="px-3 py-2 border">Parameter Uji</th>
                                        <th class="px-3 py-2 border">Standar (Kondisi Fisik)</th>
                                        <th class="px-3 py-2 border">Hasil Uji</th>
                                        <th class="px-3 py-2 border">Hasil Inspeksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($inspectionItems as $index => $item)
                                        <tr>
                                            <td class="px-3 py-2 border text-center">{{ $loop->iteration }}</td>

                                            {{-- Parameter --}}
                                            <td class="px-3 py-2 border">
                                                <span class="text-gray-900 text-sm">
                                                    {{ $item['parameter'] ?? '-' }}
                                                </span>
                                            </td>

                                            {{-- Standard --}}
                                            <td class="px-3 py-2 border">
                                                <span class="text-gray-900 text-sm">
                                                    {{ $item['standard'] ?? '-' }}
                                                </span>
                                            </td>

                                            {{-- Test Result --}}
                                            <td class="px-3 py-2 border text-center font-semibold">
                                                @if (strtolower($item['test_result'] ?? '') === 'ok')
                                                    <span class="text-green-600">OK</span>
                                                @elseif (strtolower($item['test_result'] ?? '') === 'not ok')
                                                    <span class="text-red-600">NOT OK</span>
                                                @else
                                                    <span class="text-gray-400">-</span>
                                                @endif
                                            </td>

                                            {{-- Inspection Result --}}
                                            <td class="px-3 py-2 border text-center font-semibold">
                                                @if (strtoupper($item['inspection_result'] ?? '') === 'OK')
                                                    <span class="text-green-600">OK</span>
                                                @elseif (strtoupper($item['inspection_result'] ?? '') === 'NOT OK')
                                                    <span class="text-red-600">NOT OK</span>
                                                @else
                                                    <span class="text-gray-400">-</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    {{-- DOKUMEN MATERIAL --}}
                    <div class="mb-6">
                        <h3 class="text-xs font-semibold uppercase text-gray-500 mb-3">Dokumen Material</h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            @php
                                $documents = $material->files->filter(function ($file) {
                                    return in_array(strtolower(pathinfo($file->file_name, PATHINFO_EXTENSION)), [
                                        'pdf',
                                        'doc',
                                        'docx',
                                        'xls',
                                        'xlsx',
                                        'txt',
                                    ]);
                                });
                            @endphp

                            @forelse($documents as $file)
                                <div class="border rounded-lg p-4 bg-gray-50 text-xs space-y-2">
                                    <div class="font-medium text-gray-800">{{ $file->file_name ?? '-' }}</div>
                                    <div class="text-gray-500">Kategori: {{ strtoupper($file->category ?? '-') }}</div>
                                    <a href="{{ route('incoming-material.file', basename($file->file_path)) }}"
                                        target="_blank"
                                        class="inline-block px-3 py-1 bg-blue-600 text-white text-xs font-semibold rounded hover:bg-blue-700 transition">
                                        Lihat File
                                    </a>
                                </div>
                            @empty
                                <div class="text-gray-500 text-xs">Tidak ada dokumen.</div>
                            @endforelse
                        </div>
                    </div>

                    {{-- FOTO MATERIAL --}}
                    <div>
                        <h3 class="text-xs font-semibold uppercase text-gray-500 mb-3">Foto Material</h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            @php
                                $photos = $material->files->filter(function ($file) {
                                    return in_array(strtolower(pathinfo($file->file_name, PATHINFO_EXTENSION)), [
                                        'jpg',
                                        'jpeg',
                                        'png',
                                        'gif',
                                        'webp',
                                    ]);
                                });
                            @endphp

                            @forelse($photos as $file)
                                <div class="border rounded-lg p-4 bg-gray-50 text-xs space-y-2">
                                    <div class="font-medium text-gray-800">{{ $file->file_name ?? '-' }}</div>
                                    <div class="text-gray-500">Kategori: {{ strtoupper($file->category ?? '-') }}</div>
                                    <a href="{{ route('incoming-material.file', basename($file->file_path)) }}"
                                        target="_blank"
                                        class="inline-block px-3 py-1 bg-blue-600 text-white text-xs font-semibold rounded hover:bg-blue-700 transition">
                                        Lihat Foto
                                    </a>
                                </div>
                            @empty
                                <div class="text-gray-500 text-xs">Tidak ada foto.</div>
                            @endforelse
                        </div>
                    </div>
                    {{-- CATATAN --}}
                    <div>
                        <h3 class="text-xs font-semibold uppercase text-gray-500 mb-2">Catatan</h3>
                        <div class="border rounded-lg p-4 bg-gray-50 text-xs text-gray-700">
                            {{ $material->notes ?? 'Tidak ada catatan.' }}
                        </div>
                    </div>
                    @php
                        $logs = \App\Models\Log::where('model_type', get_class($material))
                            ->where('model_id', $material->id)
                            ->with(['changes', 'user'])
                            ->latest()
                            ->get();
                    @endphp

                    @if ($logs->count())
                        <div class="px-5 py-4 sm:px-6 sm:py-5 border-t border-gray-100">

                            <h3 class="text-xs font-semibold uppercase text-gray-500 mb-2">
                                Riwayat Perubahan
                            </h3>

                            {{-- CONTAINER SCROLL --}}
                            <div class="space-y-2 max-h-30 overflow-y-auto pr-2">

                                @foreach ($logs as $log)
                                    <div class="border rounded-lg p-3 bg-gray-50 text-xs space-y-2">
                                        <div class="flex justify-between">
                                            <span class="font-semibold text-gray-700">
                                                {{ ucfirst($log->action) }}
                                            </span>
                                            <span class="text-gray-400">
                                                {{ $log->created_at->format('d M Y') }}
                                            </span>
                                        </div>

                                        <div class="text-gray-500">
                                            Oleh: {{ $log->user->name ?? 'System' }}
                                        </div>

                                        @foreach ($log->changes as $change)
                                            <div>
                                                <span class="font-medium">
                                                    {{ ucfirst(str_replace('_', ' ', $change->field)) }}
                                                </span>
                                                :
                                                <span class="text-red-600">
                                                    {{ $change->old_value ?? '-' }}
                                                </span>
                                                →
                                                <span class="text-emerald-600">
                                                    {{ $change->new_value ?? '-' }}
                                                </span>
                                            </div>
                                        @endforeach
                                    </div>
                                @endforeach

                            </div>
                        </div>
                    @endif
                </div>

                {{-- FOOTER --}}
                <div class="px-6 py-4 border-t bg-gray-50 flex justify-end rounded-b-2xl">
                    <button wire:click="closeDetail"
                        class="px-4 py-2 text-xs rounded-xl bg-gray-100 hover:bg-gray-200">Tutup</button>
                </div>

            </div>
        </div>
    @endif
</div>
