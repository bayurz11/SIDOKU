<div x-data="{ openNotif: false }" x-on:click.outside="openNotif = false" class="relative" wire:poll.30s>
    <button type="button" x-on:click="openNotif = !openNotif"
        class="relative p-2 text-gray-500 hover:text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500 rounded-md">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none"
            stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" />
        </svg>

        @if ($unreadCount > 0)
            <span
                class="absolute -top-1 -right-1 min-w-5 h-5 px-1 rounded-full bg-red-500 text-white text-[10px] font-bold flex items-center justify-center">
                {{ $unreadCount > 99 ? '99+' : $unreadCount }}
            </span>
        @endif
    </button>

    <div x-show="openNotif" x-transition
        class="absolute right-0 mt-2 w-80 bg-white shadow-lg rounded-xl border border-gray-200 z-50" x-cloak>
        <div class="px-4 py-2 border-b border-gray-100 flex items-center justify-between">
            <span class="text-sm font-semibold text-gray-800">Notifications</span>
            <a href="{{ route('notifications.index') }}" class="text-xs text-blue-600 hover:underline">
                Lihat semua
            </a>
        </div>

        <div class="max-h-80 overflow-y-auto divide-y divide-gray-100">
            @forelse($notifications as $notif)
                @php $data = $notif->data; @endphp
                <button type="button" wire:click="openNotification('{{ $notif->id }}')"
                    class="block w-full px-4 py-3 hover:bg-gray-50 text-left text-sm text-gray-700">
                    <div class="font-semibold">{{ $data['title'] ?? 'Perubahan data' }}</div>
                    <div class="text-xs text-gray-500 mt-0.5">
                        {{ $data['message'] ?? '' }}
                    </div>
                    <div class="text-[10px] text-gray-400 mt-1">
                        {{ $notif->created_at->diffForHumans() }}
                    </div>
                </button>
            @empty
                <div class="px-4 py-3 text-xs text-gray-500">
                    Tidak ada notifikasi baru.
                </div>
            @endforelse
        </div>

        @if ($unreadCount > 0)
            <div class="px-4 py-2 border-t border-gray-100 bg-gray-50 rounded-b-xl flex items-center justify-between">
                <button type="button" wire:click="markAllAsRead" class="text-xs text-gray-600 hover:text-gray-900">
                    Tandai semua dibaca
                </button>
                <a href="{{ route('notifications.index') }}" class="text-xs font-semibold text-blue-600 hover:underline">
                    Buka halaman
                </a>
            </div>
        @endif
    </div>
</div>
