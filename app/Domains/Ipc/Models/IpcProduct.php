<?php

namespace App\Domains\Ipc\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Domains\User\Models\User;

class IpcProduct extends Model
{
    protected $table = 'ipc_check_product';

    protected $fillable = [
        'line_group',
        'sub_line',
        'test_date',
        'product_name',
        'shift',
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
        'test_date'         => 'date',
        'shift'             => 'integer',
        'avg_weight_g'      => 'decimal:3',
        'avg_ph'            => 'decimal:2',
        'avg_brix'          => 'decimal:2',
        'avg_tds_ppm'       => 'decimal:2',
        'avg_chlorine'      => 'decimal:3',
        'avg_ozone'         => 'decimal:3',
        'avg_turbidity_ntu' => 'decimal:3',
        'avg_salinity'      => 'decimal:3',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Konstanta Line Group (ASSOCIATIVE: key = value di DB, value = label)
     */
    public const LINE_GROUPS = [
        'LINE_TEH'               => 'Line Teh',
        'LINE_POWDER'            => 'Line Powder',
        'LINE_MINUMAN_BERPERISA' => 'Line Minuman Berperisa',
        'LINE_AMDK'              => 'Line AMDK',
        'LINE_CONDIMENTS'        => 'Line Condiments',
    ];

    /**
     * Konstanta Sub Line Teh (ASSOCIATIVE)
     */
    public const SUB_LINES = [
        'TEH_ORI'        => 'Teh Ori',
        'TEH_SACHET'     => 'Teh Sachet',
        'TEH_SEDUH_50G'  => 'Teh Seduh 50 g',
        'TEH_SEDUH_100G' => 'Teh Seduh 100 g',
        'TEH_BUBUK_1KG'  => 'Teh Bubuk 1 kg',
        'TEH_AMPLOP'     => 'Teh Amplop',
        'TEH_HIJAU'      => 'Teh Hijau',
        'TEH_JASMINE'    => 'Teh Jasmine',
    ];
}
