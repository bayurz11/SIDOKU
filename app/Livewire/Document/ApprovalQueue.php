<?php

namespace App\Livewire\Document;

use Livewire\Component;
use Livewire\WithPagination;
use App\Shared\Traits\WithAlerts;
use Illuminate\Support\Facades\Auth;
use App\Domains\Document\Models\DocumentApprovalStep;
use App\Domains\Document\Services\DocumentApprovalService;

class ApprovalQueue extends Component
{
    use WithPagination, WithAlerts;

    public int $perPage = 10;
    public string $search = '';
    public ?string $status = 'pending'; // pending|approved|rejected|null

    // modal action
    public bool $showActionModal = false;
    public ?int $selectedStepId = null;
    public ?string $actionType = null; // approve|reject
    public string $note = '';

    // ✅ penting: deklarasikan property ini
    public ?DocumentApprovalStep $selectedStep = null;

    protected $queryString = [
        'search'  => ['except' => ''],
        'status'  => ['except' => 'pending'],
        'perPage' => ['except' => 10],
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }
    public function updatingStatus()
    {
        $this->resetPage();
    }
    public function updatingPerPage()
    {
        $this->resetPage();
    }

    public function openActionModal(int $stepId, string $type): void
    {
        $this->selectedStepId = $stepId;
        $this->actionType = $type;
        $this->note = '';

        // ✅ Ambil step + relasi lengkap untuk modal
        $this->selectedStep = DocumentApprovalStep::query()
            ->with([
                'approvalRequest',
                'approvalRequest.document.department',
                'approvalRequest.document.documentType',
            ])
            ->where('id', $stepId)
            ->where('approver_id', Auth::id()) // ✅ pastikan milik user yg login
            ->firstOrFail();

        $this->showActionModal = true;
    }

    public function closeActionModal(): void
    {
        // ✅ reset semua termasuk selectedStep
        $this->reset([
            'showActionModal',
            'selectedStepId',
            'actionType',
            'note',
            'selectedStep',
        ]);
    }

    public function submitAction(): void
    {
        $service = app(DocumentApprovalService::class);

        if (!$this->selectedStepId || !$this->actionType) {
            $this->showErrorToast('Step tidak valid.');
            return;
        }

        $user = Auth::user();
        if (!$user) {
            $this->showErrorToast('Unauthorized.');
            return;
        }

        // ✅ Ambil ulang step terkini (biar tidak approve step yang sudah berubah)
        $step = DocumentApprovalStep::query()
            ->with(['approvalRequest.document'])
            ->where('id', $this->selectedStepId)
            ->where('approver_id', $user->id)
            ->firstOrFail();

        try {
            if ($this->actionType === 'approve') {
                if (!$user->hasAnyPermission(['documents.approve'])) {
                    throw new \RuntimeException('Tidak punya izin approve.');
                }

                $service->approveStep($step, $this->note ?: null);
                $this->showSuccessToast('Step berhasil di-approve.');
            } elseif ($this->actionType === 'reject') {
                if (!$user->hasAnyPermission(['documents.review'])) {
                    throw new \RuntimeException('Tidak punya izin reject/review.');
                }

                if (trim($this->note) === '') {
                    throw new \RuntimeException('Catatan reject wajib diisi.');
                }

                $service->rejectStep($step, $this->note);
                $this->showSuccessToast('Step berhasil di-reject.');
            } else {
                throw new \RuntimeException('Action tidak dikenali.');
            }

            $this->closeActionModal();
            $this->resetPage();
        } catch (\Throwable $e) {
            $this->showErrorToast($e->getMessage());
        }
    }

    public function render()
    {
        $userId = Auth::id();

        $query = DocumentApprovalStep::query()
            ->join('document_approval_requests', 'document_approval_requests.id', '=', 'document_approval_steps.approval_request_id')
            ->select('document_approval_steps.*')
            ->with([
                'approvalRequest',
                'approvalRequest.document.department',
                'approvalRequest.document.documentType',
            ])
            ->where('document_approval_steps.approver_id', $userId)
            // ✅ hanya request yang masih pending
            ->where('document_approval_requests.status', 'pending')
            // ✅ hanya step yang aktif (current_step)
            ->whereColumn('document_approval_steps.step_order', 'document_approval_requests.current_step')
            // filter status step (pending/approved/rejected)
            ->when($this->status, fn($q) => $q->where('document_approval_steps.status', $this->status))
            // search by doc code / title
            ->when($this->search, function ($q) {
                $term = '%' . $this->search . '%';

                $q->whereHas('approvalRequest.document', function ($docQ) use ($term) {
                    $docQ->where('document_code', 'like', $term)
                        ->orWhere('title', 'like', $term);
                });
            })
            ->orderByDesc('document_approval_steps.id');

        $data = $query->paginate($this->perPage)->onEachSide(0);

        return view('livewire.document.approval-queue', compact('data'));
    }
}
