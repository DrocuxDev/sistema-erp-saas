<?php

namespace App\Http\Controllers;

use App\Models\Proveedor;
use App\Support\ImagenUpload;
use Illuminate\Http\Request;

class ProveedorController extends Controller
{
    public function index(Request $request)
    {
        $query = Proveedor::query();

        // Filtro de búsqueda general
        if ($request->filled('buscar')) {
            $buscar = $request->buscar;
            $query->where(function ($q) use ($buscar) {
                $q->where('nombre', 'like', '%'.$buscar.'%')
                    ->orWhere('ruc', 'like', '%'.$buscar.'%')
                    ->orWhere('razon_social', 'like', '%'.$buscar.'%');
            });
        }

        // Filtro por estado
        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        // Filtro por condición de pago
        if ($request->filled('condicion_pago')) {
            $query->where('condicion_pago', 'like', '%'.$request->condicion_pago.'%');
        }

        $proveedores = $query->paginate(15)->withQueryString();

        return view('proveedores.index', compact('proveedores'));
    }

    public function create()
    {
        return view('proveedores.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:255', 'unique:proveedores'],
            'razon_social' => ['required', 'string', 'max:255'],
            'ruc' => ['required', 'string', 'max:20', 'unique:proveedores'],
            'direccion' => ['required', 'string', 'max:255'],
            'telefono' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'contacto' => ['nullable', 'string', 'max:255'],
            'condicion_pago' => ['required', 'string', 'max:100'],
            'estado' => ['required', 'in:activo,inactivo'],
            ...ImagenUpload::rules(),
        ]);

        ImagenUpload::crear($request, 'proveedores', function ($ruta) use ($validated) {
            Proveedor::create([...$validated, 'imagen' => $ruta]);
        });

        return redirect()->route('proveedores.index')
            ->with('success', 'Proveedor creado exitosamente');
    }

    public function show(Proveedor $proveedor)
    {
        return view('proveedores.show', compact('proveedor'));
    }

    public function edit(Proveedor $proveedor)
    {
        return view('proveedores.edit', compact('proveedor'));
    }

    public function update(Request $request, Proveedor $proveedor)
    {
        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:255', 'unique:proveedores,nombre,'.$proveedor->id],
            'razon_social' => ['required', 'string', 'max:255'],
            'ruc' => ['required', 'string', 'max:20', 'unique:proveedores,ruc,'.$proveedor->id],
            'direccion' => ['required', 'string', 'max:255'],
            'telefono' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'contacto' => ['nullable', 'string', 'max:255'],
            'condicion_pago' => ['required', 'string', 'max:100'],
            'estado' => ['required', 'in:activo,inactivo'],
            'eliminar_imagen' => ['nullable', 'boolean'],
            ...ImagenUpload::rules(),
        ]);

        ImagenUpload::actualizar($request, 'proveedores', $proveedor, $validated);

        return redirect()->route('proveedores.index')
            ->with('success', 'Proveedor actualizado exitosamente');
    }

    public function destroy(Proveedor $proveedor)
    {
        ImagenUpload::eliminarModelo($proveedor);

        return redirect()->route('proveedores.index')
            ->with('success', 'Proveedor eliminado exitosamente');
    }
}
