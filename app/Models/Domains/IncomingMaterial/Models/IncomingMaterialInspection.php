<?php

namespace App\Models\Domains\IncomingMaterial\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class IncomingMaterialInspection extends Model
{
    use HasFactory;

    protected $table = 'incoming_material_inspections';

    protected $fillable = [
        'incoming_material_id',
        'parameter',
        'standard',
        'test_result',
        'inspection_result',
        'created_by',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function material()
    {
        return $this->belongsTo(
            IncomingMaterial::class,
            'incoming_material_id'
        );
    }
}
