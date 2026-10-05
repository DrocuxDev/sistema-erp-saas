/**
 * Combobox con autocompletado para la Matriz Comercial de Cotización.
 *
 * Uso desde crearFilaLinea(tr, opts):
 *   window.CotizacionProductoCombobox.attach({
 *     container, input, hidden, idx,
 *     productos, searchUrl, csrfToken,
 *     initialId, initialNombre,
 *     onSelect(producto), onClear(), getAllSelectedIds()
 *   });
 */
(function () {
    'use strict';

    function normalizar(texto) {
        return (texto || '')
            .toString()
            .toLowerCase()
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '');
    }

    function debounce(fn, ms) {
        let t = null;
        return function (...args) {
            clearTimeout(t);
            t = setTimeout(() => fn.apply(this, args), ms);
        };
    }

    function formatoPrecio(v) {
        if (v === null || v === undefined || v === '') return '—';
        const n = Number(v);
        if (Number.isNaN(n)) return '—';
        return 'S/ ' + n.toFixed(2);
    }

    function filtrarLocal(productos, query, limit) {
        const nq = normalizar(query).trim();
        if (!nq) return (productos || []).slice(0, limit);
        return (productos || [])
            .filter((p) => normalizar((p.nombre || '') + ' ' + (p.sku || '')).includes(nq))
            .slice(0, limit);
    }

    // ---------- Dropdown global (fuera de la tabla para evitar overflow) ----------
    let dropdown = null;
    let modalEl = null;

    function getDropdown() {
        if (dropdown) return dropdown;
        dropdown = document.createElement('div');
        dropdown.id = 'combo-producto-dropdown';
        dropdown.setAttribute('role', 'listbox');
        dropdown.style.cssText = [
            'position:fixed', 'z-index:9999', 'display:none',
            'min-width:280px', 'max-width:420px',
            'max-height:240px', 'overflow-y:auto',
            'border-radius:12px', 'padding:4px',
            'box-shadow:0 12px 32px rgba(0,0,0,.45)',
        ].join(';');
        dropdown.className = 'bg-white dark:bg-slate-900 border border-gray-300 dark:border-gray-700';
        document.body.appendChild(dropdown);
        return dropdown;
    }

    function posicionar(input) {
        const dd = getDropdown();
        const r = input.getBoundingClientRect();
        const width = Math.max(r.width, 280);
        dd.style.minWidth = width + 'px';
        dd.style.maxWidth = Math.max(width, 420) + 'px';
        dd.style.left = Math.min(r.left, window.innerWidth - Math.max(width, 300) - 8) + 'px';
        dd.style.top = (r.bottom + 4) + 'px';
        // Si no hay espacio abajo, abrir hacia arriba
        const altoEstimado = Math.min(240, dd.scrollHeight || 200);
        if (r.bottom + 4 + altoEstimado > window.innerHeight && r.top - altoEstimado - 4 > 0) {
            dd.style.top = (r.top - altoEstimado - 4) + 'px';
        }
    }

    function cerrarDropdown() {
        if (dropdown) {
            dropdown.style.display = 'none';
            dropdown.innerHTML = '';
        }
        document.querySelectorAll('.combo-prod-item-active').forEach((el) => el.classList.remove('combo-prod-item-active'));
    }

    // ---------- Modal "+ Crear producto nuevo" (único, global) ----------
    function getModal() {
        if (modalEl) return modalEl;
        modalEl = document.createElement('div');
        modalEl.id = 'modal-producto-rapido';
        modalEl.style.cssText = 'position:fixed;inset:0;z-index:10000;display:none;align-items:center;justify-content:center;background:rgba(0,0,0,.6);padding:16px;';
        modalEl.innerHTML = `
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-gray-200 dark:border-gray-700 w-full" style="max-width:440px;box-shadow:0 20px 60px rgba(0,0,0,.5)">
                <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100 dark:border-gray-700">
                    <h4 class="text-sm font-bold text-gray-900 dark:text-white">+ Crear producto nuevo</h4>
                    <button type="button" data-close class="p-1.5 rounded-lg text-gray-400 hover:text-gray-700 dark:hover:text-gray-200">✕</button>
                </div>
                <div class="px-5 py-4 space-y-3">
                    <p class="text-[11px] text-gray-500 dark:text-gray-400">Se registra en el catálogo y se selecciona automáticamente en la fila.</p>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Nombre *</label>
                        <input type="text" data-f="nombre" placeholder="Ej: UPS APC Smart-UPS 3KVA" class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-slate-800 px-2.5 py-1.5 text-xs text-gray-900 dark:text-white">
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Código / SKU *</label>
                            <input type="text" data-f="sku" placeholder="Ej: UPS-3KVA-01" class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-slate-800 px-2.5 py-1.5 text-xs text-gray-900 dark:text-white">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Unidad</label>
                            <input type="text" data-f="unidad_medida" value="UND" maxlength="20" class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-slate-800 px-2.5 py-1.5 text-xs text-gray-900 dark:text-white">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">P. Venta (S/)</label>
                            <input type="number" data-f="precio_venta" min="0" step="0.01" placeholder="0.00" class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-slate-800 px-2.5 py-1.5 text-xs text-gray-900 dark:text-white">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">P. Compra (S/) *</label>
                            <input type="number" data-f="precio_compra" min="0" step="0.01" placeholder="0.00" class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-slate-800 px-2.5 py-1.5 text-xs text-gray-900 dark:text-white">
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Proveedor *</label>
                        <select data-f="proveedor_id" class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-slate-800 px-2.5 py-1.5 text-xs text-gray-900 dark:text-white"></select>
                    </div>
                    <p data-err class="hidden text-[11px] font-semibold text-red-600 dark:text-red-400"></p>
                </div>
                <div class="flex justify-end gap-2 px-5 py-4 border-t border-gray-100 dark:border-gray-700">
                    <button type="button" data-close class="px-3 py-2 rounded-xl text-xs font-semibold bg-gray-100 dark:bg-slate-800 text-gray-700 dark:text-gray-200">Cancelar</button>
                    <button type="button" data-save class="px-4 py-2 rounded-xl text-xs font-bold bg-emerald-600 hover:bg-emerald-700 text-white">Guardar y seleccionar</button>
                </div>
            </div>`;
        document.body.appendChild(modalEl);
        modalEl.querySelectorAll('[data-close]').forEach((b) => b.addEventListener('click', () => {
            modalEl.style.display = 'none';
        }));
        modalEl.addEventListener('click', (e) => {
            if (e.target === modalEl) modalEl.style.display = 'none';
        });
        return modalEl;
    }

    function abrirModal(nombreSugerido, createUrl, csrfToken, proveedores, onCreated) {
        const m = getModal();
        m.querySelector('[data-f="nombre"]').value = nombreSugerido || '';
        ['sku', 'precio_venta', 'precio_compra'].forEach((campo) => {
            m.querySelector('[data-f="' + campo + '"]').value = '';
        });
        m.querySelector('[data-f="unidad_medida"]').value = 'UND';
        const selectProveedor = m.querySelector('[data-f="proveedor_id"]');
        selectProveedor.replaceChildren(new Option('Selecciona un proveedor', ''));
        (proveedores || []).forEach((p) => selectProveedor.add(new Option(p.nombre, p.id)));
        const err = m.querySelector('[data-err]');
        err.classList.add('hidden');
        err.textContent = '';
        m.style.display = 'flex';
        setTimeout(() => m.querySelector('[data-f="nombre"]').focus(), 50);
        const btnSave = m.querySelector('[data-save]');
        btnSave.onclick = async () => {
            err.classList.add('hidden');
            const payload = {
                nombre: m.querySelector('[data-f="nombre"]').value.trim(),
                sku: m.querySelector('[data-f="sku"]').value.trim(),
                unidad_medida: m.querySelector('[data-f="unidad_medida"]').value.trim() || 'UND',
                precio_venta: m.querySelector('[data-f="precio_venta"]').value || null,
                precio_compra: m.querySelector('[data-f="precio_compra"]').value || null,
                proveedor_id: selectProveedor.value,
            };
            if (!payload.nombre || !payload.sku) {
                err.textContent = 'Completa Nombre y Código / SKU.';
                err.classList.remove('hidden');
                return;
            }
            if (!payload.proveedor_id || payload.precio_compra === null) {
                err.textContent = 'Selecciona un proveedor e ingresa el precio de compra.';
                err.classList.remove('hidden');
                return;
            }
            btnSave.disabled = true;
            btnSave.textContent = 'Guardando…';
            try {
                const res = await fetch(createUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    body: JSON.stringify(payload),
                });
                if (!res.ok) {
                    let msg = 'No se pudo crear el producto.';
                    try {
                        const data = await res.json();
                        const first = data && data.errors ? Object.values(data.errors)[0] : null;
                        if (first && first[0]) msg = first[0];
                        else if (data && data.message) msg = data.message;
                    } catch (_) { /* noop */ }
                    throw new Error(msg);
                }
                const producto = await res.json();
                m.style.display = 'none';
                onCreated(producto);
            } catch (e) {
                err.textContent = e.message || 'No se pudo crear el producto.';
                err.classList.remove('hidden');
            } finally {
                btnSave.disabled = false;
                btnSave.textContent = 'Guardar y seleccionar';
            }
        };
    }

    // ---------- Attach principal ----------
    function attach(opts) {
        const input = opts.input;
        const hidden = opts.hidden;
        const errorEl = opts.errorEl;
        const productos = opts.productos || [];
        const searchUrl = opts.searchUrl;
        const createUrl = opts.createUrl;
        const csrfToken = opts.csrfToken;
        const limit = 15;
        let resultados = [];
        let activeIndex = -1;
        let seleccionId = opts.initialId ? String(opts.initialId) : '';

        function marcarError(msg) {
            if (!errorEl) return;
            if (msg) {
                errorEl.textContent = msg;
                errorEl.classList.remove('hidden');
                input.classList.add('!border-red-500');
            } else {
                errorEl.textContent = '';
                errorEl.classList.add('hidden');
                input.classList.remove('!border-red-500');
            }
        }

        function validarFila() {
            const texto = input.value.trim();
            if (!texto) {
                marcarError('Selecciona un producto de la lista');
                return false;
            }
            if (!hidden.value) {
                marcarError('Selecciona un producto de la lista');
                return false;
            }
            marcarError(null);
            return true;
        }

        function render(lista, query) {
            const dd = getDropdown();
            dd.innerHTML = '';
            activeIndex = -1;
            if (!lista.length) {
                const empty = document.createElement('div');
                empty.className = 'px-3 py-2.5 text-xs text-gray-500 dark:text-gray-400';
                empty.textContent = 'Sin resultados';
                dd.appendChild(empty);
            } else {
                lista.forEach((p, i) => {
                    const b = document.createElement('button');
                    b.type = 'button';
                    b.setAttribute('role', 'option');
                    b.dataset.index = String(i);
                    b.className = 'w-full text-left px-3 py-2 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors';
                    b.innerHTML =
                        '<div class="text-xs font-semibold text-gray-900 dark:text-white leading-tight">' + escapeHtml(p.nombre || '') + '</div>' +
                        '<div class="flex items-center justify-between gap-2 mt-0.5">' +
                        '<span class="text-[11px] font-mono text-gray-500 dark:text-gray-400">' + escapeHtml(p.sku || 'SIN-CODIGO') + '</span>' +
                        '<span class="text-[11px] font-mono font-bold text-blue-700 dark:text-blue-300">' + escapeHtml(formatoPrecio(p.precio_venta)) + '</span>' +
                        '</div>';
                    b.addEventListener('mousedown', (e) => {
                        e.preventDefault();
                        seleccionar(p);
                    });
                    b.addEventListener('mousemove', () => setActive(i));
                    dd.appendChild(b);
                });
            }
            // Botón crear al final (siempre visible para registro rápido)
            const crear = document.createElement('button');
            crear.type = 'button';
            crear.className = 'w-full text-left px-3 py-2 mt-1 rounded-lg text-xs font-bold text-emerald-700 dark:text-emerald-300 hover:bg-emerald-50 dark:hover:bg-emerald-950/40 border-t border-gray-100 dark:border-gray-800';
            crear.textContent = '+ Crear producto nuevo' + (query ? ' (' + query.trim().slice(0, 30) + ')' : '');
            crear.addEventListener('mousedown', (e) => {
                e.preventDefault();
                cerrarDropdown();
                abrirModal(query ? query.trim() : '', createUrl, csrfToken, opts.proveedores, (nuevo) => {
                    productos.unshift(nuevo);
                    seleccionar(nuevo);
                });
            });
            dd.appendChild(crear);

            dd.style.display = 'block';
            posicionar(input);
        }

        function setActive(i) {
            const dd = getDropdown();
            const items = dd.querySelectorAll('[role="option"]');
            items.forEach((el) => el.classList.remove('combo-prod-item-active', 'bg-slate-100', 'dark:bg-slate-800'));
            activeIndex = i;
            if (items[i]) {
                items[i].classList.add('combo-prod-item-active', 'bg-slate-100', 'dark:bg-slate-800');
                items[i].scrollIntoView({ block: 'nearest' });
            }
        }

        function seleccionar(p) {
            seleccionId = String(p.id);
            hidden.value = String(p.id);
            input.value = p.nombre || '';
            marcarError(null);
            cerrarDropdown();
            if (typeof opts.onSelect === 'function') opts.onSelect(p);
            if (typeof opts.onValidate === 'function') opts.onValidate();
        }

        function limpiarSeleccion() {
            if (hidden.value) {
                hidden.value = '';
                seleccionId = '';
                if (typeof opts.onClear === 'function') opts.onClear();
            }
            // Si hay texto pero sin selección, marcar error suave (sin bloquear mientras escribe)
            if (input.value.trim()) marcarError('Selecciona un producto de la lista');
            else marcarError(null);
        }

        async function buscar(query) {
            // 1) Servidor (para 1000+ productos sin lag)
            if (searchUrl) {
                try {
                    const url = searchUrl + '?q=' + encodeURIComponent(query) + '&limit=' + limit;
                    const res = await fetch(url, { headers: { 'Accept': 'application/json' } });
                    if (res.ok) {
                        const data = await res.json();
                        if (Array.isArray(data)) {
                            resultados = data.slice(0, limit);
                            // Solo renderizar si el input sigue enfocado / con el mismo texto
                            if (document.activeElement === input || getDropdown().style.display === 'block') {
                                render(resultados, query);
                            }
                            return;
                        }
                    }
                } catch (_) { /* fallback local */ }
            }
            // 2) Fallback local (insensible a tildes)
            resultados = filtrarLocal(productos, query, limit);
            render(resultados, query);
        }
        const buscarDebounced = debounce(buscar, 200);

        input.addEventListener('input', () => {
            limpiarSeleccion();
            const q = input.value;
            getDropdown().style.display = 'block';
            posicionar(input);
            buscarDebounced(q);
        });

        input.addEventListener('focus', () => {
            if (input.value.trim() === '') {
                resultados = (productos || []).slice(0, limit);
                render(resultados, '');
            } else if (!hidden.value) {
                buscarDebounced(input.value);
            } else {
                posicionar(input);
            }
        });

        input.addEventListener('keydown', (e) => {
            const dd = getDropdown();
            const abierto = dd.style.display === 'block';
            if (e.key === 'ArrowDown' && abierto) {
                e.preventDefault();
                setActive(Math.min(activeIndex + 1, resultados.length - 1));
            } else if (e.key === 'ArrowUp' && abierto) {
                e.preventDefault();
                setActive(Math.max(activeIndex - 1, 0));
            } else if (e.key === 'Enter' && abierto && activeIndex >= 0 && resultados[activeIndex]) {
                e.preventDefault();
                seleccionar(resultados[activeIndex]);
            } else if (e.key === 'Escape') {
                cerrarDropdown();
                input.blur();
            }
        });

        input.addEventListener('blur', () => {
            // Cerrar con retardo para permitir click en resultados (mousedown ya lo maneja)
            setTimeout(() => {
                cerrarDropdown();
                validarFila();
                if (typeof opts.onValidate === 'function') opts.onValidate();
            }, 150);
        });

        // Valor inicial (modo edición / old input)
        if (opts.initialId) {
            hidden.value = String(opts.initialId);
            if (opts.initialNombre) {
                input.value = opts.initialNombre;
            } else {
                const found = (productos || []).find((p) => String(p.id) === String(opts.initialId));
                if (found) input.value = found.nombre || '';
            }
        }

        return { validarFila, seleccionar };
    }

    function escapeHtml(s) {
        return String(s)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

    // Cerrar dropdown global al hacer scroll fuera o click fuera
    document.addEventListener('click', (e) => {
        if (dropdown && dropdown.style.display === 'block') {
            const dentroDropdown = dropdown.contains(e.target);
            const esInput = e.target && e.target.classList && e.target.classList.contains('combo-prod-input');
            if (!dentroDropdown && !esInput) cerrarDropdown();
        }
    }, true);
    window.addEventListener('resize', cerrarDropdown);
    document.addEventListener('scroll', () => {
        if (dropdown && dropdown.style.display === 'block') cerrarDropdown();
    }, true);

    window.CotizacionProductoCombobox = { attach, normalizar, filtrarLocal };
})();
