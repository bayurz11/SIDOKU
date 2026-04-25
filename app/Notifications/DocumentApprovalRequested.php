<?php

namespace App\Notifications;

use App\Domains\Document\Models\Document;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

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
            'title' => 'Approval dokumen baru',
            'type' => 'document_approval_requested',
            'document_id' => $this->document->id,
            'document_code' => $this->document->document_code ?? null,
            'document_title' => $this->document->title,
            'status' => $this->document->status,
            'requested_by' => $this->requestedByName,
            'url' => route('documents.approval-queue'),
            'message' => "Dokumen {$this->document->title} membutuhkan approval Anda.",
        ];
    }
}
