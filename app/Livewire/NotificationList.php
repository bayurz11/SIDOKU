<?php

namespace App\Livewire;

use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class NotificationList extends Component
{
    use WithPagination;

    public string $search = '';

    public string $status = 'unread';

    public int $perPage = 10;

    protected array $allowedStatuses = ['all', 'unread', 'read'];

    protected array $allowedPerPage = [10, 25, 50];

    protected $queryString = [
        'search' => ['except' => ''],
        'status' => ['except' => 'unread'],
        'perPage' => ['except' => 10],
    ];

    protected $listeners = [
        'app:data_changed' => '$refresh',
        'document:saved' => '$refresh',
        'document:imported' => '$refresh',
        'document:approval_updated' => '$refresh',
    ];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatus(): void
    {
        $this->resetPage();
    }

    public function updatingPerPage(): void
    {
        if (! in_array((int) $this->perPage, $this->allowedPerPage, true)) {
            $this->perPage = 10;
        }

        $this->resetPage();
    }

    public function openNotification(string $notificationId)
    {
        $notification = $this->notificationQuery()
            ->whereKey($notificationId)
            ->firstOrFail();

        $notification->markAsRead();

        return redirect($notification->data['url'] ?? route('dashboard'));
    }

    public function markAsRead(string $notificationId): void
    {
        $notification = $this->notificationQuery()
            ->whereKey($notificationId)
            ->whereNull('read_at')
            ->first();

        if ($notification) {
            $notification->markAsRead();
        }
    }

    public function markAsUnread(string $notificationId): void
    {
        $notification = $this->notificationQuery()
            ->whereKey($notificationId)
            ->whereNotNull('read_at')
            ->first();

        if ($notification) {
            $notification->update(['read_at' => null]);
        }
    }

    public function markAllAsRead(): void
    {
        Auth::user()?->unreadNotifications()->update(['read_at' => now()]);
    }

    public function deleteNotification(string $notificationId): void
    {
        $this->notificationQuery()
            ->whereKey($notificationId)
            ->delete();

        $this->resetPage();
    }

    public function render()
    {
        if (! in_array($this->status, $this->allowedStatuses, true)) {
            $this->status = 'unread';
        }

        if (! in_array((int) $this->perPage, $this->allowedPerPage, true)) {
            $this->perPage = 10;
        }

        $query = $this->notificationQuery()
            ->when($this->status === 'unread', fn ($q) => $q->whereNull('read_at'))
            ->when($this->status === 'read', fn ($q) => $q->whereNotNull('read_at'))
            ->when($this->search, function ($q) {
                $term = '%'.strtolower($this->search).'%';

                $q->whereRaw('LOWER(data) LIKE ?', [$term]);
            })
            ->latest();

        $data = $query->paginate($this->perPage)->onEachSide(0);
        $unreadCount = Auth::user()?->unreadNotifications()->count() ?? 0;

        return view('livewire.notification-list', compact('data', 'unreadCount'));
    }

    private function notificationQuery()
    {
        return DatabaseNotification::query()
            ->where('notifiable_type', Auth::user()?->getMorphClass())
            ->where('notifiable_id', Auth::id());
    }
}
