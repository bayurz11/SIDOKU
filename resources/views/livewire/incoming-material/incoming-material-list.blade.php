@php
    // ===============================
    // STATIC DATA - INCOMING MATERIAL
    // ===============================
    $incomingData = collect([
        (object) [
            'date' => '2026-02-01',
            'supplier' => 'PT Sumber Pangan',
            'material_name' => 'Gula Kristal',
            'batch_number' => 'GL-2201',
            'quantity' => 500,
            'status' => 'ACCEPTED',
        ],
        (object) [
            'date' => '2026-02-03',
            'supplier' => 'PT Indo Ingredient',
            'material_name' => 'Essence Lemon',
            'batch_number' => 'EL-8891',
            'quantity' => 120,
            'status' => 'HOLD',
        ],
        (object) [
            'date' => '2026-02-05',
            'supplier' => 'CV Makmur Jaya',
            'material_name' => 'Botol PET',
            'batch_number' => 'BP-7744',
            'quantity' => 1000,
            'status' => 'REJECTED',
        ],
    ]);

    $totalAll = $incomingData->count();
    $totalAccepted = $incomingData->where('status', 'ACCEPTED')->count();
    $totalHold = $incomingData->where('status', 'HOLD')->count();
    $totalRejected = $incomingData->where('status', 'REJECTED')->count();
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
    </div>

    {{-- ================= LIST TABLE ================= --}}
    <div class="bg-white shadow-xl rounded-2xl border border-gray-200 overflow-hidden">

        {{-- HEADER --}}
        <div class="bg-gradient-to-r from-blue-50 to-indigo-50 px-6 py-6 border-b border-gray-200">
            <h2 class="text-2xl font-bold text-gray-900">Incoming Material</h2>
            <p class="text-sm text-gray-600 mt-1">
                Monitoring hasil inspeksi penerimaan material tahap 1.
            </p>
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
