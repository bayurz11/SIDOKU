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

    protected $queryString = [
        'search' => ['except' => ''],
        'status' => ['except' => 'pending'],
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
        $this->showActionModal = true;
    }

    public function closeActionModal(): void
    {
        $this->showActionModal = false;
        $this->selectedStepId = null;
        $this->actionType = null;
        $this->note = '';
    }

    public function submitAction(DocumentApprovalService $service): void
    {
        if (!$this->selectedStepId || !$this->actionType) {
            $this->showErrorToast('Step tidak valid.');
            return;
        }

        $step = DocumentApprovalStep::query()
            ->with(['approvalRequest.document'])
            ->findOrFail($this->selectedStepId);

        try {
            if ($this->actionType === 'approve') {
                // permission check (pakai permission middleware di route juga)
                if (!Auth::user()->hasPermission('documents.approve')) {
                    throw new \RuntimeException('Tidak punya izin approve.');
                }

                $service->approveStep($step, $this->note ?: null);
                $this->showSuccessToast('Step berhasil di-approve.');
            } else {
                if (!Auth::user()->hasPermission('documents.review')) {
                    throw new \RuntimeException('Tidak punya izin reject/review.');
                }

                if (trim($this->note) === '') {
                    throw new \RuntimeException('Catatan reject wajib diisi.');
                }

                $service->rejectStep($step, $this->note);
                $this->showSuccessToast('Step berhasil di-reject.');
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
            ->with([
                'approvalRequest',
                'approvalRequest.document',
                'approvalRequest.document.department',
                'approvalRequest.document.documentType',
            ])
            ->where('approver_id', $userId)
            // hanya step yang sedang aktif (current_step)
            ->whereHas('approvalRequest', function ($q) {
                $q->whereColumn('document_approval_requests.current_step', 'document_approval_steps.step_order')
                    ->where('status', 'pending');
            })
            ->when($this->status, fn($q) => $q->where('status', $this->status))
            ->when($this->search, function ($q) {
                $term = '%' . $this->search . '%';
                $q->whereHas('approvalRequest.document', function ($docQ) use ($term) {
                    $docQ->where('document_code', 'like', $term)
                        ->orWhere('title', 'like', $term);
                });
            })
            ->orderByDesc('id');

        $data = $query->paginate($this->perPage)->onEachSide(0);

        return view('livewire.document.approval-queue', compact('data'));
    }
}
