<?php

namespace App\Livewire;

use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class NotificationMenu extends Component
{
    protected $listeners = [
        'app:data_changed' => '$refresh',
        'document:saved' => '$refresh',
        'document:imported' => '$refresh',
        'document:approval_updated' => '$refresh',
        'department:saved' => '$refresh',
        'documentType:saved' => '$refresh',
        'documentPrefix:saved' => '$refresh',
        'incoming-material:saved' => '$refresh',
        'ipc:product_saved' => '$refresh',
        'ipc:product_check_saved' => '$refresh',
        'tiup-botol:saved' => '$refresh',
        'roleSaved' => '$refresh',
        'userSaved' => '$refresh',
    ];

    public function markAsRead(string $notificationId): void
    {
        $notification = $this->notificationQuery()
            ->whereKey($notificationId)
            ->first();

        if ($notification) {
            $notification->markAsRead();
        }
    }

    public function openNotification(string $notificationId)
    {
        $notification = $this->notificationQuery()
            ->whereKey($notificationId)
            ->firstOrFail();

        $notification->markAsRead();

        return redirect($notification->data['url'] ?? route('dashboard'));
    }

    public function markAllAsRead(): void
    {
        Auth::user()?->unreadNotifications()->update(['read_at' => now()]);
    }

    public function render()
    {
        $notifications = Auth::user()
            ?->unreadNotifications()
            ->latest()
            ->take(5)
            ->get() ?? collect();

        $unreadCount = Auth::user()?->unreadNotifications()->count() ?? 0;

        return view('livewire.notification-menu', compact('notifications', 'unreadCount'));
    }

    private function notificationQuery()
    {
        return DatabaseNotification::query()
            ->where('notifiable_type', Auth::user()?->getMorphClass())
            ->where('notifiable_id', Auth::id())
            ->whereNull('read_at');
    }
}
