<?php

namespace App\Domains\Ipc\Models;

use App\Auditable;
use App\Domains\User\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class TiupBotolCheck extends Model
{
    use Auditable, HasFactory;

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
        'updated_by',
    ];

    protected $casts = [
        'tanggal' => 'date',
    ];

    /**
     * ENUM: nilai tetap untuk kondisi botol.
     */
    public const DROP_TEST = [
        'TDK_BCR' => 'Tidak Bocor / Tidak Pecah',
        'BCR' => 'Bocor / Pecah',
    ];

    public const OK_NOK = [
        'OK' => 'OK',
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
        if (! $filename) {
            return null;
        }

        // pastikan sudah bikin disk "public_path" di config/filesystems.php
        return Storage::disk('public_path')->url(self::imagePath().'/'.$filename);
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
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    protected static function booted(): void
    {
        static::creating(function (self $model) {
            if (! $model->created_by) {
                $model->created_by = auth()->id();
            }
        });

        static::updating(function (self $model) {
            $model->updated_by = auth()->id();
        });
    }
}
