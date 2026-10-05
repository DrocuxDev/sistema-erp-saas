<?php

use App\Support\Database\MySqlSchema;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('auditoria', function (Blueprint $table) {
            $table->dropForeign(['usuario_id']);
        });

        if (MySqlSchema::isMySql()) {
            // Sin doctrine/dbal: ALTER nativo (conserva los datos existentes).
            DB::statement('ALTER TABLE `auditoria` MODIFY `usuario_id` BIGINT UNSIGNED NULL');
            Schema::table('auditoria', function (Blueprint $table) {
                $table->foreign('usuario_id')->references('id')->on('users')->nullOnDelete();
            });
        } else {
            // SQLite (tests): change() funciona de forma nativa en Laravel 11+.
            Schema::table('auditoria', function (Blueprint $table) {
                $table->foreignId('usuario_id')->nullable()->change();
                $table->foreign('usuario_id')->references('id')->on('users')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        Schema::table('auditoria', function (Blueprint $table) {
            $table->dropForeign(['usuario_id']);
        });

        if (MySqlSchema::isMySql()) {
            DB::statement('ALTER TABLE `auditoria` MODIFY `usuario_id` BIGINT UNSIGNED NOT NULL');
            Schema::table('auditoria', function (Blueprint $table) {
                $table->foreign('usuario_id')->references('id')->on('users');
            });
        } else {
            Schema::table('auditoria', function (Blueprint $table) {
                $table->foreignId('usuario_id')->nullable(false)->change();
                $table->foreign('usuario_id')->references('id')->on('users');
            });
        }
    }
};
