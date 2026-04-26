<?php

namespace App\Livewire\Document;

use App\Domains\Document\Models\Document;
use App\Domains\Document\Services\DocumentApprovalService;
use App\Domains\Document\Services\DocumentRevisionWorkflowService;
use App\Shared\Traits\WithAlerts;
use Livewire\Component;

class DocumentDetailForm extends Component
{
    use WithAlerts;

    public bool $showModal = false;

    public ?Document $document = null;

    protected $listeners = [
        'openDocumentDetail' => 'open',
        'document:approval_updated' => 'refreshDocument',
    ];

    public function open(int $id): void
    {
        $this->document = Document::query()
            ->with([
                'documentType',
                'department',
                'revisions.changedBy',
                'parent',
                'approvalRequests.requester',
                'approvalRequests.steps.approver',
            ])
            ->findOrFail($id);

        $this->showModal = true;
    }

    public function requestApproval(int $id): void
    {
        if (! auth()->user()?->hasAnyPermission(['documents.create', 'documents.review', 'documents.approve'])) {
            $this->showErrorToast('Tidak punya izin mengajukan dokumen.');

            return;
        }

        $doc = Document::findOrFail($id);

        try {
            app(DocumentApprovalService::class)->submit($doc);
            $this->open($id);
            $this->dispatch('document:saved');
            $this->showSuccessToast('Dokumen berhasil diajukan untuk approval.');
        } catch (\Throwable $exception) {
            $this->showErrorToast($exception->getMessage());
        }
    }

    public function startRevision(int $id): void
    {
        if (! auth()->user()?->hasPermission('documents.revision')) {
            $this->showErrorToast('Tidak punya izin membuat revisi dokumen.');

            return;
        }

        $doc = Document::findOrFail($id);

        try {
            app(DocumentRevisionWorkflowService::class)->startRevision($doc);
            $this->open($id);
            $this->dispatch('document:saved');
            $this->showSuccessToast('Flow revisi dokumen berhasil dimulai.');
        } catch (\Throwable $exception) {
            $this->showErrorToast($exception->getMessage());
        }
    }

    public function closeModal(): void
    {
        $this->reset(['showModal', 'document']);
    }

    public function refreshDocument(): void
    {
        if ($this->showModal && $this->document) {
            $this->open($this->document->id);
        }
    }

    public function render()
    {
        return view('livewire.document.document-detail-form');
    }
}
