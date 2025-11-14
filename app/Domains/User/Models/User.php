<?php

namespace App\Domains\User\Models;

use App\Shared\Traits\HasRoles;
use App\Shared\Traits\HasPermissions;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles, HasPermissions;

    protected static function newFactory()
    {
        return \Database\Factories\UserFactory::new();
    }

    protected $fillable = [
        'name',
        'email',
        'password',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }
    public function department(): BelongsTo
    {
        return $this->belongsTo(\App\Domains\Department\Models\Department::class);
    }
}
