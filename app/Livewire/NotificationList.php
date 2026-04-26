<?php

namespace App\Livewire;

use App\Shared\Services\NotificationAudienceService;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Pagination\LengthAwarePaginator;
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

        abort_unless($this->isVisible($notification), 403);

        $notification->markAsRead();

        return redirect($notification->data['url'] ?? route('dashboard'));
    }

    public function markAsRead(string $notificationId): void
    {
        $notification = $this->notificationQuery()
            ->whereKey($notificationId)
            ->whereNull('read_at')
            ->first();

        if ($notification && $this->isVisible($notification)) {
            $notification->markAsRead();
        }
    }

    public function markAsUnread(string $notificationId): void
    {
        $notification = $this->notificationQuery()
            ->whereKey($notificationId)
            ->whereNotNull('read_at')
            ->first();

        if ($notification && $this->isVisible($notification)) {
            $notification->update(['read_at' => null]);
        }
    }

    public function markAllAsRead(): void
    {
        $this->visibleNotifications()
            ->whereNull('read_at')
            ->each(fn (DatabaseNotification $notification) => $notification->markAsRead());
    }

    public function deleteNotification(string $notificationId): void
    {
        $notification = $this->notificationQuery()
            ->whereKey($notificationId)
            ->first();

        if ($notification && $this->isVisible($notification)) {
            $notification->delete();
        }

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

        $notifications = $this->visibleNotifications()
            ->when($this->status === 'unread', fn ($q) => $q->whereNull('read_at'))
            ->when($this->status === 'read', fn ($q) => $q->whereNotNull('read_at'))
            ->when($this->search, function ($q) {
                $term = '%'.strtolower($this->search).'%';

                $q->whereRaw('LOWER(data) LIKE ?', [$term]);
            })
            ->latest()
            ->get()
            ->filter(fn (DatabaseNotification $notification) => $this->isVisible($notification))
            ->values();

        $page = $this->getPage();
        $data = new LengthAwarePaginator(
            $notifications->forPage($page, $this->perPage)->values(),
            $notifications->count(),
            $this->perPage,
            $page,
            ['path' => request()->url(), 'query' => request()->query()]
        );

        $data->onEachSide(0);

        $unreadCount = $this->visibleNotifications()
            ->whereNull('read_at')
            ->get()
            ->filter(fn (DatabaseNotification $notification) => $this->isVisible($notification))
            ->count();

        return view('livewire.notification-list', compact('data', 'unreadCount'));
    }

    private function visibleNotifications()
    {
        return $this->notificationQuery();
    }

    private function notificationQuery()
    {
        return DatabaseNotification::query()
            ->where('notifiable_type', Auth::user()?->getMorphClass())
            ->where('notifiable_id', Auth::id());
    }

    private function isVisible(DatabaseNotification $notification): bool
    {
        $user = Auth::user();

        return $user && NotificationAudienceService::isVisibleTo($user, $notification);
    }
}
