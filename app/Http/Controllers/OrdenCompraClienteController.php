<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\OrdenCompraCliente;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class OrdenCompraClienteController extends Controller
{
    public function index(Request $request)
    {
        $query = OrdenCompraCliente::with('cliente', 'usuario')
            ->latest('created_at');

        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        if ($request->filled('cliente_id')) {
            $query->where('cliente_id', $request->cliente_id);
        }

        $ordenes  = $query->paginate(15)->withQueryString();
        $clientes = Cliente::where('estado', 'activo')->orderBy('nombre')->get();

        return view('ordenes-compra-cliente.index', compact('ordenes', 'clientes'));
    }

    public function create()
    {
        $clientes = Cliente::where('estado', 'activo')->orderBy('nombre')->get();
        return view('ordenes-compra-cliente.create', compact('clientes'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'cliente_id'      => 'required|exists:clientes,id',
            'numero_oc'       => 'required|string|max:100|unique:ordenes_compra_cliente,numero_oc,NULL,id,cliente_id,' . $request->cliente_id,
            'fecha_recepcion' => 'required|date',
            'archivo'         => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'observaciones'   => 'nullable|string|max:255',
        ]);

        if ($request->hasFile('archivo')) {
            $data['archivo'] = Storage::disk('public')
                ->putFile('ordenes-compra-cliente', $request->file('archivo'));
        }

        $data['usuario_id'] = auth()->id();
        $data['estado']     = 'pendiente';

        OrdenCompraCliente::create($data);

        return redirect()->route('ordenes-compra-cliente.index')
            ->with('success', 'Orden de compra registrada correctamente.');
    }

    public function show(OrdenCompraCliente $ordenesCompraCliente)
    {
        $ordenesCompraCliente->load('cliente', 'usuario', 'cotizacionGenerada', 'ventaGenerada');
        return view('ordenes-compra-cliente.show', ['orden' => $ordenesCompraCliente]);
    }

    public function edit(OrdenCompraCliente $ordenesCompraCliente)
    {
        $clientes = Cliente::where('estado', 'activo')->orderBy('nombre')->get();
        return view('ordenes-compra-cliente.edit', [
            'orden'    => $ordenesCompraCliente,
            'clientes' => $clientes,
        ]);
    }

    public function update(Request $request, OrdenCompraCliente $ordenesCompraCliente)
    {
        $data = $request->validate([
            'cliente_id'      => 'required|exists:clientes,id',
            'numero_oc'       => 'required|string|max:100|unique:ordenes_compra_cliente,numero_oc,' . $ordenesCompraCliente->id . ',id,cliente_id,' . $request->cliente_id,
            'fecha_recepcion' => 'required|date',
            'archivo'         => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'observaciones'   => 'nullable|string|max:255',
            'estado'          => 'required|in:pendiente,procesada,anulada',
        ]);

        if ($request->hasFile('archivo')) {
            if ($ordenesCompraCliente->archivo) {
                Storage::disk('public')->delete($ordenesCompraCliente->archivo);
            }
            $data['archivo'] = Storage::disk('public')
                ->putFile('ordenes-compra-cliente', $request->file('archivo'));
        }

        $ordenesCompraCliente->update($data);

        return redirect()->route('ordenes-compra-cliente.show', $ordenesCompraCliente)
            ->with('success', 'Orden de compra actualizada correctamente.');
    }

    public function destroy(OrdenCompraCliente $ordenesCompraCliente)
    {
        if ($ordenesCompraCliente->archivo) {
            Storage::disk('public')->delete($ordenesCompraCliente->archivo);
        }
        $ordenesCompraCliente->delete();

        return redirect()->route('ordenes-compra-cliente.index')
            ->with('success', 'Orden de compra eliminada.');
    }
}