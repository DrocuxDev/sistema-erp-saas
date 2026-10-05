<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Cotización {{ $codigoCompleto }}</title>
    <style>
        /* A4, márgenes 15-20 mm, sans-serif, base 10-11 pt. Colores por variables.
           Solo tablas HTML y CSS 2.1 (sin flex ni grid). */
        @page {
            size: A4 portrait;
            margin: 18mm 15mm 18mm 15mm;
        }

        *, *::before, *::after {
            box-sizing: border-box;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 10pt;
            line-height: 1.4;
            color: #222222;
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

        .no-romper {
            page-break-inside: avoid;
        }

        /* Documento: la franja y la caja del título van en el thead para que
           se repitan en cada página cuando la cotización pasa de una. */
        .doc {
            width: 100%;
            border-collapse: collapse;
        }

        .doc td {
            padding: 0;
        }

        /* 1. PORTADA (página A4 completa con la imagen de configuración) */
        .portada {
            width: 100%;
            text-align: center;
        }

        .portada img {
            width: 100%;
            height: 257mm;
            display: block;
        }

        /* 2. ENCABEZADO: franja PNG (degradado principal → turquesa con
           corte diagonal izquierdo) + caja del título de 4 celdas. */
        .franja {
            margin-bottom: 8px;
        }

        .franja img {
            width: 100%;
            display: block;
        }

        .caja-cabecera {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
        }

        .caja-cabecera td {
            border: 1.5px solid {{ $doc['color_principal'] }};
            text-align: center;
            vertical-align: middle;
            padding: 6px 4px;
        }

        .caja-titulo {
            font-size: 14pt;
            font-weight: bold;
            letter-spacing: 1px;
        }

        .caja-numero {
            font-size: 11pt;
            font-weight: bold;
            white-space: nowrap;
        }

        .caja-version {
            font-size: 8pt;
            font-weight: bold;
            color: #555555;
        }

        .caja-tipo {
            font-size: 7.5pt;
            color: #555555;
            white-space: nowrap;
        }

        .caja-logo img {
            max-height: 18mm;
            max-width: 100%;
        }

        /* 3. DATOS DEL CLIENTE */
        .cliente-linea {
            font-weight: bold;
            font-size: 10pt;
            margin: 2px 0;
        }

        .cliente-datos {
            width: 100%;
            border-collapse: collapse;
            margin: 6px 0 3px 0;
        }

        .cliente-datos td {
            padding: 2px 6px 2px 0;
            font-size: 9pt;
            vertical-align: top;
        }

        .asunto {
            font-weight: bold;
            font-size: 10.5pt;
            margin: 4px 0 6px 0;
        }

        /* 4. SALUDO */
        .saludo {
            text-align: justify;
            margin: 0 0 8px 0;
        }

        /* 5. PRESUPUESTO */
        .titulo-presupuesto {
            font-weight: bold;
            text-decoration: underline;
            font-size: 11pt;
            margin: 0 0 4px 0;
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 0;
            border: 1px solid #9ca3af;
        }

        .items-table thead th {
            background-color: {{ $doc['color_tabla_encabezado'] }};
            color: #ffffff;
            font-size: 8.5pt;
            font-weight: bold;
            text-transform: uppercase;
            padding: 6px 5px;
            border: 1px solid {{ $doc['color_tabla_encabezado'] }};
            letter-spacing: 0.3px;
        }

        .items-table tbody td {
            padding: 5px;
            font-size: 9pt;
            vertical-align: top;
            border: 1px solid #9ca3af;
            line-height: 1.35;
        }

        .items-table tbody tr {
            page-break-inside: avoid;
        }

        .col-item { width: 6%; text-align: center; }
        .col-codigo { width: 16%; text-align: center; font-size: 8pt; white-space: nowrap; }
        .col-detalles { width: 38%; }
        .col-cant { width: 7%; text-align: center; }
        .col-um { width: 7%; text-align: center; }
        .col-punit { width: 13%; }
        .col-subtotal { width: 13%; }

        .detalle-nombre {
            font-weight: bold;
            display: block;
        }

        .detalle-extra {
            font-size: 8pt;
            color: #6b7280;
            margin-top: 2px;
        }

        .monto-tabla {
            width: 100%;
            border-collapse: collapse;
        }

        .monto-tabla td {
            border: none !important;
            padding: 0 !important;
        }

        .monto-simbolo { text-align: left; }
        .monto-valor { text-align: right; }

        /* 6. SON + TOTALES (fila nueva debajo de la tabla) */
        .son-totales {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
            margin-bottom: 10px;
        }

        .son-totales td {
            padding: 0;
        }

        .son-letras {
            width: 55%;
            font-size: 9pt;
            font-weight: bold;
            padding-right: 14px;
        }

        .totales-tabla {
            width: 100%;
            border-collapse: collapse;
            background-color: {{ $doc['color_principal'] }};
            color: #ffffff;
        }

        .totales-tabla td {
            padding: 6px 10px;
            font-size: 9.5pt;
            font-weight: bold;
            text-transform: uppercase;
        }

        .totales-tabla .t-val {
            text-align: right;
        }

        .totales-tabla .t-total td {
            font-size: 12pt;
        }

        /* 7. TÉRMINOS */
        .titulo-terminos {
            text-align: center;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 11pt;
            margin: 0 0 6px 0;
        }

        .terminos {
            text-align: justify;
            margin-bottom: 10px;
        }

        .terminos p {
            margin: 3px 0;
        }

        /* 8. CIERRE (firma + cuentas lado a lado) */
        .cierre-tabla {
            width: 100%;
            border-collapse: collapse;
        }

        .cierre-tabla td {
            vertical-align: top;
            padding: 0;
        }

        .cierre-firma {
            width: 40%;
            padding-right: 12px !important;
            text-align: justify;
        }

        .espacio-firma {
            height: 25mm;
        }

        .cierre-solo {
            text-align: justify;
        }

        .caja-pago {
            border: 1px solid #9ca3af;
        }

        .caja-pago-titulo {
            background-color: {{ $doc['color_principal'] }};
            color: #ffffff;
            font-weight: bold;
            font-size: 9.5pt;
            text-transform: uppercase;
            padding: 7px 10px;
            letter-spacing: 0.3px;
        }

        .caja-pago-cuerpo {
            padding: 8px 10px;
            font-size: 9pt;
            line-height: 1.5;
        }

        .caja-pago-cuerpo p {
            margin: 3px 0;
        }
    </style>
</head>
<body>

    @if($conPortada)
        <!-- 1. PORTADA (página A4 completa, misma imagen para todas las cotizaciones) -->
        <div class="portada page-break">
            <img src="{{ $portadaBase64 }}" alt="Portada">
        </div>
    @endif

    <table class="doc" cellpadding="0" cellspacing="0">
        <thead>
            <tr>
                <td>
                    <!-- 2. ENCABEZADO (se repite en cada página) -->
                    <div class="franja"><img src="{{ $franjaBase64 }}" alt=""></div>

                    <table class="caja-cabecera" cellpadding="0" cellspacing="0">
                        <tr>
                            <td style="width:25%;"><div class="caja-titulo">COTIZACIÓN</div></td>
                            <td style="width:25%;"><div class="caja-numero">N° {{ $codigoCompleto }}</div></td>
                            <td style="width:30%;">
                                <div class="caja-version">VERSION: {{ $cotizacion->version_formateada }}</div>
                                <div class="caja-tipo">{{ strtoupper($cotizacion->tipo_cotizacion_label) }}</div>
                            </td>
                            <td style="width:20%;">
                                @if($logoBase64)
                                    <img src="{{ $logoBase64 }}" alt="Logo" style="max-height:18mm;max-width:100%;">
                                @endif
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>
                    <!-- 3. DATOS DEL CLIENTE -->
                    <div class="cliente-linea">ATENCIÓN: {{ $cotizacion->representante ?: ($cotizacion->cliente->contacto ?? '—') }}</div>
                    <div class="cliente-linea">DE: {{ $cotizacion->cliente->razon_social ?: $cotizacion->cliente->nombre }}</div>
                    <div class="cliente-linea">RUC/DNI: {{ $cotizacion->cliente->ruc ?: '—' }}</div>

                    <table class="cliente-datos" cellpadding="0" cellspacing="0">
                        <tr>
                            <td style="width:50%;"><strong>F. Emisión:</strong> {{ $cotizacion->fecha ? $cotizacion->fecha->format('d/m/Y') : date('d/m/Y') }}</td>
                            <td style="width:50%;"><strong>Validez:</strong> {{ $cotizacion->validez_oferta ?: '—' }}</td>
                        </tr>
                        <tr>
                            <td><strong>Moneda:</strong> Soles (S/)</td>
                            <td><strong>Teléfono:</strong> {{ $cotizacion->cliente->telefono ?: '—' }}</td>
                        </tr>
                        <tr>
                            <td colspan="2"><strong>Email:</strong> {{ $cotizacion->cliente->email ?: '—' }} &nbsp;&nbsp; <strong>Dirección:</strong> {{ $cotizacion->cliente->direccion ?: '—' }}</td>
                        </tr>
                    </table>

                    <div class="asunto">ASUNTO: {{ $cotizacion->asunto ?: 'COTIZACIÓN COMERCIAL' }}</div>

                    <!-- 4. SALUDO -->
                    <p class="saludo">{!! nl2br(e($saludo)) !!}</p>

                    <!-- 5. PRESUPUESTO -->
                    <p class="titulo-presupuesto">PRESUPUESTO:</p>
                </td>
            </tr>
            <tr>
                <td>
                    <table class="items-table" cellpadding="0" cellspacing="0">
                        <thead>
                            <tr>
                                <th class="col-item">Item</th>
                                <th class="col-codigo">Código</th>
                                <th class="col-detalles">Detalles</th>
                                <th class="col-cant">Cant.</th>
                                <th class="col-um">U.M.</th>
                                <th class="col-punit">Precio unitario</th>
                                <th class="col-subtotal">Sub total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($cotizacion->detalles as $idx => $item)
                                <tr class="fila-item">
                                    <td class="col-item">{{ $idx + 1 }}</td>
                                    <td class="col-codigo">{{ $item->producto->sku ?? str_pad($item->producto_id, 3, '0', STR_PAD_LEFT) }}</td>
                                    <td class="col-detalles">
                                        <span class="detalle-nombre">{{ $item->producto->nombre ?? 'Ítem' }}</span>
                                        @if(!empty($item->descripcion) && $item->descripcion !== ($item->producto->nombre ?? ''))
                                            <div class="detalle-extra">{!! nl2br(e($item->descripcion)) !!}</div>
                                        @endif
                                    </td>
                                    <td class="col-cant">{{ number_format($item->cantidad, 2) }}</td>
                                    <td class="col-um">{{ $item->unidad_medida ?: ($item->producto->unidad_medida ?? 'UND') }}</td>
                                    <td>
                                        <table class="monto-tabla" cellpadding="0" cellspacing="0"><tr>
                                            <td class="monto-simbolo">S/</td>
                                            <td class="monto-valor">{{ number_format($item->precio_venta_unit, 2) }}</td>
                                        </tr></table>
                                    </td>
                                    <td>
                                        <table class="monto-tabla" cellpadding="0" cellspacing="0"><tr>
                                            <td class="monto-simbolo">S/</td>
                                            <td class="monto-valor"><strong>{{ number_format($item->precio_venta_total ?? ($item->cantidad * $item->precio_venta_unit), 2) }}</strong></td>
                                        </tr></table>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" style="text-align:center; padding:15px; color:#666;">No hay ítems registrados.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </td>
            </tr>
            <tr>
                <td>
                    <!-- 6. SON + TOTALES -->
                    <table class="son-totales no-romper" cellpadding="0" cellspacing="0">
                        <tr>
                            <td class="son-letras" valign="top">SON: {{ $montoEnLetras }}</td>
                            <td style="width:45%;" valign="top">
                                <table class="totales-tabla" cellpadding="0" cellspacing="0">
                                    <tr>
                                        <td>Sub total</td>
                                        <td class="t-val">S/ {{ number_format($subtotal, 2) }}</td>
                                    </tr>
                                    <tr>
                                        <td>IGV ({{ number_format($pctIgv, 0) }}%)</td>
                                        <td class="t-val">S/ {{ number_format($igv, 2) }}</td>
                                    </tr>
                                    @if($retencion > 0)
                                    <tr>
                                        <td>Retención</td>
                                        <td class="t-val">- S/ {{ number_format($retencion, 2) }}</td>
                                    </tr>
                                    @endif
                                    <tr class="t-total">
                                        <td>Total</td>
                                        <td class="t-val">S/ {{ number_format($total, 2) }}</td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr>
                <td>
                    <!-- 7. TÉRMINOS Y CONDICIONES DEL SERVICIO -->
                    <div class="terminos no-romper">
                        <p class="titulo-terminos">Términos y condiciones del servicio</p>
                        <p>• <strong>Plazo de entrega:</strong> {{ $cotizacion->plazo_entrega ?: '—' }}</p>
                        <p>• <strong>Forma de pago:</strong> {{ $cotizacion->forma_pago ?: '—' }}</p>
                        <p>• <strong>Logística/Flete:</strong> {{ $cotizacion->logistica ?: '—' }}</p>
                        <p>• <strong>Validez de la oferta:</strong> {{ $cotizacion->validez_oferta ?: '—' }}</p>
                        @if(!empty($cotizacion->observaciones))
                            <p>• <strong>Observación:</strong> {!! nl2br(e($cotizacion->observaciones)) !!}</p>
                        @endif
                        @if(!empty($cotizacion->condiciones_adicionales))
                            <p>• <strong>Condiciones adicionales:</strong> {!! nl2br(e($cotizacion->condiciones_adicionales)) !!}</p>
                        @endif
                    </div>
                </td>
            </tr>
            @php
                $tieneBanco = count($doc['cuentas'] ?? []) > 0 || !empty($doc['cuentas_bancarias_texto']);
            @endphp
            <tr>
                <td>
                    <!-- 8. CIERRE (firma + cuentas lado a lado) -->
                    @if($tieneBanco)
                        <table class="cierre-tabla no-romper" cellpadding="0" cellspacing="0">
                            <tr>
                                <td class="cierre-firma" valign="top">
                                    <p style="margin:0;">{!! nl2br(e($firma)) !!}</p>
                                    <div class="espacio-firma"></div>
                                </td>
                                <td valign="top">
                                    <div class="caja-pago">
                                        <div class="caja-pago-titulo">Información de pago bancario</div>
                                        <div class="caja-pago-cuerpo">
                                            @foreach($doc['cuentas'] as $cuenta)
                                                <p><strong>{{ $cuenta['banco'] }} {{ $cuenta['moneda'] }}:</strong> {{ $cuenta['numero_cuenta'] }}@if(!empty($cuenta['cci'])) | <strong>CCI:</strong> {{ $cuenta['cci'] }}@endif</p>
                                            @endforeach
                                            @if(empty($doc['cuentas']) && !empty($doc['cuentas_bancarias_texto']))
                                                <p>{!! nl2br(e($doc['cuentas_bancarias_texto'])) !!}</p>
                                            @endif
                                            @if(!empty($doc['titular_cuenta']))
                                                <p><strong>Titular:</strong> {{ $doc['titular_cuenta'] }}</p>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        </table>
                    @else
                        <div class="cierre-solo no-romper">
                            <p style="margin:0;">{!! nl2br(e($firma)) !!}</p>
                            <div class="espacio-firma"></div>
                        </div>
                    @endif
                </td>
            </tr>
        </tbody>
    </table>

    <!-- Sin pie de página ni numerado, por indicación del usuario. -->

</body>
</html>
