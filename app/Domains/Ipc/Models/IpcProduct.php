<?php

namespace App\Domains\Ipc\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Domains\User\Models\User; // sesuaikan namespace user kamu

class IpcProduct extends Model
{
    /**
     * Nama tabel
     */
    protected $table = 'ipc_check_product';

    /**
     * Mass assignment
     */
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
        'created_by',
    ];

    /**
     * Casting tipe data
     */
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

    /**
     * Relasi ke user penginput
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Konstanta Line Group
     */
    public const LINE_GROUPS = [
        'LINE_TEH',
        'LINE_POWDER',
        'LINE_MINUMAN_BERPERISA',
        'LINE_AMDK',
        'LINE_CONDIMENTS',
    ];

    /**
     * Konstanta Sub Line Teh
     */
    public const SUB_LINES = [
        'TEH_ORI',
        'TEH_SACHET',
        'TEH_SEDUH_50G',
        'TEH_SEDUH_100G',
        'TEH_BUBUK_1KG',
        'TEH_AMPLOP',
        'TEH_HIJAU',
        'TEH_JASMINE',
    ];
}
