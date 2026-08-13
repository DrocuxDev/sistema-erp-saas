<?php

namespace App\Support\Database;

use Illuminate\Support\Facades\Schema;

class MySqlSchema
{
    public static function isMySql(): bool
    {
        return Schema::getConnection()->getDriverName() === 'mysql';
    }
}
