<?php

namespace App\Models\Domains\IncomingMaterial\Models;

use App\Domains\User\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IncomingMaterialStage2Check extends Model
{
    public const RESULT_PENDING = 'PENDING';

    public const RESULT_OK = 'OK';

    public const RESULT_NOT_OK = 'NOT_OK';

    public const MICRO_NOT_REQUIRED = 'NOT_REQUIRED';

    public const MICRO_WAITING = 'WAITING';

    public const DECISION_ACCEPTED = 'ACCEPTED';

    public const DECISION_HOLD = 'HOLD';

    public const DECISION_REJECTED = 'REJECTED';

    protected $table = 'incoming_material_stage2_checks';

    protected $fillable = [
        'incoming_material_id',
        'incoming_material_item_id',
        'field_results',
        'physical_result',
        'microbiology_result',
        'final_decision',
        'notes',
        'checked_by',
        'checked_at',
    ];

    protected $casts = [
        'field_results' => 'array',
        'checked_at' => 'datetime',
    ];

    public function incomingMaterial(): BelongsTo
    {
        return $this->belongsTo(IncomingMaterial::class, 'incoming_material_id');
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(IncomingMaterialItem::class, 'incoming_material_item_id');
    }

    public function checkedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'checked_by');
    }
}
