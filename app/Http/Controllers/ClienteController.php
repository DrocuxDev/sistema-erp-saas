<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Configuracion;
use App\Support\ImagenUpload;
use Illuminate\Http\Request;

class ClienteController extends Controller
{
    public function index(Request $request)
    {
        $query = Cliente::query();

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

        $clientes = $query->paginate(15)->withQueryString();

        return view('clientes.index', compact('clientes'));
    }

    public function create()
    {
        $configuracion = Configuracion::where('activo', true)->first();

        return view('clientes.create', compact('configuracion'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:255', 'unique:clientes'],
            'razon_social' => ['required', 'string', 'max:255'],
            'ruc' => ['required', 'string', 'max:20', 'unique:clientes'],
            'direccion' => ['required', 'string', 'max:255'],
            'telefono' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'contacto' => ['nullable', 'string', 'max:255'],
            'condicion_pago' => ['required', 'string', 'max:100'],
            'precio_incluye_igv' => ['nullable', 'boolean'],
            'pct_igv' => ['nullable', 'numeric', 'between:0,100', 'decimal:0,2'],
            'pct_retencion' => ['nullable', 'numeric', 'between:0,100', 'decimal:0,2'],
            'estado' => ['required', 'in:activo,inactivo'],
            ...ImagenUpload::rules(),
        ]);

        $configuracion = Configuracion::where('activo', true)->first();
        $validated['precio_incluye_igv'] = $request->boolean('precio_incluye_igv');
        $validated['pct_igv'] ??= $configuracion?->pct_igv ?? 18;
        $validated['pct_retencion'] ??= $configuracion?->pct_retencion ?? 0;
        ImagenUpload::crear($request, 'clientes', function ($ruta) use ($validated) {
            Cliente::create([...$validated, 'imagen' => $ruta]);
        });

        return redirect()->route('clientes.index')
            ->with('success', 'Cliente creado exitosamente');
    }

    public function show(Cliente $cliente)
    {
        return view('clientes.show', compact('cliente'));
    }

    public function edit(Cliente $cliente)
    {
        return view('clientes.edit', compact('cliente'));
    }

    public function update(Request $request, Cliente $cliente)
    {
        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:255', 'unique:clientes,nombre,'.$cliente->id],
            'razon_social' => ['required', 'string', 'max:255'],
            'ruc' => ['required', 'string', 'max:20', 'unique:clientes,ruc,'.$cliente->id],
            'direccion' => ['required', 'string', 'max:255'],
            'telefono' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'contacto' => ['nullable', 'string', 'max:255'],
            'condicion_pago' => ['required', 'string', 'max:100'],
            'precio_incluye_igv' => ['nullable', 'boolean'],
            'pct_igv' => ['nullable', 'numeric', 'between:0,100', 'decimal:0,2'],
            'pct_retencion' => ['nullable', 'numeric', 'between:0,100', 'decimal:0,2'],
            'estado' => ['required', 'in:activo,inactivo'],
            'eliminar_imagen' => ['nullable', 'boolean'],
            ...ImagenUpload::rules(),
        ]);

        $validated['precio_incluye_igv'] = $request->has('precio_incluye_igv')
            ? $request->boolean('precio_incluye_igv') : $cliente->precio_incluye_igv;
        ImagenUpload::actualizar($request, 'clientes', $cliente, $validated);

        return redirect()->route('clientes.index')
            ->with('success', 'Cliente actualizado exitosamente');
    }

    public function destroy(Cliente $cliente)
    {
        ImagenUpload::eliminarModelo($cliente);

        return redirect()->route('clientes.index')
            ->with('success', 'Cliente eliminado exitosamente');
    }
}
