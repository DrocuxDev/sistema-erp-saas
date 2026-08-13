<?php

namespace App\Http\Controllers;

use App\Models\MovimientoInventario;
use App\Models\Producto;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
            'cantidad' => ['required', 'numeric', 'gt:0'],
        ]);

        try {
            MovimientoInventario::create([
                'producto_id' => $validated['producto_id'],
                'tipo' => $validated['tipo'],
                'cantidad' => $validated['cantidad'],
                'referencia_tipo' => 'ajuste',
                'referencia_id' => null,
                'usuario_id' => auth()->id(),
                'fecha' => now()->toDateString(),
            ]);

            return redirect()->route('inventario.kardex')
                ->with('success', 'Ajuste de inventario registrado exitosamente.');
        } catch (Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Error al realizar ajuste: ' . ($e->getMessage() ?: 'Stock insuficiente o validación fallida.'));
        }
    }
}
