<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Models\ProductoPresentacionCompra;
use App\Models\ProductoProveedor;
use App\Models\Proveedor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ProductoController extends Controller
{
    public function index(Request $request)
    {
        $query = Producto::query();

        // Filtro por tipo
        if ($request->filled('tipo')) {
            if ($request->tipo === 'vendible') {
                $query->where('puede_venderse', true);
            } elseif ($request->tipo === 'comprable') {
                $query->where('puede_comprarse', true);
            }
        }

        // Filtro por estado (disponibilidad)
        if ($request->filled('estado')) {
            if ($request->estado === 'bajo') {
                $query->whereColumn('stock_actual', '<=', 'stock_minimo');
            } elseif ($request->estado === 'disponible') {
                $query->whereColumn('stock_actual', '>', 'stock_minimo');
            }
        }

        // Filtro por categoría
        if ($request->filled('categoria')) {
            $query->where('categoria', 'like', '%' . $request->categoria . '%');
        }

        $productos = $query->paginate(15)->withQueryString();
        
        return view('productos.index', compact('productos'));
    }

    public function create()
    {
        return view('productos.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'sku' => ['required', 'string', 'max:50', 'unique:productos'],
            'nombre' => ['required', 'string', 'max:255'],
            'descripcion' => ['nullable', 'string', 'max:500'],
            'unidad_medida' => ['required', 'string', 'max:50'],
            'categoria' => ['nullable', 'string', 'max:100'],
            'stock_actual' => ['required', 'numeric', 'min:0'],
            'stock_minimo' => ['required', 'numeric', 'min:0'],
            'puede_venderse' => ['boolean'],
            'puede_comprarse' => ['boolean'],
        ]);

        Producto::create($validated);

        return redirect()->route('productos.index')
            ->with('success', 'Producto creado exitosamente');
    }

    public function show(Producto $producto)
    {
        $producto->load(['productoProveedores.proveedor', 'presentacionesCompra']);
        $proveedoresDisponibles = Proveedor::where('estado', 'activo')->get();

        return view('productos.show', compact('producto', 'proveedoresDisponibles'));
    }

    public function edit(Producto $producto)
    {
        return view('productos.edit', compact('producto'));
    }

    public function update(Request $request, Producto $producto)
    {
        $validated = $request->validate([
            'sku' => ['required', 'string', 'max:50', 'unique:productos,sku,' . $producto->id],
            'nombre' => ['required', 'string', 'max:255'],
            'descripcion' => ['nullable', 'string', 'max:500'],
            'unidad_medida' => ['required', 'string', 'max:50'],
            'categoria' => ['nullable', 'string', 'max:100'],
            'stock_actual' => ['required', 'numeric', 'min:0'],
            'stock_minimo' => ['required', 'numeric', 'min:0'],
            'puede_venderse' => ['boolean'],
            'puede_comprarse' => ['boolean'],
        ]);

        $producto->update($validated);

        return redirect()->route('productos.index')
            ->with('success', 'Producto actualizado exitosamente');
    }

    public function destroy(Producto $producto)
    {
        $producto->delete();

        return redirect()->route('productos.index')
            ->with('success', 'Producto eliminado exitosamente');
    }

    public function addProveedor(Request $request, Producto $producto): RedirectResponse
    {
        $validated = $request->validate([
            'proveedor_id' => ['required', 'exists:proveedores,id'],
            'precio_compra' => ['required', 'numeric', 'min:0'],
        ]);

        ProductoProveedor::updateOrCreate(
            ['producto_id' => $producto->id, 'proveedor_id' => $validated['proveedor_id']],
            [
                'precio_compra' => $validated['precio_compra'],
                'fecha_actualizacion' => now()->toDateString(),
            ]
        );

        return redirect()->route('productos.show', $producto)
            ->with('success', 'Proveedor asociado correctamente.');
    }

    public function removeProveedor(Producto $producto, int $proveedorId): RedirectResponse
    {
        ProductoProveedor::where('producto_id', $producto->id)
            ->where('proveedor_id', $proveedorId)
            ->delete();

        return redirect()->route('productos.show', $producto)
            ->with('success', 'Proveedor desasociado correctamente.');
    }

    public function addPresentacion(Request $request, Producto $producto): RedirectResponse
    {
        $validated = $request->validate([
            'unidad_medida' => ['required', 'string', 'max:50'],
            'equivalencia' => ['required', 'numeric', 'gt:0'],
            'preferida' => ['nullable', 'boolean'],
        ]);

        if (!empty($validated['preferida'])) {
            ProductoPresentacionCompra::where('producto_id', $producto->id)->update(['preferida' => false]);
        }

        ProductoPresentacionCompra::create([
            'producto_id' => $producto->id,
            'unidad_medida' => $validated['unidad_medida'],
            'equivalencia' => $validated['equivalencia'],
            'preferida' => $validated['preferida'] ?? false,
        ]);

        return redirect()->route('productos.show', $producto)
            ->with('success', 'Presentación de compra agregada.');
    }

    public function removePresentacion(Producto $producto, int $presentacionId): RedirectResponse
    {
        ProductoPresentacionCompra::where('producto_id', $producto->id)
            ->where('id', $presentacionId)
            ->delete();

        return redirect()->route('productos.show', $producto)
            ->with('success', 'Presentación de compra eliminada.');
    }
}
