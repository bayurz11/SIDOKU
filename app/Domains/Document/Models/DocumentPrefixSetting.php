<?php

namespace App\Domains\Document\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentPrefixSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_prefix',
        'document_type_id',
        'department_id',
        'sub_reference_format',
        'format_nomor',
        'last_sequence',
        'last_subsequence',
        'reset_interval',
        'example_output',
        'is_active',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function documentType()
    {
        return $this->belongsTo(\App\Domains\Document\Models\DocumentType::class);
    }

    public function department()
    {
        return $this->belongsTo(\App\Domains\Department\Models\Department::class);
    }
}
