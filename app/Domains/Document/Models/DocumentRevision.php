<?php

namespace App\Domains\Document\Models;

use App\Domains\User\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentRevision extends Model
{
    protected $fillable = [
        'document_id',
        'revision_no',
        'change_note',
        'file_path',
        'changed_by',
        'changed_at',
    ];

    protected $casts = [
        'changed_at' => 'datetime',
    ];

    // ===============================
    // RELATIONSHIPS
    // ===============================

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
