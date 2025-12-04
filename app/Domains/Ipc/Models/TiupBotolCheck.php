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
        'hari',
        'nama_botol',

        'drop_test',
        'penyebaran_rata',
        'bottom_tidak_menonjol',
        'tidak_ada_material',

        'drop_test_image',
        'penyebaran_rata_image',
        'bottom_tidak_menonjol_image',
        'tidak_ada_material_image',

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
     * Helper ambil URL gambar dari disk public_path.
     */
    protected function imageUrl(?string $filename): ?string
    {
        if (!$filename) {
            return null;
        }

        // pastikan sudah bikin disk "public_path" di config/filesystems.php
        return Storage::disk('public_path')->url(self::imagePath() . '/' . $filename);
    }

    public function getDropTestImageUrlAttribute()
    {
        return $this->imageUrl($this->drop_test_image);
    }

    public function getPenyebaranRataImageUrlAttribute()
    {
        return $this->imageUrl($this->penyebaran_rata_image);
    }

    public function getBottomTidakMenonjolImageUrlAttribute()
    {
        return $this->imageUrl($this->bottom_tidak_menonjol_image);
    }

    public function getTidakAdaMaterialImageUrlAttribute()
    {
        return $this->imageUrl($this->tidak_ada_material_image);
    }

    /**
     * Relasi ke User yang membuat data.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
