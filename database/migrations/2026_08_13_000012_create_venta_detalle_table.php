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
        Schema::create('venta_detalle', function (Blueprint $table) {
            $table->id();
            $table->foreignId('venta_id')->constrained('ventas')->cascadeOnDelete();
            $table->foreignId('producto_id')->constrained('productos');
            $table->decimal('cantidad', 10, 2);
            $table->decimal('precio_venta_unit', 12, 2);
            $table->decimal('precio_compra_unit', 12, 2);
            $table->index('producto_id', 'idx_venta_detalle_producto');
        });

        if (MySqlSchema::isMySql()) {
            DB::statement('ALTER TABLE venta_detalle ADD ganancia_linea DECIMAL(12,2) GENERATED ALWAYS AS (cantidad * (precio_venta_unit - precio_compra_unit)) STORED');

            DB::unprepared('
                CREATE TRIGGER trg_venta_detalle_valida_producto BEFORE INSERT ON venta_detalle FOR EACH ROW
                BEGIN
                    DECLARE vendible BOOLEAN;
                    SELECT puede_venderse INTO vendible FROM productos WHERE id = NEW.producto_id;
                    IF vendible = FALSE THEN
                        SIGNAL SQLSTATE \'45000\' SET MESSAGE_TEXT = \'Este producto no está habilitado para venta\';
                    END IF;
                END
            ');
        }
    }

    public function down(): void
    {
        if (MySqlSchema::isMySql()) {
            DB::unprepared('DROP TRIGGER IF EXISTS trg_venta_detalle_valida_producto');
        }

        Schema::dropIfExists('venta_detalle');
    }
};
