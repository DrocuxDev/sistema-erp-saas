<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Cotización {{ $cotizacion->codigo_formateado }}</title>
    <style>
        /* 1. MÁRGENES DE PÁGINA Y REGLAS GENERALES */
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

        .page-break {
            page-break-after: always;
            break-after: page;
        }

        /* -------------------------------------------------------------------------
           PÁGINA 1: HOJA EN BLANCO (RESERVADA PARA LA PORTADA CORPORATIVA)
           ------------------------------------------------------------------------- */
        .blank-first-page {
            width: 100%;
            height: 260mm;
            background: #ffffff;
            position: relative;
        }

        /* -------------------------------------------------------------------------
           PÁGINA 2: COTIZACIÓN CORPORATIVA BIEN AIREADA
           ------------------------------------------------------------------------- */
        .quotation-page {
            width: 100%;
            background: #ffffff;
        }

        /* 1. Encabezado Superior (3 Bloques) */
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

        /* Recuadro Oficial RUC + COTIZACIÓN + Código */
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

        .ruc-version-line {
            font-size: 7.5pt;
            font-weight: bold;
            color: #555555;
            margin-top: 3px;
        }

        /* 2. Bloque Datos del Cliente (Recuadro 2 Columnas) */
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
            width: 110px;
            white-space: nowrap;
        }

        .c-value {
            color: #222222;
        }

        /* 3. Tabla de Ítems / Presupuesto */
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
        .th-cod { width: 12%; text-align: center; }
        .th-cant { width: 8%; text-align: right; }
        .th-um { width: 7%; text-align: center; }
        .th-desc { width: 46%; }
        .th-punit { width: 11%; text-align: right; }
        .th-imp { width: 11%; text-align: right; }

        .item-title {
            font-weight: bold;
            color: #111111;
            display: block;
            text-transform: uppercase;
            line-height: 1.3;
        }

        .item-specs {
            font-size: 7.5pt;
            color: #4b5563;
            margin-top: 3px;
            line-height: 1.3;
        }

        /* 4. Resumen y Monto en Letras */
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

        /* 5. Bloque de Firma */
        .author-sign {
            margin-bottom: 15px;
            font-size: 8.5pt;
            color: #222222;
            line-height: 1.4;
        }

        /* 6. ENCABEZADO FINAL (BLOQUE ESTRUCTURADO DE PAGOS Y CONDICIONES) */
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

        .contenido-bloque-final p {
            margin: 4px 0;
        }
    </style>
</head>
<body>

    @php
        // 1. Obtener Configuración General de la Empresa
        $config = \App\Models\Configuracion::where('activo', true)->first() 
            ?? \App\Models\Configuracion::first()
            ?? new \App\Models\Configuracion([
                'nombre' => 'JHARDSYSTEX',
                'razon_social' => 'JHARDSYSTEX S.A.C.',
                'ruc' => '20601245781',
                'direccion' => 'Av. Principal N° 450 Int. 302, Lima',
                'telefono' => '(+51) 987 654 321 - (01) 748-2300',
                'email' => 'ventas@jhardsystex.com | info@jhardsystex.com',
                'sitio_web' => 'www.jhardsystex.com',
            ]);

        // 2. Cargar Logo si existe en el servidor en base64 para DomPDF
        $logoPath = public_path('imagenes/logo-jhardsystex.jpeg');
        $logoBase64 = file_exists($logoPath)
            ? 'data:image/jpeg;base64,' . base64_encode(file_get_contents($logoPath))
            : null;

        $subtotal = $cotizacion->resumen->subtotal ?? 0;
        $pctIgv = $config->pct_igv ?? 18;
        $igv = $cotizacion->resumen->igv ?? ($subtotal * ($pctIgv / 100));
        $total = $cotizacion->resumen->total_facturar ?? ($subtotal + $igv);
        $montoEnLetras = \App\Support\NumeroALetras::convertir((float) $total, 'Soles');

        // Código Comercial Oficial
        $codigoCompleto = $cotizacion->codigo_formateado ?: (($cotizacion->serie ?: 'C001') . '-' . ($cotizacion->correlativo ?? 0));
    @endphp

    <!-- ========================================================================= -->
    <!-- PÁGINA 1: HOJA EN BLANCO (RESERVADA PARA LA PORTADA CORPORATIVA)         -->
    <!-- ========================================================================= -->
    <div class="blank-first-page page-break">
        <!-- Hoja 1 en blanco para posterior importación de portada -->
    </div>

    <!-- ========================================================================= -->
    <!-- PÁGINA 2: COTIZACIÓN CORPORATIVA CON ESTRUCTURA LIMPIA Y BIEN AIREADA     -->
    <!-- ========================================================================= -->
    <div class="quotation-page">
        
        {{-- 1. Encabezado Superior (3 Bloques) --}}
        <table class="header-table" cellpadding="0" cellspacing="0">
            <tr>
                {{-- Logo Izquierda --}}
                <td class="logo-box">
                    @if($logoBase64)
                        <img src="{{ $logoBase64 }}" alt="Logo">
                    @else
                        <div style="font-size:14pt; font-weight:bold; color:#0076a8;">{{ $config->nombre ?: 'JHARDSYSTEX' }}</div>
                    @endif
                </td>

                {{-- Datos de la Empresa Emisora --}}
                <td class="company-info-box">
                    <div class="company-name-bold">{{ $config->razon_social ?: 'JHARDSYSTEX S.A.C.' }}</div>
                    <div>Ruc: {{ $config->ruc ?: '20601245781' }}</div>
                    <div>{{ $config->direccion ?: 'Av. Principal N° 450 Int. 302, Lima' }}</div>
                    <div>Teléfono: {{ $config->telefono ?: '(+51) 987 654 321 - (01) 748-2300' }}</div>
                    <div>E-mail: {{ $config->email ?: 'ventas@jhardsystex.com | info@jhardsystex.com' }}</div>
                    <div>Web: {{ $config->sitio_web ?: 'www.jhardsystex.com' }}</div>
                </td>

                {{-- Recuadro Oficial RUC + COTIZACIÓN + Código --}}
                <td class="ruc-col">
                    <div class="ruc-table-box">
                        <div class="ruc-header-line">RUC: {{ $config->ruc ?: '20601245781' }}</div>
                        <div class="ruc-type-line">COTIZACIÓN</div>
                        <div class="ruc-code-line">{{ $codigoCompleto }}</div>
                        <div class="ruc-version-line">VERSION: {{ $cotizacion->version_formateada }} [{{ strtoupper($cotizacion->tipo_cotizacion_label) }}]</div>
                    </div>
                </td>
            </tr>
        </table>

        {{-- 2. Bloque de Datos del Cliente (Recuadro 2 Columnas) --}}
        <div class="client-box">
            <table class="client-table" cellpadding="3" cellspacing="0">
                <tr>
                    <td class="c-label">F. EMISIÓN</td>
                    <td class="c-value">: {{ $cotizacion->fecha ? $cotizacion->fecha->format('d/m/Y') : date('d/m/Y') }}</td>
                    <td class="c-label" style="width:115px;">FECHA VALIDEZ</td>
                    <td class="c-value">: {{ $cotizacion->validez_oferta ?: ($config->validez_oferta_default ?: '15 días calendario') }}</td>
                </tr>
                <tr>
                    <td class="c-label">CLIENTE</td>
                    <td class="c-value" style="font-weight:bold;">: {{ $cotizacion->cliente->razon_social ?: $cotizacion->cliente->nombre }}</td>
                    <td class="c-label">MONEDA</td>
                    <td class="c-value">: Soles (S/)</td>
                </tr>
                <tr>
                    <td class="c-label">RUC/DNI/OTRO</td>
                    <td class="c-value">: {{ $cotizacion->cliente->ruc ?: '—' }}</td>
                    <td class="c-label">CONDICIÓN PAGO</td>
                    <td class="c-value">: {{ $cotizacion->forma_pago ?: ($config->forma_pago_default ?: 'Contado') }}</td>
                </tr>
                <tr>
                    <td class="c-label">ATENCIÓN</td>
                    <td class="c-value">: {{ $cotizacion->representante ?: ($cotizacion->cliente->contacto ?: '—') }}</td>
                    <td class="c-label">PLAZO ENTREGA</td>
                    <td class="c-value">: {{ $cotizacion->plazo_entrega ?: ($config->plazo_entrega_default ?: '2 a 5 días hábiles') }}</td>
                </tr>
                <tr>
                    <td class="c-label">DIRECCIÓN</td>
                    <td class="c-value">: {{ $cotizacion->cliente->direccion ?: '—' }}</td>
                    <td class="c-label">LOGÍSTICA / FLETE</td>
                    <td class="c-value">: {{ $cotizacion->logistica ?: ($config->logistica_default ?: 'Lima Metropolitana') }}</td>
                </tr>
                <tr>
                    <td class="c-label">TELÉF.</td>
                    <td class="c-value">: {{ $cotizacion->cliente->telefono ?: '—' }}</td>
                    <td class="c-label">PROYECTO / ASUNTO</td>
                    <td class="c-value" style="font-weight:bold; color:#0076a8;">: {{ $cotizacion->asunto ?: 'COTIZACIÓN COMERCIAL' }}</td>
                </tr>
                <tr>
                    <td class="c-label">EMAIL</td>
                    <td class="c-value">: {{ $cotizacion->cliente->email ?: '—' }}</td>
                    <td class="c-label">OBSERVACIÓN</td>
                    <td class="c-value">: {{ $cotizacion->observaciones ?: '—' }}</td>
                </tr>
            </table>
        </div>

        {{-- 3. Tabla de Ítems / Presupuesto --}}
        <table class="items-table" cellpadding="8" cellspacing="0">
            <thead>
                <tr>
                    <th class="th-num text-center">#</th>
                    <th class="th-cod text-center">CÓDIGO</th>
                    <th class="th-cant text-right">CANT.</th>
                    <th class="th-um text-center">U.M.</th>
                    <th class="th-desc">DESCRIPCIÓN</th>
                    <th class="th-punit text-right">P.UNIT</th>
                    <th class="th-imp text-right">IMPORTE</th>
                </tr>
            </thead>
            <tbody>
                @forelse($cotizacion->detalles as $idx => $item)
                    <tr>
                        <td class="th-num text-center">{{ $idx + 1 }}</td>
                        <td class="th-cod text-center">{{ $item->producto->sku ?? str_pad($item->producto_id, 3, '0', STR_PAD_LEFT) }}</td>
                        <td class="th-cant text-right">{{ number_format($item->cantidad, 2) }}</td>
                        <td class="th-um text-center">{{ $item->unidad_medida ?: ($item->producto->unidad_medida ?? 'UND') }}</td>
                        <td class="th-desc">
                            <span class="item-title">{{ $item->producto->nombre ?? $item->descripcion }}</span>
                            @if(!empty($item->descripcion) && $item->descripcion !== ($item->producto->nombre ?? ''))
                                <div class="item-specs">{!! nl2br(e($item->descripcion)) !!}</div>
                            @endif
                        </td>
                        <td class="th-punit text-right">{{ number_format($item->precio_venta_unit, 2) }}</td>
                        <td class="th-imp text-right" style="font-weight:bold;">{{ number_format($item->precio_venta_total, 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" style="text-align:center; padding:15px; color:#666;">No hay ítems registrados.</td>
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
                            <td class="totals-lbl">IGV ({{ number_format($pctIgv, 0) }}%)</td>
                            <td class="totals-val">S/ {{ number_format($igv, 2) }}</td>
                        </tr>
                        @if(isset($cotizacion->resumen->retencion) && $cotizacion->resumen->retencion > 0)
                        <tr>
                            <td class="totals-lbl">RETENCIÓN</td>
                            <td class="totals-val">- S/ {{ number_format($cotizacion->resumen->retencion, 2) }}</td>
                        </tr>
                        @endif
                        <tr>
                            <td class="totals-lbl">TOTAL</td>
                            <td class="totals-val">S/ {{ number_format($total, 2) }}</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>

        {{-- 5. Firma --}}
        <div class="author-sign">
            <div>Atentamente,</div>
            <div style="font-weight:bold; margin-top:2px;">{{ $cotizacion->usuario->name ?? 'Área Comercial TI' }}</div>
            <div>{{ $config->razon_social ?: 'JHARDSYSTEX S.A.C.' }}</div>
        </div>

        {{-- 6. ENCABEZADO FINAL (BLOQUE ESTRUCTURADO DE PAGOS Y CONDICIONES) --}}
        <div class="bloque-final">
            <div class="encabezado-seccion-final">
                INFORMACIÓN DE PAGO BANCARIO Y CONDICIONES
            </div>
            <div class="contenido-bloque-final">
                <strong>FORMAS Y MEDIOS DE PAGO</strong><br>
                <strong>DEPÓSITO O TRANSFERENCIA BANCARIA:</strong><br>
                {!! nl2br(e($config->cuentas_bancarias ?: "• BCP Soles: 193-98765432-0-11 | CCI: 002-193-009876543201-14\n• BBVA Soles: 0011-0123-0100045678 | CCI: 011-123-000100045678-75\n• Titular: " . ($config->razon_social ?: 'JHARDSYSTEX S.A.C.'))) !!}
            </div>
        </div>

    </div>

</body>
</html>