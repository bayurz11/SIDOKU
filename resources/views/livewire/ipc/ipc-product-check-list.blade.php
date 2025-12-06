@php
    use Illuminate\Support\Str;

    $lineGroupLabels = \App\Domains\Ipc\Models\IpcProductCheck::LINE_GROUPS;
    $subLineLabels = \App\Domains\Ipc\Models\IpcProductCheck::SUB_LINES_TEH ?? [];

    // Bentuk label dan value untuk Chart.js (tetap dari $moistureSummary)
    $chartLabels = $moistureSummary
        ->map(function ($row) use ($lineGroupLabels, $subLineLabels) {
            $lineLabel = $lineGroupLabels[$row->line_group] ?? $row->line_group;
            $subLabel = $row->sub_line ? $subLineLabels[$row->sub_line] ?? $row->sub_line : null;

            return $subLabel ? "{$lineLabel} - {$subLabel}" : $lineLabel;
        })
        ->values();

    $chartValues = $moistureSummary
        ->map(function ($row) {
            return round($row->avg_moisture, 2);
        })
        ->values();

    // 🔴 ALERT: ambil dari data asli ($data), bukan summary
    // $data biasanya Paginator ->getCollection() untuk ambil koleksinya
    $highMoistureItems = $data
        ->getCollection()
        ->filter(function ($ipc) {
            return $ipc->avg_moisture_percent >= 10;
        })
        ->map(function ($ipc) {
            // samakan nama field agar bisa pakai $row->avg_moisture di Blade
            $ipc->avg_moisture = $ipc->avg_moisture_percent;
            return $ipc;
        });

    $hasHighMoistureAlert = $highMoistureItems->isNotEmpty();
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
                        IPC Product Overview
                    </h2>
                    <p class="text-xs sm:text-sm text-gray-600 mt-1">
                        Ringkasan jumlah pemeriksaan IPC per Line dari data yang sedang ditampilkan.
                    </p>
                </div>
            </div>
        </div>

        {{-- "Chart" per Line Group --}}
        <div class="px-4 py-4 sm:px-6 sm:py-5">
            @if ($moistureSummary->isEmpty())
                <p class="text-sm text-gray-500 italic">
                    Belum ada data moisture untuk ditampilkan. Atur filter line / tanggal terlebih dahulu.
                </p>
            @else
                {{-- ALERT KADAR AIR TINGGI --}}
                @if ($hasHighMoistureAlert)
                    <div class="mb-4 rounded-xl border border-red-300 bg-red-50 px-4 py-3 shadow-sm">
                        <div class="flex items-start gap-3">
                            <div
                                class="flex h-10 w-10 items-center justify-center rounded-full bg-red-500 text-white flex-shrink-0">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 9v3m0 4h.01M10.29 3.86l-8.02 14A2 2 0 0 0 4.02 21h15.96a2 2 0 0 0 1.73-3.14l-8.02-14a2 2 0 0 0-3.46 0Z" />
                                </svg>
                            </div>

                            <div>
                                <h4 class="text-sm font-semibold text-red-800">
                                    ⚠ Peringatan Kadar Air Tinggi (≥ 10%)
                                </h4>
                                <p class="text-xs text-red-700 mt-1">
                                    Terdapat {{ $highMoistureItems->count() }} line / produk dengan kadar air di atas
                                    atau
                                    sama dengan 10%.
                                </p>

                                <ul class="mt-2 space-y-1 text-xs text-red-700 list-disc list-inside">
                                    @foreach ($highMoistureItems as $row)
                                        <li>
                                            {{ $lineGroupLabels[$row->line_group] ?? $row->line_group }}
                                            @if ($row->sub_line)
                                                - {{ $subLineLabels[$row->sub_line] ?? $row->sub_line }}
                                            @endif

                                            {{-- Nama produk --}}
                                            → <strong>{{ Str::limit($row->product_name, 40) }}</strong>

                                            {{-- Kadar air --}}
                                            → <strong>{{ round($row->avg_moisture, 2) }}%</strong>

                                            {{-- Tanggal --}}
                                            <span class="ml-1 text-red-600">
                                                {{ optional($row->test_date)->format('d M Y') }}
                                            </span>
                                        </li>
                                    @endforeach
                                </ul>


                            </div>
                        </div>
                    </div>
                @endif

                {{-- wrapper dengan tinggi beda untuk mobile & desktop --}}
                <div class="h-56 sm:h-72" wire:ignore>
                    <canvas id="ipcMoistureChart"></canvas>
                </div>
            @endif
        </div>
    </div>

    {{-- CARD LIST IPC PRODUCT CHECKS --}}
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
                        <h2 class="text-2xl font-bold text-gray-900">Kadar Air & Berat</h2>
                        <p class="text-sm text-gray-600 mt-1">
                            Kelola hasil IPC (kadar air & berat) per Line dan Produk.
                        </p>
                    </div>
                </div>

                {{-- RIGHT SECTION (BUTTON) --}}
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-end gap-3 w-full md:w-auto">

                    @permission('ipc_product_checks.create')
                        <button wire:click="$dispatch('openIpcProductImport')"
                            class="group bg-gradient-to-r from-green-600 to-green-500 hover:from-green-700 hover:to-green-600
                   text-white px-5 py-3 rounded-xl text-sm font-semibold inline-flex items-center
                   shadow-lg hover:shadow-xl transition-all duration-300 transform hover:scale-105
                   w-full sm:w-auto justify-center">
                            <svg class="w-5 h-5 mr-2 group-hover:rotate-90 transition-transform duration-300"
                                xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="m9 12.75 3 3m0 0 3-3m-3 3v-7.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                            </svg>
                            Import
                        </button>
                    @endpermission

                    @permission('ipc_product_checks.create')
                        <button wire:click="$dispatch('openIpcProductCheckForm')"
                            class="group bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700
                   text-white px-5 py-3 rounded-xl text-sm font-semibold flex items-center
                   shadow-lg hover:shadow-xl transition-all duration-300 transform hover:scale-105
                   w-full sm:w-auto justify-center">
                            <svg class="w-5 h-5 mr-2 group-hover:rotate-90 transition-transform duration-300" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                            Add
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
                            <input wire:model.live="search" type="text" placeholder="Cari nama produk..."
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

                {{-- Dropdown filter line / subline / date range --}}
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Line Group</label>
                        <select wire:model.live="filterLineGroup"
                            class="block w-full px-3 py-2 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                            <option value="">Semua Line</option>
                            @foreach ($lineGroups as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Sub Line( @if ($filterLineGroup !== 'LINE_TEH')
                                Aktif jika Line Group = Line Teh
                            @endif)
                        </label>
                        <select wire:model.live="filterSubLine"
                            class="block w-full px-3 py-2 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white"
                            @if ($filterLineGroup !== 'LINE_TEH') disabled @endif>
                            <option value="">Semua Sub Line</option>
                            @foreach ($subLinesTeh as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>

                    </div>

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
                </div>
            </div>
        </div>

        {{-- TABLE --}}
        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead class="bg-gradient-to-r from-gray-50 to-gray-100">
                    <tr>
                        <th wire:click="sortBy('test_date')"
                            class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider cursor-pointer hover:bg-gray-200 rounded-tl-xl">
                            Tanggal
                        </th>
                        <th wire:click="sortBy('line_group')"
                            class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider cursor-pointer hover:bg-gray-200">
                            Line
                        </th>
                        <th wire:click="sortBy('sub_line')"
                            class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider cursor-pointer hover:bg-gray-200">
                            Sub Line
                        </th>
                        <th wire:click="sortBy('product_name')"
                            class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider cursor-pointer hover:bg-gray-200">
                            Produk
                        </th>
                        <th wire:click="sortBy('shift')"
                            class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider cursor-pointer hover:bg-gray-200">
                            Shift
                        </th>
                        <th wire:click="sortBy('avg_moisture_percent')"
                            class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider cursor-pointer hover:bg-gray-200">
                            Kadar Air (%)
                        </th>

                        <th
                            class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider rounded-tr-xl">
                            Aksi
                        </th>
                    </tr>
                </thead>

                <tbody class="bg-white divide-y divide-gray-100">
                    @forelse($data as $ipc)
                        <tr
                            class="hover:bg-gradient-to-r hover:from-blue-50 hover:to-green-50 transition-all duration-300">
                            {{-- Tanggal --}}
                            <td class="px-6 py-5 whitespace-nowrap text-sm text-gray-800">
                                <div class="font-semibold">
                                    {{ optional($ipc->test_date)->format('d M Y') }}
                                </div>
                            </td>

                            {{-- Line --}}
                            <td class="px-6 py-5 whitespace-nowrap text-sm text-gray-800">
                                @php
                                    $lineLabel = $lineGroupLabels[$ipc->line_group] ?? $ipc->line_group;
                                @endphp
                                <span
                                    class="inline-flex items-center px-3 py-1.5 rounded-xl text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-100">
                                    {{ $lineLabel }}
                                </span>
                            </td>

                            {{-- Sub Line --}}
                            <td class="px-6 py-5 whitespace-nowrap text-sm text-gray-700">
                                @if ($ipc->sub_line)
                                    <span
                                        class="inline-flex items-center px-3 py-1.5 rounded-xl text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-100">
                                        {{ \App\Domains\Ipc\Models\IpcProductCheck::SUB_LINES_TEH[$ipc->sub_line] ?? $ipc->sub_line }}
                                    </span>
                                @else
                                    <span class="text-xs text-gray-400 italic">-</span>
                                @endif
                            </td>

                            {{-- Produk --}}
                            <td class="px-6 py-5 whitespace-nowrap text-sm text-gray-800">
                                <div class="font-semibold">
                                    {{ Str::limit($ipc->product_name, 40) }}
                                </div>
                                @if ($ipc->notes)
                                    <div class="text-xs text-gray-500">
                                        {{ Str::limit($ipc->notes, 50) }}
                                    </div>
                                @endif
                            </td>

                            {{-- Shift --}}
                            <td class="px-6 py-5 whitespace-nowrap text-sm text-gray-700">
                                @if ($ipc->shift)
                                    <span
                                        class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-gray-100 text-gray-800 text-xs font-semibold">
                                        {{ $ipc->shift }}
                                    </span>
                                @else
                                    <span class="text-xs text-gray-400 italic">-</span>
                                @endif
                            </td>

                            {{-- Kadar Air --}}
                            <td class="px-6 py-5 whitespace-nowrap text-sm text-gray-700">
                                @if (!is_null($ipc->avg_moisture_percent))
                                    <span class="font-mono">
                                        {{ number_format($ipc->avg_moisture_percent, 2) }}
                                    </span>
                                @else
                                    <span class="text-xs text-gray-400 italic">-</span>
                                @endif
                            </td>

                            {{-- Aksi --}}
                            <td class="px-6 py-5 whitespace-nowrap text-sm font-medium">
                                <div class="flex items-center gap-2">
                                    @permission('ipc_product_checks.view')
                                        <button
                                            wire:click="$dispatch('openIpcProductDetail', { id: {{ $ipc->id }} })"
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

                                    @permission('ipc_product_checks.edit')
                                        <button
                                            wire:click="$dispatch('openIpcProductCheckForm', { id: {{ $ipc->id }} })"
                                            class="inline-flex items-center px-3 py-2 text-xs font-semibold text-blue-600 bg-blue-50 rounded-lg hover:bg-blue-100 hover:text-blue-700 transition-all duration-200">
                                            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414
                                                                                                                                                                                                                                                                                                                                                                                                    a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                                                </path>
                                            </svg>
                                            Edit
                                        </button>
                                    @endpermission

                                    @permission('ipc_product_checks.delete')
                                        <button wire:click="delete({{ $ipc->id }})"
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
                    @empty
                        <tr>
                            {{-- total kolom sekarang: 8 --}}
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
                                    <h3 class="text-xl font-semibold text-gray-900 mb-2">Belum ada data IPC</h3>
                                    <p class="text-gray-500 mb-6 max-w-sm text-center">
                                        @if ($search || $filterLineGroup || $filterSubLine || $filterDateFrom || $filterDateTo)
                                            Coba ubah kata kunci atau filter pencarian.
                                        @else
                                            Tambahkan data IPC pertama untuk mulai memonitor kualitas proses.
                                        @endif
                                    </p>

                                    @if (!$search && !$filterLineGroup && !$filterSubLine && !$filterDateFrom && !$filterDateTo)
                                        @permission('ipc_product_checks.create')
                                            <button wire:click="$dispatch('openIpcProductCheckForm')"
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
                                            wire:click="$set('search', ''); $set('filterLineGroup', null); $set('filterSubLine', null); $set('filterDateFrom', null); $set('filterDateTo', null)"
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
                    data Kadar Air & Berat.
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
            if (window.__ipcChartInitialized) return;
            window.__ipcChartInitialized = true;

            window.ipcMoistureChart = null;

            function renderIpcMoistureChart() {
                const canvas = document.getElementById('ipcMoistureChart');
                if (!canvas) return;

                const labels = @json($chartLabels ?? []);
                const dataValues = @json($chartValues ?? []);

                if (!labels.length || !dataValues.length) {
                    if (window.ipcMoistureChart && typeof window.ipcMoistureChart.destroy === 'function') {
                        window.ipcMoistureChart.destroy();
                        window.ipcMoistureChart = null;
                    }
                    return;
                }

                if (window.ipcMoistureChart && typeof window.ipcMoistureChart.destroy === 'function') {
                    window.ipcMoistureChart.destroy();
                    window.ipcMoistureChart = null;
                }

                const ctx = canvas.getContext('2d');

                window.ipcMoistureChart = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: 'Rata-rata Moisture (%)',
                            data: dataValues,
                            backgroundColor: 'rgba(16, 185, 129, 0.6)',
                            borderColor: 'rgba(5, 150, 105, 1)',
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
                                    text: 'Moisture (%)'
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
                                    label: (ctx) => ctx.parsed.x.toFixed(2) + ' %'
                                }
                            }
                        }
                    }
                });
            }

            function boot() {
                renderIpcMoistureChart();

                if (window.Livewire) {
                    Livewire.hook('message.processed', () => {
                        renderIpcMoistureChart();
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
