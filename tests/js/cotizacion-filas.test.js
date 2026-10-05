import test from 'node:test';
import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';
import vm from 'node:vm';

// Ejecuta los handlers reales de ambas vistas con un DOM mínimo de dos filas.
for (const vista of ['create', 'edit']) {
    test(`${vista}: quitar una fila vacía permite guardar la fila válida restante`, () => {
        const source = readFileSync(new URL(`../../resources/views/cotizaciones/${vista}.blade.php`, import.meta.url), 'utf8');
        const quitar = source.match(/tr\.querySelector\('\.btn-quitar-linea'\)\.addEventListener\('click', \(\) => \{([\s\S]*?)\n            \}\);/)[1];
        const validar = source.slice(source.indexOf('function validarFormularioLineas()'), source.indexOf('function recalcularTotales()'));
        const classList = { add() {}, remove() {}, toggle() {} };
        const filaValida = { querySelector: () => ({ value: '1', classList }) };
        let filas = [filaValida, {}];
        const comboApi = { validarFila: () => false };
        const context = vm.createContext({
            comboApi,
            comboApis: [{ validarFila: () => true }, comboApi],
            tr: { remove: () => { filas = [filaValida]; } },
            document: {
                querySelectorAll: () => filas,
                getElementById: () => ({ classList }),
            },
            recalcularTotales() {},
            validarDuplicados: () => true,
        });
        vm.runInContext(quitar, context);
        assert.equal(filas.length, 1);
        vm.runInContext(validar, context);
        assert.equal(vm.runInContext('validarFormularioLineas()', context), true);
    });
}
