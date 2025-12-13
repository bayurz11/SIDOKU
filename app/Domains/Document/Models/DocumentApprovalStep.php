<?php

namespace App\Domains\Document\Models;

use App\Domains\User\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Domains\Document\Models\DocumentApprovalLog;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentApprovalStep extends Model
{
    protected $fillable = [
        'approval_request_id',
        'step_order',
        'approver_id',
        'status',
        'acted_at',
        'note',
    ];

    protected $casts = [
        'acted_at' => 'datetime',
    ];

    public const STATUS_PENDING  = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';

    // ===============================
    // RELATIONSHIPS
    // ===============================

    public function approvalRequest(): BelongsTo
    {
        return $this->belongsTo(
            DocumentApprovalRequest::class,
            'approval_request_id'
        );
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approver_id');
    }

    public function logs(): HasMany
    {
        return $this->hasMany(
            DocumentApprovalLog::class,
            'approval_step_id'
        );
    }
}
