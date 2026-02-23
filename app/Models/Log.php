<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Log extends Model
{
    protected $fillable = [
        'model_type',
        'model_id',
        'action',
        'user_id',
        'ip_address',
    ];

    public function changes()
    {
        return $this->hasMany(LogChange::class);
    }

    public function user()
    {
        return $this->belongsTo(\App\Domains\User\Models\User::class, 'user_id');
    }
}
