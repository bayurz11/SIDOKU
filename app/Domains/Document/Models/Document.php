<?php

namespace App\Domains\Document\Models;

use App\Domains\User\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Document extends Model
{
    protected $fillable = [
        'document_type_id',
        'department_id',
        'document_prefix_setting_id',
        'parent_document_id',
        'document_code',
        'title',
        'level',
        'revision_no',
        'status',
        'effective_date',
        'expired_date',
        'file_path',
        'summary',
        'is_active',
        'is_locked',
        'submitted_at',
        'approved_at',
        'current_approval_request_id',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'effective_date' => 'date',
        'expired_date'   => 'date',
        'submitted_at'   => 'datetime',
        'approved_at'    => 'datetime',
        'is_active'      => 'boolean',
        'is_locked'      => 'boolean',
    ];

    // ===============================
    // ISO DOCUMENT STATUS CONSTANTS
    // ===============================
    public const STATUS_DRAFT     = 'draft';
    public const STATUS_IN_REVIEW = 'in_review';
    public const STATUS_REJECTED  = 'rejected';
    public const STATUS_APPROVED  = 'approved';
    public const STATUS_REVISION  = 'revision';
    public const STATUS_OBSOLETE  = 'obsolete';

    // ===============================
    // RELATIONSHIPS (MASTER)
    // ===============================

    public function documentType(): BelongsTo
    {
        return $this->belongsTo(DocumentType::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(\App\Domains\Department\Models\Department::class);
    }

    public function prefixSetting(): BelongsTo
    {
        return $this->belongsTo(DocumentPrefixSetting::class, 'document_prefix_setting_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_document_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_document_id');
    }

    // ===============================
    // REVISION
    // ===============================
    public function revisions(): HasMany
    {
        return $this->hasMany(DocumentRevision::class);
    }

    // ===============================
    // APPROVAL
    // ===============================
    public function approvalRequests(): HasMany
    {
        return $this->hasMany(DocumentApprovalRequest::class);
    }

    public function currentApproval(): BelongsTo
    {
        return $this->belongsTo(
            DocumentApprovalRequest::class,
            'current_approval_request_id'
        );
    }

    // ===============================
    // AUDIT USER
    // ===============================
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
