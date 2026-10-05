import Alpine from 'alpinejs';
import Chart from 'chart.js/auto';
import { aCentimos, calcularResumenCotizacion, formatearCentimos, importeLineaCentimos } from './cotizacion-calculator';

window.Alpine = Alpine;
window.Chart = Chart;
window.calcularResumenCotizacion = calcularResumenCotizacion;
window.formatearCentimos = formatearCentimos;
window.importeLineaCentimos = importeLineaCentimos;
window.aCentimos = aCentimos;

Alpine.start();

// Tabs del sistema dark (ui-tab): delegado global, sin JS por página.
// Ámbito: contenedor [data-ui-tabs] más cercano (o documento). No interfiere
// con otros sistemas de tabs (.tab-btn + showTab() de las fichas show, ni
// .tab-button legacy de clientes) porque exige la clase .ui-tab.
document.addEventListener('click', (e) => {
    const btn = e.target.closest('button.ui-tab[data-tab]');
    if (!btn) return;
    const scope = btn.closest('[data-ui-tabs]') || document;
    scope.querySelectorAll('button.ui-tab[data-tab]').forEach((b) => {
        b.classList.toggle('ui-tab-active', b === btn);
    });
    scope.querySelectorAll('.tab-content[id^="tab-"]').forEach((p) => {
        p.classList.toggle('hidden', p.id !== 'tab-' + btn.dataset.tab);
    });
});
