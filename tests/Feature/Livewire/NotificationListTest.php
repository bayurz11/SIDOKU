<?php

namespace Tests\Feature\Livewire;

use App\Domains\User\Models\User;
use App\Livewire\NotificationList;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Tests\TestCase;

class NotificationListTest extends TestCase
{
    use RefreshDatabase;

    public function test_notifications_page_requires_authentication(): void
    {
        $this->get(route('notifications.index'))
            ->assertRedirect(route('login'));
    }

    public function test_user_can_view_and_mark_notification_as_read(): void
    {
        $user = User::factory()->create();

        $notificationId = (string) Str::uuid();
        $user->notifications()->create([
            'id' => $notificationId,
            'type' => 'database',
            'data' => [
                'title' => 'Approval dokumen baru',
                'message' => 'Dokumen SOP membutuhkan approval Anda.',
                'url' => route('dashboard'),
            ],
            'read_at' => null,
        ]);

        $this->actingAs($user)
            ->get(route('notifications.index'))
            ->assertOk();

        Livewire::actingAs($user)
            ->test(NotificationList::class)
            ->assertSee('Approval dokumen baru')
            ->assertSee('Dokumen SOP membutuhkan approval Anda.')
            ->call('markAsRead', $notificationId);

        $this->assertDatabaseHas('notifications', [
            'id' => $notificationId,
            'notifiable_id' => $user->id,
            'notifiable_type' => $user->getMorphClass(),
        ]);

        $this->assertNotNull($user->notifications()->first()?->read_at);
    }
}
