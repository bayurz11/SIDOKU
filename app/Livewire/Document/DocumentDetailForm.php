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
        'document:showDetail' => 'open',   // nama event bebas, penting konsisten di Blade
    ];

    public function open(int $documentId): void
    {
        $this->document = Document::query()
            ->with([
                'documentType',
                'department',
                'revisions.changedBy',   // pastikan relasi changedBy ada di DocumentRevision
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
