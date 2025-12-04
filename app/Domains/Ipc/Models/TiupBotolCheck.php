<?php

namespace App\Domains\Ipc\Models;

use App\Domains\User\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TiupBotolCheck extends Model
{
    use HasFactory;

    protected $table = 'tiup_botol_checks';

    protected $fillable = [
        'tanggal',
        'nama_botol',

        'drop_test',
        'gambar_drop_test',

        'penyebaran_rata',
        'gambar_penyebaran_rata',

        'bottom_tidak_menonjol',
        'gambar_bottom_tidak_menonjol',

        'tidak_ada_material',
        'gambar_tidak_ada_material',

        'catatan',
        'created_by',
    ];

    protected $casts = [
        'tanggal' => 'date',
    ];

    /**
     * ENUM: nilai tetap untuk kondisi botol.
     */
    public const DROP_TEST = [
        'TDK_BCR' => 'Tidak Bocor / Tidak Pecah',
        'BCR'     => 'Bocor / Pecah',
    ];

    public const OK_NOK = [
        'OK'  => 'OK',
        'NOK' => 'NOK',
    ];

    /**
     * Folder penyimpanan gambar.
     */
    public static function imagePath(): string
    {
        return 'tiup_botol';
    }

    /**
     * Accessor otomatis untuk mengambil URL penuh gambar.
     */
    protected function getGambarUrl($value)
    {
        return $value ? Storage::url(self::imagePath() . '/' . $value) : null;
    }

    public function getGambarDropTestUrlAttribute()
    {
        return $this->getGambarUrl($this->gambar_drop_test);
    }

    public function getGambarPenyebaranRataUrlAttribute()
    {
        return $this->getGambarUrl($this->gambar_penyebaran_rata);
    }

    public function getGambarBottomTidakMenonjolUrlAttribute()
    {
        return $this->getGambarUrl($this->gambar_bottom_tidak_menonjol);
    }

    public function getGambarTidakAdaMaterialUrlAttribute()
    {
        return $this->getGambarUrl($this->gambar_tidak_ada_material);
    }

    /**
     * Relasi ke User yang membuat data.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Helper label singkat (opsional)
     */
    public static function dropTestLabel(?string $key): ?string
    {
        return $key ? (self::DROP_TEST[$key] ?? $key) : null;
    }

    public static function okNokLabel(?string $key): ?string
    {
        return $key ? (self::OK_NOK[$key] ?? $key) : null;
    }
}
