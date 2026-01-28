<?php

namespace App\Support;

use Illuminate\Support\Facades\Auth;

class AuthAccess
{
    protected static $user = null;

    public static function user()
    {
        if (self::$user !== null) {
            return self::$user;
        }

        self::$user = Auth::check() ? Auth::user() : null;

        return self::$user;
    }

    public static function check(): bool
    {
        return self::user() !== null;
    }
}
