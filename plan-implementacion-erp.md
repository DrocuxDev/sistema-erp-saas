# Plan de implementación — Sistema ERP (Cotizaciones, Ventas, Compras e Inventario)

## Estado del proyecto (leer esto primero, antes que nada)

**Instrucción para cualquier agente que trabaje en este documento:** antes de escribir una sola línea de código, lee estos 5 campos para saber exactamente dónde quedó el proyecto — no hace falta revisar el código para ubicarte. Cuando termines tu sesión de trabajo (te quedes sin tokens, termines una fase, o el usuario corte la conversación), **actualiza estos campos como tu último paso antes de responder por última vez**. Así el siguiente agente retoma sin perder contexto.

- **Última actualización:** 3 oct 2026 (remediación de los 16 hallazgos de `AUDITORIA.md`)
- **Fase actual:** Fase 11 — QA y validación de migraciones MySQL antes del despliegue
- **Último paso completado:**
  0) **Remediación de auditoría:** permisos críticos restringidos en servidor; plantilla PDF editable retirada; perfil tributario por cliente heredado en cotización, venta y comprobante con excepción motivada; ventas y recepciones parciales conciliadas bajo bloqueo; stock transaccional en servicio común; correlativos únicos y sin retroceso; auditoría de cambios y permisos ampliada; archivos anteriores conservados hasta confirmar la base. Laravel 13.34.0 y CommonMark 2.10.3 sin avisos en `composer audit --locked`. Suite: 55 pruebas y 281 aserciones en verde; vistas Blade compiladas, Pint y `git diff --check` correctos.
  1) **Fase 7a — Comprobantes de Venta:** `ComprobanteVentaController` (factura `F001` / boleta `B001` / nota de venta `NV01`) con emisión desde venta (hereda subtotal+IGV, 1 comprobante vigente por venta), vistas `comprobantes/index-create-show-pdf` bajo estándar corporativo, visor embebido, acción Anular y rutas bajo `modulo:comprobantes` + enlace en sidebar y botones en `ventas/show`.
  2) **Fase 7b — Guías de Remisión:** `GuiaRemisionController` (serie `T001`, precarga de ítems desde venta, traslado sin venta permitido, no mueve stock), vistas `guias-remision/index-create-show-pdf` bajo estándar corporativo, acción Anular y rutas bajo `modulo:comprobantes` + enlace en sidebar.
  3) **Fase 8 — Compras/Recepción:** `CompraController` (precarga desde O/C aprobada o compra directa, movimientos `entrada` en kardex, O/C pasa a `recibida`), vistas `compras/index-create-show`, rutas bajo `modulo:compras`, enlace en sidebar y botón "Recepcionar compra" en `ordenes-compra/show`.
  4) **Fase 9 — Auditoría:** `AuditoriaObserver` genérico registrado en `AppServiceProvider` para `Cotizacion, Venta, Compra, ComprobanteVenta, GuiaRemision, OrdenCompra, User` (creacion/edicion/anulacion, nunca rompe la operación); `AuditoriaController` + vista alineados al esquema real (`creacion/edicion/anulacion`, campo `detalle`, filtro por fecha); migración `auditoria.usuario_id` nullable con `nullOnDelete` para no bloquear eliminación de usuarios.
  5) **Fase 11 — QA:** `FlujosComercialCompraTest` (flujo Venta→Comprobante→Guía con PDFs y anulaciones; flujo O/C→aprobación→recepción con kardex; seguridad 403 para rol ventas en comprobantes/compras/guías) + `SystemSmokeTest` ampliado a las nuevas rutas. Suite completa: 46 tests pasando. Correcciones multi-driver: accesor `total` en `ComprobanteVenta` (columna generada solo existe en MySQL) y fallback `OCC01` en `SerieDocumento::siguienteCodigo()`.
- **Próximo paso concreto:** En un entorno MySQL de prueba, respaldar datos, revisar duplicados de serie/correlativo, aplicar las migraciones `2026_10_03_000001` a `000004` y probar concurrencia de facturas, stock y recepciones. El despliegue en cPanel sigue diferido por petición del usuario.
- **Bloqueos o decisiones pendientes:** MySQL local no disponible (`Connection refused` en `gestion_cotizaciones`); las pruebas automáticas se ejecutaron con SQLite en memoria. No se aplicaron migraciones a la base real ni se desplegó. La migración de unicidad detendrá la ejecución con un mensaje si encuentra correlativos históricos duplicados.
- **Corrección crítica 20 sep 2026 (UrlGenerationException en proveedores):** el singularizador inglés generaba `{proveedore}` y `{ordenes_compra_cliente}`, rompiendo el implicit binding (el controlador recibía un modelo vacío y la vista fallaba al generar la URL). Solución: `->parameters()` explícitos en `proveedores` y `ordenes-compra-cliente`; además se restringieron con `->only()` los resources con métodos inexistentes (`ventas`, `ordenes-compra`, `users`, `roles`, `series-documentos`) para que respondan 404/405 en vez de 500. Regresión cubierta por `RouteBindingRegressionTest`.
- **Notas para el siguiente agente:** Cuentas activas en el sistema: 1) SuperAdmin: `admin@jhardsystex.com` / `admin@jhardsystex` (Nombre: Administrador General), 2) `ventas@jhardsystex.com` / `ventas@jhardsystex` (Nombre: Ejecutivo de Ventas), 3) `soporte@jhardsystex.com` / `soporte@jhardsystex` (Nombre: Soporte / Logística). Password para todos: `password`.


---

## Rol

Actúa como ingeniero de software senior implementando este sistema desde cero. Sigue las fases en orden — cada una depende de la anterior. Marca cada checkbox (`- [ ]` → `- [x]`) **a medida que completes cada tarea**, no al final de la fase. No avances de fase sin haber corrido las migraciones y verificado que el módulo anterior funciona. Mantén el bloque "Estado del proyecto" de arriba siempre al día — es lo primero que va a leer el siguiente agente.

## Contexto del negocio

ERP interno para gestionar el ciclo comercial y operativo completo de la empresa: un cliente manda su orden de compra → se genera una cotización con margen y comisiones calculados automáticamente (con tasas ajustables por documento) → se aprueba y se convierte en venta → se emite comprobante (factura/boleta/nota de venta) y guía de remisión → el stock se descuenta solo mediante movimientos kardex. En paralelo, existe el flujo de compra a proveedores: orden de compra formal (`OC01`) → recepción (compra) → el stock se incrementa solo.

## Supuestos técnicos

- PHP 8.2+, Laravel 11.x, MySQL 8.0.16+
- Autenticación: Laravel Breeze (stack Blade clásico) con pantalla dividida (Split Screen) corporativa
- Motor de vistas: Blade puro, formularios HTML estándar, JS plano para pestañas e interactividad de líneas
- Estilos: Tailwind CSS con paleta oficial Deep Slate + Neon Cyan + Neon Emerald con modo claro y oscuro totalmente responsivos
- Base de datos: `gestion_cotizaciones`, ampliada con control de roles dinámicos, catálogo de módulos, excepciones de usuario, series correlativas transaccionales y tasas por documento

---

## Fase 0 — Setup del proyecto

- [x] Crear proyecto: `composer create-project laravel/laravel sistema-erp`
- [x] Instalar Breeze: `composer require laravel/breeze --dev` → `php artisan breeze:install blade`
- [x] Configurar `.env` con la base de datos `gestion_cotizaciones`
- [x] Diseñar pantalla de Login corporativa (Split Screen) con alternador de contraseña y estética de alta gama
- [x] Configurar tokens de diseño en `tailwind.config.js` y modo oscuro/claro interactivo
- [x] Instalar dependencias JS: `npm install && npm run build`
- [x] Verificar que `php artisan serve` levanta y el login funciona

---

## Fase 1 — Base de datos y modelos

### 1.1 Migraciones
Convierte cada tabla de `sistema_erp.sql` en su migración Laravel equivalente y crea las tablas estructurales:

- [x] `users` (ampliada con `rol`, `rol_id`, `punto_emision`, `is_superadmin`, `activo`)
- [x] `configuracion` (datos de empresa, RUC, cuentas bancarias, tasas predeterminadas de IGV, retenciones y comisiones)
- [x] `proveedores`
- [x] `clientes`
- [x] `productos`
- [x] `producto_proveedor`
- [x] `producto_presentaciones_compra`
- [x] `ordenes_compra_cliente` (recepción y adjuntos de OC de clientes)
- [x] `cotizaciones` (con campos corporativos: serie, correlativo, código, versión, asunto, representante, condiciones)
- [x] `cotizacion_detalle`
- [x] `cotizacion_resumen` (ampliada con `pct_igv`, `pct_retencion`, `pct_comision_venta`, `pct_comision_ganancia` por documento)
- [x] `ventas` (con campos corporativos: serie `V001`, correlativo, código, subtotal, igv, total)
- [x] `venta_detalle`
- [x] `ordenes_compra` (a proveedor, con serie `OC01`, correlativo, código, condiciones de entrega)
- [x] `orden_compra_detalle`
- [x] `compras`
- [x] `compra_detalle`
- [x] `movimientos_inventario`
- [x] `comprobantes_venta`
- [x] `guias_remision`
- [x] `guia_remision_detalle`
- [x] `series_documentos` (con soporte dinámico para cotizaciones `C001`, ventas `V001`, OC proveedor `OC01`, OC cliente `OCC01`, facturas `F001`, boletas `B001`, notas de venta `NV01` y guías `T001`)
- [x] `roles` y `modulos` (catálogo oficial de módulos y permisos por rol)
- [x] `user_modulo` (excepciones y overrides individuales por usuario)
- [x] `auditoria`

### 1.2 Triggers y columnas generadas (`STORED`)
- [x] Columnas generadas `precio_venta_total`, `precio_compra_total`, `ganancia_linea` en `cotizacion_detalle`
- [x] Columnas generadas `total_facturar`, `ganancia`, `comision_total`, `saldo_a_favor`, `rentabilidad` en `cotizacion_resumen`
- [x] Columna generada `ganancia_linea` en `venta_detalle`
- [x] Columna generada `subtotal` en `orden_compra_detalle`
- [x] Columna generada `total` en `comprobantes_venta`
- [x] Los triggers históricos `trg_movimiento_stock` y `trg_movimiento_valida` se sustituyeron por `InventarioService` transaccional en la migración `2026_10_03_000002` (la lógica se prueba también en SQLite)
- [x] Trigger `trg_resumen_valida_subtotal`
- [x] Trigger `trg_venta_detalle_valida_producto`
- [x] Trigger `trg_compra_detalle_valida_producto`
- [x] Índices de apoyo para búsquedas y correlativos

### 1.3 Modelos Eloquent
- [x] Modelos creados: `User`, `Rol`, `Modulo`, `Configuracion`, `Proveedor`, `Cliente`, `Producto`, `ProductoProveedor`, `ProductoPresentacionCompra`, `OrdenCompraCliente`, `Cotizacion`, `CotizacionDetalle`, `CotizacionResumen`, `Venta`, `VentaDetalle`, `OrdenCompra`, `OrdenCompraDetalle`, `Compra`, `CompraDetalle`, `MovimientoInventario`, `ComprobanteVenta`, `GuiaRemision`, `GuiaRemisionDetalle`, `SerieDocumento`, `Auditoria`.
- [x] Seeder `ConfiguracionSeeder`
- [x] Seeder `RolesAndModulosSeeder` con módulos, roles del sistema y 3 usuarios iniciales

---

## Fase 2 — Autenticación, Roles Dinámicos y Permisos Granulares

- [x] Middleware `CheckModulo` (`modulo:codigo_modulo`): evalúa si el usuario en sesión es SuperAdmin, revisa excepciones en `user_modulo` o verifica los módulos asignados a su `rol_id`
- [x] Protección inmutable de SuperAdmin (`is_superadmin = true`): ningún administrador puede eliminar ni degradar la cuenta padre
- [x] `UserController` y `RolController`: gestión completa de usuarios y roles dinámicos
- [x] Jerarquía de seguridad: usuarios no pueden eliminarse a sí mismos ni alterar cuentas de nivel superior
- [x] Vistas: `users/index.blade.php`, `users/create.blade.php`, `users/edit.blade.php`, `roles/index.blade.php`, `roles/create.blade.php`, `roles/edit.blade.php` con tooltips informativos y bloqueos de seguridad

---

## Fase 3 — Módulos maestros (catálogo)

- [x] `ProveedorController` resource + vistas `proveedores/`
- [x] `ClienteController` resource + vistas `clientes/`
- [x] `ConfiguracionController`: vista `configuracion/edit.blade.php` con edición de IGV, retenciones, comisiones y datos institucionales (definidos como valores predeterminados para nuevos documentos)
- [x] `ProductoController` resource + validación (`puede_venderse`/`puede_comprarse`)
- [x] `productos/index.blade.php` y `productos/show.blade.php` con pestañas (`DETALLES`, `INVENTARIO`, `OPCIONES`, `PROVEEDORES`, `PRESENTACIONES`) en paleta unificada Slate/Cyan
- [x] `MovimientoInventarioController`: kardex por producto y ajustes manuales de inventario
- [x] Validación de stock y kardex en servicio transaccional con errores de validación amigables

---

## Fase 4 — Órdenes de compra del cliente

- [x] `OrdenCompraClienteController` resource
- [x] `ordenes-compra-cliente/index.blade.php`: listado filtrable por estado y cliente
- [x] `ordenes-compra-cliente/create.blade.php`: cliente, número de OC, fecha de recepción, subida de PDF/imagen
- [x] `ordenes-compra-cliente/show.blade.php`: vista estructurada en pestañas (`DATOS DE LA ORDEN`, `DOCUMENTO ADJUNTO`, `DOCS. DERIVADOS`, `ACCIONES`) y botones "Generar cotización" / "Generar venta"
- [x] Diferenciación formal de serie: asignación de serie `OCC01` en `series_documentos` para evitar confusión con compras a proveedores
- [x] Ruta dedicada de streaming de archivos adjuntos para visualización segura

---

## Fase 5 — Cotizaciones (módulo central)

- [x] `CotizacionController` resource
- [x] `cotizaciones/create.blade.php`: cabecera + panel colapsable de tasas impositivas y comisiones personalizables + tabla dinámica interactiva de líneas (JS plano) con cálculos en tiempo real
- [x] `cotizaciones/edit.blade.php`: formulario de edición con ajuste de tasas por versión de cotización
- [x] Guardado en transacción (`DB::transaction`) de cabecera, líneas y `cotizacion_resumen` con tasas del documento
- [x] `CotizacionResumenCalculator`: cálculo desacoplado con soporte para array dinámico de tasas (`pct_igv`, `pct_retencion`, `pct_comision_venta`, `pct_comision_ganancia`)
- [x] `cotizaciones/show.blade.php`: interfaz organizada en pestañas (`DATOS DEL PROYECTO`, `ÍTEMS`, `RESUMEN FINANCIERO`, `VISOR PDF`) con desglose de tasas aplicadas y métricas financieras de rentabilidad
- [x] Visor de PDF embebido interactivo en `cotizaciones/show.blade.php` (`<object>` / `<iframe>`)
- [x] Generación de PDF corporativo de alta gama (`cotizaciones/pdf.blade.php`) con hoja 1 en blanco (para portada) y hoja 2 membretada con 3 bloques de encabezado, tabla de ítems y condiciones de pago
- [x] Acción "Aprobar" cotización para habilitar su facturación / venta

---

## Fase 6 — Ventas

- [x] `VentaController` resource
- [x] `ventas/create.blade.php`: precarga desde cotización aprobada o venta directa
- [x] Validación de `puede_venderse = true` y stock suficiente disponible
- [x] Asignación transaccional de Serie `V001` y correlativo dinámico con `SerieDocumento::siguienteCodigo('venta')`
- [x] Generación automática de movimientos de inventario de tipo `salida` (dispara trigger de stock)
- [x] Al nacer de una cotización, actualiza `cotizaciones.estado = 'facturada'`
- [x] `ventas/show.blade.php`: interfaz organizada en pestañas (`DATOS DE LA VENTA`, `PRODUCTOS`, `VISOR PDF`) con visor PDF embebido interactivo y desglose de base imponible e IGV
- [x] Generación de PDF corporativo de Nota de Venta (`ventas/pdf.blade.php`) conforme al estándar institucional

---

## Fase 7 — Series, Correlativos y Comprobantes de Venta

- [x] `SerieDocumentoController`: administración completa de series y correlativo actual por tipo de documento (`series-documentos/index.blade.php`, `create.blade.php`, `edit.blade.php`)
- [x] Soporte para todos los tipos de documentos: `cotizacion` (`C001`), `venta` (`V001`), `orden_compra` (`OC01`), `orden_compra_cliente` (`OCC01`), `factura` (`F001`), `boleta` (`B001`), `nota_venta` (`NV01`), `guia_remision` (`T001`)
- [x] Control transaccional de concurrencia mediante `lockForUpdate()` en `SerieDocumento::siguienteCodigo()` para evitar correlativos duplicados
- [x] `ComprobanteVentaController`: emisión de Facturas, Boletas y Notas de Venta
- [x] `comprobantes/index-create-show-pdf.blade.php` (estándar corporativo + visor embebido)
- [x] `GuiaRemisionController`: formulario con datos de traslado (punto de partida/llegada, transportista, placa) y detalle
- [x] `guias-remision/index-create-show-pdf.blade.php` (estándar corporativo + visor embebido)
- [x] Acción "Anular" en comprobantes y guías

---

## Fase 8 — Compras a Proveedor

- [x] `OrdenCompraController` resource para pedidos de compra a proveedores (Serie `OC01`)
- [x] `ordenes-compra/create.blade.php`: proveedor + líneas dinámicas (producto, cantidad, precio de compra, cálculo automático de subtotal e IGV)
- [x] `ordenes-compra/show.blade.php`: interfaz organizada en pestañas (`DATOS DE LA O/C`, `BIENES`, `VISOR PDF`) con visor interactivo de PDF embebido y acciones de aprobación/anulación
- [x] Generación de PDF corporativo de Orden de Compra a Proveedor (`ordenes-compra/pdf.blade.php`)
- [x] `CompraController`: recepción de mercadería precargada desde O/C aprobada o compra directa
- [x] Al confirmar la compra, generar automáticamente los `movimientos_inventario` de tipo `entrada`
- [x] Actualizar `ordenes_compra.estado = 'recibida'`

---

## Fase 9 — Auditoría

- [x] `AuditoriaObserver` sobre los modelos clave (`Cotizacion`, `Venta`, `Compra`, `ComprobanteVenta`, `GuiaRemision`, `OrdenCompra`, `User`)
- [x] `auditoria/index.blade.php`: listado filtrable por tabla, usuario, acción y fecha (alineado al esquema real)

---

## Fase 10 — Dashboard

- [x] `DashboardController@index`: consultas agregadas en tiempo real (Ventas Totales, Margen Comercial S/, Margen % / Rentabilidad Promedio, Tasa de Conversión de Cotizaciones, Órdenes Pendientes) con filtros por Año, Mes y atajos rápidos
- [x] `dashboard.blade.php`: panel de Inteligencia Comercial de alto impacto con 5 gráficos interactivos Chart.js (Ventas Mensuales, Top Productos por Facturación, Medios/Condiciones de Pago, Tendencia Diaria de Ventas, Líneas de Negocio), Top 5 Clientes, Alertas de Stock Bajo y Últimas Ventas
- [x] Cobertura de pruebas unitarias y de integración `DashboardMetricsTest.php` validando cálculos y filtros temporales

---

## Fase 11 — Pulido y QA

- [x] Sistema de diseño Deep Slate + Neon Cyan + Neon Emerald con modo claro y oscuro totalmente responsivos
- [x] Homologación de íconos vectoriales SVG en sidebars y encabezados de todos los módulos
- [x] Sistema de navegación por pestañas (`show.blade.php`) estandarizado en Productos, Cotizaciones, Ventas, Órdenes de Compra a Proveedores y O/C de Clientes
- [x] Estandarización de PDFs corporativos (Cotizaciones, Notas de Venta, Órdenes de Compra)
- [x] Cobertura de pruebas automatizadas completa con 40 tests unitarios y de integración pasando al 100%
- [x] Pruebas end-to-end de flujo comercial completo (Venta → Comprobante → Guía, con PDFs y anulaciones)
- [x] Pruebas de flujo de compra (Orden de Compra → Recepción de Compra → kardex de entrada)
- [x] Verificación de seguridad de rutas y permisos (403 para rol ventas en comprobantes/compras/guías; smoke test ampliado)

---

## Estándar Oficial de Diseño y Diagramación para Documentos PDF

Todos los documentos generados e impresos en PDF (Cotizaciones, Órdenes de Compra a Proveedores, Ventas, Comprobantes de Venta y Guías de Remisión) **utilizan estrictamente la misma estructura de diseño, paleta y reglas de diagramación**:

### 1. Reglas de Márgenes y Aireación (CSS Global)
- **Tamaño y márgenes de página:**
  ```css
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
  }
  ```

### 2. Estructura Visual Unificada de Documentos
1. **Encabezado Superior (3 Bloques):**
   - **Izquierda (25%):** Logo de la empresa en base64.
   - **Centro (40%):** Membrete institucional con razón social, RUC, dirección, teléfonos, correos y web desde `Configuracion`.
   - **Derecha (35%):** Recuadro con borde `#0076a8`, indicando RUC de la empresa, tipo de documento y correlativo oficial (`C001-X`, `V001-X`, `OC01-X`, `F001-X`).
2. **Bloque de Datos del Destinatario (Cliente / Proveedor):** Recuadro cerrado con borde `#d1d5db` en 2 columnas equilibradas.
3. **Tabla de Ítems / Presupuesto:** Cabecera en azul corporativo (`#0076a8`), texto blanco en mayúsculas, celdas con espaciado holgado `8px 10px`.
4. **Totales y Monto en Letras:** Monto en letras a la izquierda y tabla de `SUBTOTAL`, `IGV` y `TOTAL` a la derecha.
5. **Bloque Final Estructurado (`.bloque-final`):** Encabezado `#0076a8` con condiciones bancarias e instrucciones.

### 3. Documentos implementados bajo este estándar:
- [x] **Cotizaciones:** `resources/views/cotizaciones/pdf.blade.php`
- [x] **Ventas / Notas de Venta:** `resources/views/ventas/pdf.blade.php`
- [x] **Órdenes de Compra a Proveedor:** `resources/views/ordenes-compra/pdf.blade.php`
- [x] **Comprobantes de Venta (Facturas / Boletas):** `resources/views/comprobantes/pdf.blade.php`
- [x] **Guías de Remisión:** `resources/views/guias-remision/pdf.blade.php`
