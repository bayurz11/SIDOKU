<?php

namespace App\Models\Domains\IncomingMaterial\Models;

use Illuminate\Database\Eloquent\Model;

class IncomingMaterialFile extends Model
{
    protected $fillable = [
        'incoming_material_id',
        'file_name',
        'file_path',
        'file_type',
        'category',
        'uploaded_by',
    ];

    public function incomingMaterial()
    {
        return $this->belongsTo(IncomingMaterial::class);
    }
}
