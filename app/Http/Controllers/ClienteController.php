<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use Illuminate\Http\Request;

class ClienteController extends Controller
{
    public function index(Request $request)
    {
        $query = Cliente::query();

        // Filtro de búsqueda general
        if ($request->filled('buscar')) {
            $buscar = $request->buscar;
            $query->where(function($q) use ($buscar) {
                $q->where('nombre', 'like', '%' . $buscar . '%')
                  ->orWhere('ruc', 'like', '%' . $buscar . '%')
                  ->orWhere('razon_social', 'like', '%' . $buscar . '%');
            });
        }

        // Filtro por estado
        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        // Filtro por condición de pago
        if ($request->filled('condicion_pago')) {
            $query->where('condicion_pago', 'like', '%' . $request->condicion_pago . '%');
        }

        $clientes = $query->paginate(15)->withQueryString();
        
        return view('clientes.index', compact('clientes'));
    }

    public function create()
    {
        return view('clientes.create');
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
            'estado' => ['required', 'in:activo,inactivo'],
        ]);

        Cliente::create($validated);

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
            'nombre' => ['required', 'string', 'max:255', 'unique:clientes,nombre,' . $cliente->id],
            'razon_social' => ['required', 'string', 'max:255'],
            'ruc' => ['required', 'string', 'max:20', 'unique:clientes,ruc,' . $cliente->id],
            'direccion' => ['required', 'string', 'max:255'],
            'telefono' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'contacto' => ['nullable', 'string', 'max:255'],
            'condicion_pago' => ['required', 'string', 'max:100'],
            'estado' => ['required', 'in:activo,inactivo'],
        ]);

        $cliente->update($validated);

        return redirect()->route('clientes.index')
            ->with('success', 'Cliente actualizado exitosamente');
    }

    public function destroy(Cliente $cliente)
    {
        $cliente->delete();

        return redirect()->route('clientes.index')
            ->with('success', 'Cliente eliminado exitosamente');
    }
}
