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

    public function closeModal(): void
    {
        $this->reset(['showModal', 'document']);
    }

    public function render()
    {
        return view('livewire.document.document-detail-form');
    }
}
