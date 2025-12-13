<?php

namespace App\Domains\Document\Models;

use App\Domains\User\Models\User;
use Illuminate\Database\Eloquent\Model;
use App\Domains\Document\Models\Document;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Domains\Document\Models\DocumentApprovalStep;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentApprovalRequest extends Model
{
    protected $fillable = [
        'document_id',
        'status',
        'current_step',
        'request_note',
        'requested_by',
        'requested_at',
        'completed_at',
    ];

    protected $casts = [
        'requested_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public const STATUS_PENDING   = 'pending';
    public const STATUS_APPROVED  = 'approved';
    public const STATUS_REJECTED  = 'rejected';
    public const STATUS_CANCELLED = 'cancelled';

    // ===============================
    // RELATIONSHIPS
    // ===============================

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    public function steps(): HasMany
    {
        return $this->hasMany(DocumentApprovalStep::class, 'approval_request_id');
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }
}
