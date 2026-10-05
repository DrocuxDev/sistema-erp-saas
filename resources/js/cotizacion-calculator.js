export const aCentimos = (value) => {
    const decimal = String(value ?? '').trim() || '0';
    const match = decimal.match(/^(\d+)(?:\.(\d{1,2}))?$/);

    if (!match) return 0n;

    return BigInt(match[1]) * 100n + BigInt((match[2] ?? '').padEnd(2, '0'));
};

const dividirRedondeando = (numerador, denominador) =>
    (numerador + denominador / 2n) / denominador;

export const importeLineaCentimos = (cantidad, precioUnitario) =>
    dividirRedondeando(aCentimos(cantidad) * aCentimos(precioUnitario), 100n);

export const formatearCentimos = (centimos) => {
    const signo = centimos < 0n ? '-' : '';
    const absoluto = centimos < 0n ? -centimos : centimos;

    return `${signo}${absoluto / 100n}.${String(absoluto % 100n).padStart(2, '0')}`;
};

const porcentajeCentimos = (importeCentimos, tasaCentimos) =>
    (importeCentimos / 10000n) * tasaCentimos
    + dividirRedondeando((importeCentimos % 10000n) * tasaCentimos, 10000n);

export const calcularResumenCotizacion = ({
    lineas,
    pctIgv = '18',
    pctRetencion = '3',
    pctComisionVenta = '5',
    pctComisionGanancia = '10',
    precioIncluyeIgv = false,
    noUtilizable = '0',
}) => {
    const tasaIgvCentimos = aCentimos(pctIgv);
    const tasaRetencionCentimos = aCentimos(pctRetencion);
    const tasaComisionVentaCentimos = aCentimos(pctComisionVenta);
    const tasaComisionGananciaCentimos = aCentimos(pctComisionGanancia);
    let importeCentimos = 0n;
    let compraActivaCentimos = 0n;
    let compraFijaCentimos = aCentimos(noUtilizable);

    for (const linea of lineas) {
        const importeVenta = importeLineaCentimos(linea.cantidad, linea.precio_venta_unit);
        const importeCompra = importeLineaCentimos(linea.cantidad, linea.precio_compra_unit);
        const igvCompraCentimos = aCentimos(linea.igv_compra);

        importeCentimos += importeVenta;
        compraActivaCentimos += importeCompra;
        compraFijaCentimos += importeCompra + igvCompraCentimos;
    }

    let subtotalCentimos;
    let igvCentimos;
    let totalCentimos;

    if (precioIncluyeIgv) {
        const denominador = 10000n + tasaIgvCentimos;
        subtotalCentimos = (importeCentimos / denominador) * 10000n
            + dividirRedondeando((importeCentimos % denominador) * 10000n, denominador);
        totalCentimos = importeCentimos;
        igvCentimos = totalCentimos - subtotalCentimos;
    } else {
        subtotalCentimos = importeCentimos;
        igvCentimos = porcentajeCentimos(subtotalCentimos, tasaIgvCentimos);
        totalCentimos = subtotalCentimos + igvCentimos;
    }

    const retencionCentimos = porcentajeCentimos(subtotalCentimos, tasaRetencionCentimos);
    const gananciaCentimos = subtotalCentimos - compraFijaCentimos;
    const comisionVentaCentimos = porcentajeCentimos(subtotalCentimos, tasaComisionVentaCentimos);
    const comisionGananciaCentimos = porcentajeCentimos(
        gananciaCentimos > 0n ? gananciaCentimos : 0n,
        tasaComisionGananciaCentimos,
    );

    return {
        subtotal: formatearCentimos(subtotalCentimos),
        igv: formatearCentimos(igvCentimos),
        retencion: formatearCentimos(retencionCentimos),
        totalFacturar: formatearCentimos(totalCentimos - retencionCentimos),
        compraActiva: formatearCentimos(compraActivaCentimos),
        compraFija: formatearCentimos(compraFijaCentimos),
        ganancia: formatearCentimos(gananciaCentimos),
        comisionVenta: formatearCentimos(comisionVentaCentimos),
        comisionGanancia: formatearCentimos(comisionGananciaCentimos),
    };
};
