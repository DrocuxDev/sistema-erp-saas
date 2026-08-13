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
        Schema::create('compra_detalle', function (Blueprint $table) {
            $table->id();
            $table->foreignId('compra_id')->constrained('compras')->cascadeOnDelete();
            $table->foreignId('producto_id')->constrained('productos');
            $table->foreignId('presentacion_compra_id')->nullable()->constrained('producto_presentaciones_compra');
            $table->decimal('cantidad_presentacion', 10, 2)->nullable();
            $table->decimal('cantidad', 10, 2);
            $table->decimal('precio_compra_unit', 12, 2);
        });

        if (MySqlSchema::isMySql()) {
            DB::unprepared('
                CREATE TRIGGER trg_compra_detalle_valida_producto BEFORE INSERT ON compra_detalle FOR EACH ROW
                BEGIN
                    DECLARE comprable BOOLEAN;
                    SELECT puede_comprarse INTO comprable FROM productos WHERE id = NEW.producto_id;
                    IF comprable = FALSE THEN
                        SIGNAL SQLSTATE \'45000\' SET MESSAGE_TEXT = \'Este producto no está habilitado para compra\';
                    END IF;
                END
            ');
        }
    }

    public function down(): void
    {
        if (MySqlSchema::isMySql()) {
            DB::unprepared('DROP TRIGGER IF EXISTS trg_compra_detalle_valida_producto');
        }

        Schema::dropIfExists('compra_detalle');
    }
};
