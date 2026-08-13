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
        Schema::create('movimientos_inventario', function (Blueprint $table) {
            $table->id();
            $table->foreignId('producto_id')->constrained('productos');
            $table->enum('tipo', ['entrada', 'salida', 'ajuste']);
            $table->decimal('cantidad', 10, 2);
            $table->enum('referencia_tipo', ['compra', 'venta', 'ajuste', 'guia_remision', 'orden_compra']);
            $table->unsignedBigInteger('referencia_id')->nullable();
            $table->foreignId('usuario_id')->nullable()->constrained('users');
            $table->date('fecha');
            $table->timestamp('created_at')->useCurrent();
            $table->index(['producto_id', 'fecha'], 'idx_movimientos_producto_fecha');
        });

        if (MySqlSchema::isMySql()) {
            DB::unprepared('
                CREATE TRIGGER trg_movimiento_stock AFTER INSERT ON movimientos_inventario FOR EACH ROW
                BEGIN
                    IF NEW.tipo = \'entrada\' THEN
                        UPDATE productos SET stock_actual = stock_actual + NEW.cantidad WHERE id = NEW.producto_id;
                    ELSEIF NEW.tipo = \'salida\' THEN
                        UPDATE productos SET stock_actual = stock_actual - NEW.cantidad WHERE id = NEW.producto_id;
                    ELSE
                        UPDATE productos SET stock_actual = NEW.cantidad WHERE id = NEW.producto_id;
                    END IF;
                END
            ');
        }
    }

    public function down(): void
    {
        if (MySqlSchema::isMySql()) {
            DB::unprepared('DROP TRIGGER IF EXISTS trg_movimiento_stock');
        }

        Schema::dropIfExists('movimientos_inventario');
    }
};
