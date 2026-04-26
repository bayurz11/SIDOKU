<?php

namespace Tests\Feature\Livewire;

use App\Domains\User\Models\User;
use App\Livewire\NotificationList;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Tests\Feature\Livewire\Document\Concerns\BuildsDocumentFixtures;
use Tests\TestCase;

class NotificationListTest extends TestCase
{
    use BuildsDocumentFixtures;
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

    public function test_notification_list_hides_notifications_not_matching_current_role(): void
    {
        $user = $this->createUserWithAccess(['documents.create'], ['user']);

        $user->notifications()->create([
            'id' => (string) Str::uuid(),
            'type' => 'database',
            'data' => [
                'title' => 'Approval dokumen baru',
                'type' => 'document_approval_requested',
                'message' => 'Dokumen membutuhkan approval QSM.',
                'target_user_id' => $user->id,
                'target_role' => 'quality-system-manager',
                'url' => route('documents.approval-queue'),
            ],
            'read_at' => null,
        ]);

        Livewire::actingAs($user)
            ->test(NotificationList::class)
            ->assertDontSee('Dokumen membutuhkan approval QSM.');
    }
}
