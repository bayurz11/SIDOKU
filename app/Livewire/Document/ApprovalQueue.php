<?php

namespace App\Livewire\Document;

use App\Domains\Document\Models\DocumentApprovalStep;
use App\Domains\Document\Services\DocumentApprovalService;
use App\Shared\Traits\WithAlerts;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class ApprovalQueue extends Component
{
    use WithAlerts, WithPagination;

    public int $perPage = 10;

    public string $search = '';

    public ?string $status = 'pending';

    public bool $showActionModal = false;

    public ?int $selectedStepId = null;

    public ?string $actionType = null;

    public string $note = '';

    public ?DocumentApprovalStep $selectedStep = null;

    public array $statusOptions = [
        'pending' => 'Pending Aktif',
        'approved' => 'Approved',
        'rejected' => 'Rejected',
        'all' => 'Semua Riwayat',
    ];

    protected $queryString = [
        'search' => ['except' => ''],
        'status' => ['except' => 'pending'],
        'perPage' => ['except' => 10],
    ];

    protected array $allowedPerPage = [10, 25, 50, 100];

    protected array $allowedStatuses = ['pending', 'approved', 'rejected', 'all'];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatus(): void
    {
        $this->resetPage();
    }

    public function updatingPerPage(): void
    {
        $this->resetPage();
    }

    public function openActionModal(int $stepId, string $type): void
    {
        $this->selectedStepId = $stepId;
        $this->actionType = $type;
        $this->note = '';

        $this->selectedStep = DocumentApprovalStep::query()
            ->with([
                'approvalRequest',
                'approvalRequest.document.department',
                'approvalRequest.document.documentType',
            ])
            ->where('id', $stepId)
            ->where('approver_id', Auth::id())
            ->firstOrFail();

        $this->showActionModal = true;
    }

    public function closeActionModal(): void
    {
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

        if (! $this->selectedStepId || ! $this->actionType) {
            $this->showErrorToast('Step tidak valid.');

            return;
        }

        $user = Auth::user();
        if (! $user) {
            $this->showErrorToast('Unauthorized.');

            return;
        }

        $step = DocumentApprovalStep::query()
            ->with(['approvalRequest.document'])
            ->where('id', $this->selectedStepId)
            ->where('approver_id', $user->id)
            ->firstOrFail();

        try {
            if ($this->actionType === 'approve') {
                if (! $user->hasAnyPermission(['documents.approve'])) {
                    throw new \RuntimeException('Tidak punya izin approve.');
                }

                $service->approveStep($step, $this->note ?: null);
                $this->showSuccessToast('Step berhasil di-approve.');
            } elseif ($this->actionType === 'reject') {
                if (! $user->hasAnyPermission(['documents.review'])) {
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

            $this->dispatch('document:approval_updated');
            $this->dispatch('document:saved');
            $this->closeActionModal();
            $this->resetPage();
        } catch (\Throwable $e) {
            $this->showErrorToast($e->getMessage());
        }
    }

    public function render()
    {
        if (! in_array((string) $this->status, $this->allowedStatuses, true)) {
            $this->status = 'pending';
        }

        if (! in_array((int) $this->perPage, $this->allowedPerPage, true)) {
            $this->perPage = 10;
        }

        $search = trim($this->search);

        $query = DocumentApprovalStep::query()
            ->join('document_approval_requests', 'document_approval_requests.id', '=', 'document_approval_steps.approval_request_id')
            ->select('document_approval_steps.*')
            ->with([
                'approvalRequest.requester',
                'approvalRequest.document.department',
                'approvalRequest.document.documentType',
            ])
            ->where('document_approval_steps.approver_id', Auth::id())
            ->when($this->status === 'pending', function ($q) {
                $this->applyActivePendingFilter($q);
            })
            ->when(in_array($this->status, ['approved', 'rejected'], true), function ($q) {
                $q->where('document_approval_steps.status', $this->status);
            })
            ->when($this->status === 'all', function ($q) {
                $q->where(function ($statusQuery) {
                    $statusQuery
                        ->where(function ($pendingQuery) {
                            $this->applyActivePendingFilter($pendingQuery);
                        })
                        ->orWhereIn('document_approval_steps.status', [
                            DocumentApprovalStep::STATUS_APPROVED,
                            DocumentApprovalStep::STATUS_REJECTED,
                        ]);
                });
            })
            ->when($search !== '', function ($q) use ($search) {
                $term = '%'.$search.'%';

                $q->where(function ($searchQuery) use ($term) {
                    $searchQuery
                        ->where('document_approval_steps.note', 'like', $term)
                        ->orWhere('document_approval_steps.status', 'like', $term)
                        ->orWhere('document_approval_requests.status', 'like', $term)
                        ->orWhere('document_approval_requests.request_note', 'like', $term)
                        ->orWhereHas('approvalRequest.requester', function ($userQ) use ($term) {
                            $userQ->where('name', 'like', $term)
                                ->orWhere('email', 'like', $term);
                        })
                        ->orWhereHas('approvalRequest.document', function ($docQ) use ($term) {
                            $docQ->where('document_code', 'like', $term)
                                ->orWhere('title', 'like', $term)
                                ->orWhere('summary', 'like', $term)
                                ->orWhereHas('department', function ($departmentQ) use ($term) {
                                    $departmentQ->where('name', 'like', $term)
                                        ->orWhere('description', 'like', $term);
                                })
                                ->orWhereHas('documentType', function ($typeQ) use ($term) {
                                    $typeQ->where('name', 'like', $term)
                                        ->orWhere('description', 'like', $term);
                                });
                        });
                });
            })
            ->orderByDesc('document_approval_steps.acted_at')
            ->orderByDesc('document_approval_steps.id');

        $data = $query->paginate($this->perPage)->onEachSide(0);

        return view('livewire.document.approval-queue', compact('data'));
    }

    private function applyActivePendingFilter($query): void
    {
        $query->where('document_approval_requests.status', 'pending')
            ->where('document_approval_steps.status', DocumentApprovalStep::STATUS_PENDING)
            ->whereColumn('document_approval_steps.step_order', 'document_approval_requests.current_step');
    }
}
