<?php

namespace App\Domains\Document\Services;

use Illuminate\Support\Arr;
use App\Domains\User\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use App\Domains\Document\Models\Document;
use App\Domains\Document\Models\DocumentApprovalLog;
use App\Domains\Document\Models\DocumentApprovalStep;
use App\Domains\Document\Models\DocumentApprovalRequest;

class DocumentApprovalService
{
    /**
     * Default flow role-based:
     * 1) document_controller
     * 2) quality_system_manager
     */
    public const DEFAULT_FLOW_ROLES = [
        1 => 'document-controller',
        2 => 'quality-system-manager',
    ];

    /**
     * Submit dokumen untuk approval.
     *
     * @param  Document $doc
     * @param  array|null $approverUserIds  Optional: [userId1, userId2, ...]
     * @param  string|null $note
     * @return DocumentApprovalRequest
     */
    public function submit(Document $doc, ?array $approverUserIds = null, ?string $note = null): DocumentApprovalRequest
    {
        if ($doc->status !== Document::STATUS_DRAFT && $doc->status !== Document::STATUS_REVISION) {
            throw new \RuntimeException('Hanya dokumen status draft/revision yang bisa diajukan.');
        }

        if ($doc->is_locked) {
            throw new \RuntimeException('Dokumen sedang locked (dalam proses approval).');
        }

        // Tentukan approver (by role) jika tidak dikirim manual
        $approvers = $approverUserIds ?: $this->resolveDefaultApprovers($doc);

        if (count($approvers) < 1) {
            throw new \RuntimeException('Approver tidak ditemukan. Pastikan role approver sudah ada.');
        }

        return DB::transaction(function () use ($doc, $approvers, $note) {

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

            $request = $step->approvalRequest()->lockForUpdate()->first();
            $doc     = $request->document()->lockForUpdate()->first();

            $this->assertCanActOnStep($step);

            if ($request->status !== DocumentApprovalRequest::STATUS_PENDING) {
                throw new \RuntimeException('Approval request sudah tidak pending.');
            }

            if ($step->status !== DocumentApprovalStep::STATUS_PENDING) {
                throw new \RuntimeException('Step ini sudah diproses.');
            }

            // approve current step
            $step->update([
                'status'  => DocumentApprovalStep::STATUS_APPROVED,
                'acted_at' => now(),
                'note'    => $note,
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

            $request = $step->approvalRequest()->lockForUpdate()->first();
            $doc     = $request->document()->lockForUpdate()->first();

            $this->assertCanActOnStep($step);

            if ($request->status !== DocumentApprovalRequest::STATUS_PENDING) {
                throw new \RuntimeException('Approval request sudah tidak pending.');
            }

            if ($step->status !== DocumentApprovalStep::STATUS_PENDING) {
                throw new \RuntimeException('Step ini sudah diproses.');
            }

            $step->update([
                'status'  => DocumentApprovalStep::STATUS_REJECTED,
                'acted_at' => now(),
                'note'    => $note,
            ]);

            $this->logAction($step, 'rejected');

            $request->update([
                'status' => DocumentApprovalRequest::STATUS_REJECTED,
                'completed_at' => now(),
            ]);

            // dokumen balik draft (atau rejected sesuai preferensi ISO kamu)
            $doc->update([
                'status' => Document::STATUS_DRAFT, // atau Document::STATUS_REJECTED jika kamu ingin status khusus
                'is_locked' => false,
                'current_approval_request_id' => null,
            ]);
        });
    }

    // ==========================================================
    // INTERNAL HELPERS
    // ==========================================================

    /**
     * Ambil approver default berdasarkan role.
     * - step 1: document_controller
     * - step 2: quality_system_manager
     *
     * Default: ambil user pertama yang match role.
     * Bisa kamu upgrade: filter per department_id doc.
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
     * Cari user berdasarkan role.
     * - Jika pakai Spatie: hasRole()
     * - Fallback: join relasi roles (jika ada)
     */
    protected function findUserByRole(string $role, ?int $departmentId = null)
    {
        $userModel = config('auth.providers.users.model', User::class);
        $query = $userModel::query();

        // opsional: batasi department jika ingin
        // NOTE: kalau approver tidak selalu punya department, comment baris ini
        if ($departmentId && Schema::hasColumn((new $userModel)->getTable(), 'department_id')) {
            $query->where(function ($q) use ($departmentId) {
                $q->whereNull('department_id')
                    ->orWhere('department_id', $departmentId);
            });
        }

        // Spatie style
        if (method_exists($userModel, 'role')) {
            // beberapa project punya scope role()
            $query->role($role);
            return $query->first();
        }

        // fallback: coba hasRole via eager check (ambil semua, cek satu-satu) - kurang efisien tapi aman
        $candidates = $query->limit(50)->get();
        foreach ($candidates as $u) {
            if (method_exists($u, 'hasRole') && $u->hasRole($role)) {
                return $u;
            }
            if (method_exists($u, 'roles')) {
                if ($u->roles()->where('name', $role)->exists()) {
                    return $u;
                }
            }
        }

        return null;
    }

    /**
     * Validasi approver yang boleh action.
     */
    protected function assertCanActOnStep(DocumentApprovalStep $step): void
    {
        $userId = auth()->id();
        if (!$userId) {
            throw new \RuntimeException('Unauthorized.');
        }

        if ((int) $step->approver_id !== (int) $userId) {
            throw new \RuntimeException('Anda bukan approver untuk step ini.');
        }

        // boleh juga: pastikan step yang sedang aktif = current_step
        $request = $step->approvalRequest;
        if ((int) $request->current_step !== (int) $step->step_order) {
            throw new \RuntimeException('Step ini belum aktif / sudah lewat.');
        }
    }

    /**
     * Digital signature log
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
            'device_name'         => null, // bisa kamu isi dari front-end kalau mau
            'signed_at'           => now(),
        ]);
    }
}
