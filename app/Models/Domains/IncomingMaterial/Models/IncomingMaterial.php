<?php

namespace App\Models\Domains\IncomingMaterial\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class IncomingMaterial extends Model
{
    protected $table = 'incoming_materials';

    protected $fillable = [
        'date',
        'receipt_time',
        'supplier',
        'material_name',
        'batch_number',
        'quantity',
        'quantity_unit',
        'sample_quantity',
        'vehicle_number',
        'status',
        'notes',
        'inspection_items',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'date' => 'date',
        'receipt_time' => 'datetime:H:i',
        'inspection_items' => 'array',
        'quantity' => 'decimal:2',
        'sample_quantity' => 'decimal:2',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */

    public function files(): HasMany
    {
        return $this->hasMany(IncomingMaterialFile::class);
    }
}
