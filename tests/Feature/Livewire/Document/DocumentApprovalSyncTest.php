<?php

namespace Tests\Feature\Livewire\Document;

use App\Domains\Department\Models\Department;
use App\Domains\Document\Models\Document;
use App\Domains\Document\Models\DocumentApprovalStep;
use App\Domains\Document\Models\DocumentRevision;
use App\Domains\Document\Models\DocumentType;
use App\Domains\Document\Services\DocumentApprovalService;
use App\Livewire\Document\ApprovalQueue;
use App\Livewire\Document\DocumentList;
use App\Livewire\Document\DocumentRevisionList;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\Feature\Livewire\Document\Concerns\BuildsDocumentFixtures;
use Tests\TestCase;

class DocumentApprovalSyncTest extends TestCase
{
    use BuildsDocumentFixtures;
    use RefreshDatabase;

    public function test_final_approval_creates_revision_and_dispatches_sync_event(): void
    {
        $creator = $this->createUserWithAccess(['documents.create']);
        $controller = $this->createUserWithAccess(['documents.approve'], ['document-controller']);
        $manager = $this->createUserWithAccess(['documents.approve', 'documents.revision'], ['quality-system-manager']);

        $document = $this->createDraftDocument($creator->id);

        $this->actingAs($creator);
        app(DocumentApprovalService::class)->submit($document);

        $this->assertDatabaseHas('notifications', [
            'notifiable_id' => $controller->id,
            'notifiable_type' => $controller->getMorphClass(),
        ]);

        $firstStep = DocumentApprovalStep::query()
            ->where('approval_request_id', $document->refresh()->current_approval_request_id)
            ->where('step_order', 1)
            ->firstOrFail();

        $this->actingAs($controller);
        Livewire::test(ApprovalQueue::class)
            ->call('openActionModal', $firstStep->id, 'approve')
            ->call('submitAction')
            ->assertDispatched('document:approval_updated');

        $this->assertDatabaseMissing('document_revisions', [
            'document_id' => $document->id,
        ]);

        $secondStep = DocumentApprovalStep::query()
            ->where('approval_request_id', $document->refresh()->current_approval_request_id)
            ->where('step_order', 2)
            ->firstOrFail();

        $this->actingAs($manager);
        Livewire::test(ApprovalQueue::class)
            ->set('note', 'Final approval QA')
            ->call('openActionModal', $secondStep->id, 'approve')
            ->set('note', 'Final approval QA')
            ->call('submitAction')
            ->assertDispatched('document:approval_updated');

        $document->refresh();

        $this->assertSame(Document::STATUS_APPROVED, $document->status);
        $this->assertFalse($document->is_locked);
        $this->assertNull($document->current_approval_request_id);
        $this->assertDatabaseHas('document_revisions', [
            'document_id' => $document->id,
            'revision_no' => 0,
            'change_note' => 'Final approval QA',
            'changed_by' => $manager->id,
        ]);

        Livewire::test(DocumentRevisionList::class)
            ->assertSee('PRP/SOP/QC/001')
            ->assertSee('Prosedur Sampling')
            ->assertSee('Final approval QA');
    }

    public function test_document_download_route_returns_approval_queue_file(): void
    {
        Storage::fake('public');

        $approver = $this->createUserWithAccess(['documents.approve']);
        $document = $this->createDraftDocument($approver->id);
        $document->update(['file_path' => 'documents/prosedur-sampling.pdf']);

        Storage::disk('public')->put($document->file_path, 'dummy-pdf-content');

        $this->actingAs($approver)
            ->get(route('documents.download', $document))
            ->assertOk()
            ->assertHeader('content-disposition');
    }

    public function test_revision_flow_can_be_started_rejected_and_resubmitted_for_final_approval(): void
    {
        $owner = $this->createUserWithAccess(['documents.create', 'documents.revision']);
        $controller = $this->createUserWithAccess(['documents.approve', 'documents.review'], ['document-controller']);
        $manager = $this->createUserWithAccess(['documents.approve', 'documents.review'], ['quality-system-manager']);

        $document = $this->createDraftDocument($owner->id);
        $document->update([
            'status' => Document::STATUS_APPROVED,
            'revision_no' => 0,
            'is_active' => true,
            'approved_at' => now(),
        ]);

        DocumentRevision::query()->create([
            'document_id' => $document->id,
            'revision_no' => 0,
            'change_note' => 'Initial approved version.',
            'file_path' => '',
            'changed_by' => $owner->id,
            'changed_at' => now(),
        ]);

        $this->actingAs($owner);

        Livewire::test(DocumentList::class)
            ->call('startRevision', $document->id);

        $document->refresh();

        $this->assertSame(Document::STATUS_REVISION, $document->status);
        $this->assertSame(1, $document->revision_no);
        $this->assertFalse($document->is_active);

        app(DocumentApprovalService::class)->submit($document);
        $firstStep = DocumentApprovalStep::query()
            ->where('approval_request_id', $document->refresh()->current_approval_request_id)
            ->where('step_order', 1)
            ->firstOrFail();

        $this->actingAs($controller);
        Livewire::test(ApprovalQueue::class)
            ->call('openActionModal', $firstStep->id, 'reject')
            ->set('note', 'Perlu perbaikan format revisi.')
            ->call('submitAction');

        $document->refresh();
        $this->assertSame(Document::STATUS_REVISION, $document->status);
        $this->assertFalse($document->is_locked);

        $this->actingAs($owner);
        app(DocumentApprovalService::class)->submit($document);

        $firstStep = DocumentApprovalStep::query()
            ->where('approval_request_id', $document->refresh()->current_approval_request_id)
            ->where('step_order', 1)
            ->latest('id')
            ->firstOrFail();

        $this->actingAs($controller);
        Livewire::test(ApprovalQueue::class)
            ->call('openActionModal', $firstStep->id, 'approve')
            ->call('submitAction');

        Livewire::test(ApprovalQueue::class)
            ->set('status', 'approved')
            ->assertSee('PRP/SOP/QC/001');

        $secondStep = DocumentApprovalStep::query()
            ->where('approval_request_id', $document->refresh()->current_approval_request_id)
            ->where('step_order', 2)
            ->firstOrFail();

        $this->actingAs($manager);
        Livewire::test(ApprovalQueue::class)
            ->call('openActionModal', $secondStep->id, 'approve')
            ->set('note', 'Revisi final disetujui.')
            ->call('submitAction');

        $document->refresh();

        $this->assertSame(Document::STATUS_APPROVED, $document->status);
        $this->assertTrue($document->is_active);
        $this->assertDatabaseHas('document_revisions', [
            'document_id' => $document->id,
            'revision_no' => 1,
            'change_note' => 'Revisi final disetujui.',
        ]);
    }

    private function createDraftDocument(int $creatorId): Document
    {
        $type = DocumentType::query()->create([
            'name' => 'SOP',
            'description' => 'Standard Operating Procedure',
            'is_active' => true,
        ]);

        $department = Department::query()->create([
            'name' => 'QC',
            'description' => 'Quality Control',
            'is_active' => true,
        ]);

        return Document::query()->create([
            'document_type_id' => $type->id,
            'department_id' => $department->id,
            'document_code' => 'PRP/SOP/QC/001',
            'title' => 'Prosedur Sampling',
            'level' => 2,
            'revision_no' => 0,
            'status' => Document::STATUS_DRAFT,
            'is_active' => true,
            'created_by' => $creatorId,
        ]);
    }
}
