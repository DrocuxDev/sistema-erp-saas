<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Cotización #{{ $cotizacion->id }}</title>
    <style>
        html, body {
            margin: 0;
            padding: 20px;
            overflow-x: hidden;
            box-sizing: border-box; 
        }
        * {
            box-sizing: border-box;
        }

        @page { margin: 30px 35px; }

        /* Encabezado */
        .header {
            width: 100%;
            border-bottom: 3px solid #4c1d95;
            padding-bottom: 12px;
            margin-bottom: 18px;
        }
        .header table { width: 100%; border-collapse: collapse; }
        .header td { border: none; padding: 0; vertical-align: middle; }
        .logo img { height: 55px; }
        .doc-title {
            text-align: right;
        }
        .doc-title h1 {
            font-size: 20px;
            color: #4c1d95;
            margin: 0 0 2px 0;
            letter-spacing: 1px;
        }
        .doc-title .numero {
            font-size: 13px;
            font-weight: bold;
            color: #1f2937;
        }
        .doc-title .fecha {
            font-size: 10px;
            color: #6b7280;
        }
        .badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 10px;
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
            margin-top: 4px;
        }
        .badge-pendiente { background: #fef3c7; color: #92400e; }
        .badge-aprobada  { background: #dbeafe; color: #1e40af; }
        .badge-rechazada { background: #fee2e2; color: #991b1b; }
        .badge-facturada { background: #d1fae5; color: #065f46; }

        /* Datos cliente */
        .info-box {
            width: 100%;
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 4px;
            padding: 10px 14px;
            margin-bottom: 16px;
        }
        .info-box table { width: 100%; border-collapse: collapse; }
        .info-box td { border: none; padding: 2px 0; font-size: 11px; }
        .info-label { color: #6b7280; width: 90px; }
        .info-value { font-weight: bold; color: #1f2937; }

        /* Tabla de líneas */
        table.detalle { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
        table.detalle th {
            background: #4c1d95;
            color: #fff;
            padding: 7px 8px;
            font-size: 10px;
            text-transform: uppercase;
            text-align: left;
        }
        table.detalle td {
            border-bottom: 1px solid #e5e7eb;
            padding: 6px 8px;
            font-size: 10.5px;
        }
        table.detalle tr:nth-child(even) td { background: #fafafa; }
        .text-right { text-align: right; }

        /* Totales */
        .totales-wrap { width: 100%; }
        .totales {
            width: 260px;
            margin-left: auto;
            border-collapse: collapse;
        }
        .totales td { border: none; padding: 4px 2px; font-size: 11px; }
        .totales .label { color: #6b7280; }
        .totales .valor { text-align: right; font-weight: bold; }
        .totales .total-row td {
            border-top: 2px solid #4c1d95;
            padding-top: 8px;
            font-size: 13px;
            color: #4c1d95;
        }

        /* Observaciones */
        .observaciones {
            margin-top: 14px;
            padding: 10px 14px;
            background: #f9fafb;
            border-left: 3px solid #4c1d95;
            font-size: 10.5px;
            color: #374151;
        }

        /* Footer */
        .footer {
            margin-top: 30px;
            padding-top: 10px;
            border-top: 1px solid #e5e7eb;
            font-size: 9px;
            color: #9ca3af;
            text-align: center;
        }

        .no-print {
            background: #fffbeb;
            border: 1px solid #fbbf24;
            color: #92400e;
            padding: 8px 12px;
            font-size: 10px;
            margin-bottom: 12px;
            border-radius: 4px;
        }
        @media print { .no-print { display: none; } }
    </style>
</head>
<body>

    @if(! class_exists(\Barryvdh\DomPDF\Facade\Pdf::class))
        <div class="no-print">Vista previa. Use Ctrl+P para imprimir o guardar como PDF.</div>
    @endif

    <div class="header">
        <table>
            <tr>
                <td class="logo" style="width: 40%;">
            @php
                $logoPath = public_path('imagenes/logo-jhardsystex.jpeg');
                $logoBase64 = file_exists($logoPath)
                ? 'data:image/jpeg;base64,' . base64_encode(file_get_contents($logoPath))
                : null;
            @endphp
                @if($logoBase64)
                <img src="{{ $logoBase64 }}" alt="JHARDSYSTEX">
            @else
                <strong style="font-size:16px; color:#4c1d95;">JHARDSYSTEX</strong>
            @endif
                </td>
                <td class="doc-title" style="width: 60%;">
                    <h1>COTIZACIÓN</h1>
                    <div class="numero">N° {{ str_pad($cotizacion->id, 6, '0', STR_PAD_LEFT) }}</div>
                    <div class="fecha">{{ $cotizacion->fecha?->format('d/m/Y') }}</div>
                    <span class="badge badge-{{ $cotizacion->estado }}">{{ ucfirst($cotizacion->estado) }}</span>
                </td>
            </tr>
        </table>
    </div>

    <div class="info-box">
        <table>
            <tr>
                <td class="info-label">Cliente:</td>
                <td class="info-value">{{ $cotizacion->cliente->nombre ?? '—' }}</td>
                <td class="info-label" style="width:70px;">RUC:</td>
                <td class="info-value">{{ $cotizacion->cliente->ruc ?? '—' }}</td>
            </tr>
            @if($cotizacion->cliente->direccion ?? false)
            <tr>
                <td class="info-label">Dirección:</td>
                <td colspan="3">{{ $cotizacion->cliente->direccion }}</td>
            </tr>
            @endif
            @if($cotizacion->cliente->contacto ?? false)
            <tr>
                <td class="info-label">Contacto:</td>
                <td>{{ $cotizacion->cliente->contacto }}</td>
                <td class="info-label">Teléfono:</td>
                <td>{{ $cotizacion->cliente->telefono ?? '—' }}</td>
            </tr>
            @endif
        </table>
    </div>

    <table class="detalle">
        <thead>
            <tr>
                <th style="width:38%;">Producto</th>
                <th style="width:18%;">Proveedor</th>
                <th style="width:10%;" class="text-right">Cant.</th>
                <th style="width:16%;" class="text-right">P. Unit.</th>
                <th style="width:18%;" class="text-right">Total</th>
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
    <div class="totales-wrap">
        <table class="totales">
            <tr>
                <td class="label">Subtotal</td>
                <td class="valor">S/ {{ number_format($cotizacion->resumen->subtotal, 2) }}</td>
            </tr>
            <tr>
                <td class="label">IGV</td>
                <td class="valor">S/ {{ number_format($cotizacion->resumen->igv, 2) }}</td>
            </tr>
            <tr>
                <td class="label">Retención</td>
                <td class="valor">S/ {{ number_format($cotizacion->resumen->retencion, 2) }}</td>
            </tr>
            <tr class="total-row">
                <td>Total a facturar</td>
                <td class="valor">S/ {{ number_format($cotizacion->resumen->total_facturar, 2) }}</td>
            </tr>
        </table>
    </div>
    @endif

    @if($cotizacion->observaciones && strtolower(trim($cotizacion->observaciones)) !== 'null')
    <div class="observaciones">
        <strong>Observaciones:</strong> {{ $cotizacion->observaciones }}
    </div>
    @endif

    <div class="footer">
        JHARDSYSTEX — Documento generado el {{ now()->format('d/m/Y H:i') }} — Este documento es una cotización, no constituye un comprobante de pago.
    </div>

</body>
</html>