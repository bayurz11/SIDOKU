<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LogChange extends Model
{
    protected $fillable = [
        'log_id',
        'field',
        'old_value',
        'new_value',
    ];

    public function log()
    {
        return $this->belongsTo(Log::class);
    }
    public function user()
    {
        return $this->belongsTo(\App\Domains\User\Models\User::class, 'user_id');
    }
}
