@php
    use Carbon\Carbon;
    use App\Models\Domains\IncomingMaterial\Models\IncomingMaterial;

    // ================= MONTH SELECTION =================
    $selectedMonth = request('month') ?? Carbon::now()->format('Y-m');
    $currentDate = Carbon::createFromFormat('Y-m', $selectedMonth);
    $previousDate = $currentDate->copy()->subMonth();

    // ================= ALL TIME DATA =================
    $allData = IncomingMaterial::latest('date')->get();
    $totalAll = $allData->count();
    $totalAccepted = $allData->where('status', 'ACCEPTED')->count();
    $totalHold = $allData->where('status', 'HOLD')->count();
    $totalRejected = $allData->where('status', 'REJECTED')->count();

    // ================= MONTHLY DATA =================
    $incomingData = IncomingMaterial::whereYear('date', $currentDate->year)
        ->whereMonth('date', $currentDate->month)
        ->latest('date')
        ->get();

    $previousMonthData = IncomingMaterial::whereYear('date', $previousDate->year)
        ->whereMonth('date', $previousDate->month)
        ->get();

    // ================= MONTHLY CALCULATION =================
    $monthlyTotal = $incomingData->count();
    $monthlyAccepted = $incomingData->where('status', 'ACCEPTED')->count();
    $monthlyHold = $incomingData->where('status', 'HOLD')->count();
    $monthlyRejected = $incomingData->where('status', 'REJECTED')->count();

    $monthlyRate = $monthlyTotal > 0 ? round(($monthlyAccepted / $monthlyTotal) * 100, 1) : 0;
    $previousRate =
        $previousMonthData->count() > 0
            ? round(($previousMonthData->where('status', 'ACCEPTED')->count() / $previousMonthData->count()) * 100, 1)
            : 0;
    $trend = round($monthlyRate - $previousRate, 1);
    $rejectRate = $monthlyTotal > 0 ? round(($monthlyRejected / $monthlyTotal) * 100, 1) : 0;
@endphp

<div id="page-content" class="space-y-6">

    {{-- ================= OVERVIEW CARD ================= --}}
    <div class="bg-white shadow-xl rounded-2xl border border-gray-200 overflow-hidden">

        {{-- HEADER --}}
        <div class="px-6 py-5 bg-gradient-to-r from-green-50 to-emerald-50 border-b border-gray-200">
            <h2 class="text-lg font-bold text-gray-900">Incoming Material Overview</h2>
            <p class="text-sm text-gray-600 mt-1">Ringkasan penerimaan material berdasarkan status inspeksi.</p>
        </div>

        {{-- ALL TIME SUMMARY --}}
        <div class="p-6 grid grid-cols-1 md:grid-cols-4 gap-6">
            <div class="bg-gray-50 p-5 rounded-xl border">
                <div class="text-sm text-gray-500">Total Kedatangan</div>
                <div class="text-3xl font-bold mt-2">{{ $totalAll }}</div>
            </div>
            <div class="bg-green-50 p-5 rounded-xl border border-green-200">
                <div class="text-sm text-green-700">Accepted</div>
                <div class="text-3xl font-bold text-green-800 mt-2">{{ $totalAccepted }}</div>
            </div>
            <div class="bg-yellow-50 p-5 rounded-xl border border-yellow-200">
                <div class="text-sm text-yellow-700">Hold</div>
                <div class="text-3xl font-bold text-yellow-800 mt-2">{{ $totalHold }}</div>
            </div>
            <div class="bg-red-50 p-5 rounded-xl border border-red-200">
                <div class="text-sm text-red-700">Rejected</div>
                <div class="text-3xl font-bold text-red-800 mt-2">{{ $totalRejected }}</div>
            </div>
        </div>

        {{-- MONTHLY SUMMARY --}}
        <div class="border-t border-gray-200 bg-gray-50 px-6 py-6">

            {{-- Dropdown & Trend --}}
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
                <div class="flex items-center gap-4">
                    <h3 class="text-md font-bold text-gray-800">Summary Bulan
                        {{ $currentDate->translatedFormat('F Y') }}</h3>
                    <form method="GET">
                        <input type="month" name="month" value="{{ $selectedMonth }}" onchange="this.form.submit()"
                            class="border rounded-lg px-3 py-2 text-sm bg-white shadow-sm">
                    </form>
                </div>
                <div class="text-sm">
                    Dibanding {{ $previousDate->translatedFormat('F Y') }} :
                    @if ($trend > 0)
                        <span class="text-green-600 font-semibold">▲ +{{ $trend }}%</span>
                    @elseif($trend < 0)
                        <span class="text-red-600 font-semibold">▼ {{ $trend }}%</span>
                    @else
                        <span class="text-gray-500">Tidak berubah</span>
                    @endif
                </div>
            </div>

            {{-- ALERT REJECT --}}
            @if ($rejectRate > 5)
                <div class="mb-6 p-4 rounded-xl bg-red-50 border border-red-200">
                    <div class="font-semibold text-red-700">⚠️ Reject Rate {{ $rejectRate }}%</div>
                    <div class="text-sm text-red-600">Melebihi batas toleransi 5%. Segera lakukan evaluasi supplier atau
                        proses inspeksi.</div>
                </div>
            @endif

            {{-- Acceptance Rate --}}
            <div class="mb-6">
                <div class="flex justify-between text-sm mb-2">
                    <span class="font-medium text-gray-700">Acceptance Rate</span>
                    <span
                        class="font-semibold {{ $monthlyRate >= 90 ? 'text-green-600' : ($monthlyRate >= 70 ? 'text-yellow-600' : 'text-red-600') }}">
                        {{ $monthlyRate }}%
                    </span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-3">
                    <div class="h-3 rounded-full transition-all duration-500 {{ $monthlyRate >= 90 ? 'bg-green-500' : ($monthlyRate >= 70 ? 'bg-yellow-500' : 'bg-red-500') }}"
                        style="width: {{ $monthlyRate }}%">
                    </div>
                </div>
            </div>

            {{-- Monthly Cards --}}
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <div class="bg-white p-4 rounded-xl border shadow-sm">
                    <div class="text-xs text-gray-500">Total Bulan Ini</div>
                    <div class="text-2xl font-bold mt-1">{{ $monthlyTotal }}</div>
                </div>
                <div class="bg-green-100 p-4 rounded-xl border border-green-200 shadow-sm">
                    <div class="text-xs text-green-700">Accepted</div>
                    <div class="text-2xl font-bold text-green-800 mt-1">{{ $monthlyAccepted }}</div>
                </div>
                <div class="bg-yellow-100 p-4 rounded-xl border border-yellow-200 shadow-sm">
                    <div class="text-xs text-yellow-700">Hold</div>
                    <div class="text-2xl font-bold text-yellow-800 mt-1">{{ $monthlyHold }}</div>
                </div>
                <div class="bg-red-100 p-4 rounded-xl border border-red-200 shadow-sm">
                    <div class="text-xs text-red-700">Rejected</div>
                    <div class="text-2xl font-bold text-red-800 mt-1">{{ $monthlyRejected }}</div>
                </div>
            </div>

        </div>

    </div>

    {{-- ================= LIST TABLE ================= --}}
    <div class="bg-white shadow-xl rounded-2xl border border-gray-200 overflow-hidden">
        <div
            class="bg-gradient-to-r from-blue-50 to-indigo-50 px-6 py-6 border-b border-gray-200 flex flex-col md:flex-row md:justify-between md:items-center gap-4">
            <div>
                <h2 class="text-2xl font-bold text-gray-900">Incoming Material</h2>
                <p class="text-sm text-gray-600 mt-1">Monitoring hasil inspeksi penerimaan material tahap 1.</p>
            </div>
            <div class="flex justify-start md:justify-end">
                <button wire:click="$dispatch('openIncomingMaterialForm')" type="button"
                    class="group bg-gradient-to-r from-green-600 to-green-600
                           hover:from-green-700 hover:to-green-700
                           text-white px-5 py-3 rounded-xl text-sm font-semibold
                           inline-flex items-center shadow-lg hover:shadow-xl
                           transition-all duration-300 transform hover:scale-105">
                    <svg class="w-5 h-5 mr-2 group-hover:rotate-90 transition-transform duration-300" fill="none"
                        stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Data Barang
                </button>
            </div>
        </div>

        {{-- TABLE --}}
        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead class="bg-gradient-to-r from-gray-50 to-gray-100">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase">Tanggal</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase">Supplier</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase">Material</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase">Batch</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase">Qty</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase">Status</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase">Action</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-100">
                    @foreach ($incomingData as $row)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-6 py-4 text-sm">{{ \Carbon\Carbon::parse($row->date)->format('d M Y') }}</td>
                            <td class="px-6 py-4 text-sm font-semibold text-gray-800">{{ $row->supplier }}</td>
                            <td class="px-6 py-4 text-sm">{{ $row->material_name }}</td>
                            <td class="px-6 py-4 text-sm font-mono">{{ $row->batch_number }}</td>
                            <td class="px-6 py-4 text-sm">{{ number_format($row->quantity) }}</td>
                            <td class="px-6 py-4 text-sm text-center">
                                @php $baseClass = 'inline-flex items-center justify-center min-w-[100px] h-7 px-3 rounded-full text-xs font-semibold'; @endphp
                                @if ($row->status === 'ACCEPTED')
                                    <span class="{{ $baseClass }} bg-green-100 text-green-700">Accepted</span>
                                @elseif($row->status === 'HOLD')
                                    <span class="{{ $baseClass }} bg-yellow-100 text-yellow-700">Hold</span>
                                @else
                                    <span class="{{ $baseClass }} bg-red-100 text-red-700">Rejected</span>
                                @endif
                            </td>
                            <td class="px-6 py-5 whitespace-nowrap text-sm font-medium flex gap-2">
                                {{-- DETAIL --}}
                                @permission('incoming_material.view')
                                    <button wire:click="showIncomingMaterialDetail({{ $row->id }})"
                                        class="inline-flex items-center px-3 py-2 text-xs font-semibold text-green-600 bg-green-50 rounded-lg hover:bg-green-100 hover:text-green-700 transition-all duration-200">
                                        Detail
                                    </button>
                                @endpermission
                                {{-- EDIT --}}
                                @permission('incoming_material.edit')
                                    <button
                                        wire:click="$dispatchTo('incoming-material.incoming-material-form', 'openIncomingMaterialForm', { id: {{ $row->id }} })"
                                        class="inline-flex items-center px-3 py-2 text-xs font-semibold text-blue-600 bg-blue-50 rounded-lg hover:bg-blue-100 hover:text-blue-700 transition-all duration-200">
                                        Edit
                                    </button>
                                @endpermission
                                {{-- DELETE --}}
                                @permission('incoming_material.delete')
                                    <button x-on:click="$dispatch('confirm-delete', {{ $row->id }})"
                                        class="inline-flex items-center px-3 py-2 text-xs font-semibold text-red-600 bg-red-50 rounded-lg hover:bg-red-100 hover:text-red-700 transition-all duration-200">
                                        Delete
                                    </button>
                                @endpermission
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- ================= MODAL DELETE ================= --}}
    <div x-data="{ open: false, deleteId: null }" x-on:confirm-delete.window="open = true; deleteId = $event.detail" x-show="open"
        x-transition.opacity
        class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 min-h-screen overflow-y-auto">

        <div class="bg-white rounded-lg shadow-lg w-96 p-5 mx-4 sm:mx-0" @click.away="open = false"
            x-transition.scale.100.origin.center>
            <h3 class="text-lg font-semibold text-gray-700 mb-4">Konfirmasi Hapus</h3>
            <p class="text-sm text-gray-500 mb-6">Apakah Anda yakin ingin menghapus data ini? Tindakan ini tidak dapat
                dikembalikan.</p>

            <div class="flex justify-end space-x-3">
                <button @click="open = false"
                    class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded hover:bg-gray-200">
                    Batal
                </button>
                <button @click="$wire.delete(deleteId); open = false"
                    class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded hover:bg-red-700">
                    Hapus
                </button>
            </div>
        </div>
    </div>
</div>
