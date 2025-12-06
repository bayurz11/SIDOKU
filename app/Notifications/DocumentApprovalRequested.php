<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\DatabaseMessage;
use App\Domains\Document\Models\Document; // sesuaikan namespace

class DocumentApprovalRequested extends Notification
{
    use Queueable;

    public function __construct(
        public Document $document,
        public ?string $requestedByName = null,
    ) {}

    public function via($notifiable)
    {
        return ['database']; // bisa tambahkan 'mail' nanti
    }

    public function toDatabase($notifiable)
    {
        return [
            'type'        => 'document_approval_requested',
            'document_id' => $this->document->id,
            'document_code' => $this->document->document_code ?? null,
            'title'       => $this->document->title,
            'status'      => $this->document->status,
            'requested_by' => $this->requestedByName,
            'url'         => route('documents.show', $this->document->id),
            'message'     => "Dokumen {$this->document->title} membutuhkan approval Anda.",
        ];
    }
}
