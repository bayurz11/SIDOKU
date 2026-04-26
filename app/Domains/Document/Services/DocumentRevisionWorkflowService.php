<?php

namespace App\Domains\Document\Services;

use App\Domains\Document\Models\Document;
use Illuminate\Support\Facades\DB;

class DocumentRevisionWorkflowService
{
    public function startRevision(Document $document): Document
    {
        if ($document->status !== Document::STATUS_APPROVED) {
            throw new \RuntimeException('Hanya dokumen approved yang bisa dibuat revisinya.');
        }

        if ($document->is_locked || $document->current_approval_request_id) {
            throw new \RuntimeException('Dokumen masih dalam proses approval.');
        }

        return DB::transaction(function () use ($document) {
            $document = Document::query()->lockForUpdate()->findOrFail($document->id);

            if ($document->status !== Document::STATUS_APPROVED) {
                throw new \RuntimeException('Status dokumen sudah berubah. Muat ulang halaman.');
            }

            $document->update([
                'revision_no' => ((int) $document->revision_no) + 1,
                'status' => Document::STATUS_REVISION,
                'is_active' => false,
                'is_locked' => false,
                'submitted_at' => null,
                'approved_at' => null,
                'current_approval_request_id' => null,
                'updated_by' => auth()->id(),
            ]);

            return $document->fresh();
        });
    }
}
