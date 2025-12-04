<?php

namespace App\Domains\Ipc\Models;

use App\Domains\User\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class IpcProductCheck extends Model
{
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
    ];

    protected $casts = [
        'test_date' => 'date',
        'shift'     => 'integer',
        'avg_moisture_percent' => 'float',
        'avg_weight_g'         => 'float',
        'cup_weight'           => 'float',
        'product_weight'       => 'float',
        'total_cup_plus_product' => 'float',
        'weighing_1'           => 'float',
        'weighing_2'           => 'float',
    ];

    /**
     * Dropdown Line Group yang dipakai SEKARANG.
     * (Line lain belum digunakan → tidak ditampilkan dulu)
     */
    public const LINE_GROUPS = [
        'LINE_TEH'    => 'Line Teh',
        'LINE_POWDER' => 'Line Powder',
    ];

    /**
     * Sub line khusus untuk LINE_TEH
     */
    public const SUB_LINES_TEH = [
        'TEH_ORI'        => 'Teh Ori',
        'TEH_SACHET'     => 'Teh Sachet',
        'TEH_SEDUH_50G'  => 'Teh Seduh 50 g',
        'TEH_SEDUH_100G' => 'Teh Seduh 100 g',
        'TEH_BUBUK_1KG'  => 'Teh Bubuk 1 kg',
        'TEH_AMPLOP'     => 'Teh Amplop',
        'TEH_HIJAU'      => 'Teh Hijau',
        'TEH_BAE'      => 'Teh Bae',
    ];

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
