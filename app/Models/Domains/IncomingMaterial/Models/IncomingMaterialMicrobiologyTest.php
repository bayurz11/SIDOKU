<?php

namespace App\Models\Domains\IncomingMaterial\Models;

use App\Domains\User\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IncomingMaterialMicrobiologyTest extends Model
{
    public const STATUS_DRAFT = 'DRAFT';

    public const STATUS_COMPLETED = 'COMPLETED';

    public const RESULT_PENDING = 'PENDING';

    public const RESULT_PASS = 'PASS';

    public const RESULT_FAIL = 'FAIL';

    protected $table = 'incoming_material_microbiology_tests';

    protected $fillable = [
        'incoming_material_id',
        'tpc',
        'yeast_mold',
        'coliform',
        'e_coli',
        'salmonella',
        'result',
        'status',
        'notes',
        'tested_by',
        'tested_at',
    ];

    protected $casts = [
        'tpc' => 'decimal:2',
        'yeast_mold' => 'decimal:2',
        'coliform' => 'decimal:2',
        'tested_at' => 'datetime',
    ];

    public function incomingMaterial(): BelongsTo
    {
        return $this->belongsTo(IncomingMaterial::class, 'incoming_material_id');
    }

    public function tested_by(): BelongsTo
    {
        return $this->belongsTo(User::class, 'tested_by');
    }

    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }
}
