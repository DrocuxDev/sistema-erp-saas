# Plan de implementación — Sistema ERP (Cotizaciones, Ventas, Compras e Inventario)

## Estado del proyecto (leer esto primero, antes que nada)

**Instrucción para cualquier agente que trabaje en este documento:** antes de escribir una sola línea de código, lee estos 5 campos para saber exactamente dónde quedó el proyecto — no hace falta revisar el código para ubicarte. Cuando termines tu sesión de trabajo (te quedes sin tokens, termines una fase, o el usuario corte la conversación), **actualiza estos campos como tu último paso antes de responder por última vez**. Así el siguiente agente retoma sin perder contexto.

- **Última actualización:** 15 ago 2026
- **Fase actual:** Fase 5 — Cotizaciones (COMPLETADA)
- **Último paso completado:** Fases 4 y 5 completadas: módulo OC de clientes (CRUD, archivo, filtros, enlaces a cotización/venta) y módulo Cotizaciones (CRUD, líneas dinámicas, resumen financiero, aprobar, PDF imprimible).
- **Próximo paso concreto:** Fase 6 — Ventas (`VentaController` y vistas)
- **Bloqueos o decisiones pendientes:** Instalar `barryvdh/laravel-dompdf` con PHP 8.3+ para descarga PDF directa (`composer require barryvdh/laravel-dompdf`). La ruta `/cotizaciones/{id}/pdf` ya funciona como vista imprimible sin el paquete.
- **Notas para el siguiente agente:** Usuarios: `admin@erp.local` / `trabajador@erp.local` (password: `password`). Panel de usuarios y configuración solo para admin.

---

## Rol

Actúa como ingeniero de software senior implementando este sistema desde cero. Sigue las fases en orden — cada una depende de la anterior. Marca cada checkbox (`- [ ]` → `- [x]`) **a medida que completes cada tarea**, no al final de la fase. No avances de fase sin haber corrido las migraciones y verificado que el módulo anterior funciona. Mantén el bloque "Estado del proyecto" de arriba siempre al día — es lo primero que va a leer el siguiente agente.

## Contexto del negocio

ERP interno (solo dos tipos de usuario: `administrador` y `trabajador`, sin clientes externos usando el sistema) para gestionar el ciclo completo: un cliente manda su orden de compra → se genera una cotización con margen y comisiones calculados automáticamente → se aprueba y se convierte en venta → se emite comprobante (factura/boleta/nota de venta) y guía de remisión → el stock se descuenta solo. En paralelo, existe el flujo de compra a proveedores: orden de compra → recepción (compra) → el stock se incrementa solo.

## Supuestos técnicos (ajustar si no aplica)

- PHP 8.2+, Laravel 11.x, MySQL 8.0.16+
- Autenticación: Laravel Breeze (stack Blade, sin Livewire/Inertia — el usuario pidió Blade clásico)
- Motor de vistas: Blade puro, formularios HTML estándar, JS plano solo donde haga falta (líneas dinámicas de cotización/venta)
- Sin frontend build complejo: Tailwind vía Breeze por defecto, sin componentes JS pesados
- Base de datos: `gestion_cotizaciones` (ya definida en `sistema_erp.sql`, con el agregado de `ordenes_compra_cliente` y la columna `rol` en `users`, ver Fase 1)
- Roles: columna `rol` en `users`, ENUM `administrador` / `trabajador`, sin tabla de permisos separada (no se necesita granularidad mayor por ahora)

Si alguno de estos supuestos es incorrecto, corrígelo en este documento antes de empezar a programar.

---

## Fase 0 — Setup del proyecto

- [x] Crear proyecto: `composer create-project laravel/laravel sistema-erp`
- [x] Instalar Breeze: `composer require laravel/breeze --dev` → `php artisan breeze:install blade`
- [x] Configurar `.env` con la base de datos `gestion_cotizaciones`
- [x] Instalar dependencias JS: `npm install && npm run build`
- [x] Verificar que `php artisan serve` levanta y el login de Breeze funciona
- [x] Crear rama de control de versiones (git) antes de tocar el esquema

---

## Fase 1 — Base de datos y modelos

### 1.1 Migraciones

Convierte cada tabla de `sistema_erp.sql` en su migración Laravel equivalente (usa `php artisan make:migration create_x_table`). Respeta el orden por dependencias de FK:

- [x] `users` (ampliar la migración por defecto de Breeze, no crear una nueva): agregar `rol ENUM('administrador','trabajador') DEFAULT 'trabajador'`
- [x] `configuracion`
- [x] `proveedores`
- [x] `clientes`
- [x] `productos`
- [x] `producto_proveedor`
- [x] `producto_presentaciones_compra`
- [x] `ordenes_compra_cliente` (tabla **nueva**, no está en el SQL original — ver definición abajo)
- [x] `cotizaciones` (agregar FK `orden_compra_cliente_id` nullable → `ordenes_compra_cliente.id`)
- [x] `cotizacion_detalle`
- [x] `cotizacion_resumen`
- [x] `ventas` (agregar FK `orden_compra_cliente_id` nullable → `ordenes_compra_cliente.id`)
- [x] `venta_detalle`
- [x] `ordenes_compra` (a proveedor)
- [x] `orden_compra_detalle`
- [x] `compras`
- [x] `compra_detalle`
- [x] `movimientos_inventario`
- [x] `comprobantes_venta`
- [x] `guias_remision`
- [x] `guia_remision_detalle`
- [x] `series_documentos`
- [x] `auditoria`
- [x] Migración de vuelta a `ordenes_compra_cliente` para agregar la FK circular hacia `cotizaciones`/`ventas` (o usar `Schema::table` separado después de crear ambas, para evitar problema de orden circular)

**Definición de la tabla nueva `ordenes_compra_cliente`:**

```sql
CREATE TABLE ordenes_compra_cliente (
    id                INT AUTO_INCREMENT PRIMARY KEY,
    cliente_id        INT NOT NULL,
    cotizacion_id     INT NULL,
    venta_id          INT NULL,
    usuario_id        BIGINT UNSIGNED NULL,
    numero_oc         VARCHAR(50) NOT NULL,
    archivo           VARCHAR(255) NULL,
    fecha_recepcion   DATE NOT NULL,
    estado            ENUM('pendiente','procesada','anulada') NOT NULL DEFAULT 'pendiente',
    observaciones     VARCHAR(255),
    created_at        TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (cliente_id) REFERENCES clientes(id),
    FOREIGN KEY (cotizacion_id) REFERENCES cotizaciones(id),
    FOREIGN KEY (venta_id) REFERENCES ventas(id),
    CONSTRAINT fk_oc_cliente_usuario FOREIGN KEY (usuario_id) REFERENCES users(id),
    UNIQUE KEY uq_oc_cliente (cliente_id, numero_oc)
) ENGINE=InnoDB;
```

### 1.2 Triggers y columnas generadas (`STORED`)

MySQL soporta columnas `GENERATED ALWAYS AS (...) STORED` y `TRIGGER` directamente en migraciones usando `DB::statement()`. No los repliques en PHP como lógica separada — mantenlos en la base de datos para que la integridad se garantice sin importar por dónde entren los datos:

- [x] En la migración de `cotizacion_detalle`: columnas generadas `precio_venta_total`, `precio_compra_total`, `ganancia_linea` vía `DB::statement()`
- [x] En la migración de `cotizacion_resumen`: columnas generadas `total_facturar`, `ganancia`, `comision_total`, `saldo_a_favor`, `rentabilidad`
- [x] En la migración de `venta_detalle`: columna generada `ganancia_linea`
- [x] En la migración de `orden_compra_detalle`: columna generada `subtotal`
- [x] En la migración de `comprobantes_venta`: columna generada `total`
- [x] Trigger `trg_movimiento_stock` (actualiza `stock_actual` en `productos`)
- [x] Trigger `trg_movimiento_valida` (valida stock suficiente y referencia válida)
- [x] Trigger `trg_resumen_valida_subtotal`
- [x] Trigger `trg_venta_detalle_valida_producto`
- [x] Trigger `trg_compra_detalle_valida_producto`
- [x] Índices de apoyo (`idx_cotizaciones_fecha`, etc.)

### 1.3 Modelos Eloquent

Por cada tabla, crear el modelo con `php artisan make:model NombreModelo` y definir:
- [x] `$fillable` con los campos correspondientes
- [x] Relaciones (`belongsTo`, `hasMany`, `hasOne`) según las FK
- [x] Los campos `GENERATED` se marcan como no editables (no van en `$fillable`) y se leen normal tras refrescar el modelo

Modelos a crear: `User` (ampliar), `Configuracion`, `Proveedor`, `Cliente`, `Producto`, `ProductoProveedor`, `ProductoPresentacionCompra`, `OrdenCompraCliente`, `Cotizacion`, `CotizacionDetalle`, `CotizacionResumen`, `Venta`, `VentaDetalle`, `OrdenCompra`, `OrdenCompraDetalle`, `Compra`, `CompraDetalle`, `MovimientoInventario`, `ComprobanteVenta`, `GuiaRemision`, `GuiaRemisionDetalle`, `SerieDocumento`, `Auditoria`.

- [x] Ejecutar `php artisan migrate` y verificar que todas las tablas y triggers se crearon sin error
- [x] Sembrar `configuracion` con el registro "General" vía seeder (`php artisan make:seeder ConfiguracionSeeder`)
- [x] Crear un seeder de usuario administrador inicial

---

## Fase 2 — Autenticación y control de acceso por rol

- [x] Middleware `CheckRol` (`php artisan make:middleware CheckRol`): recibe el rol requerido, compara con `auth()->user()->rol`, aborta con 403 si no coincide
- [x] Registrar el middleware con alias `rol` en `bootstrap/app.php`
- [x] Aplicar `->middleware('rol:administrador')` a las rutas de Configuración y Auditoría
- [x] El resto de módulos usan solo `->middleware('auth')` (accesibles para ambos roles)
- [x] Layout base (`layouts/app.blade.php`): sidebar que oculta los enlaces a Configuración/Auditoría si `auth()->user()->rol !== 'administrador'`
- [x] Probar login con un usuario `trabajador` y confirmar que no puede acceder a `/configuracion` ni por URL directa

---

## Fase 3 — Módulos maestros (catálogo)

Repetir este patrón para **Proveedores** y **Clientes** (estructura idéntica):

- [x] Controlador resource (`php artisan make:controller ProveedorController --resource`)
- [x] `Route::resource('proveedores', ProveedorController::class)`
- [x] Form Request para validación (`StoreProveedorRequest`, `UpdateProveedorRequest`)
- [x] Vistas: `index.blade.php` (tabla con búsqueda por nombre), `create.blade.php`, `edit.blade.php`, `show.blade.php`
- [x] En `show.blade.php` de cliente: listar sus últimas cotizaciones/ventas. En `show.blade.php` de proveedor: listar sus últimas compras

**Configuración:**
- [x] Controlador simple (no resource completo, solo `edit`/`update`, ya que es un único registro)
- [x] Vista `configuracion/edit.blade.php`: formulario con IGV, retención, comisión de venta, comisión de ganancia

**Productos e inventario:**
- [x] `ProductoController` resource + validación (`sku` único, `puede_venderse`/`puede_comprarse` con el check de que al menos uno sea `true`)
- [x] `productos/index.blade.php`: filtro de "stock bajo" (`stock_actual < stock_minimo`)
- [x] `productos/show.blade.php`: tabs o secciones para proveedores asociados (`producto_proveedor`) y presentaciones de compra (`producto_presentaciones_compra`)
- [x] Subformularios/endpoints AJAX simples para agregar/quitar proveedor y presentación sin salir de la ficha del producto
- [x] `MovimientoInventarioController`: solo `index` (kardex por producto) y `store` (ajuste manual, `tipo = 'ajuste'`)
- [x] `inventario/kardex.blade.php`: tabla de movimientos filtrable por producto y fecha
- [x] `inventario/ajuste.blade.php`: formulario de ajuste manual
- [x] Probar que insertar un movimiento con `tipo = 'salida'` mayor al stock dispara el error del trigger y Laravel lo captura como excepción legible (try/catch alrededor del insert, mostrar mensaje amigable)

---

## Fase 4 — Órdenes de compra del cliente (módulo nuevo)

- [x] `OrdenCompraClienteController` resource
- [x] `ordenes-compra-cliente/index.blade.php`: listado filtrable por estado (pendiente/procesada/anulada) y cliente
- [x] `ordenes-compra-cliente/create.blade.php`: cliente, número de OC, fecha de recepción, input `type="file"` para PDF/imagen, observaciones
- [x] Guardar el archivo con `Storage::disk('public')->putFile('ordenes-compra-cliente', $request->file('archivo'))`
- [x] `ordenes-compra-cliente/show.blade.php`: detalle + enlace de descarga del archivo + botones "Generar cotización" y "Generar venta"
- [x] Los botones enlazan a `cotizaciones/create?orden_compra_cliente_id=X` / `ventas/create?orden_compra_cliente_id=X`, precargando `cliente_id` desde la OC
- [x] Al guardar la cotización/venta generada, actualizar `estado = 'procesada'` y el FK correspondiente en `ordenes_compra_cliente`
- [x] `ordenes-compra-cliente/edit.blade.php`: editar datos o anular

---

## Fase 5 — Cotizaciones (módulo central)

- [x] `CotizacionController` resource
- [x] `cotizaciones/create.blade.php`: cabecera (cliente, fecha, observaciones) + tabla dinámica de líneas (producto, proveedor, cantidad, precio venta, precio compra, IGV compra) con JS plano para agregar/quitar filas
- [x] Al enviar, guardar `cotizacion` + todas las `cotizacion_detalle` en una transacción (`DB::transaction`)
- [x] Calcular y guardar `cotizacion_resumen` en el mismo submit: `subtotal` = suma de `precio_venta_total` del detalle, `igv`, `retencion`, `compra_activa`, `compra_fija`, `comision_venta`, `comision_ganancia` según los porcentajes de `configuracion`
- [x] Recordar que el trigger `trg_resumen_valida_subtotal` rechaza el insert si el `subtotal` no cuadra con la suma real — validar en el backend antes de enviar para evitar una excepción SQL cruda
- [x] `cotizaciones/index.blade.php`: filtro por estado (pendiente/aprobada/rechazada/facturada)
- [x] `cotizaciones/show.blade.php`: detalle de líneas + bloque de resumen financiero (ganancia, comisiones, rentabilidad) — visible completo solo para `administrador`; el `trabajador` ve el detalle de líneas pero puede ocultarse la rentabilidad si se decide restringir (definir con el negocio si aplica)
- [x] `cotizaciones/edit.blade.php`: solo editable si `estado = 'pendiente'`
- [x] Acción "Aprobar": cambia `estado` a `aprobada`, disponible para generar la venta
- [x] `cotizaciones/pdf.blade.php`: vista imprimible (usar `dompdf` o similar, `composer require barryvdh/laravel-dompdf`)

---

## Fase 6 — Ventas

- [ ] `VentaController` resource
- [ ] `ventas/create.blade.php`: si viene de una cotización aprobada (`?cotizacion_id=X`), precargar cliente y copiar las líneas de `cotizacion_detalle` a `venta_detalle`; si no, permitir venta directa
- [ ] Validar en backend que cada producto tenga `puede_venderse = true` antes del insert (el trigger `trg_venta_detalle_valida_producto` es el respaldo final, no la única validación)
- [ ] Al confirmar la venta, generar automáticamente los `movimientos_inventario` de tipo `salida` por cada línea (esto dispara el trigger de stock)
- [ ] Si la venta nace de una cotización, actualizar `cotizaciones.estado = 'facturada'`
- [ ] `ventas/index.blade.php`, `ventas/show.blade.php`

---

## Fase 7 — Comprobantes de venta y guías de remisión

- [ ] `SerieDocumentoController`: administración de series y correlativo actual por tipo de documento (`configuracion/series.blade.php`)
- [ ] `ComprobanteVentaController`: al emitir, tomar el siguiente `correlativo_actual` de `series_documentos` para la serie elegida (usar `lockForUpdate()` dentro de una transacción para evitar correlativos duplicados por concurrencia), incrementar el contador, y crear el comprobante
- [ ] `comprobantes/create.blade.php`, `comprobantes/pdf.blade.php`
- [ ] `GuiaRemisionController`: mismo patrón de correlativo. Formulario con datos de traslado (punto de partida/llegada, transportista, placa) y detalle de productos/cantidades
- [ ] `guias-remision/create.blade.php`, `guias-remision/pdf.blade.php`
- [ ] Acción "Anular" en ambos (cambia `estado` a `anulado`/`anulada`, no se borra el registro)

---

## Fase 8 — Compras a proveedor

- [ ] `OrdenCompraController` resource (la que la empresa envía al proveedor)
- [ ] `ordenes-compra/create.blade.php`: proveedor + líneas (producto, presentación de compra opcional, cantidad, precio)
- [ ] `CompraController`: `create.blade.php` puede precargarse desde una orden de compra aprobada (`?orden_compra_id=X`) o registrarse directa
- [ ] Validar `puede_comprarse = true` en backend antes del insert (respaldo del trigger `trg_compra_detalle_valida_producto`)
- [ ] Al confirmar la compra, generar automáticamente los `movimientos_inventario` de tipo `entrada` por cada línea
- [ ] Si la compra nace de una orden, actualizar `ordenes_compra.estado = 'recibida'`
- [ ] `ordenes-compra/index.blade.php`, `ordenes-compra/show.blade.php`, `compras/index.blade.php`, `compras/show.blade.php`

---

## Fase 9 — Auditoría

- [ ] Enfoque recomendado: `Observer` de Eloquent (`php artisan make:observer AuditoriaObserver`) registrado sobre los modelos clave (`Cotizacion`, `Venta`, `Compra`, `ComprobanteVenta`, etc.) que inserte en `auditoria` en los eventos `created`, `updated`, `deleted`
- [ ] `auditoria/index.blade.php`: listado filtrable por tabla, usuario y acción — solo `administrador`

---

## Fase 10 — Dashboard

- [ ] `DashboardController@index`: consultas agregadas — ventas del mes, rentabilidad total de cotizaciones facturadas, productos con `stock_actual < stock_minimo`, top clientes/proveedores del periodo
- [ ] `dashboard/index.blade.php`: tarjetas de resumen + una librería ligera de gráficos (Chart.js vía CDN es suficiente, no agregar dependencias pesadas)
- [ ] Definir esta vista como la página de inicio tras el login (`RouteServiceProvider` o redirect post-login)

---

## Fase 11 — Pulido y QA

- [ ] Revisar que todos los formularios muestren errores de validación de forma consistente (usar `@error` de Blade en todos)
- [ ] Revisar que las acciones destructivas (anular, eliminar) pidan confirmación (modal simple o `onsubmit="return confirm(...)"`)
- [ ] Probar el flujo completo de punta a punta: OC de cliente → cotización → aprobación → venta → comprobante → guía → verificar que el stock bajó correctamente
- [ ] Probar el flujo de compra completo: orden de compra → recepción → verificar que el stock subió correctamente
- [ ] Probar los tres triggers de validación forzando los casos de error (venta sin stock, producto no vendible, subtotal no cuadra) y confirmar que el mensaje que ve el usuario es entendible, no el error crudo de MySQL
- [ ] Revisar permisos: un `trabajador` no debe poder llegar a `/configuracion` ni `/auditoria` ni por URL directa
- [ ] Ejecutar `php artisan test` si se agregaron tests, o al menos hacer una pasada manual completa por cada módulo

---

## Convenciones a seguir en todo el proyecto

- Nombres de tabla y columna en español, snake_case (ya vienen así del SQL) — mantener consistencia en nombres de rutas y vistas también en español
- Rutas: `Route::resource('nombre-modulo', Controller::class)` siempre que el CRUD sea estándar; rutas adicionales explícitas para acciones especiales (`aprobar`, `anular`, `generar-venta`)
- Toda operación que toque más de una tabla (cotización + detalle + resumen, venta + movimientos de inventario) va dentro de `DB::transaction()`
- Los cálculos financieros (IGV, retención, comisiones) se validan en el backend antes de enviar a la base de datos — los triggers son la última línea de defensa, no la validación principal
- Un Form Request por cada acción de guardado (`Store...Request`, `Update...Request`), nunca validar inline en el controlador

---

## Checklist de aceptación final

- [ ] Un `trabajador` puede: crear cliente, registrar OC de cliente, crear cotización, aprobarla, generar venta, emitir comprobante y guía
- [ ] Un `administrador` además puede: editar configuración, ver auditoría, ver rentabilidad completa en el dashboard
- [ ] El stock se actualiza solo al vender y al comprar, sin intervención manual
- [ ] No es posible vender un producto marcado `puede_venderse = false`, ni comprar uno marcado `puede_comprarse = false`
- [ ] Los correlativos de comprobantes y guías nunca se duplican, incluso con dos usuarios emitiendo al mismo tiempo
