<?php

namespace App\Livewire\Document;

use Livewire\Component;
use App\Domains\Document\Models\Document;

class DocumentDetailForm extends Component
{
    public bool $showModal = false;
    public ?Document $document = null;

    protected $listeners = [
        'openDocumentDetail' => 'open',
    ];

    public function open(int $id): void
    {
        $this->document = Document::query()
            ->with([
                'documentType',
                'department',
                'revisions.changedBy',
            ])
            ->findOrFail($id);

        $this->showModal = true;
    }
    public function requestApproval(int $id)
    {
        $doc = Document::findOrFail($id);

        // Ubah status menjadi 'in_review'
        $doc->update([
            'status' => 'in_review'
        ]);

        // Bisa kirim notifikasi ke Manager QC/QS (opsional)
        // Notification::send($doc->approvers, new DocumentApprovalRequest($doc));

        // Refresh data
        $this->document = $doc->fresh();

        // Kirim notifikasi UI
        $this->dispatch('toast:success', message: 'Dokumen telah diajukan ke Department terkait.');
    }

    public function closeModal(): void
    {
        $this->reset(['showModal', 'document']);
    }

    public function render()
    {
        return view('livewire.document.document-detail-form');
    }
}
