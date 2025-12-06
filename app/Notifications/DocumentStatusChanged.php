<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use App\Domains\Document\Models\Document;

class DocumentStatusChanged extends Notification
{
    use Queueable;

    public function __construct(
        public Document $document,
        public string $oldStatus,
        public string $newStatus,
    ) {}

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toDatabase($notifiable)
    {
        return [
            'type'        => 'document_status_changed',
            'document_id' => $this->document->id,
            'document_code' => $this->document->document_code ?? null,
            'title'       => $this->document->title,
            'old_status'  => $this->oldStatus,
            'new_status'  => $this->newStatus,
            'url'         => route('documents.show', $this->document->id),
            'message'     => "Status dokumen {$this->document->title} berubah dari {$this->oldStatus} menjadi {$this->newStatus}.",
        ];
    }
}
