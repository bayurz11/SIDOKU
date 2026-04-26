<?php

namespace App\Livewire;

use App\Shared\Services\NotificationAudienceService;
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

        if ($notification && $this->isVisible($notification)) {
            $notification->markAsRead();
        }
    }

    public function openNotification(string $notificationId)
    {
        $notification = $this->notificationQuery()
            ->whereKey($notificationId)
            ->firstOrFail();

        abort_unless($this->isVisible($notification), 403);

        $notification->markAsRead();

        return redirect($notification->data['url'] ?? route('dashboard'));
    }

    public function markAllAsRead(): void
    {
        $this->visibleUnreadNotifications()
            ->each(fn (DatabaseNotification $notification) => $notification->markAsRead());
    }

    public function render()
    {
        $visibleUnreadNotifications = $this->visibleUnreadNotifications();

        $notifications = $visibleUnreadNotifications->take(5);
        $unreadCount = $visibleUnreadNotifications->count();

        return view('livewire.notification-menu', compact('notifications', 'unreadCount'));
    }

    private function visibleUnreadNotifications()
    {
        $user = Auth::user();

        if (! $user) {
            return collect();
        }

        return $user->unreadNotifications()
            ->latest()
            ->take(100)
            ->get()
            ->filter(fn (DatabaseNotification $notification) => $this->isVisible($notification))
            ->values();
    }

    private function notificationQuery()
    {
        return DatabaseNotification::query()
            ->where('notifiable_type', Auth::user()?->getMorphClass())
            ->where('notifiable_id', Auth::id())
            ->whereNull('read_at');
    }

    private function isVisible(DatabaseNotification $notification): bool
    {
        $user = Auth::user();

        return $user && NotificationAudienceService::isVisibleTo($user, $notification);
    }
}
