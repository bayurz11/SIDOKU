<div class="bg-white shadow-xl rounded-2xl border border-gray-200 overflow-hidden" wire:poll.30s>
    <div class="bg-gradient-to-r from-sky-50 via-blue-50 to-indigo-50 px-6 py-6 border-b border-gray-200">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            <div class="flex items-center space-x-4">
                <div class="w-12 h-12 bg-gradient-to-br from-sky-500 to-indigo-600 rounded-xl flex items-center justify-center shadow-lg">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" />
                    </svg>
                </div>
                <div>
                    <h2 class="text-2xl font-bold text-gray-900">Notifications</h2>
                    <p class="text-sm text-gray-600 mt-1">
                        Lihat riwayat notifikasi approval, perubahan status, dan perubahan data aplikasi.
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-semibold bg-white text-gray-700 border border-gray-200 shadow-sm">
                    Unread: <span class="ml-1 font-mono">{{ $unreadCount }}</span>
                </span>
                @if ($unreadCount > 0)
                    <button type="button" wire:click="markAllAsRead"
                        class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-xs font-semibold rounded-xl hover:bg-blue-700 transition-all duration-200">
                        Tandai semua dibaca
                    </button>
                @endif
            </div>
        </div>

        <div class="mt-6 space-y-4">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                <div class="flex-1">
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 1 1-14 0 7 7 0 0 1 14 0z" />
                            </svg>
                        </div>
                        <input wire:model.live="search" type="text" placeholder="Cari notifikasi..."
                            class="block w-full pl-12 pr-4 py-3 border border-gray-300 rounded-xl bg-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200">
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <select wire:model.live="status"
                        class="px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white text-sm font-medium transition-all duration-200">
                        <option value="unread">Belum dibaca</option>
                        <option value="read">Sudah dibaca</option>
                        <option value="all">Semua</option>
                    </select>

                    <select wire:model.live="perPage"
                        class="px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white text-sm font-medium transition-all duration-200">
                        <option value="10">10 per halaman</option>
                        <option value="25">25 per halaman</option>
                        <option value="50">50 per halaman</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <div class="divide-y divide-gray-100">
        @forelse($data as $notification)
            @php
                $payload = $notification->data;
                $isUnread = is_null($notification->read_at);
            @endphp

            <div class="px-6 py-5 {{ $isUnread ? 'bg-blue-50/60' : 'bg-white' }} hover:bg-gradient-to-r hover:from-blue-50 hover:to-indigo-50 transition-all duration-200">
                <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-4">
                    <div class="flex items-start gap-4 min-w-0">
                        <div class="mt-1 h-3 w-3 rounded-full {{ $isUnread ? 'bg-blue-600' : 'bg-gray-300' }} flex-shrink-0"></div>
                        <div class="min-w-0">
                            <div class="flex flex-wrap items-center gap-2">
                                <h3 class="text-sm font-bold text-gray-900">
                                    {{ $payload['title'] ?? 'Perubahan data' }}
                                </h3>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-semibold border {{ $isUnread ? 'bg-blue-100 text-blue-700 border-blue-200' : 'bg-gray-100 text-gray-600 border-gray-200' }}">
                                    {{ $isUnread ? 'Unread' : 'Read' }}
                                </span>
                                @if (! empty($payload['document_code']))
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-semibold bg-white text-gray-700 border border-gray-200">
                                        {{ $payload['document_code'] }}
                                    </span>
                                @endif
                            </div>

                            <p class="text-sm text-gray-700 mt-1">
                                {{ $payload['message'] ?? '-' }}
                            </p>

                            <div class="text-xs text-gray-500 mt-2">
                                {{ $notification->created_at->format('d M Y H:i') }}
                                <span class="mx-1">•</span>
                                {{ $notification->created_at->diffForHumans() }}
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-wrap items-center justify-end gap-2">
                        <button type="button" wire:click="openNotification('{{ $notification->id }}')"
                            class="inline-flex items-center px-3 py-2 text-xs font-semibold text-blue-700 bg-blue-50 rounded-lg hover:bg-blue-100 hover:text-blue-800 transition-all duration-200">
                            Buka
                        </button>

                        @if ($isUnread)
                            <button type="button" wire:click="markAsRead('{{ $notification->id }}')"
                                class="inline-flex items-center px-3 py-2 text-xs font-semibold text-emerald-700 bg-emerald-50 rounded-lg hover:bg-emerald-100 hover:text-emerald-800 transition-all duration-200">
                                Tandai dibaca
                            </button>
                        @else
                            <button type="button" wire:click="markAsUnread('{{ $notification->id }}')"
                                class="inline-flex items-center px-3 py-2 text-xs font-semibold text-amber-700 bg-amber-50 rounded-lg hover:bg-amber-100 hover:text-amber-800 transition-all duration-200">
                                Tandai belum dibaca
                            </button>
                        @endif

                        <button type="button" wire:click="deleteNotification('{{ $notification->id }}')"
                            wire:confirm="Hapus notifikasi ini?"
                            class="inline-flex items-center px-3 py-2 text-xs font-semibold text-red-700 bg-red-50 rounded-lg hover:bg-red-100 hover:text-red-800 transition-all duration-200">
                            Hapus
                        </button>
                    </div>
                </div>
            </div>
        @empty
            <div class="px-6 py-16 text-center">
                <div class="flex flex-col items-center justify-center">
                    <div class="w-20 h-20 bg-gradient-to-br from-gray-100 to-gray-200 rounded-2xl flex items-center justify-center mb-6 shadow-inner">
                        <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.75 17.25v.75a3.75 3.75 0 0 1-7.5 0v-.75m11.25-1.5A8.967 8.967 0 0 1 17.25 9.75V9a5.25 5.25 0 0 0-10.5 0v.75a8.967 8.967 0 0 1-2.25 6" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-2">Tidak ada notifikasi</h3>
                    <p class="text-gray-500 max-w-sm text-center">
                        Notifikasi approval dan perubahan data akan muncul di sini.
                    </p>
                </div>
            </div>
        @endforelse
    </div>

    <div class="px-6 py-4 bg-gradient-to-r from-gray-50 to-blue-50 border-t border-gray-200 rounded-b-2xl">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
            <div class="text-sm text-gray-600">
                Menampilkan <span class="font-medium">{{ $data->firstItem() ?? 0 }}</span>
                sampai <span class="font-medium">{{ $data->lastItem() ?? 0 }}</span>
                dari <span class="font-medium">{{ $data->total() }}</span> notifikasi
            </div>
            <div class="flex-1 flex justify-center md:justify-end">
                {{ $data->links('vendor.pagination.custom') }}
            </div>
        </div>
    </div>
</div>
