<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Cotización #{{ $cotizacion->id }}</title>
    <style>
        body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 12px; color: #111; margin: 24px; }
        h1 { font-size: 20px; margin-bottom: 4px; }
        .muted { color: #666; font-size: 11px; }
        table { width: 100%; border-collapse: collapse; margin-top: 16px; }
        th, td { border: 1px solid #ccc; padding: 6px 8px; text-align: left; }
        th { background: #f3f3f3; }
        .text-right { text-align: right; }
        .totales { margin-top: 16px; width: 320px; margin-left: auto; }
        .totales td { border: none; padding: 4px 0; }
        .totales .total { font-weight: bold; border-top: 1px solid #333; padding-top: 8px; }
        @media print { body { margin: 0; } .no-print { display: none; } }
    </style>
</head>
<body>
    @if(! class_exists(\Barryvdh\DomPDF\Facade\Pdf::class))
        <p class="no-print muted">Vista imprimible. Use Ctrl+P para guardar como PDF, o instale <code>barryvdh/laravel-dompdf</code> para descarga directa.</p>
    @endif

    <h1>COTIZACIÓN #{{ $cotizacion->id }}</h1>
    <p class="muted">Fecha: {{ $cotizacion->fecha?->format('d/m/Y') }} | Estado: {{ ucfirst($cotizacion->estado) }}</p>

    <p><strong>Cliente:</strong> {{ $cotizacion->cliente->nombre ?? '—' }}<br>
    <strong>RUC:</strong> {{ $cotizacion->cliente->ruc ?? '—' }}<br>
    @if($cotizacion->observaciones)
        <strong>Observaciones:</strong> {{ $cotizacion->observaciones }}
    @endif
    </p>

    <table>
        <thead>
            <tr>
                <th>Producto</th>
                <th>Proveedor</th>
                <th class="text-right">Cant.</th>
                <th class="text-right">P. Venta</th>
                <th class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($cotizacion->detalles as $detalle)
                <tr>
                    <td>{{ $detalle->producto->nombre ?? $detalle->descripcion }}</td>
                    <td>{{ $detalle->proveedor->nombre ?? '—' }}</td>
                    <td class="text-right">{{ number_format($detalle->cantidad, 2) }}</td>
                    <td class="text-right">S/ {{ number_format($detalle->precio_venta_unit, 2) }}</td>
                    <td class="text-right">S/ {{ number_format($detalle->precio_venta_total, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    @if($cotizacion->resumen)
    <table class="totales">
        <tr><td>Subtotal</td><td class="text-right">S/ {{ number_format($cotizacion->resumen->subtotal, 2) }}</td></tr>
        <tr><td>IGV</td><td class="text-right">S/ {{ number_format($cotizacion->resumen->igv, 2) }}</td></tr>
        <tr><td>Retención</td><td class="text-right">S/ {{ number_format($cotizacion->resumen->retencion, 2) }}</td></tr>
        <tr class="total"><td>Total a facturar</td><td class="text-right">S/ {{ number_format($cotizacion->resumen->total_facturar, 2) }}</td></tr>
    </table>
    @endif
</body>
</html>
