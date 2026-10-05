<?php

namespace App\Services;

use App\Models\MovimientoInventario;
use App\Models\Producto;
use Illuminate\Validation\ValidationException;

class InventarioService
{
    /** Debe llamarse dentro de la misma transacción que el documento origen. */
    public function registrar(int $productoId, string $tipo, string $cantidad, string $referenciaTipo, ?int $referenciaId, string $fecha): void
    {
        $producto = Producto::whereKey($productoId)->lockForUpdate()->firstOrFail();
        $cantidadNumerica = (float) $cantidad;
        $nuevoSaldo = match ($tipo) {
            'entrada' => (float) $producto->stock_actual + $cantidadNumerica,
            'salida' => (float) $producto->stock_actual - $cantidadNumerica,
            'ajuste' => $cantidadNumerica,
        };

        if ($nuevoSaldo < 0) {
            throw ValidationException::withMessages([
                'lineas' => "Existencias insuficientes para {$producto->nombre}. Disponible: {$producto->stock_actual}.",
            ]);
        }

        $producto->stock_actual = round($nuevoSaldo, 2);
        $producto->save();

        MovimientoInventario::create([
            'producto_id' => $productoId,
            'tipo' => $tipo,
            'cantidad' => $cantidad,
            'referencia_tipo' => $referenciaTipo,
            'referencia_id' => $referenciaId,
            'usuario_id' => auth()->id(),
            'fecha' => $fecha,
        ]);
    }
}
