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
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'effective_date' => 'date',
        'expired_date'   => 'date',
        'is_active'      => 'boolean',
    ];

    // ===============================
    // ISO DOCUMENT STATUS CONSTANTS
    // ===============================

    // 1. Dokumen baru dibuat, belum diajukan
    public const STATUS_DRAFT = 'draft';

    // 2. Dokumen sudah diajukan menunggu persetujuan
    public const STATUS_IN_REVIEW = 'in_review';

    // 3. Dokumen ditolak saat proses review
    public const STATUS_REJECTED = 'rejected';

    // 4. Dokumen sudah disetujui & aktif digunakan
    public const STATUS_APPROVED = 'approved';

    // 5. Dokumen dalam proses revisi
    public const STATUS_REVISION = 'revision';

    // 6. Dokumen sudah tidak berlaku (digantikan / ditarik)
    public const STATUS_OBSOLETE = 'obsolete';


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

    public function revisions(): HasMany
    {
        return $this->hasMany(DocumentRevision::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
