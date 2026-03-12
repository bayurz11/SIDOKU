<?php

namespace App\Models\Domains\IncomingMaterial\Models;

use App\Auditable;
use App\Domains\User\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IncomingMaterial extends Model
{
    use HasFactory;
    use Auditable;

    protected $table = 'incoming_materials';

    protected $fillable = [

        'date',
        'expired_date',
        'receipt_time',
        'supplier',
        'material_name',
        'batch_number',
        'quantity',
        'quantity_unit',
        'sample_quantity',
        'vehicle_number',

        'test_moisture',
        'test_microbiology',
        'test_chemical',
        'lab_status',

        'status',
        'notes',

        'created_by',
        'updated_by'
    ];

    protected $casts = [
        'date' => 'date',
        'expired_date' => 'date',
        'receipt_time' => 'datetime:H:i',

        'quantity' => 'decimal:2',
        'sample_quantity' => 'decimal:2',

        // parameter pengujian
        'test_moisture' => 'boolean',
        'test_microbiology' => 'boolean',
        'test_chemical' => 'boolean',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */

    public function inspections(): HasMany
    {
        return $this->hasMany(
            IncomingMaterialInspection::class,
            'incoming_material_id'
        );
    }

    public function files(): HasMany
    {
        return $this->hasMany(
            IncomingMaterialFile::class,
            'incoming_material_id'
        );
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'created_by'
        );
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'updated_by'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | HELPER
    |--------------------------------------------------------------------------
    */

    public function needsMicroTest(): bool
    {
        return $this->test_microbiology === true;
    }

    public function needsMoistureTest(): bool
    {
        return $this->test_moisture === true;
    }
}
