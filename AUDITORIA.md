# Auditoría del ERP

> **Estado verificado el 5 de octubre de 2026:** la sección 8 contiene el estado vigente de los hallazgos. Las secciones 1–7 conservan el diagnóstico histórico y sus recomendaciones; no deben leerse como una lista de fallos aún abiertos sin consultar la sección 8.

**Fecha:** 3 de octubre de 2026. **Alcance:** estado actual del directorio de trabajo, incluidos cambios y archivos aún no versionados. Revisión estática, pruebas con SQLite en memoria y comprobaciones de dependencias. No se alteró código ni datos del proyecto.

## 1. Resumen ejecutivo

El sistema cubre maestros, cotizaciones, ventas, compras, inventario, comprobantes y guías en un monolito Laravel. Las altas principales usan transacciones, hay permisos por módulo, autenticación con límite de intentos y paginación en los listados principales. Sin embargo, las reglas de autorización y de transición de documentos dejan vías para alterar privilegios y registrar operaciones inconsistentes. La suite no reproduce fielmente el stock de MySQL porque sus disparadores se omiten en SQLite.

**Cinco riesgos principales:** (1) un usuario con acceso al módulo `usuarios` puede asignarse un rol más privilegiado; (2) una plantilla Blade editable desde configuración se ejecuta en el servidor; (3) se puede registrar una venta vinculada a una cotización no aprobada o de otro cliente; (4) cotización, venta y comprobante aplican bases imponibles incompatibles; (5) el stock puede modificarse directamente sin movimiento de kardex.

| Área | Nota estimada /10 | Fundamento |
|---|---:|---|
| Seguridad | 4 | Control por módulo presente, pero sin límite de jerarquía en creación/edición de usuarios y con ejecución de plantilla configurable. |
| Flujos de negocio | 3 | Transacciones en altas; faltan invariantes entre documentos, stock y cálculo tributario. |
| Diseño y calidad | 5 | Estructura Laravel clara y pruebas en verde; lógica de dominio concentrada en controladores y estilo no conforme a Pint. |
| Duplicidades | 5 | Cálculos, consultas de PDF y reglas de permisos repetidos. |
| Arquitectura y operación | 4 | Auditoría parcial y diferencias relevantes entre SQLite y MySQL. |

### Comprobaciones ejecutadas

- `php -l` sobre PHP de `app`, `routes`, `database` y `tests`: sin errores de sintaxis.
- `vendor/bin/phpunit --do-not-cache-result`, con SQLite `:memory:` y vistas compiladas fuera del repositorio: **49 pruebas aprobadas, 238 aserciones** en la ejecución final. Una ejecución previa dio 48/49 (233 aserciones) porque `DashboardMetricsTest` aún esperaba un rótulo antiguo; el archivo cambió durante la auditoría, y se repitieron tanto el caso como la suite completa sobre el estado posterior.
- `vendor/bin/pint --test`: salida 1; **35 archivos** requieren formato, entre ellos `CotizacionController.php`, `VentaController.php`, `UserController.php` y `routes/web.php`. No se aplicaron correcciones.
- `npm audit --offline --json`: **0 avisos** en la información disponible del lockfile; el modo sin conexión no garantiza conocimiento de avisos nuevos.
- `composer audit --locked --no-interaction --format=json`: **3 avisos** en dos paquetes: Laravel 13.26.1 (bajo) y CommonMark 2.10.0 (uno medio y uno alto). Referencias: [Laravel XSS con debug](https://github.com/advisories/GHSA-jh5r-qr3c-85q8), [CommonMark HTML](https://github.com/advisories/GHSA-97jj-33gv-5xf9), [CommonMark DoS](https://github.com/advisories/GHSA-3q6v-r5mr-hxv8).
- No hay scripts de lint o type-check de JavaScript en `package.json:5-8`, ni analizador estático PHP configurado en `composer.json:17-29`; el chequeo de estilo PHP disponible fue Pint.

## 2. Mapa de arquitectura

1. Aplicación monolítica Laravel 13 / PHP 8.3 con Eloquent (`composer.json:9-16`).
2. `public/index.php` atiende HTTP y `artisan` provee consola.
3. `routes/web.php:27-145` concentra rutas de sesión y recursos; `routes/auth.php:14-59` configura autenticación.
4. `bootstrap/app.php:10-25` registra rutas, middleware `rol`/`modulo` y formato JSON para solicitudes que lo esperan.
5. `app/Http/Middleware/CheckModuloPermiso.php:17-36` verifica acceso a cada grupo funcional.
6. `app/Models/User.php:66-122` combina superadmin, rol personalizado y excepciones individuales.
7. Los controladores de `app/Http/Controllers` implementan maestros, cotización, venta, compra, inventario, comprobantes y guías.
8. La única clase de servicio de dominio encontrada es `app/Services/CotizacionResumenCalculator.php`.
9. Las vistas Blade están organizadas por módulo en `resources/views`; Vite, Tailwind, Alpine y Chart.js sirven la interfaz (`package.json:5-24`).
10. `database/migrations` define documentos, detalles, relaciones, usuarios, permisos y auditoría.
11. El stock se modifica mediante disparador MySQL sobre movimientos (`database/migrations/2026_08_13_000017_create_movimientos_inventario_table.php:26-39`).
12. `app/Models/SerieDocumento.php:72-123` asigna correlativos a la mayoría de los documentos; cotizaciones usa otra vía.
13. `app/Providers/AppServiceProvider.php:29-32` conecta un observador de auditoría a ciertos modelos.
14. La configuración contempla SQLite y MySQL/MariaDB (`config/database.php:18-93`); las pruebas usan SQLite en memoria (`phpunit.xml:24-26`).
15. No se identificaron entidades o rutas de cobros, pagos, asientos, cierres, RR. HH. o tenant en el alcance revisado. Son límites funcionales actuales, no defectos confirmados por sí mismos.

### Flujos seguidos

| Flujo | UI → ruta → lógica → persistencia |
|---|---|
| Cotización → venta → comprobante | `resources/views/cotizaciones/create.blade.php:188,398` → `routes/web.php:79-106` → `CotizacionController::store`, `VentaController::store`, `ComprobanteVentaController::store` → `cotizaciones`, `ventas`, `venta_detalle`, `movimientos_inventario`, `comprobantes_venta`. |
| Orden a proveedor → recepción | `resources/views/compras/create.blade.php:19-57` → `routes/web.php:109-122` → `OrdenCompraController::store`, `CompraController::store` → `ordenes_compra`, `compras`, detalles y movimientos. |
| Ajuste de stock | `resources/views/inventario/ajuste.blade.php` → `routes/web.php:67-71` → `MovimientoInventarioController::storeAjuste` → `movimientos_inventario` → disparador MySQL → `productos.stock_actual`. |
| Administración | `resources/views/users/*` → `routes/web.php:125-132` → `UserController`/`RolController` → `users`, `roles`, tablas Ipivote. |

## 3. Hallazgos (ordenados por severidad)

### Crítica

**SEC-01 — Escalada de privilegios mediante `rol_id` y permisos propios**  
**Severidad: Crítica.**  
**Ubicación:** `routes/web.php:125-132`; `app/Http/Controllers/UserController.php:126-160`; `app/Models/User.php:66-106`; `database/seeders/RolesAndModulosSeeder.php:133-152`.  
**Descripción e impacto:** el middleware exige únicamente acceso al módulo `usuarios`. `update` permite editar la cuenta propia si aún no es superadmin, acepta cualquier `rol_id` existente y sincroniza excepciones de módulos sin una comprobación de jerarquía. Un usuario con ese módulo puede asignarse el rol `superadmin` (que el seeder dota de todos los módulos) o permitirse `configuracion`, obteniendo capacidades administrativas fuera de la intención del rol original. La protección de `is_superadmin` no cubre `rol_id` ni `modulos`.  
**Comprobación:** en una base de prueba, autenticar un usuario no superadmin con módulo `usuarios`; enviar `PUT /users/{su-id}` con sus campos válidos y `rol_id` del rol `superadmin`, o `modulos_personalizados=1` más los IDs de módulos permitidos; consultar `/configuracion`.  
**Corrección:** exigir una política de administración de usuarios, denegar cambios de rol/permisos propios y limitar roles/módulos asignables según el actor. Validar los IDs de cada elemento del arreglo y escribir usuario y pivotes en una sola transacción. **Esfuerzo: M.**

### Alta

**SEC-02 — Ejecución de Blade editable desde la configuración**  
**Severidad: Alta.**  
**Ubicación:** `app/Http/Controllers/ConfiguracionController.php:39-46`; `app/Http/Controllers/CotizacionController.php:325-332`; `routes/web.php:134-140`.  
**Descripción e impacto:** `pdf_template_cotizacion` acepta cualquier cadena y `Blade::render` la compila al solicitar el PDF. Blade admite directivas PHP; una cuenta con acceso a `configuracion` puede ejecutar código del servidor al guardar la plantilla y visitar una cotización. La exposición depende de quién reciba ese permiso; SEC-01 amplía su alcance.  
**Comprobación:** en un entorno aislado, guardar como plantilla una directiva inocua `@php echo 2 + 2; @endphp` y consultar `/cotizaciones/{id}/pdf?html=1`; aparece `4`. No probar comandos ni escrituras en producción.  
**Corrección:** sustituir la plantilla ejecutable por un formato de presentación de variables permitidas o una plantilla Blade fija en código; migrar/validar las plantillas ya guardadas antes de habilitar su edición. **Esfuerzo: M.**

**FLJ-01 — Una venta puede vincularse a una cotización o pedido ajeno/inválido**  
**Severidad: Alta.**  
**Ubicación:** `resources/views/ventas/create.blade.php:63-94`; `app/Http/Controllers/VentaController.php:46-60,76-85,113-162`.  
**Descripción e impacto:** `create` comprueba que la cotización esté aprobada, pero `store` solo valida que los IDs existan. No comprueba estado, coincidencia de cliente, líneas ni uso previo. Un POST manipulado puede registrar una venta para el cliente A con `cotizacion_id` del cliente B o una cotización pendiente; después cambia ésta a `facturada`. También puede reutilizar la misma cotización. Esto falsea la trazabilidad comercial y las relaciones de facturación.  
**Comprobación:** crear cotización pendiente del cliente B y enviar directamente `POST /ventas` con `cliente_id` del cliente A, ese `cotizacion_id` y una línea válida. Inspeccionar venta y estado de la cotización en la base de prueba.  
**Corrección:** dentro de la transacción, bloquear la cotización/pedido, exigir estado esperado, cliente coincidente y unicidad de conversión; decidir explícitamente si se permiten diferencias de líneas/precios y registrarlas. **Esfuerzo: M.**

**FLJ-02 — Bases de precio e IGV incompatibles entre cotización, venta y factura**  
**Severidad: Alta.**  
**Ubicación:** `app/Services/CotizacionResumenCalculator.php:16-37`; `resources/views/ventas/create.blade.php:25`; `app/Http/Controllers/VentaController.php:105-125`; `app/Http/Controllers/ComprobanteVentaController.php:81-91`; `resources/views/cotizaciones/create.blade.php:512-527`.  
**Descripción e impacto:** la cotización trata `precio_venta_unit` como importe sin IGV y añade la tasa configurable. La vista de venta precarga ese mismo precio, pero `VentaController` lo trata como importe **con** IGV (`total = suma de líneas; subtotal = total / 1.18`). El comprobante hereda esos importes. Para una línea de S/100 e IGV 18%, la cotización da subtotal 100 + IGV 18, mientras venta/factura resultan total 100, subtotal 84.75 e IGV 15.25. Además, venta y vista usan 18% fijo aunque la cotización acepta otra tasa. Impacto: subfacturación y tributos incorrectos.  
**Comprobación:** cotizar una unidad a S/100, aprobar, abrir “generar venta” y conservar el precio precargado; comparar resumen de cotización, venta y comprobante.  
**Corrección:** definir y documentar si el precio unitario es neto o bruto; transferir la misma base y tasa a la venta, calcular importes con aritmética decimal y guardar el snapshot fiscal del documento. Compartir un calculador tributario para UI y servidor. **Esfuerzo: M.**

**FLJ-03 — Edición directa de `stock_actual` evita el kardex**  
**Severidad: Alta.**  
**Ubicación:** `app/Http/Controllers/ProductoController.php:133-150`; `database/migrations/2026_08_13_000017_create_movimientos_inventario_table.php:26-39`; `app/Http/Controllers/MovimientoInventarioController.php:47-68`.  
**Descripción e impacto:** `ProductoController::update` acepta y persiste `stock_actual` como cualquier atributo del producto. El disparador de stock solo actúa al insertar un movimiento; editar el producto altera el saldo sin entrada en kardex ni motivo/usuario de ajuste. Los reportes de existencias dejan de cuadrar con los movimientos.  
**Comprobación:** en una base de prueba, anotar stock y número de movimientos; actualizar un producto cambiando solo `stock_actual`; el saldo cambia y el kardex no.  
**Corrección:** retirar `stock_actual` del formulario/DTO de edición; inicializaciones y correcciones deben crear movimientos en una operación transaccional, con permiso específico. **Esfuerzo: S.**

**FLJ-04 — Recepción de compra no concilia líneas con la orden y permite dobles ingresos**  
**Severidad: Alta.**  
**Ubicación:** `resources/views/compras/create.blade.php:5-57`; `app/Http/Controllers/CompraController.php:65-98,100-132`.  
**Descripción e impacto:** `store` comprueba proveedor y estado de la orden, pero acepta cualquier producto, cantidad y precio; no compara con `orden_compra_detalle`. La comprobación de estado ocurre antes de la transacción y el cambio a `recibida` es incondicional, de modo que dos solicitudes concurrentes que leyeron `aprobada` pueden ingresar stock dos veces. También una sola recepción puede declarar mercancía no pedida.  
**Comprobación:** enviar `POST /compras` para una orden aprobada con una línea distinta a la aprobada; verificar que se graba la compra y el movimiento. Para la carrera, lanzar dos POST simultáneos en MySQL de prueba; **por verificar** su resultado efectivo bajo la configuración de aislamiento del despliegue.  
**Corrección:** validar diferencias permitidas frente a cada línea aprobada, bloquear la orden dentro de la transacción, hacer transición condicional `aprobada → recibida` y usar una restricción/idempotencia para la recepción única o cantidades pendientes. **Esfuerzo: M.**

**FLJ-05 — Dos comprobantes vigentes pueden emitirse para una misma venta por carrera**  
**Severidad: Alta.**  
**Ubicación:** `app/Http/Controllers/ComprobanteVentaController.php:70-91`; `database/migrations/2026_08_13_000018_create_comprobantes_venta_table.php:13-26`.  
**Descripción e impacto:** la consulta que busca un comprobante `emitido` se hace antes de la transacción. La base solo tiene unicidad de `tipo, serie, numero`, no de venta vigente. Dos solicitudes pueden superar la comprobación y crear facturas distintas para la misma venta. El caso secuencial está cubierto; el concurrente queda **por verificar** con MySQL real.  
**Comprobación:** emitir dos POST simultáneos sobre una venta sin comprobante y contar `comprobantes_venta` con `estado=emitido` para ese `venta_id`.  
**Corrección:** bloquear la venta dentro de la transacción y añadir una garantía de idempotencia/unicidad del comprobante vigente a nivel de base o modelo de emisión. **Esfuerzo: M.**

**FLJ-06 — Correlativo de cotización sin unicidad de base**  
**Severidad: Alta.**  
**Ubicación:** `app/Http/Controllers/CotizacionController.php:73-76,112-118`; `database/migrations/2026_08_24_000001_add_corporate_fields_to_cotizaciones_table.php:25`; `app/Models/SerieDocumento.php:72-123`.  
**Descripción e impacto:** las cotizaciones calculan `MAX(correlativo)+1` aparte del asignador central. `lockForUpdate()` sobre la consulta agregada no ofrece una garantía documentada para la primera fila o carreras entre transacciones, y `(serie, correlativo)` es índice normal, no único. Pueden emitirse códigos repetidos; el escenario concurrente concreto queda **por verificar** en MySQL.  
**Comprobación:** crear cotizaciones en paralelo cuando `C001` está vacía o con tráfico alto y buscar duplicados por `serie, correlativo`.  
**Corrección:** utilizar `SerieDocumento::siguienteCodigo('cotizacion')` dentro de la transacción y añadir índice único `(serie, correlativo)` tras sanear posibles duplicados; tratar colisiones con reintento limitado. **Esfuerzo: M.**

### Media

**FLJ-07 — La verificación de stock y su actualización no son atómicas**  
**Severidad: Media.**  
**Ubicación:** `app/Http/Controllers/VentaController.php:87-100,113-151`; `database/migrations/2026_08_13_000024_create_movimiento_valida_trigger.php:20-26`; `database/migrations/2026_08_13_000017_create_movimientos_inventario_table.php:28-37`.  
**Descripción e impacto:** el controlador lee stock antes de la transacción; el disparador `BEFORE INSERT` vuelve a hacer un `SELECT` sin bloqueo y otro disparador lo descuenta después. Entre lecturas y descuento puede intervenir otra venta. **Por verificar:** si dos ventas concurrentes dejan stock negativo en la versión/configuración MySQL de producción; no se hizo una prueba concurrente. El diseño carece de una actualización condicional atómica del saldo.  
**Comprobación:** en MySQL de prueba, partir de una unidad y lanzar dos ventas de una unidad en sesiones simultáneas; comprobar saldo y movimientos.  
**Corrección:** reservar stock mediante `UPDATE productos SET stock_actual = stock_actual - ? WHERE id = ? AND stock_actual >= ?`, comprobar filas afectadas y guardar detalle/movimiento en la misma transacción; evitar doble actualización desde disparador. **Esfuerzo: L.**

**SEC-03 — Auditoría incompleta y fallos silenciosos**  
**Severidad: Media.**  
**Ubicación:** `app/Providers/AppServiceProvider.php:29-32`; `app/Observers/AuditoriaObserver.php:11-44`; `app/Http/Controllers/VentaController.php:154-163`; `app/Http/Controllers/CompraController.php:132`; `app/Http/Controllers/ProductoController.php:133-150`.  
**Descripción e impacto:** el observador solo maneja `created`/`updated`, solo en siete modelos, y omite solicitudes sin `Auth::id()`. Las actualizaciones por constructor de consultas (`Cotizacion::where(...)->update`, `OrdenCompra::where(...)->update`) no disparan eventos Eloquent; cambios de stock y eliminaciones tampoco quedan registrados. Además, cualquier excepción de auditoría se descarta. Un registro financiero/comercial puede cambiar sin traza completa.  
**Comprobación:** en base de prueba, generar venta desde cotización y comprobar ausencia de evento de edición de la cotización; modificar stock desde producto y consultar `auditoria`.  
**Corrección:** establecer eventos de dominio o un servicio transaccional de auditoría que almacene actor, antes/después, motivo y correlación; cubrir borrados y operaciones masivas; alertar los fallos en vez de descartarlos. **Esfuerzo: L.**

**FLJ-08 — Pruebas SQLite no verifican el efecto real del stock MySQL**  
**Severidad: Media.**  
**Ubicación:** `phpunit.xml:24-26`; `database/migrations/2026_08_13_000017_create_movimientos_inventario_table.php:26-39`; `tests/Feature/FlujosComercialCompraTest.php:61-79`.  
**Descripción e impacto:** el disparador que modifica `stock_actual` solo se crea en MySQL; el test de venta solo comprueba el saldo cuando el driver es MySQL. Con SQLite, una venta inserta movimiento pero deja el saldo igual. Por ello la suite verde no prueba la condición decisiva de stock y podría ocultar regresiones.  
**Comprobación:** ejecutar el flujo con SQLite y consultar `productos.stock_actual` tras la venta; comparar con MySQL migrado.  
**Corrección:** llevar la actualización de stock a un servicio probado en ambos drivers y reservar pruebas de integración MySQL para restricciones/disparadores que permanezcan en base. **Esfuerzo: M.**

**SEC-04 — Paquetes PHP bloqueados con avisos de seguridad**  
**Severidad: Media.**  
**Ubicación:** `composer.lock:1290-1291,1706-1707`; `config/app.php:42`.  
**Descripción e impacto:** `composer audit --locked` detecta Laravel 13.26.1 afectado por XSS de la página de depuración si `APP_DEBUG=true`; la versión corregida es 13.30.0. También detecta CommonMark 2.10.0 afectado por bypass de HTML deshabilitado y degradación cuadrática de tablas Markdown. La ruta de explotación de CommonMark en esta aplicación **no se verificó**; el paquete llega como dependencia del framework. En `DEPLOY-CPANEL.md:69-70` se prescribe `APP_DEBUG=false` para producción, pero ello debe contrastarse con el entorno desplegado.  
**Comprobación:** repetir `composer audit --locked`; revisar la versión en el lockfile y la configuración de producción.  
**Corrección:** actualizar framework y CommonMark a versiones corregidas compatibles, ejecutar pruebas y confirmar depuración desactivada en producción. **Esfuerzo: S/M.**

**FLJ-09 — El correlativo de una serie puede retroceder manualmente**  
**Severidad: Media.**  
**Ubicación:** `app/Http/Controllers/SerieDocumentoController.php:88-124`; `app/Models/SerieDocumento.php:109-123`; `database/migrations/2026_08_26_181000_add_serie_correlativo_to_ventas_and_ordenes_compra.php:19,33`.  
**Descripción e impacto:** el endpoint de edición acepta cualquier `correlativo_actual >= 0` y lo persiste, incluso si ya se emitieron documentos con números superiores. El siguiente documento reutiliza un correlativo. Ventas y órdenes de compra solo tienen índices no únicos; comprobantes y guías podrían fallar por sus restricciones únicas.  
**Comprobación:** emitir dos ventas con la serie, editar `correlativo_actual` a 0 y emitir otra; comparar códigos.  
**Corrección:** impedir reducciones por debajo del mayor correlativo emitido, inmovilizar series con documentos o instrumentar una operación de corrección excepcional auditada; añadir unicidad donde falte. **Esfuerzo: M.**

**FLJ-10 — Archivos de imagen se borran antes de confirmar la escritura de datos**  
**Severidad: Media.**  
**Ubicación:** `app/Support/ImagenUpload.php:26-40,47-52`; `app/Http/Controllers/ProductoController.php:147-150,180-182` (mismo patrón en `ClienteController` y `ProveedorController`).  
**Descripción e impacto:** al sustituir una foto se borra la antigua antes de que `update` confirme; al eliminar producto se borra la imagen antes de `delete`, que puede fallar por claves foráneas si el producto tiene detalles de documentos. Un fallo deja referencias a imágenes inexistentes; una creación fallida puede dejar un archivo nuevo huérfano.  
**Comprobación:** en base de prueba, adjuntar imagen a un producto usado por una venta e intentar eliminarlo; comprobar rechazo de FK y pérdida del archivo anterior.  
**Corrección:** persistir primero, borrar el archivo antiguo después de `commit` y limpiar el nuevo si hay rollback; rechazar antes el borrado de entidades referenciadas. **Esfuerzo: M.**

**DUP-01 — Cálculos y reglas tributarias duplicados**  
**Severidad: Media.**  
**Ubicación:** `app/Services/CotizacionResumenCalculator.php:16-44`; `app/Http/Controllers/VentaController.php:105-111`; `app/Http/Controllers/OrdenCompraController.php:78-86`; `resources/views/cotizaciones/create.blade.php:512-527`.  
**Descripción e impacto:** subtotal, IGV, totales y redondeo se calculan por separado en servicio, controladores y JavaScript. Ya presentan una divergencia real (FLJ-02); los importes PHP se calculan con `float`, aunque la base usa `decimal(12,2)`, lo que dificulta conciliar redondeos al ampliar reglas fiscales.  
**Comprobación:** comparar el tratamiento de S/100 en los cuatro puntos citados y probar tasas distintas de 18%.  
**Corrección:** consolidar dinero/impuestos en un servicio de dominio con unidades mínimas o biblioteca decimal y contrato explícito de precio neto/bruto; dejar JavaScript como previsualización de la misma regla y probar casos de redondeo. **Esfuerzo: L.**

### Baja

**DIS-01 — El estilo PHP no supera la comprobación de Pint**  
**Severidad: Baja.**  
**Ubicación:** `app/Http/Controllers/UserController.php:11`, `app/Http/Controllers/OrdenCompraController.php:6-12`, entre otros.  
**Descripción e impacto:** `vendor/bin/pint --test` señaló 35 archivos. Por ejemplo, `UserController` conserva un import de `Str` sin uso y `OrdenCompraController` imports sin uso, junto a múltiples diferencias de espacios/orden de imports. No causa un fallo funcional observado, pero impide utilizar el formateador como puerta de calidad y aumenta el ruido en revisiones.  
**Comprobación:** ejecutar `vendor/bin/pint --test`; devuelve salida 1 e identifica archivos y reglas.  
**Corrección:** aplicar el formato en una revisión separada de cambios funcionales y añadir comprobación de solo lectura en CI. **Esfuerzo: S.**

## 4. Duplicidades y consolidación

| Grupo | Evidencia | Consolidación propuesta |
|---|---|---|
| Impuestos y totales | `CotizacionResumenCalculator.php:16-44`; `VentaController.php:105-111`; `OrdenCompraController.php:78-86`; `cotizaciones/create.blade.php:512-527` | Servicio de precios/impuestos con contrato de neto/bruto, tasa y redondeo; cubrir el caso FLJ-02. |
| Asignación de correlativos | `CotizacionController.php:112-118`; `SerieDocumento.php:72-123` | Un único asignador transaccional por tipo y restricciones únicas en tablas de documentos. |
| Carga de detalle y PDF | `VentaController.php:173-224`; `ComprobanteVentaController.php:95-159`; `OrdenCompraController.php:132-197` | Consultas de lectura reutilizables por documento y un renderizador PDF con configuración controlada; mantener plantillas específicas por documento. |
| Presentaciones de compra/venta | `ProductoController.php:215-321` | Extraer operaciones comunes a un servicio con tipo de presentación; preservar validaciones y tablas separadas si el dominio lo exige. |
| Gestión de imágenes | `ImagenUpload.php:23-52`; `ProductoController.php:147-150`; controladores de clientes/proveedores | Mantener el helper común, pero orquestar persistencia y borrado tras commit desde un servicio de adjuntos. |
| Reglas de permisos | `User.php:66-122`; `UserController.php:61-160`; `RolController.php:26-77` | Políticas para asignación de roles, módulos y gestión de cuentas, independientes de la visibilidad de cada módulo. |

## 5. Plan de acción priorizado

### Hacer ya

1. Cerrar SEC-01: bloquear autoasignación de rol/módulos y limitar jerarquía desde servidor.
2. Retirar la ejecución de plantillas editables (SEC-02) o deshabilitar temporalmente su edición hasta sustituirla.
3. Corregir invariantes de venta y conciliación fiscal (FLJ-01, FLJ-02) antes de emitir nuevos comprobantes derivados.
4. Impedir edición directa del saldo de stock (FLJ-03) y preservar kardex.

### Próximo sprint

1. Conciliar recepciones y cerrar carreras de comprobantes/cotizaciones (FLJ-04 a FLJ-06); hacer pruebas concurrentes en MySQL para los escenarios marcados «Por verificar».
2. Centralizar cálculo de importes y asignación de correlativos (DUP-01), con restricciones únicas y migración de datos existentes.
3. Actualizar dependencias señaladas por Composer (SEC-04) y normalizar el estilo PHP (DIS-01).
4. Hacer irreversible el correlativo de series con documentos y reparar gestión de imágenes (FLJ-09, FLJ-10).

### Backlog / refactor mayor

1. Extraer servicios transaccionales de venta, recepción, inventario, facturación y auditoría; dejar controladores como orquestadores HTTP.
2. Unificar la semántica de precio neto/bruto, moneda y redondeo con tipos decimales; verificar resultados de documentos históricos antes de recalcular.
3. Crear una suite de integración MySQL para disparadores, bloqueo, restricciones y concurrencia; mantener tests rápidos SQLite para lógica independiente del motor.
4. Diseñar traza de cambios completa, incluyendo borrado, ajustes de stock, actor y valores anteriores/posteriores; alertar fallos de auditoría.
5. Si el producto incorpora cobros, pagos, notas de crédito, contabilidad, cierres o multiempresa, definir sus invariantes y límites de dominio antes de agregar rutas y tablas; hoy no hay evidencia de esos módulos implementados.

## 6. Limitaciones

- No se ejecutaron pruebas de carga ni concurrencia sobre MySQL ni se consultó una base de producción. Las carreras descritas tienen código que permite la ventana, pero su efecto efectivo se marca «Por verificar» donde corresponde.
- La suite usa SQLite en memoria; no verifica disparadores ni columnas generadas MySQL. No se desplegó ni se emitieron documentos fiscales reales.
- No se inspeccionaron servicios externos de facturación electrónica, correo, pagos o infraestructura cPanel en ejecución; tampoco se encontró integración de dichos servicios en las rutas/controladores revisados.
- El repositorio ya presentaba numerosos archivos modificados y no versionados al inicio. Se auditó su estado actual sin atribuir esos cambios a esta auditoría. El `.env` local no está versionado (`git ls-files .env` no devolvió resultados); no se exponen sus secretos en este informe. La configuración real del despliegue no fue verificada.
- `npm audit` se ejecutó sin conexión; sus cero avisos no equivalen a una consulta en línea actual. La explotación de avisos CommonMark por entradas de esta aplicación permanece por verificar.
- Los componentes visuales y estilos se revisaron por estructura y prueba de dashboard, no mediante una evaluación manual de accesibilidad en navegador. No se encontró base suficiente para afirmar inconsistencias visuales concretas adicionales.

## 7. Verificación de idioma de vistas y mensajes (4 de octubre de 2026)

**Resultado:** no se cumple el requisito de mostrar todos los mensajes y vistas en español. Esta sección revisa el estado actual del directorio de trabajo y complementa la auditoría anterior; sus comprobaciones son posteriores a las de la sección 1. Se siguieron las rutas de acceso y perfil, sus vistas, las respuestas de autenticación/validación, los listados paginados y el componente de toast. Los mensajes de negocio propios de los controladores revisados están redactados en español. No se modificó código.

### Hallazgos por severidad

**IDI-01 — Errores automáticos, restablecimiento de clave y paginación en inglés.** **Severidad: Media.** **Ubicación:** `.env:7-8`, `config/app.php:81-83`, `app/Http/Requests/Auth/LoginRequest.php:48-50,71-75`, `app/Http/Controllers/Auth/PasswordResetLinkController.php:35-43`, `app/Http/Controllers/Auth/NewPasswordController.php:57-61`, `resources/views/clientes/index.blade.php:156` (mismo patrón en otros 13 listados). **Impacto:** la configuración activa usa `en` como idioma y respaldo; no hay catálogo `lang/es` en el proyecto. Laravel devuelve, por ejemplo, “These credentials do not match our records.” para credenciales inválidas y “The :attribute field is required.” para validaciones. La paginación muestra “Showing … results” y “Previous/Next”. Las respuestas y notificaciones de recuperación de contraseña heredan el inglés del framework. **Comprobación:** enviar credenciales erróneas a `/login`, omitir un campo requerido en un formulario y abrir un listado con más de una página; verificar también el mensaje al solicitar recuperación de clave. Los textos base se encuentran en `vendor/laravel/framework/src/Illuminate/Translation/lang/en/auth.php:16-18`, `validation.php:145` y `pagination.php:19-20`; el resumen de paginación se genera en `vendor/laravel/framework/src/Illuminate/Pagination/resources/views/tailwind.blade.php:32-42`. **Corrección:** configurar `APP_LOCALE=es` y `APP_FALLBACK_LOCALE=es`, publicar/mantener traducciones españolas de `auth`, `validation`, `passwords` y `pagination` y traducir `Showing`, `to`, `of`, `results`; agregar una prueba HTTP de error de acceso, validación, recuperación y paginación. **Esfuerzo: M.**

**IDI-02 — Vistas de autenticación y perfil contienen cadenas inglesas sin traducción.** **Severidad: Media.** **Ubicación:** `resources/views/auth/forgot-password.blade.php:3,14,21`, `resources/views/auth/reset-password.blade.php:10-35`, `resources/views/auth/confirm-password.blade.php:3-23`, `resources/views/auth/verify-email.blade.php:3-27`, `resources/views/profile/edit.blade.php:4`, `resources/views/profile/partials/update-profile-information-form.blade.php:4-60`, `update-password-form.blade.php:4-44`, `delete-user-form.blade.php:4-50`. **Impacto:** estas vistas usan claves literales inglesas dentro de `__()` y no existe un catálogo JSON que las traduzca; se muestran títulos, instrucciones, etiquetas, botones y confirmaciones en inglés, incluso después de guardar el perfil o la clave. Las rutas de recuperación están declaradas en `routes/auth.php:24-35` y la de perfil en `routes/web.php:41-44`. **Comprobación:** abrir `/forgot-password`, `/profile` y `/confirm-password`; se leen, por ejemplo, “Forgot your password?”, “Profile Information”, “Save” y “Delete Account”. La vista de verificación también contiene inglés, aunque el modelo `User` actual no implementa `MustVerifyEmail` (`app/Models/User.php:12-15`), por lo que su aparición en el flujo normal queda por verificar. **Corrección:** sustituir esas cadenas por español directamente o agregar sus equivalencias completas en `lang/es.json`; revisar también los estados de éxito y error. **Esfuerzo: S.**

**IDI-03 — Rótulos principales del ERP mezclan español e inglés.** **Severidad: Baja.** **Ubicación:** `resources/views/layouts/sidebar.blade.php:46`, `resources/views/layouts/navigation.blade.php:24`, `resources/views/dashboard.blade.php:10,262`, `resources/views/productos/show.blade.php:50,54`, `resources/views/inventario/kardex.blade.php:12`. **Impacto:** el menú y encabezado muestran “Dashboard”; inventario muestra “Stock Bajo”, “En Stock” y “Kardex & Control de Inventario”. La mezcla es visible en navegación y fichas y rompe la consistencia solicitada. Términos técnicos como PDF, SKU, RUC e IGV se consideraron siglas, no mensajes por traducir. **Comprobación:** abrir panel, ficha de producto y kardex. **Corrección:** adoptar un glosario visible: “Panel comercial”, “Existencias bajas”, “Disponible” y “Kardex y control de inventario”, y aplicarlo en las vistas compartidas y de módulo. **Esfuerzo: S.**

### Duplicidades y consolidación

| Grupo | Evidencia | Consolidación propuesta |
|---|---|---|
| Textos de autenticación repetidos | `auth/verify-email.blade.php:3-27`; `profile/partials/update-profile-information-form.blade.php:34-44` | Usar claves de traducción españolas compartidas para verificación, éxito y acciones de cuenta. |
| Paginación de listados | `clientes/index.blade.php:156`; `ventas/index.blade.php:116`; `users/index.blade.php:220` y otros listados | Una sola configuración/catálogo de traducción de Laravel para todos los `links()`. |
| Terminología de panel e inventario | `layouts/sidebar.blade.php:46`; `dashboard.blade.php:10,262`; `productos/show.blade.php:50,54` | Glosario de producto y etiquetas reutilizables para navegación y estados. |

### Plan de acción

- [x] **Hacer ya:** cambiar la configuración de idioma y completar el catálogo de errores de autenticación, validación, recuperación y paginación (IDI-01).
- [x] **Próximo sprint:** traducir las vistas de cuenta y perfil, con pruebas de las rutas alcanzables (IDI-02).
- [x] **Backlog / refactor mayor:** fijar el glosario de panel y existencias y aplicarlo a los rótulos visibles existentes (IDI-03). Al incorporar módulos nuevos, el glosario deberá mantenerse.

### Comprobaciones y límites de esta verificación

- `php -l` revisó **250 archivos PHP** de aplicación, rutas, vistas, configuración, migraciones y pruebas: **0 errores de sintaxis**.
- `php vendor/bin/pint --test`: **aprobado** en el estado actual. `npm audit --offline --json`: **0 vulnerabilidades en la información local disponible**. `composer audit --locked --no-interaction --format=json`: **0 avisos y 0 paquetes abandonados** al consultar el lockfile actual. Estos resultados sustituyen los resultados anteriores de Pint y Composer en la sección 1, pues el árbol cambió entre auditorías. No hay script de type-check en `package.json`.
- No se ejecutó la suite HTTP en esta revisión: compilar vistas y ejecutar tests puede crear archivos de caché, incompatible con la restricción de que el único archivo creado sea `AUDITORIA.md`. La comprobación del idioma activo se basó en `.env` y el código; no se inspeccionó un despliegue externo ni correos efectivamente enviados.
- No se encontró texto inglés fijo en el componente propio de toast (`public/js/toast-dialog.js:34-47,81-107,209`): sus botones por defecto, `aria-label` y aviso de formulario sin cambios están en español. Los títulos y contenidos se proporcionan desde cada vista/controlador y deben cubrirse en pruebas de interfaz al aplicar la traducción. Los mensajes del navegador, servicios externos y datos libres ingresados por usuarios quedan fuera de esta garantía.

## 8. Estado de implementación y verificación (5 de octubre de 2026)

La siguiente tabla marca lo **ya realizado en el árbol actual**, incluidas correcciones que estaban presentes antes de esta actualización. **Los 19 hallazgos están resueltos o implementados en el árbol actual.** «Implementado» significa que existe una corrección concreta en el código revisado; las limitaciones de pruebas se indican aparte. La sección 7 describe el estado **anterior** a la corrección de idioma.

| ID | Estado actual | Evidencia de implementación o pendiente |
|---|---|---|
| IDI-01 | **[x] Implementado y probado** | `config/app.php:81-83` y `.env:7-8` usan `es`; `lang/es/auth.php`, `validation.php`, `passwords.php`, `pagination.php` y `lang/es.json` traducen mensajes, paginación y correos. `tests/Feature/SpanishInterfaceTest.php` comprueba acceso, validación, paginación y notificación. |
| IDI-02 | **[x] Implementado y probado** | `lang/es.json` traduce las claves inglesas usadas por `resources/views/auth` y `resources/views/profile`; la prueba renderiza recuperación, restablecimiento, confirmación de contraseña, verificación de correo y perfil. |
| IDI-03 | **[x] Implementado y probado** | Menú y panel usan «Panel comercial»; producto e inventario usan «existencias» y «Kardex y control de inventario». Se actualizaron los rótulos «Email» a «Correo electrónico» en las vistas de clientes, proveedores y usuarios. `DashboardMetricsTest` comprueba el nuevo título. |
| SEC-01 | **[x] Implementado; verificación de código y suite** | `UserController.php:61-85,130-180,236-253` restringe administración, autoedición, asignación de roles/módulos y agrupa escritura en transacción; `User.php:139-158` limita a quién se puede gestionar. |
| SEC-02 | **[x] Implementado; verificación de código** | `ConfiguracionController.php:24-45` ya no acepta la plantilla PDF editable y no se encontró `Blade::render` en los controladores. El campo legado permanece en el modelo, sin ejecución detectada. |
| FLJ-01 | **[x] Implementado; verificación de código y suite** | `VentaController.php:101-145` bloquea y valida cotización/orden, cliente, estado, líneas y saldo antes de crear venta. |
| FLJ-02 | **[x] Implementado; verificación de código y suite** | `DocumentoFiscalCalculator.php:10-52` centraliza base, IGV y retención en céntimos; cotización, venta y comprobante lo usan. `DocumentosParcialesYTributosTest` cubre precios con IGV. |
| FLJ-03 | **[x] Implementado; verificación de código** | `ProductoController.php:145-166` no acepta modificar `stock_actual` en la edición y rechaza un cambio enviado manualmente; los movimientos se registran mediante `InventarioService`. |
| FLJ-04 | **[x] Implementado; verificación de código y suite** | `CompraController.php:98-145` bloquea la orden y concilia proveedor, productos, cantidades y precios pendientes. |
| FLJ-05 | **[x] Implementado y probado en MySQL** | `ComprobanteVentaController::store` bloquea la venta y hace una lectura bloqueante del comprobante vigente. La prueba concurrente descubrió que `exists()` reutilizaba una instantánea antigua bajo `REPEATABLE READ`; se sustituyó por `lockForUpdate()->first()`. Dos procesos sobre la misma venta dejan un solo comprobante emitido. |
| FLJ-06 | **[x] Implementado y probado en MySQL** | `CotizacionController` usa `SerieDocumento::siguienteCodigo`; la migración `2026_10_03_000003_secure_document_numbers_and_quote_totals.php` añade unicidad de serie/correlativo. Dos procesos que solicitan códigos de cotización reciben correlativos distintos. Aplicación en la base de despliegue: por verificar. |
| FLJ-07 | **[x] Implementado y probado en MySQL** | `InventarioService` bloquea el producto, comprueba saldo y actualiza existencias y movimiento dentro de la transacción. Dos salidas simultáneas de la última unidad dejan saldo cero y un solo movimiento. |
| SEC-03 | **[x] Implementado y probado** | `AuditoriaObserver.php:61-71` registra fallo crítico y propaga la excepción; `app/Models/Concerns/PersistsAuditably.php:7-23` envuelve escrituras y borrados auditados en transacción para que fallen con su auditoría. `DocumentosParcialesYTributosTest::test_error_de_auditoria_aborta_y_revierte_la_operacion` fuerza el error y comprueba rollback. |
| FLJ-08 | **[x] Implementado y probado en MySQL** | `InventarioService` actualiza saldo y kardex dentro de la transacción; la migración `2026_10_03_000002_move_stock_updates_to_application.php` elimina los triggers antiguos. `MySqlInventoryIntegrationTest` pasó en MySQL 8.4.3: confirmó ausencia de triggers y una sola actualización de stock por movimiento. |
| SEC-04 | **[x] Resuelto en el lockfile actual** | `composer audit --locked --no-interaction --format=json` devolvió 0 avisos y 0 abandonados. El informe inicial se basaba en una versión anterior de `composer.lock`. |
| FLJ-09 | **[x] Implementado; verificación de código** | `SerieDocumentoController.php:120-141` impide retroceder el correlativo y cambiar tipo/serie cuando ya hay documentos. |
| FLJ-10 | **[x] Implementado; verificación de código** | `ImagenUpload.php:42-88` revierte archivos nuevos si falla la transacción y borra archivos antiguos después de persistir o eliminar el modelo. |
| DUP-01 | **[x] Implementado y probado** | `DocumentoFiscalCalculator` comparte operaciones en céntimos; `CotizacionResumenCalculator.php` elimina `float` en costos y comisiones. `resources/js/cotizacion-calculator.js` centraliza la previsualización de alta/edición y alinea el redondeo con PHP. Hay pruebas PHP y Node para montos pequeños, IGV incluido y límites monetarios. |
| DIS-01 | **[x] Resuelto en el árbol actual** | `php vendor/bin/pint --test` pasó en esta actualización. |

### Comprobación final del 5 de octubre de 2026

- Tras revisar y corregir la implementación, `php vendor/bin/phpunit --do-not-cache-result`: **61 pruebas aprobadas, 328 aserciones; 2 pruebas MySQL omitidas** en la ejecución habitual porque `RUN_MYSQL_INTEGRATION_TESTS` no está habilitado.
- Con una instancia MySQL 8.4.3 temporal y una base aislada `sistema_erp_integration_test`, `php vendor/bin/phpunit --do-not-cache-result --filter MySqlInventoryIntegrationTest`: **2 pruebas aprobadas, 39 aserciones**. Cubren migraciones, ausencia de triggers de stock, actualización única y concurrencia de stock, comprobantes y asignación de correlativos de cotización.
- `npm run test:js`: **3 pruebas aprobadas**. `npm run build`: compilación Vite aprobada y recursos actualizados en `public/build`; el paquete anterior no incluía el calculador nuevo.
- `php vendor/bin/pint --test`: **aprobado** tras los cambios.
- `composer audit --locked --no-interaction --format=json`: **0 avisos y 0 paquetes abandonados**.
- `npm audit --offline --json`: **0 vulnerabilidades según los datos locales disponibles**; no confirma ausencia de avisos publicados después de la última actualización de esos datos.
- Se añadieron páginas de error en español en `resources/views/errors` para 401, 403, 404, 419, 429, 500 y 503, además de las vistas genéricas 4xx/5xx. La prueba de interfaz verifica 404.
- Los tests usan SQLite. No hubo navegación manual por cada rol ni envío real de correos; los textos aportados por servicios externos, datos ingresados por usuarios y el idioma configurado en otros despliegues siguen fuera de la verificación. En cada despliegue debe fijarse `APP_LOCALE=es` y `APP_FALLBACK_LOCALE=es` y reconstruirse la caché de configuración si existe.
- **Limitación operativa:** no se accedió a la base de despliegue ni se migró una copia representativa con datos históricos. La concurrencia se comprobó en los servicios/controlador y asignador de códigos mediante procesos PHP separados; sigue fuera del alcance una prueba HTTP completa con sesiones reales y la migración de producción.

### Revisión de las correcciones

- **Atomicidad de auditoría corregida:** `PersistsAuditably` ahora crea una transacción anidada también cuando existe una exterior. Así, capturar una excepción de auditoría no deja persistida la escritura sin su traza. La regresión cubre creación, edición y eliminación; se retiró el `DB::commit()` manual que alteraba el aislamiento de la prueba.
- **Protección de integración corregida:** `MySqlInventoryIntegrationTest::beforeRefreshingDatabase` comprueba el driver, el nombre configurado, su coincidencia con la base solicitada y `SELECT DATABASE()` antes de permitir que `RefreshDatabase` ejecute migraciones destructivas. La integración se ejecutó en MySQL temporal; `tests/Support/ConcurrentMySqlWorker.php` simula operaciones simultáneas desde conexiones independientes.
- **Carrera de comprobantes corregida:** la primera ejecución simultánea creó dos comprobantes para la misma venta. La validación `exists` abría una instantánea previa al bloqueo de la venta en MySQL `REPEATABLE READ`; la consulta bloqueante posterior ve el comprobante confirmado y rechaza la segunda emisión. La repetición de la prueba pasó.
- **Recursos de interfaz corregidos:** se reconstruyó el paquete servido por Laravel. La compilación previa en una carpeta temporal no actualizaba `public/build`, por lo que las vistas nuevas podían llamar funciones ausentes del paquete anterior.
