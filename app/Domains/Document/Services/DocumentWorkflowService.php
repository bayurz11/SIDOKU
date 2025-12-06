<?php

namespace App\Domains\Document\Services;

use App\Domains\Document\Models\Document;
use App\Domains\User\Models\User; // sesuaikan namespace user-mu
use App\Notifications\DocumentApprovalRequested;
use Illuminate\Support\Facades\DB;

class DocumentWorkflowService
{
    /**
     * Kirim dokumen ke tahap "butuh approval".
     *
     * - Ubah status dokumen ke in_review
     * - (opsional) set field submitted_at / submitted_by
     * - Kirim notifikasi ke approver
     */
    public function sendForApproval(Document $document, ?User $requestedBy = null): void
    {
        DB::transaction(function () use ($document, $requestedBy) {

            // 1. Update status dokumen
            $document->status = Document::STATUS_IN_REVIEW; // atau 'in_review'
            if ($requestedBy) {
                $document->submitted_by = $requestedBy->id ?? null;   // kalau ada kolom ini
            }
            $document->submitted_at = now();                          // kalau ada kolom ini
            $document->save();

            // 2. Tentukan siapa saja approver-nya
            // Contoh: semua user dengan role "Document Approver"
            $approvers = User::role('Document Approver')->get();  // kalau pakai spatie/roles
            // Atau bisa juga pakai permission:
            // $approvers = User::permission('documents.approve')->get();

            // 3. Kirim notifikasi ke masing-masing approver
            foreach ($approvers as $approver) {
                $approver->notify(new DocumentApprovalRequested(
                    document: $document,
                    requestedByName: $requestedBy?->name
                ));
            }
        });
    }

    /**
     * (Bonus) Approve dokumen
     */
    public function approve(Document $document, User $approvedBy): void
    {
        DB::transaction(function () use ($document, $approvedBy) {
            $oldStatus = $document->status;

            $document->status       = Document::STATUS_APPROVED;
            $document->approved_by  = $approvedBy->id ?? null;
            $document->approved_at  = now();
            $document->save();

            // Notif ke creator, dll… (bisa pakai DocumentStatusChanged)
            // $document->creator?->notify(new DocumentStatusChanged($document, $oldStatus, $document->status));
        });
    }

    /**
     * (Bonus) Reject dokumen
     */
    public function reject(Document $document, User $rejectedBy, ?string $reason = null): void
    {
        DB::transaction(function () use ($document, $rejectedBy, $reason) {
            $oldStatus = $document->status;

            $document->status       = Document::STATUS_REJECTED;
            $document->rejected_by  = $rejectedBy->id ?? null;
            $document->rejected_at  = now();
            $document->reject_note  = $reason;
            $document->save();

            // Notif ke creator…
            // $document->creator?->notify(new DocumentStatusChanged($document, $oldStatus, $document->status));
        });
    }
}
