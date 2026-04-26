<?php

namespace App\Models\Domains\IncomingMaterial\Models;

use App\Auditable;
use App\Domains\User\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class IncomingMaterialItem extends Model
{
    use Auditable;

    public const CATEGORY_GENERAL = 'general';

    public const CATEGORY_TEA = 'tea';

    protected $table = 'incoming_material_items';

    protected $fillable = [
        'name',
        'category',
        'default_unit',
        'requires_microbiology',
        'stage2_fields',
        'is_active',
        'description',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'requires_microbiology' => 'boolean',
        'stage2_fields' => 'array',
        'is_active' => 'boolean',
    ];

    public function incomingMaterials(): HasMany
    {
        return $this->hasMany(IncomingMaterial::class, 'incoming_material_item_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function stage2FieldList(): array
    {
        $fields = $this->stage2_fields;

        if (is_array($fields) && $fields !== []) {
            return array_values(array_filter(array_map('trim', $fields)));
        }

        if ($this->category === self::CATEGORY_TEA) {
            return [
                'Kondisi kemasan',
                'Warna daun teh',
                'Aroma',
                'Benda asing',
                'Kadar air visual',
                'Kesesuaian COA',
            ];
        }

        return [
            'Kondisi kemasan',
            'Kebersihan material',
            'Kesesuaian label',
            'Kesesuaian COA',
        ];
    }
}
