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

    public const DROP_TEST = [
        'TDK_BCR' => 'Tidak Bocor / Tidak Pecah',
        'BCR'     => 'Bocor / Pecah',
    ];

    public const OK_NOK = [
        'OK'  => 'OK',
        'NOK' => 'NOK',
    ];

    public static function imagePath(): string
    {
        return 'tiup_botol';
    }

    /**
     * Helper untuk bikin URL gambar.
     */
    protected function buildImageUrl(?string $filename): ?string
    {
        return $filename
            ? asset(self::imagePath() . '/' . $filename)
            : null;
    }

    public function getDropTestImageUrlAttribute(): ?string
    {
        return $this->buildImageUrl($this->drop_test_image);
    }

    public function getPenyebaranRataImageUrlAttribute(): ?string
    {
        return $this->buildImageUrl($this->penyebaran_rata_image);
    }

    public function getBottomTidakMenonjolImageUrlAttribute(): ?string
    {
        return $this->buildImageUrl($this->bottom_tidak_menonjol_image);
    }

    public function getTidakAdaMaterialImageUrlAttribute(): ?string
    {
        return $this->buildImageUrl($this->tidak_ada_material_image);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
