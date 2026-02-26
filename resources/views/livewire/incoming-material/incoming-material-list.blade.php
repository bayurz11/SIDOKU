@php
    use Carbon\Carbon;

    // ===============================
    // STATIC DATA - INCOMING MATERIAL
    // ===============================
    $incomingData = collect([
        (object) [
            'id' => 1,
            'date' => '2026-02-01',
            'supplier' => 'PT Sumber Pangan',
            'material_name' => 'Gula Kristal',
            'batch_number' => 'GL-2201',
            'quantity' => 500,
            'status' => 'ACCEPTED',
        ],
        (object) [
            'id' => 2,
            'date' => '2026-02-03',
            'supplier' => 'PT Indo Ingredient',
            'material_name' => 'Essence Lemon',
            'batch_number' => 'EL-8891',
            'quantity' => 120,
            'status' => 'HOLD',
        ],
        (object) [
            'id' => 3,
            'date' => '2026-02-05',
            'supplier' => 'CV Makmur Jaya',
            'material_name' => 'Botol PET',
            'batch_number' => 'BP-7744',
            'quantity' => 1000,
            'status' => 'REJECTED',
        ],
    ]);

    // ===============================
    // TOTAL ALL TIME
    // ===============================
    $totalAll = $incomingData->count();
    $totalAccepted = $incomingData->where('status', 'ACCEPTED')->count();
    $totalHold = $incomingData->where('status', 'HOLD')->count();
    $totalRejected = $incomingData->where('status', 'REJECTED')->count();

    // ===============================
    // MONTH SELECTION
    // ===============================
    $selectedMonth = request('month') ?? Carbon::now()->format('Y-m');
    $currentDate = Carbon::createFromFormat('Y-m', $selectedMonth);
    $previousDate = $currentDate->copy()->subMonth();

    // ===============================
    // FILTER CURRENT MONTH
    // ===============================
    $monthlyData = $incomingData->filter(function ($row) use ($currentDate) {
        $date = Carbon::parse($row->date);
        return $date->month == $currentDate->month && $date->year == $currentDate->year;
    });

    // ===============================
    // FILTER PREVIOUS MONTH
    // ===============================
    $previousMonthData = $incomingData->filter(function ($row) use ($previousDate) {
        $date = Carbon::parse($row->date);
        return $date->month == $previousDate->month && $date->year == $previousDate->year;
    });

    // ===============================
    // MONTHLY CALCULATION
    // ===============================
    $monthlyTotal = $monthlyData->count();
    $monthlyAccepted = $monthlyData->where('status', 'ACCEPTED')->count();
    $monthlyHold = $monthlyData->where('status', 'HOLD')->count();
    $monthlyRejected = $monthlyData->where('status', 'REJECTED')->count();

    $monthlyRate = $monthlyTotal > 0 ? round(($monthlyAccepted / $monthlyTotal) * 100, 1) : 0;

    $previousRate =
        $previousMonthData->count() > 0
            ? round(($previousMonthData->where('status', 'ACCEPTED')->count() / $previousMonthData->count()) * 100, 1)
            : 0;

    $trend = round($monthlyRate - $previousRate, 1);

    $rejectRate = $monthlyTotal > 0 ? round(($monthlyRejected / $monthlyTotal) * 100, 1) : 0;
@endphp

<div class="space-y-6">

    {{-- ================= OVERVIEW CARD ================= --}}
    <div class="bg-white shadow-xl rounded-2xl border border-gray-200 overflow-hidden">

        {{-- HEADER --}}
        <div class="px-6 py-5 bg-gradient-to-r from-green-50 to-emerald-50 border-b border-gray-200">
            <h2 class="text-lg font-bold text-gray-900">
                Incoming Material Overview
            </h2>
            <p class="text-sm text-gray-600 mt-1">
                Ringkasan penerimaan material berdasarkan status inspeksi.
            </p>
        </div>

        {{-- ================= ALL TIME SUMMARY ================= --}}
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">

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
        </div>

        {{-- ================= MONTHLY SUMMARY ================= --}}
        <div class="border-t border-gray-200 bg-gray-50 px-6 py-6">

            {{-- Dropdown & Trend --}}
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">

                <div class="flex items-center gap-4">
                    <h3 class="text-md font-bold text-gray-800">
                        Summary Bulan {{ $currentDate->translatedFormat('F Y') }}
                    </h3>

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
                    <div class="font-semibold text-red-700">
                        ⚠️ Reject Rate {{ $rejectRate }}%
                    </div>
                    <div class="text-sm text-red-600">
                        Melebihi batas toleransi 5%. Segera lakukan evaluasi supplier atau proses inspeksi.
                    </div>
                </div>
            @endif

            {{-- Acceptance Rate --}}
            <div class="mb-6">
                <div class="flex justify-between text-sm mb-2">
                    <span class="font-medium text-gray-700">Acceptance Rate</span>
                    <span
                        class="font-semibold
                    {{ $monthlyRate >= 90 ? 'text-green-600' : ($monthlyRate >= 70 ? 'text-yellow-600' : 'text-red-600') }}">
                        {{ $monthlyRate }}%
                    </span>
                </div>

                <div class="w-full bg-gray-200 rounded-full h-3">
                    <div class="h-3 rounded-full transition-all duration-500
                    {{ $monthlyRate >= 90 ? 'bg-green-500' : ($monthlyRate >= 70 ? 'bg-yellow-500' : 'bg-red-500') }}"
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
                    <div class="text-2xl font-bold text-green-800 mt-1">
                        {{ $monthlyAccepted }}
                    </div>
                </div>

                <div class="bg-yellow-100 p-4 rounded-xl border border-yellow-200 shadow-sm">
                    <div class="text-xs text-yellow-700">Hold</div>
                    <div class="text-2xl font-bold text-yellow-800 mt-1">
                        {{ $monthlyHold }}
                    </div>
                </div>

                <div class="bg-red-100 p-4 rounded-xl border border-red-200 shadow-sm">
                    <div class="text-xs text-red-700">Rejected</div>
                    <div class="text-2xl font-bold text-red-800 mt-1">
                        {{ $monthlyRejected }}
                    </div>
                </div>

            </div>
        </div>

    </div>


    {{-- ================= LIST TABLE ================= --}}
    <div class="bg-white shadow-xl rounded-2xl border border-gray-200 overflow-hidden">

        {{-- HEADER --}}
        <div class="bg-gradient-to-r from-blue-50 to-indigo-50 px-6 py-6 border-b border-gray-200">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">

                <div>
                    <h2 class="text-2xl font-bold text-gray-900">Incoming Material</h2>
                    <p class="text-sm text-gray-600 mt-1">
                        Monitoring hasil inspeksi penerimaan material tahap 1.
                    </p>
                </div>

                {{-- BUTTON TAMBAH DATA --}}
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
                            <td class="px-6 py-4 text-sm">
                                {{ \Carbon\Carbon::parse($row->date)->format('d M Y') }}
                            </td>
                            <td class="px-6 py-4 text-sm font-semibold text-gray-800">
                                {{ $row->supplier }}
                            </td>
                            <td class="px-6 py-4 text-sm">
                                {{ $row->material_name }}
                            </td>
                            <td class="px-6 py-4 text-sm font-mono">
                                {{ $row->batch_number }}
                            </td>
                            <td class="px-6 py-4 text-sm">
                                {{ number_format($row->quantity) }}
                            </td>
                            <td class="px-6 py-4 text-sm">
                                @if ($row->status === 'ACCEPTED')
                                    <span
                                        class="px-3 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-700">
                                        Accepted
                                    </span>
                                @elseif($row->status === 'HOLD')
                                    <span
                                        class="px-3 py-1 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-700">
                                        Hold
                                    </span>
                                @else
                                    <span class="px-3 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-700">
                                        Rejected
                                    </span>
                                @endif
                            </td>
                            {{-- Aksi --}}
                            <td class="px-6 py-5 whitespace-nowrap text-sm font-medium">
                                <div class="flex items-center gap-2">

                                    {{-- DETAIL --}}
                                    @permission('incoming_material.view')
                                        <button
                                            wire:click="$dispatch('openIncomingMaterialDetail', { id: {{ $row->id ?? 1 }} })"
                                            class="inline-flex items-center px-3 py-2 text-xs font-semibold text-green-600 bg-green-50 rounded-lg hover:bg-green-100 hover:text-green-700 transition-all duration-200">

                                            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24" stroke-width="1.5">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                            </svg>
                                            Detail
                                        </button>
                                    @endpermission
                                    {{-- VIEW DOKUMEN --}}
                                    @permission('incoming_material.view')
                                        <a href="#" target="_blank"
                                            class="inline-flex items-center px-3 py-2 text-xs font-semibold text-purple-600 bg-purple-50 rounded-lg hover:bg-purple-100 hover:text-purple-700 transition-all duration-200">

                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 mr-1.5" fill="none"
                                                stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                                            </svg>

                                            Dokumen
                                        </a>
                                    @endpermission


                                    {{-- PRINT DOKUMEN --}}
                                    @permission('incoming_material.view')
                                        <a href="#" target="_blank"
                                            class="inline-flex items-center px-3 py-2 text-xs font-semibold text-gray-600 bg-gray-100 rounded-lg hover:bg-gray-200 hover:text-gray-800 transition-all duration-200">

                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 mr-1.5" fill="none"
                                                stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0 1 10.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0 .229 2.523a1.125 1.125 0 0 1-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0 0 21 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 0 0-1.913-.247M6.34 18H5.25A2.25 2.25 0 0 1 3 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 0 1 1.913-.247m10.5 0a48.536 48.536 0 0 0-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5Zm-3 0h.008v.008H15V10.5Z" />
                                            </svg>
                                            Print
                                        </a>
                                    @endpermission

                                    {{-- EDIT --}}
                                    @permission('incoming_material.edit')
                                        <button
                                            wire:click="$dispatch('openIncomingMaterialForm', { id: {{ $row->id ?? 1 }} })"
                                            class="inline-flex items-center px-3 py-2 text-xs font-semibold text-blue-600 bg-blue-50 rounded-lg hover:bg-blue-100 hover:text-blue-700 transition-all duration-200">

                                            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414
                                                                                                                                     a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                            Edit
                                        </button>
                                    @endpermission


                                    {{-- DELETE --}}
                                    @permission('incoming_material.delete')
                                        <button wire:click="delete({{ $row->id ?? 1 }})"
                                            wire:confirm="Yakin ingin menghapus data ini?"
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
                    @endforeach
                </tbody>
            </table>
        </div>

    </div>

</div>
@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {

            // ================= MIXED CHART =================
            const payload = document.getElementById('incomingDataPayload');
            if (payload) {
                const labels = JSON.parse(payload.dataset.labels);
                const totals = JSON.parse(payload.dataset.totals);
                const acceptance = JSON.parse(payload.dataset.acceptance);

                new Chart(document.getElementById('incomingMixedChart'), {
                    data: {
                        labels: labels,
                        datasets: [{
                                type: 'bar',
                                label: 'Total Material',
                                data: totals,
                                backgroundColor: 'rgba(59,130,246,0.6)',
                                borderRadius: 6,
                                yAxisID: 'y'
                            },
                            {
                                type: 'line',
                                label: 'Acceptance Rate (%)',
                                data: acceptance,
                                borderColor: '#16a34a',
                                backgroundColor: '#16a34a',
                                tension: 0.3,
                                yAxisID: 'y1'
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: true,
                                position: 'left'
                            },
                            y1: {
                                beginAtZero: true,
                                max: 100,
                                position: 'right',
                                grid: {
                                    drawOnChartArea: false
                                }
                            }
                        }
                    }
                });
            }

            // ================= PIE CHART =================
            const statusPayload = document.getElementById('incomingStatusPayload');
            if (statusPayload) {
                const values = JSON.parse(statusPayload.dataset.values);

                new Chart(document.getElementById('incomingPieChart'), {
                    type: 'pie',
                    data: {
                        labels: ['Accepted', 'Hold', 'Rejected'],
                        datasets: [{
                            data: values,
                            backgroundColor: [
                                '#16a34a',
                                '#eab308',
                                '#dc2626'
                            ]
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false
                    }
                });
            }

        });
    </script>
@endpush
