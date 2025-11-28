<?php

namespace App\Livewire\Document;

use Livewire\Component;
use App\Domains\Document\Models\Document;

class DocumentDetailForm extends Component
{
    public bool $showModal = false;
    public ?Document $document = null;

    // Event dari component lain (misal DocumentList)
    protected $listeners = [
        'openDocumentDetail' => 'open',   // nama event bebas, penting konsisten di Blade
    ];

    public function open($payload): void
    {
        // Bisa array (dari { id: ... }) atau scalar
        $documentId = is_array($payload)
            ? ($payload['id'] ?? null)
            : $payload;

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
