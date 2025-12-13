<?php

namespace App\Domains\Document\Models;

use App\Domains\User\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentApprovalLog extends Model
{
    protected $fillable = [
        'approval_request_id',
        'approval_step_id',
        'user_id',
        'action',
        'ip_address',
        'user_agent',
        'device_name',
        'signed_at',
    ];

    protected $casts = [
        'signed_at' => 'datetime',
    ];

    public const ACTION_APPROVED = 'approved';
    public const ACTION_REJECTED = 'rejected';

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

    public function approvalStep(): BelongsTo
    {
        return $this->belongsTo(
            DocumentApprovalStep::class,
            'approval_step_id'
        );
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
