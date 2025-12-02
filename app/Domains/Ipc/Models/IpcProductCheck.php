<?php

namespace App\Domains\Ipc\Models;

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
        'avg_moisture_percent',
        'avg_weight_g',
        'avg_ph',
        'avg_brix',
        'avg_tds_ppm',
        'avg_chlorine',
        'avg_ozone',
        'avg_turbidity_ntu',
        'avg_salinity',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'test_date' => 'date',
        'shift'     => 'integer',
    ];

    // Optional: array untuk dropdown
    public const LINE_GROUPS = [
        'LINE_TEH'               => 'Line Teh',
        'LINE_POWDER'            => 'Line Powder',
        'LINE_MINUMAN_BERPERISA' => 'Line Minuman Berperisa',
        'LINE_AMDK'              => 'Line AMDK',
        'LINE_CONDIMENTS'        => 'Line Condiments',
    ];

    public const SUB_LINES_TEH = [
        'TEH_ORI'        => 'Teh Ori',
        'TEH_SACHET'     => 'Teh Sachet',
        'TEH_SEDUH_50G'  => 'Teh Seduh 50 g',
        'TEH_SEDUH_100G' => 'Teh Seduh 100 g',
        'TEH_BUBUK_1KG'  => 'Teh Bubuk 1 kg',
        'TEH_AMPLOP'     => 'Teh Amplop',
        'TEH_HIJAU'      => 'Teh Hijau',
    ];
}
