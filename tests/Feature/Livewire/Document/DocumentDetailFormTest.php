<?php

namespace Tests\Feature\Livewire\Document;

use App\Domains\Department\Models\Department;
use App\Domains\Document\Models\Document;
use App\Domains\Document\Models\DocumentApprovalRequest;
use App\Domains\Document\Models\DocumentType;
use App\Livewire\Document\DocumentDetailForm;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\Feature\Livewire\Document\Concerns\BuildsDocumentFixtures;
use Tests\TestCase;

class DocumentDetailFormTest extends TestCase
{
    use BuildsDocumentFixtures;
    use RefreshDatabase;

    public function test_request_approval_from_detail_uses_approval_workflow(): void
    {
        $actor = $this->createUserWithAccess(['documents.create']);
        $this->createUserWithAccess([], ['document-controller']);
        $this->createUserWithAccess([], ['quality-system-manager']);

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

        $document = Document::query()->create([
            'document_type_id' => $type->id,
            'department_id' => $department->id,
            'document_code' => 'PRP/SOP/QC/001',
            'title' => 'Prosedur Sampling',
            'level' => 2,
            'revision_no' => 0,
            'status' => Document::STATUS_DRAFT,
            'is_active' => true,
            'created_by' => $actor->id,
        ]);

        $this->actingAs($actor);

        Livewire::test(DocumentDetailForm::class)
            ->call('open', $document->id)
            ->call('requestApproval', $document->id)
            ->assertSet('showModal', true);

        $document->refresh();

        $this->assertSame(Document::STATUS_IN_REVIEW, $document->status);
        $this->assertTrue($document->is_locked);
        $this->assertNotNull($document->current_approval_request_id);
        $this->assertDatabaseHas('document_approval_requests', [
            'id' => $document->current_approval_request_id,
            'document_id' => $document->id,
            'status' => DocumentApprovalRequest::STATUS_PENDING,
            'current_step' => 1,
        ]);
        $this->assertDatabaseCount('document_approval_steps', 2);
    }
}
