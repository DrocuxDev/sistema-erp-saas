<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\OrdenCompraCliente;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

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

        $ordenes = $query->paginate(15)->withQueryString();
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
            'cliente_id' => 'required|exists:clientes,id',
            'numero_oc' => 'required|string|max:100|unique:ordenes_compra_cliente,numero_oc,NULL,id,cliente_id,'.$request->cliente_id,
            'fecha_recepcion' => 'required|date',
            'archivo' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'observaciones' => 'nullable|string|max:255',
        ]);

        $data['usuario_id'] = auth()->id();
        $data['estado'] = 'pendiente';
        $ruta = $request->hasFile('archivo')
            ? Storage::disk('public')->putFile('ordenes-compra-cliente', $request->file('archivo')) : null;
        try {
            DB::transaction(fn () => OrdenCompraCliente::create([...$data, 'archivo' => $ruta]));
        } catch (\Throwable $e) {
            if ($ruta) {
                Storage::disk('public')->delete($ruta);
            }
            throw $e;
        }

        return redirect()->route('ordenes-compra-cliente.index')
            ->with('success', 'Orden de compra registrada correctamente.');
    }

    public function show(OrdenCompraCliente $ordenesCompraCliente)
    {
        $ordenesCompraCliente->load('cliente', 'usuario', 'cotizacionGenerada', 'ventaGenerada', 'ventas');

        return view('ordenes-compra-cliente.show', ['orden' => $ordenesCompraCliente]);
    }

    public function edit(OrdenCompraCliente $ordenesCompraCliente)
    {
        $clientes = Cliente::where('estado', 'activo')->orderBy('nombre')->get();

        return view('ordenes-compra-cliente.edit', [
            'orden' => $ordenesCompraCliente,
            'clientes' => $clientes,
        ]);
    }

    public function update(Request $request, OrdenCompraCliente $ordenesCompraCliente)
    {
        $data = $request->validate([
            'cliente_id' => 'required|exists:clientes,id',
            'numero_oc' => 'required|string|max:100|unique:ordenes_compra_cliente,numero_oc,'.$ordenesCompraCliente->id.',id,cliente_id,'.$request->cliente_id,
            'fecha_recepcion' => 'required|date',
            'archivo' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'observaciones' => 'nullable|string|max:255',
            'estado' => 'required|in:pendiente,procesada,anulada',
        ]);

        if (($ordenesCompraCliente->cotizacion_id || $ordenesCompraCliente->ventas()->exists()) &&
            ((int) $data['cliente_id'] !== (int) $ordenesCompraCliente->cliente_id ||
             $data['numero_oc'] !== $ordenesCompraCliente->numero_oc || $data['estado'] !== $ordenesCompraCliente->estado)) {
            throw ValidationException::withMessages(['estado' => 'Una orden con documentos derivados no puede cambiar de cliente o estado.']);
        }
        $anterior = $ordenesCompraCliente->archivo;
        $nuevo = $request->hasFile('archivo')
            ? Storage::disk('public')->putFile('ordenes-compra-cliente', $request->file('archivo')) : $anterior;
        if ($request->hasFile('archivo')) {
            $data['archivo'] = $nuevo;
        }
        try {
            DB::transaction(fn () => $ordenesCompraCliente->update($data));
        } catch (\Throwable $e) {
            if ($nuevo !== $anterior && $nuevo) {
                Storage::disk('public')->delete($nuevo);
            }
            throw $e;
        }
        if ($nuevo !== $anterior && $anterior) {
            Storage::disk('public')->delete($anterior);
        }

        return redirect()->route('ordenes-compra-cliente.show', $ordenesCompraCliente)
            ->with('success', 'Orden de compra actualizada correctamente.');
    }

    public function destroy(OrdenCompraCliente $ordenesCompraCliente)
    {
        if ($ordenesCompraCliente->cotizacion_id || $ordenesCompraCliente->ventas()->exists()) {
            throw ValidationException::withMessages(['orden_compra_cliente' => 'La orden tiene documentos derivados y no se puede eliminar.']);
        }
        $ruta = $ordenesCompraCliente->archivo;
        DB::transaction(fn () => $ordenesCompraCliente->delete());
        if ($ruta) {
            Storage::disk('public')->delete($ruta);
        }

        return redirect()->route('ordenes-compra-cliente.index')
            ->with('success', 'Orden de compra eliminada.');
    }

    public function archivo(OrdenCompraCliente $ordenesCompraCliente)
    {
        if (! $ordenesCompraCliente->archivo || ! Storage::disk('public')->exists($ordenesCompraCliente->archivo)) {
            abort(404, 'El archivo adjunto no existe o fue eliminado del servidor.');
        }

        return Storage::disk('public')->response($ordenesCompraCliente->archivo);
    }
}
