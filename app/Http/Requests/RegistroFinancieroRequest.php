<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegistroFinancieroRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $tipos = $this->routeIs('ventas.cobros.store') ? 'cobro,reembolso' : 'pago,reintegro';

        return [
            'idempotency_key' => 'required|uuid',
            'tipo' => 'required|in:'.$tipos,
            'fecha' => 'required|date|before_or_equal:today',
            'monto' => 'required|numeric|min:0.01|max:9999999999.99|decimal:0,2',
            'medio' => 'required|in:efectivo,transferencia,deposito,tarjeta,otro',
            'referencia' => 'required_unless:medio,efectivo|nullable|string|max:100',
            'observacion' => 'nullable|string|max:1000',
        ];
    }
}
