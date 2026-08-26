<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100);
            $table->string('slug', 100)->unique();
            $table->string('descripcion', 255)->nullable();
            $table->boolean('es_sistema')->default(false); // Para roles predeterminados que no deberían eliminarse
            $table->timestamps();
        });

        Schema::create('modulos', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 50)->unique(); // dashboard, clientes, proveedores, etc.
            $table->string('nombre', 100);
            $table->string('categoria', 50)->default('General'); // Maestros, Ventas, Inventario, Sistema
            $table->string('icono', 50)->nullable();
            $table->string('ruta_base', 100)->nullable();
            $table->integer('orden')->default(0);
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        Schema::create('rol_modulo', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rol_id')->constrained('roles')->onDelete('cascade');
            $table->foreignId('modulo_id')->constrained('modulos')->onDelete('cascade');
            $table->boolean('puede_ver')->default(true);
            $table->boolean('puede_crear')->default(true);
            $table->boolean('puede_editar')->default(true);
            $table->boolean('puede_eliminar')->default(true);
            $table->timestamps();

            $table->unique(['rol_id', 'modulo_id']);
        });

        Schema::create('user_modulo', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('modulo_id')->constrained('modulos')->onDelete('cascade');
            $table->boolean('permitido')->default(true); // true: concede acceso extra, false: revoca acceso explícito
            $table->timestamps();

            $table->unique(['user_id', 'modulo_id']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('rol_id')->nullable()->after('rol')->constrained('roles')->nullOnDelete();
            $table->string('username', 100)->nullable()->after('email');
            $table->string('punto_emision', 100)->default('OFICINA')->after('rol');
            $table->boolean('is_superadmin')->default(false)->after('rol_id');
            $table->boolean('activo')->default(true)->after('is_superadmin');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['rol_id']);
            $table->dropColumn(['rol_id', 'username', 'punto_emision', 'is_superadmin', 'activo']);
        });

        Schema::dropIfExists('user_modulo');
        Schema::dropIfExists('rol_modulo');
        Schema::dropIfExists('modulos');
        Schema::dropIfExists('roles');
    }
};
