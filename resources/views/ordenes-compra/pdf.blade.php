<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Orden de Compra {{ $orden_compra->codigo_formateado }}</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 18mm 15mm;
        }

        *, *::before, *::after {
            box-sizing: border-box;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 9pt;
            line-height: 1.4;
            color: #333333;
            margin: 0;
            padding: 0;
            background-color: #ffffff;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .quotation-page {
            width: 100%;
            background: #ffffff;
        }

        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .header-table td {
            vertical-align: middle;
            padding: 0;
        }

        .logo-box {
            width: 25%;
            vertical-align: middle;
        }

        .logo-box img {
            max-height: 60px;
            max-width: 160px;
            display: block;
        }

        .company-info-box {
            width: 40%;
            padding: 0 15px;
            font-size: 8pt;
            line-height: 1.4;
            color: #222222;
        }

        .company-name-bold {
            font-size: 10pt;
            font-weight: bold;
            color: #111111;
            margin-bottom: 3px;
            text-transform: uppercase;
        }

        .ruc-col {
            width: 35%;
            vertical-align: middle;
        }

        .ruc-table-box {
            width: 100%;
            border: 1.5px solid #0076a8;
            border-radius: 4px;
            text-align: center;
            padding: 8px 6px;
            background-color: #ffffff;
        }

        .ruc-header-line {
            font-size: 10pt;
            font-weight: bold;
            color: #111111;
            letter-spacing: 0.5px;
        }

        .ruc-type-line {
            border-top: 1px solid #0076a8;
            border-bottom: 1px solid #0076a8;
            margin: 5px 0;
            padding: 3px 0;
            font-size: 11.5pt;
            font-weight: bold;
            color: #0076a8;
            letter-spacing: 1px;
            text-transform: uppercase;
        }

        .ruc-code-line {
            font-size: 11pt;
            font-weight: bold;
            color: #111111;
            letter-spacing: 0.5px;
            margin: 2px 0;
            white-space: nowrap;
        }

        .client-box {
            width: 100%;
            border: 1px solid #d1d5db;
            border-radius: 4px;
            padding: 10px 14px;
            margin-bottom: 20px;
            background-color: #ffffff;
        }

        .client-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 0;
        }

        .client-table td {
            padding: 3px 6px;
            font-size: 8.5pt;
            vertical-align: top;
            line-height: 1.4;
        }

        .c-label {
            font-weight: bold;
            color: #111111;
            text-transform: uppercase;
            width: 120px;
            white-space: nowrap;
        }

        .c-value {
            color: #222222;
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            border: 1px solid #d1d5db;
        }

        .items-table thead th {
            background-color: #0076a8;
            color: #ffffff;
            font-size: 8pt;
            font-weight: bold;
            text-transform: uppercase;
            padding: 8px 10px;
            border: 1px solid #0076a8;
            text-align: left;
            letter-spacing: 0.5px;
        }

        .items-table thead th.text-center { text-align: center; }
        .items-table thead th.text-right { text-align: right; }

        .items-table tbody td {
            padding: 8px 10px;
            font-size: 8.5pt;
            vertical-align: top;
            border-bottom: 1px solid #e5e7eb;
            color: #222222;
            line-height: 1.35;
        }

        .th-num { width: 5%; text-align: center; }
        .th-cod { width: 15%; text-align: center; }
        .th-cant { width: 10%; text-align: right; }
        .th-um { width: 8%; text-align: center; }
        .th-desc { width: 38%; }
        .th-punit { width: 12%; text-align: right; }
        .th-imp { width: 12%; text-align: right; }

        .item-title {
            font-weight: bold;
            color: #111111;
            display: block;
            text-transform: uppercase;
            line-height: 1.3;
        }

        .summary-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .summary-table td {
            vertical-align: top;
            padding: 0;
        }

        .son-letras {
            width: 60%;
            font-size: 8.5pt;
            font-weight: bold;
            color: #111111;
            padding-right: 20px;
            padding-top: 6px;
            line-height: 1.4;
        }

        .totals-table {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #d1d5db;
            margin-bottom: 0;
        }

        .totals-table td {
            padding: 6px 10px;
            font-size: 8.5pt;
            border-bottom: 1px solid #e5e7eb;
        }

        .totals-lbl {
            font-weight: bold;
            color: #111111;
            text-transform: uppercase;
            width: 50%;
        }

        .totals-val {
            text-align: right;
            font-weight: bold;
            color: #111111;
            width: 50%;
        }

        .totals-table tr:last-child td {
            border-bottom: none;
            background-color: #f9fafb;
        }

        .author-sign {
            margin-bottom: 15px;
            font-size: 8.5pt;
            color: #222222;
            line-height: 1.4;
        }

        .bloque-final {
            margin-top: 20px;
            border: 1px solid #d1d5db;
            border-radius: 4px;
            background-color: #fafafa;
            page-break-inside: avoid;
        }

        .encabezado-seccion-final {
            background-color: #0076a8;
            color: #ffffff;
            font-weight: bold;
            font-size: 8.5pt;
            text-transform: uppercase;
            padding: 8px 12px;
            letter-spacing: 0.5px;
            border-top-left-radius: 3px;
            border-top-right-radius: 3px;
        }

        .contenido-bloque-final {
            padding: 12px 15px;
            font-size: 8pt;
            line-height: 1.5;
            color: #222222;
        }
    </style>
</head>
<body>

    @php
        $config = $configuracion ?? \App\Models\Configuracion::where('activo', true)->first()
            ?? new \App\Models\Configuracion([
                'nombre' => 'JHARDSYSTEX',
                'razon_social' => 'JHARDSYSTEX S.A.C.',
                'ruc' => '20601245781',
                'direccion' => 'Av. Principal N° 450 Int. 302, Lima',
                'telefono' => '(+51) 987 654 321 - (01) 748-2300',
                'email' => 'compras@jhardsystex.com | info@jhardsystex.com',
                'sitio_web' => 'www.jhardsystex.com',
            ]);

        $logoPath = public_path('imagenes/logo-jhardsystex.jpeg');
        $logoBase64 = file_exists($logoPath)
            ? 'data:image/jpeg;base64,' . base64_encode(file_get_contents($logoPath))
            : null;

        $calcSubtotal = $detalles->sum(fn($d) => $d->cantidad * ($d->precio_compra_unit ?? $d->precio_compra ?? 0));
        $subtotal = (float) ($orden_compra->subtotal > 0 ? $orden_compra->subtotal : $calcSubtotal);
        $igv = (float) ($orden_compra->igv > 0 ? $orden_compra->igv : round($subtotal * 0.18, 2));
        $total = (float) ($orden_compra->total > 0 ? $orden_compra->total : round($subtotal + $igv, 2));
        $montoEnLetras = \App\Support\NumeroALetras::convertir((float) $total, 'Soles');
        $codigoCompleto = $orden_compra->codigo_formateado;
    @endphp

    <div class="quotation-page">
        
        {{-- 1. Encabezado Superior --}}
        <table class="header-table" cellpadding="0" cellspacing="0">
            <tr>
                <td class="logo-box">
                    @if($logoBase64)
                        <img src="{{ $logoBase64 }}" alt="Logo">
                    @else
                        <div style="font-size:14pt; font-weight:bold; color:#0076a8;">{{ $config->nombre ?: 'JHARDSYSTEX' }}</div>
                    @endif
                </td>

                <td class="company-info-box">
                    <div class="company-name-bold">{{ $config->razon_social ?: 'JHARDSYSTEX S.A.C.' }}</div>
                    <div>Ruc: {{ $config->ruc ?: '20601245781' }}</div>
                    <div>{{ $config->direccion ?: 'Av. Principal N° 450 Int. 302, Lima' }}</div>
                    <div>Teléfono: {{ $config->telefono ?: '(+51) 987 654 321 - (01) 748-2300' }}</div>
                    <div>E-mail: {{ $config->email ?: 'compras@jhardsystex.com' }}</div>
                    <div>Web: {{ $config->sitio_web ?: 'www.jhardsystex.com' }}</div>
                </td>

                <td class="ruc-col">
                    <div class="ruc-table-box">
                        <div class="ruc-header-line">RUC: {{ $config->ruc ?: '20601245781' }}</div>
                        <div class="ruc-type-line">ORDEN DE COMPRA</div>
                        <div class="ruc-code-line">{{ $codigoCompleto }}</div>
                    </div>
                </td>
            </tr>
        </table>

        {{-- 2. Bloque de Datos del Proveedor --}}
        <div class="client-box">
            <table class="client-table" cellpadding="3" cellspacing="0">
                <tr>
                    <td class="c-label">F. EMISIÓN</td>
                    <td class="c-value">: {{ $orden_compra->fecha ? $orden_compra->fecha->format('d/m/Y') : date('d/m/Y') }}</td>
                    <td class="c-label">FORMA DE PAGO</td>
                    <td class="c-value">: {{ $orden_compra->forma_pago ?: '30 días crédito / Factura' }}</td>
                </tr>
                <tr>
                    <td class="c-label">PROVEEDOR</td>
                    <td class="c-value" style="font-weight:bold;">: {{ $orden_compra->proveedor->razon_social ?: $orden_compra->proveedor->nombre }}</td>
                    <td class="c-label">MONEDA</td>
                    <td class="c-value">: Soles (S/)</td>
                </tr>
                <tr>
                    <td class="c-label">RUC / DOC</td>
                    <td class="c-value">: {{ $orden_compra->proveedor->ruc ?: '—' }}</td>
                    <td class="c-label">PLAZO ENTREGA</td>
                    <td class="c-value">: {{ $orden_compra->plazo_entrega ?: '3 a 5 días hábiles' }}</td>
                </tr>
                <tr>
                    <td class="c-label">DIRECCIÓN PROV.</td>
                    <td class="c-value">: {{ $orden_compra->proveedor->direccion ?: '—' }}</td>
                    <td class="c-label">LUGAR ENTREGA</td>
                    <td class="c-value">: {{ $orden_compra->lugar_entrega ?: 'Almacén Central JHARDSYSTEX' }}</td>
                </tr>
                <tr>
                    <td class="c-label">CONTACTO / TEL</td>
                    <td class="c-value">: {{ $orden_compra->proveedor->contacto ?: '—' }} ({{ $orden_compra->proveedor->telefono ?: '—' }})</td>
                    <td class="c-label">SOLICITANTE</td>
                    <td class="c-value">: {{ $orden_compra->usuario->name ?? 'Área de Compras & Logística' }}</td>
                </tr>
                <tr>
                    <td class="c-label">EMAIL PROVEEDOR</td>
                    <td class="c-value">: {{ $orden_compra->proveedor->email ?: '—' }}</td>
                    <td class="c-label">OBSERVACIONES</td>
                    <td class="c-value">: {{ $orden_compra->observaciones ?: '—' }}</td>
                </tr>
            </table>
        </div>

        {{-- 3. Tabla de Ítems / Productos a Comprar --}}
        <table class="items-table" cellpadding="8" cellspacing="0">
            <thead>
                <tr>
                    <th class="th-num text-center">#</th>
                    <th class="th-cod text-center">CÓDIGO / SKU</th>
                    <th class="th-cant text-right">CANT.</th>
                    <th class="th-um text-center">U.M.</th>
                    <th class="th-desc">DESCRIPCIÓN DEL PRODUCTO / BIEN</th>
                    <th class="th-punit text-right">P. COMPRA</th>
                    <th class="th-imp text-right">SUBTOTAL</th>
                </tr>
            </thead>
            <tbody>
                @forelse($detalles as $idx => $item)
                    <tr>
                        <td class="th-num text-center">{{ $idx + 1 }}</td>
                        <td class="th-cod text-center">{{ $item->producto_sku ?? ('SKU-' . $item->producto_id) }}</td>
                        <td class="th-cant text-right">{{ number_format($item->cantidad, 2) }}</td>
                        <td class="th-um text-center">{{ $item->unidad_medida ?: 'UND' }}</td>
                        <td class="th-desc">
                            <span class="item-title">{{ $item->producto_nombre }}</span>
                        </td>
                        <td class="th-punit text-right">{{ number_format($item->precio_compra_unit ?? $item->precio_compra ?? 0, 2) }}</td>
                        <td class="th-imp text-right" style="font-weight:bold;">{{ number_format($item->cantidad * ($item->precio_compra_unit ?? $item->precio_compra ?? 0), 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" style="text-align:center; padding:15px; color:#666;">No hay ítems registrados en esta orden.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        {{-- 4. Totales y Son Letras --}}
        <table class="summary-table" cellpadding="0" cellspacing="0">
            <tr>
                <td class="son-letras">
                    SON: {{ $montoEnLetras }}
                </td>
                <td style="width: 35%;">
                    <table class="totals-table" cellpadding="6" cellspacing="0">
                        <tr>
                            <td class="totals-lbl">SUBTOTAL</td>
                            <td class="totals-val">S/ {{ number_format($subtotal, 2) }}</td>
                        </tr>
                        <tr>
                            <td class="totals-lbl">IGV (18%)</td>
                            <td class="totals-val">S/ {{ number_format($igv, 2) }}</td>
                        </tr>
                        <tr>
                            <td class="totals-lbl">TOTAL ORDEN</td>
                            <td class="totals-val">S/ {{ number_format($total, 2) }}</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>

        {{-- 5. Firma --}}
        <div class="author-sign">
            <div>Aprobado y emitido por:</div>
            <div style="font-weight:bold; margin-top:2px;">{{ $orden_compra->usuario->name ?? 'Área de Logística y Compras' }}</div>
            <div>{{ $config->razon_social ?: 'JHARDSYSTEX S.A.C.' }}</div>
        </div>

        {{-- 6. Bloque de Instrucciones al Proveedor --}}
        <div class="bloque-final">
            <div class="encabezado-seccion-final">
                INSTRUCCIONES DE DESPACHO Y FACTURACIÓN AL PROVEEDOR
            </div>
            <div class="contenido-bloque-final">
                • Es obligatorio adjuntar copia física o referencia de esta <strong>ORDEN DE COMPRA ({{ $codigoCompleto }})</strong> en la Guía de Remisión y Factura.<br>
                • Enviar facturación electrónica (XML y PDF) a: <strong>{{ $config->email ?: 'facturacion@jhardsystex.com' }}</strong>.<br>
                • La recepción de mercadería se realizará en horario de almacén previa validación de cantidades y especificaciones técnicas.
            </div>
        </div>

    </div>

</body>
</html>
