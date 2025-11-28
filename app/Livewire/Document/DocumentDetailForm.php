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
        'openDocumentDetail' => 'open',   // nama event harus sama dengan yang di-dispatch
    ];

    public function open($payload = null): void
    {
        // Bisa array (dari { id: ... }) atau scalar (angka langsung)
        $documentId = null;

        if (is_array($payload)) {
            $documentId = $payload['id'] ?? null;
        } elseif (is_numeric($payload)) {
            $documentId = $payload;
        }

        // Kalau tidak ada id yang valid, cukup keluar tanpa error
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
