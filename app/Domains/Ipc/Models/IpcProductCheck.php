<?php

namespace App\Domains\Ipc\Models;

use App\Auditable;
use App\Domains\Ipc\Support\IpcSubLineCatalog;
use App\Domains\User\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IpcProductCheck extends Model
{
    use Auditable;
    use HasFactory;

    protected $table = 'ipc_product_checks';

    protected $fillable = [
        'line_group',
        'sub_line',
        'test_date',
        'product_name',
        'shift',

        // Ringkasan hasil
        'avg_moisture_percent',
        'avg_weight_g',

        // Field perhitungan kadar air (dibutuhkan Livewire Form)
        'cup_weight',
        'product_weight',
        'total_cup_plus_product',
        'weighing_1',
        'weighing_2',

        'notes',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'test_date' => 'date',
        'shift' => 'integer',
        'avg_moisture_percent' => 'float',
        'avg_weight_g' => 'float',
        'cup_weight' => 'float',
        'product_weight' => 'float',
        'total_cup_plus_product' => 'float',
        'weighing_1' => 'float',
        'weighing_2' => 'float',
    ];

    /**
     * Dropdown Line Group yang dipakai SEKARANG.
     * (Line lain belum digunakan → tidak ditampilkan dulu)
     */
    public const LINE_GROUPS = [
        'LINE_TEH' => 'Line Teh',
        'LINE_POWDER' => 'Line Powder',
    ];

    /**
     * Sub line khusus untuk LINE_TEH
     */
    public const SUB_LINES_TEH = [
        ...IpcSubLineCatalog::TEA_SUB_LINES,
    ];

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    protected static function booted()
    {
        static::creating(function ($model) {
            $model->created_by = auth()->id();
        });

        static::updating(function ($model) {
            $model->updated_by = auth()->id();
        });
    }
}
