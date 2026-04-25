<?php

namespace Tests\Feature\Livewire\Document;

use App\Domains\Department\Models\Department;
use App\Domains\Document\Models\Document;
use App\Domains\Document\Models\DocumentApprovalStep;
use App\Domains\Document\Models\DocumentType;
use App\Domains\Document\Services\DocumentApprovalService;
use App\Livewire\Document\ApprovalQueue;
use App\Livewire\Document\DocumentRevisionList;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
