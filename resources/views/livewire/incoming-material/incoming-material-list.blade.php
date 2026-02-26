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
    // MONTHLY SUMMARY
    // ===============================
    $currentMonth = Carbon::now()->month;
    $currentYear = Carbon::now()->year;

    $monthlyData = $incomingData->filter(function ($row) use ($currentMonth, $currentYear) {
        $date = Carbon::parse($row->date);
        return $date->month == $currentMonth && $date->year == $currentYear;
    });

    $monthlyTotal = $monthlyData->count();
    $monthlyAccepted = $monthlyData->where('status', 'ACCEPTED')->count();
    $monthlyHold = $monthlyData->where('status', 'HOLD')->count();
    $monthlyRejected = $monthlyData->where('status', 'REJECTED')->count();

    $monthlyRate = $monthlyTotal > 0 ? round(($monthlyAccepted / $monthlyTotal) * 100, 1) : 0;
@endphp

<div class="space-y-6">

    {{-- ================= OVERVIEW CARD ================= --}}
    <div class="bg-white shadow-xl rounded-2xl border border-gray-200 overflow-hidden">

        <div class="px-6 py-5 bg-gradient-to-r from-green-50 to-emerald-50 border-b border-gray-200">
            <h2 class="text-lg font-bold text-gray-900">
                Incoming Material Overview
            </h2>
            <p class="text-sm text-gray-600 mt-1">
                Ringkasan penerimaan material berdasarkan status inspeksi.
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 p-6">

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
        {{-- ================= MONTHLY SUMMARY ================= --}}
        <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-4">
            <h3 class="text-md font-bold text-gray-800">
                Summary Bulan {{ \Carbon\Carbon::now()->translatedFormat('F Y') }}
            </h3>

            <div class="text-sm text-gray-500">
                Acceptance Rate:
                <span class="font-semibold text-green-600">
                    {{ $monthlyRate }}%
                </span>
            </div>
        </div>

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
                    <button wire:click="$dispatch('openIncomingMaterialForm')"
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
                                            class="inline-flex items-center px-3 py-2 text-xs font-semibold
                       text-green-600 bg-green-50 rounded-lg
                       hover:bg-green-100 hover:text-green-700
                       transition-all duration-200">

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


                                    {{-- EDIT --}}
                                    @permission('incoming_material.edit')
                                        <button
                                            wire:click="$dispatch('openIncomingMaterialForm', { id: {{ $row->id ?? 1 }} })"
                                            class="inline-flex items-center px-3 py-2 text-xs font-semibold
                       text-blue-600 bg-blue-50 rounded-lg
                       hover:bg-blue-100 hover:text-blue-700
                       transition-all duration-200">

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
                                            class="inline-flex items-center px-3 py-2 text-xs font-semibold
                       text-red-600 bg-red-50 rounded-lg
                       hover:bg-red-100 hover:text-red-700
                       transition-all duration-200">

                                            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6
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
