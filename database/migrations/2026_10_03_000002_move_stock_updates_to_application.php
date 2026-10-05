<?php

use App\Support\Database\MySqlSchema;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (MySqlSchema::isMySql()) {
            DB::unprepared('DROP TRIGGER IF EXISTS trg_movimiento_stock');
            DB::unprepared('DROP TRIGGER IF EXISTS trg_movimiento_valida');
        }
    }

    public function down(): void
    {
        if (MySqlSchema::isMySql()) {
            DB::unprepared("CREATE TRIGGER trg_movimiento_valida BEFORE INSERT ON movimientos_inventario FOR EACH ROW
                BEGIN
                    DECLARE stock_disponible DECIMAL(12,2) DEFAULT 0;
                    DECLARE existe INT DEFAULT 0;
                    IF NEW.tipo = 'salida' THEN
                        SELECT stock_actual INTO stock_disponible FROM productos WHERE id = NEW.producto_id;
                        IF stock_disponible < NEW.cantidad THEN
                            SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Stock insuficiente para esta salida';
                        END IF;
                    END IF;
                    IF NEW.referencia_tipo = 'compra' THEN
                        SELECT COUNT(*) INTO existe FROM compras WHERE id = NEW.referencia_id;
                    ELSEIF NEW.referencia_tipo = 'venta' THEN
                        SELECT COUNT(*) INTO existe FROM ventas WHERE id = NEW.referencia_id;
                    ELSEIF NEW.referencia_tipo = 'guia_remision' THEN
                        SELECT COUNT(*) INTO existe FROM guias_remision WHERE id = NEW.referencia_id;
                    ELSEIF NEW.referencia_tipo = 'orden_compra' THEN
                        SELECT COUNT(*) INTO existe FROM ordenes_compra WHERE id = NEW.referencia_id;
                    ELSE
                        SET existe = 1;
                    END IF;
                    IF existe = 0 THEN
                        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'referencia_id no existe para ese referencia_tipo';
                    END IF;
                END");
            DB::unprepared("CREATE TRIGGER trg_movimiento_stock AFTER INSERT ON movimientos_inventario FOR EACH ROW
                BEGIN
                    IF NEW.tipo = 'entrada' THEN
                        UPDATE productos SET stock_actual = stock_actual + NEW.cantidad WHERE id = NEW.producto_id;
                    ELSEIF NEW.tipo = 'salida' THEN
                        UPDATE productos SET stock_actual = stock_actual - NEW.cantidad WHERE id = NEW.producto_id;
                    ELSE
                        UPDATE productos SET stock_actual = NEW.cantidad WHERE id = NEW.producto_id;
                    END IF;
                END");
        }
    }
};
