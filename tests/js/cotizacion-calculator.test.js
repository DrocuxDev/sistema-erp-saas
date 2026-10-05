import test from 'node:test';
import assert from 'node:assert/strict';
import { calcularResumenCotizacion, importeLineaCentimos } from '../../resources/js/cotizacion-calculator.js';

test('calcula neto, costos y comisiones con redondeo en centimos', () => {
    const resumen = calcularResumenCotizacion({
        lineas: [{ cantidad: '3', precio_venta_unit: '0.10', precio_compra_unit: '0.01', igv_compra: '0.01' }],
        pctIgv: '18',
        pctRetencion: '10',
        pctComisionVenta: '10',
        pctComisionGanancia: '10',
        noUtilizable: '0.01',
    });

    assert.deepEqual(resumen, {
        subtotal: '0.30',
        igv: '0.05',
        retencion: '0.03',
        totalFacturar: '0.32',
        compraActiva: '0.03',
        compraFija: '0.05',
        ganancia: '0.25',
        comisionVenta: '0.03',
        comisionGanancia: '0.03',
    });
});

test('desglosa correctamente un precio que incluye IGV', () => {
    const resumen = calcularResumenCotizacion({
        lineas: [{ cantidad: '1', precio_venta_unit: '118.00', precio_compra_unit: '50.00', igv_compra: '0' }],
        pctIgv: '18',
        pctRetencion: '0',
        precioIncluyeIgv: true,
    });

    assert.equal(resumen.subtotal, '100.00');
    assert.equal(resumen.igv, '18.00');
    assert.equal(resumen.totalFacturar, '118.00');
});

test('mantiene exactitud dentro de los limites monetarios permitidos', () => {
    assert.equal(importeLineaCentimos('1000000', '1000000'), 100000000000000n);
});
