<?php

namespace App\Livewire\Document;

use Livewire\Component;
use App\Domains\Document\Models\Document;

class DocumentDetailForm extends Component
{
    public bool $showModal = false;
    public ?Document $document = null;

    // Sama seperti openDocumentForm: dengarkan event-nya
    protected $listeners = [
        'openDocumentDetail' => 'open',
    ];

    // TERIMA ID SAJA, OPTIONAL
    public function open(?int $documentId = null): void
    {
        if (!$documentId) {
            return;
        }

        $this->document = Document::query()
            ->with([
                'documentType',
                'department',
                'revisions.changedBy',
            ])
            ->findOrFail($documentId);

        $this->showModal = true;
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
