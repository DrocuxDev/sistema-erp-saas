<?php

namespace App\Http\Controllers;

use App\Models\MovimientoInventario;
use App\Models\Producto;
use App\Services\InventarioService;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class MovimientoInventarioController extends Controller
{
    public function kardex(Request $request): View
    {
        $query = MovimientoInventario::with(['producto', 'usuario']);

        if ($request->filled('producto_id')) {
            $query->where('producto_id', $request->producto_id);
        }

        if ($request->filled('tipo')) {
            $query->where('tipo', $request->tipo);
        }

        if ($request->filled('fecha_inicio')) {
            $query->where('fecha', '>=', $request->fecha_inicio);
        }

        if ($request->filled('fecha_fin')) {
            $query->where('fecha', '<=', $request->fecha_fin);
        }

        $movimientos = $query->orderBy('id', 'desc')->paginate(20)->withQueryString();
        $productos = Producto::orderBy('nombre')->get();

        return view('inventario.kardex', compact('movimientos', 'productos'));
    }

    public function createAjuste(): View
    {
        $productos = Producto::orderBy('nombre')->get();

        return view('inventario.ajuste', compact('productos'));
    }

    public function storeAjuste(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'producto_id' => ['required', 'exists:productos,id'],
            'tipo' => ['required', 'in:entrada,salida,ajuste'],
            'cantidad' => ['required', 'numeric', 'gt:0', 'max:1000000', 'decimal:0,2'],
        ]);

        try {
            DB::transaction(fn () => app(InventarioService::class)->registrar(
                (int) $validated['producto_id'], $validated['tipo'], (string) $validated['cantidad'],
                'ajuste', null, now()->toDateString()
            ));

            return redirect()->route('inventario.kardex')
                ->with('success', 'Ajuste de inventario registrado exitosamente.');
        } catch (Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Error al realizar ajuste: '.($e->getMessage() ?: 'Existencias insuficientes o validación fallida.'));
        }
    }
}
