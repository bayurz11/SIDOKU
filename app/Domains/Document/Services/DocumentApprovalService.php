<?php

namespace App\Domains\Document\Services;

use App\Domains\User\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Domains\Document\Models\Document;
use App\Domains\Document\Models\DocumentApprovalLog;
use App\Domains\Document\Models\DocumentApprovalStep;
use App\Domains\Document\Models\DocumentApprovalRequest;

class DocumentApprovalService
{
    /**
     * Default flow role-based (sesuai slug roles di DB):
     * Step 1: document-controller
     * Step 2: quality-system-manager
     */
    public const DEFAULT_FLOW_ROLES = [
        1 => 'document-controller',
        2 => 'quality-system-manager',
    ];

    /**
     * Submit dokumen untuk approval.
     */
    public function submit(Document $doc, ?array $approverUserIds = null, ?string $note = null): DocumentApprovalRequest
    {
        if (!in_array($doc->status, [Document::STATUS_DRAFT, Document::STATUS_REVISION], true)) {
            throw new \RuntimeException('Hanya dokumen status draft/revision yang bisa diajukan.');
        }

        if ($doc->is_locked) {
            throw new \RuntimeException('Dokumen sedang locked (dalam proses approval).');
        }

        // Tentukan approver (by role) jika tidak dikirim manual
        $approvers = $approverUserIds ?: $this->resolveDefaultApprovers($doc);

        // ✅ Validasi harus lengkap sesuai jumlah step
        $expectedSteps = count(self::DEFAULT_FLOW_ROLES);
        if (count($approvers) < $expectedSteps) {
            $missing = $this->getMissingApproversByRole($doc);
            throw new \RuntimeException(
                'Approver tidak ditemukan untuk: ' . implode(', ', $missing) .
                    '. Pastikan user sudah di-assign role tersebut (cek tabel user_roles).'
            );
        }

        return DB::transaction(function () use ($doc, $approvers, $note) {

            // ✅ Cegah double-submit jika masih ada current approval
            if ($doc->current_approval_request_id) {
                throw new \RuntimeException('Dokumen sudah memiliki pengajuan aktif.');
            }

            // buat approval request
            $request = DocumentApprovalRequest::create([
                'document_id'  => $doc->id,
                'status'       => DocumentApprovalRequest::STATUS_PENDING,
                'current_step' => 1,
                'request_note' => $note,
                'requested_by' => auth()->id(),
                'requested_at' => now(),
            ]);

            // buat steps berurutan
            foreach (array_values($approvers) as $idx => $userId) {
                $request->steps()->create([
                    'step_order'  => $idx + 1,
                    'approver_id' => $userId,
                    'status'      => DocumentApprovalStep::STATUS_PENDING,
                ]);
            }

            // update document status + lock
            $doc->update([
                'status' => Document::STATUS_IN_REVIEW,
                'submitted_at' => now(),
                'current_approval_request_id' => $request->id,
                'is_locked' => true,
            ]);

            return $request;
        });
    }

    /**
     * Approve step tertentu (oleh approver pada step tersebut).
     */
    public function approveStep(DocumentApprovalStep $step, ?string $note = null): void
    {
        DB::transaction(function () use ($step, $note) {

            // lock request + document biar aman race condition
            $request = $step->approvalRequest()->lockForUpdate()->firstOrFail();
            $doc     = $request->document()->lockForUpdate()->firstOrFail();

            $this->assertCanActOnStep($step, $request);

            if ($request->status !== DocumentApprovalRequest::STATUS_PENDING) {
                throw new \RuntimeException('Approval request sudah tidak pending.');
            }

            if ($step->status !== DocumentApprovalStep::STATUS_PENDING) {
                throw new \RuntimeException('Step ini sudah diproses.');
            }

            // approve current step
            $step->update([
                'status'   => DocumentApprovalStep::STATUS_APPROVED,
                'acted_at' => now(),
                'note'     => $note,
            ]);

            $this->logAction($step, 'approved');

            // apakah ada next step?
            $nextStep = $request->steps()
                ->where('step_order', $step->step_order + 1)
                ->first();

            if ($nextStep) {
                // lanjut ke step berikutnya
                $request->update(['current_step' => $nextStep->step_order]);
                return;
            }

            // FINAL APPROVED (tidak ada next step)
            $request->update([
                'status' => DocumentApprovalRequest::STATUS_APPROVED,
                'completed_at' => now(),
            ]);

            $doc->update([
                'status' => Document::STATUS_APPROVED,
                'approved_at' => now(),
                'is_locked' => false,
                'is_active' => true,
                'current_approval_request_id' => null, // ✅ clear active request
                // set effective_date jika belum ada
                'effective_date' => $doc->effective_date ?: now()->toDateString(),
            ]);
        });
    }

    /**
     * Reject step tertentu (oleh approver pada step tersebut).
     */
    public function rejectStep(DocumentApprovalStep $step, string $note): void
    {
        if (trim($note) === '') {
            throw new \RuntimeException('Catatan reject wajib diisi.');
        }

        DB::transaction(function () use ($step, $note) {

            $request = $step->approvalRequest()->lockForUpdate()->firstOrFail();
            $doc     = $request->document()->lockForUpdate()->firstOrFail();

            $this->assertCanActOnStep($step, $request);

            if ($request->status !== DocumentApprovalRequest::STATUS_PENDING) {
                throw new \RuntimeException('Approval request sudah tidak pending.');
            }

            if ($step->status !== DocumentApprovalStep::STATUS_PENDING) {
                throw new \RuntimeException('Step ini sudah diproses.');
            }

            $step->update([
                'status'   => DocumentApprovalStep::STATUS_REJECTED,
                'acted_at' => now(),
                'note'     => $note,
            ]);

            $this->logAction($step, 'rejected');

            $request->update([
                'status' => DocumentApprovalRequest::STATUS_REJECTED,
                'completed_at' => now(),
            ]);

            // dokumen balik draft (atau rejected sesuai preferensi ISO kamu)
            $doc->update([
                'status' => Document::STATUS_DRAFT, // bisa diganti STATUS_REJECTED kalau kamu pakai status itu
                'is_locked' => false,
                'current_approval_request_id' => null, // ✅ clear active request
                // 'submitted_at' => null, // opsional: reset waktu pengajuan
            ]);
        });
    }

    // ==========================================================
    // INTERNAL HELPERS
    // ==========================================================

    /**
     * Ambil approver default berdasarkan role.
     * Menggunakan query whereHas('roles') agar sesuai pivot user_roles.
     */
    protected function resolveDefaultApprovers(Document $doc): array
    {
        $approvers = [];

        foreach (self::DEFAULT_FLOW_ROLES as $order => $role) {
            $user = $this->findUserByRole($role, $doc->department_id);

            if ($user) {
                $approvers[] = $user->id;
            }
        }

        return $approvers;
    }

    /**
     * Cari 1 user pertama yang punya role tertentu (role.name),
     * opsional bisa di-scope department.
     */
    protected function findUserByRole(string $roleName, ?int $departmentId = null): ?User
    {
        $query = User::query();

        // Optional: scope dept jika memang approver per dept
        // Jika approver global, kamu boleh hapus block ini.
        if ($departmentId && Schema::hasColumn('users', 'department_id')) {
            $query->where(function ($q) use ($departmentId) {
                $q->whereNull('department_id')
                    ->orWhere('department_id', $departmentId);
            });
        }

        return $query
            ->whereHas('roles', function ($q) use ($roleName) {
                $q->where('roles.name', $roleName);
            })
            ->first();
    }

    /**
     * Buat list step yang missing supaya error jelas.
     */
    protected function getMissingApproversByRole(Document $doc): array
    {
        $missing = [];

        foreach (self::DEFAULT_FLOW_ROLES as $step => $role) {
            if (!$this->findUserByRole($role, $doc->department_id)) {
                $missing[] = "Step {$step} ({$role})";
            }
        }

        return $missing ?: ['(unknown)'];
    }

    /**
     * Validasi approver yang boleh action + pastikan step aktif.
     */
    protected function assertCanActOnStep(DocumentApprovalStep $step, DocumentApprovalRequest $request): void
    {
        $userId = auth()->id();

        if (!$userId) {
            throw new \RuntimeException('Unauthorized.');
        }

        if ((int) $step->approver_id !== (int) $userId) {
            throw new \RuntimeException('Anda bukan approver untuk step ini.');
        }

        if ((int) $request->current_step !== (int) $step->step_order) {
            throw new \RuntimeException('Step ini belum aktif / sudah lewat.');
        }
    }

    /**
     * Digital signature log (audit ISO).
     */
    protected function logAction(DocumentApprovalStep $step, string $action): void
    {
        DocumentApprovalLog::create([
            'approval_request_id' => $step->approval_request_id,
            'approval_step_id'    => $step->id,
            'user_id'             => auth()->id(),
            'action'              => $action,
            'ip_address'          => request()->ip(),
            'user_agent'          => substr((string) request()->userAgent(), 0, 255),
            'device_name'         => null, // opsional: isi dari frontend
            'signed_at'           => now(),
        ]);
    }
}
