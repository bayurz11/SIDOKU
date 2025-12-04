@php
    use App\Domains\Ipc\Models\TiupBotolCheck;

    $dropTestLabels = TiupBotolCheck::DROP_TEST;

    // Data untuk chart
    $chartLabels = $dropSummary
        ->map(function ($row) use ($dropTestLabels) {
            return $dropTestLabels[$row->drop_test] ?? $row->drop_test;
        })
        ->values();

    $chartValues = $dropSummary->map(fn($row) => (int) $row->total_samples)->values();
@endphp

<div class="space-y-6">
    {{-- CARD CHART / OVERVIEW --}}
    <div class="bg-white shadow-xl rounded-2xl border border-gray-200 overflow-hidden">
        {{-- HEADER --}}
        <div
            class="px-4 py-4 sm:px-6 sm:py-5 bg-gradient-to-r from-emerald-50 via-blue-50 to-indigo-50
               border-b border-gray-200 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 sm:gap-4">

            <div class="flex items-start sm:items-center gap-3 sm:gap-4">
                <div
                    class="w-10 h-10 sm:w-12 sm:h-12 bg-gradient-to-br from-emerald-500 to-blue-600
                       rounded-xl flex items-center justify-center shadow-lg flex-shrink-0">
                    <svg class="w-5 h-5 sm:w-6 sm:h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 3v18h18M7 15l3-3 4 4 3-7" />
                    </svg>
                </div>
                <div>
                    <h2 class="text-base sm:text-lg font-bold text-gray-900">
                        Tiup Botol Overview
                    </h2>
                    <p class="text-xs sm:text-sm text-gray-600 mt-1">
                        Ringkasan jumlah sampel berdasarkan hasil Drop Test.
                    </p>
                </div>
            </div>
        </div>

        {{-- CHART --}}
        <div class="px-4 py-4 sm:px-6 sm:py-5">
            @if ($dropSummary->isEmpty())
                <p class="text-sm text-gray-500 italic">
                    Belum ada data tiup botol untuk ditampilkan. Atur filter tanggal terlebih dahulu.
                </p>
            @else
                {{-- tinggi lebih kecil di mobile --}}
                <div class="h-56 sm:h-72" wire:ignore>
                    <canvas id="tiupBotolChart"></canvas>
                </div>
            @endif
        </div>
    </div>

    {{-- CARD LIST TIUP BOTOL --}}
    <div class="bg-white shadow-xl rounded-2xl border border-gray-200 overflow-hidden">
        {{-- HEADER --}}
        <div class="bg-gradient-to-r from-blue-50 via-green-50 to-lime-50 px-6 py-6 border-b border-gray-200">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                {{-- LEFT SECTION --}}
                <div class="flex items-center space-x-4">
                    <div
                        class="w-12 h-12 bg-gradient-to-br from-blue-500 to-purple-600 rounded-xl flex items-center justify-center shadow-lg">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 6h16M4 10h16M4 14h10m-6 4h6" />
                        </svg>
                    </div>

                    <div>
                        <h2 class="text-2xl font-bold text-gray-900">Data Tiup Botol</h2>
                        <p class="text-sm text-gray-600 mt-1">
                            Kelola hasil uji tiup botol (drop test & kondisi visual).
                        </p>
                    </div>
                </div>

                {{-- RIGHT SECTION (BUTTON) --}}
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-end gap-3 w-full md:w-auto">
                    @permission('ipc_product_checks.create')
                        <button wire:click="$dispatch('openTiupBotolForm')"
                            class="group bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700
                               text-white px-5 py-3 rounded-xl text-sm font-semibold flex items-center
                               shadow-lg hover:shadow-xl transition-all duration-300 transform hover:scale-105
                               w-full sm:w-auto justify-center">
                            <svg class="w-5 h-5 mr-2 group-hover:rotate-90 transition-transform duration-300" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                            Tambah Data
                        </button>
                    @endpermission
                </div>
            </div>

            {{-- FILTERS --}}
            <div class="mt-6 space-y-4">
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                    {{-- Search --}}
                    <div class="flex-1">
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                            </div>
                            <input wire:model.live="search" type="text" placeholder="Cari nama botol / catatan..."
                                class="block w-full pl-12 pr-4 py-3 border border-gray-300 rounded-xl bg-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200">
                        </div>
                    </div>

                    {{-- Per page --}}
                    <div>
                        <select wire:model.live="perPage"
                            class="px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white text-sm font-medium transition-all duration-200">
                            <option value="10">10 per halaman</option>
                            <option value="25">25 per halaman</option>
                            <option value="50">50 per halaman</option>
                            <option value="100">100 per halaman</option>
                        </select>
                    </div>
                </div>

                {{-- Dropdown filter tanggal & drop test --}}
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Tanggal Dari</label>
                        <input type="date" wire:model.live="filterDateFrom"
                            class="block w-full px-3 py-2 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Tanggal Sampai</label>
                        <input type="date" wire:model.live="filterDateTo"
                            class="block w-full px-3 py-2 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Hasil Drop Test</label>
                        <select wire:model.live="filterDropTest"
                            class="block w-full px-3 py-2 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                            <option value="">Semua</option>
                            @foreach ($dropTestLabels as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Kosongkan 1 kolom biar rapi di desktop --}}
                    <div class="hidden md:block"></div>
                </div>
            </div>
        </div>

        {{-- TABLE --}}
        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead class="bg-gradient-to-r from-gray-50 to-gray-100">
                    <tr>
                        <th wire:click="sortBy('tanggal')"
                            class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider cursor-pointer hover:bg-gray-200 rounded-tl-xl">
                            Tanggal
                        </th>
                        <th wire:click="sortBy('nama_botol')"
                            class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider cursor-pointer hover:bg-gray-200">
                            Nama Botol
                        </th>
                        <th wire:click="sortBy('drop_test')"
                            class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider cursor-pointer hover:bg-gray-200">
                            Drop Test
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                            Penyebaran Rata
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                            Bottom Tidak Menonjol
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                            Tidak Ada Material
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                            Catatan
                        </th>
                        <th
                            class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider rounded-tr-xl">
                            Aksi
                        </th>
                    </tr>
                </thead>

                <tbody class="bg-white divide-y divide-gray-100">
                    @forelse($data as $row)
                        <tr
                            class="hover:bg-gradient-to-r hover:from-blue-50 hover:to-green-50 transition-all duration-300">
                            {{-- Tanggal --}}
                            <td class="px-6 py-5 whitespace-nowrap text-sm text-gray-800">
                                <div class="font-semibold">
                                    {{ optional($row->tanggal)->format('d M Y') }}
                                </div>
                            </td>

                            {{-- Nama Botol --}}
                            <td class="px-6 py-5 whitespace-nowrap text-sm text-gray-800">
                                <div class="font-semibold">
                                    {{ \Illuminate\Support\Str::limit($row->nama_botol, 40) }}
                                </div>
                            </td>

                            {{-- Drop Test --}}
                            <td class="px-6 py-5 whitespace-nowrap text-sm text-gray-800">
                                @php
                                    $label = $dropTestLabels[$row->drop_test] ?? $row->drop_test;
                                    $isOk = $row->drop_test === 'TDK_BCR';
                                @endphp
                                <span
                                    class="inline-flex items-center px-3 py-1.5 rounded-xl text-xs font-semibold border
                                        {{ $isOk ? 'bg-emerald-50 text-emerald-700 border-emerald-100' : 'bg-red-50 text-red-700 border-red-100' }}">
                                    {{ $label }}
                                </span>
                            </td>

                            {{-- Penyebaran Rata --}}
                            <td class="px-6 py-5 whitespace-nowrap text-sm text-gray-800">
                                @if ($row->penyebaran_rata)
                                    <span
                                        class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-100">
                                        {{ $row->penyebaran_rata }}
                                    </span>
                                    @if ($row->gambar_penyebaran_rata_url)
                                        <div class="mt-1">
                                            <img src="{{ $row->gambar_penyebaran_rata_url }}"
                                                class="w-10 h-10 rounded-lg object-cover border border-gray-200"
                                                alt="Penyebaran Rata">
                                        </div>
                                    @endif
                                @else
                                    <span class="text-xs text-gray-400 italic">-</span>
                                @endif
                            </td>

                            {{-- Bottom Tidak Menonjol --}}
                            <td class="px-6 py-5 whitespace-nowrap text-sm text-gray-800">
                                @if ($row->bottom_tidak_menonjol)
                                    <span
                                        class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-100">
                                        {{ $row->bottom_tidak_menonjol }}
                                    </span>
                                    @if ($row->gambar_bottom_tidak_menonjol_url)
                                        <div class="mt-1">
                                            <img src="{{ $row->gambar_bottom_tidak_menonjol_url }}"
                                                class="w-10 h-10 rounded-lg object-cover border border-gray-200"
                                                alt="Bottom Tidak Menonjol">
                                        </div>
                                    @endif
                                @else
                                    <span class="text-xs text-gray-400 italic">-</span>
                                @endif
                            </td>

                            {{-- Tidak Ada Material --}}
                            <td class="px-6 py-5 whitespace-nowrap text-sm text-gray-800">
                                @if ($row->tidak_ada_material)
                                    <span
                                        class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-100">
                                        {{ $row->tidak_ada_material }}
                                    </span>
                                    @if ($row->gambar_tidak_ada_material_url)
                                        <div class="mt-1">
                                            <img src="{{ $row->gambar_tidak_ada_material_url }}"
                                                class="w-10 h-10 rounded-lg object-cover border border-gray-200"
                                                alt="Tidak Ada Material">
                                        </div>
                                    @endif
                                @else
                                    <span class="text-xs text-gray-400 italic">-</span>
                                @endif
                            </td>

                            {{-- Catatan --}}
                            <td class="px-6 py-5 whitespace-nowrap text-sm text-gray-800">
                                @if ($row->catatan)
                                    <div class="text-xs text-gray-600">
                                        {{ \Illuminate\Support\Str::limit($row->catatan, 60) }}
                                    </div>
                                @else
                                    <span class="text-xs text-gray-400 italic">-</span>
                                @endif
                            </td>

                            {{-- Aksi --}}
                            <td class="px-6 py-5 whitespace-nowrap text-sm font-medium">
                                <div class="flex items-center gap-2">
                                    @permission('ipc_product_checks.view')
                                        <button wire:click="showDetail({{ $row->id }})"
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

                                    @permission('ipc_product_checks.delete')
                                        <button wire:click="delete({{ $row->id }})"
                                            class="inline-flex items-center px-3 py-2 text-xs font-semibold text-red-600 bg-red-50 rounded-lg hover:bg-red-100 hover:text-red-700 transition-all duration-200">
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
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-16 text-center">
                                <div class="flex flex-col items-center justify-center">
                                    <div
                                        class="w-20 h-20 bg-gradient-to-br from-gray-100 to-gray-200 rounded-2xl flex items-center justify-center mb-6 shadow-inner">
                                        <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M3 7.5A2.25 2.25 0 015.25 5.25h13.5A2.25 2.25 0 0121 7.5v9a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 16.5v-9zM7.5 9h9m-9 3.75h4.5" />
                                        </svg>
                                    </div>
                                    <h3 class="text-xl font-semibold text-gray-900 mb-2">Belum ada data Tiup Botol</h3>
                                    <p class="text-gray-500 mb-6 max-w-sm text-center">
                                        @if ($search || $filterDateFrom || $filterDateTo || $filterDropTest)
                                            Coba ubah kata kunci atau filter pencarian.
                                        @else
                                            Tambahkan data tiup botol pertama untuk mulai memonitor kualitas.
                                        @endif
                                    </p>

                                    @if (!$search && !$filterDateFrom && !$filterDateTo && !$filterDropTest)
                                        @permission('ipc_product_checks.create')
                                            <button wire:click="$dispatch('openTiupBotolForm')"
                                                class="group bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white px-6 py-3 rounded-xl text-sm font-semibold flex items-center shadow-lg hover:shadow-xl transition-all duration-300 transform hover:scale-105">
                                                <svg class="w-5 h-5 mr-2 group-hover:rotate-90 transition-transform duration-300"
                                                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                                </svg>
                                                Tambah Data
                                            </button>
                                        @endpermission
                                    @else
                                        <button
                                            wire:click="$set('search', ''); $set('filterDateFrom', null); $set('filterDateTo', null); $set('filterDropTest', null)"
                                            class="inline-flex items-center px-6 py-3 bg-gray-100 text-gray-700 font-semibold rounded-xl hover:bg-gray-200 transition-all duration-300">
                                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0
                                                    0a8.003 8.003 0 01-15.357-2m15.357
                                                    2H15" />
                                            </svg>
                                            Hapus Filter
                                        </button>
                                    @endif
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
                    data Tiup Botol.
                </div>
                <div class="flex-1 flex justify-center md:justify-end">
                    {{ $data->links() }}
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        (function() {
            if (window.__tiupBotolChartInitialized) return;
            window.__tiupBotolChartInitialized = true;

            window.tiupBotolChart = null;

            function renderTiupBotolChart() {
                const canvas = document.getElementById('tiupBotolChart');
                if (!canvas) return;

                const labels = @json($chartLabels ?? []);
                const dataValues = @json($chartValues ?? []);

                if (!labels.length || !dataValues.length) {
                    if (window.tiupBotolChart && typeof window.tiupBotolChart.destroy === 'function') {
                        window.tiupBotolChart.destroy();
                        window.tiupBotolChart = null;
                    }
                    return;
                }

                if (window.tiupBotolChart && typeof window.tiupBotolChart.destroy === 'function') {
                    window.tiupBotolChart.destroy();
                    window.tiupBotolChart = null;
                }

                const ctx = canvas.getContext('2d');

                window.tiupBotolChart = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: 'Jumlah Sampel',
                            data: dataValues,
                            backgroundColor: 'rgba(59, 130, 246, 0.6)',
                            borderColor: 'rgba(37, 99, 235, 1)',
                            borderWidth: 1,
                            borderRadius: 6,
                        }]
                    },
                    options: {
                        indexAxis: 'y',
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            x: {
                                beginAtZero: true,
                                title: {
                                    display: true,
                                    text: 'Jumlah Sampel'
                                }
                            },
                            y: {
                                ticks: {
                                    autoSkip: false,
                                    font: {
                                        size: 10
                                    }
                                }
                            }
                        },
                        plugins: {
                            legend: {
                                display: false
                            },
                            tooltip: {
                                callbacks: {
                                    label: (ctx) => ctx.parsed.x + ' sampel'
                                }
                            }
                        }
                    }
                });
            }

            function boot() {
                renderTiupBotolChart();

                if (window.Livewire) {
                    Livewire.hook('message.processed', () => {
                        renderTiupBotolChart();
                    });
                }
            }

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', boot);
            } else {
                boot();
            }
        })();
    </script>
@endpush
