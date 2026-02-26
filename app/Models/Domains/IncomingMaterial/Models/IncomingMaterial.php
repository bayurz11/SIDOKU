<?php

namespace App\Models\Domains\IncomingMaterial\Models;

use App\Models\Domains\IncomingMaterial\Models\IncomingMaterialFile;
use Illuminate\Database\Eloquent\Model;

class IncomingMaterial extends Model
{
    protected $fillable = [
        'date',
        'supplier',
        'material_name',
        'batch_number',
        'quantity',
        'status',
        'notes',
        'created_by',
        'updated_by',
    ];
    protected $casts = [
        'inspection_items' => 'array',
        'receipt_date'     => 'date',
    ];

    public function files()
    {
        return $this->hasMany(IncomingMaterialFile::class);
    }
}
