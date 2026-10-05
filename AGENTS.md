Actúa como un auditor senior de software especializado en sistemas ERP (seguridad, arquitectura y calidad de código). Vas a auditar este repositorio en MODO SOLO LECTURA: no modifiques, borres ni crees archivos de código. El único archivo que puedes crear es AUDITORIA.md en la raíz, con el informe final.



\## Contexto del proyecto

\- Tipo: ERP con los módulos \[ventas, compras, inventario, facturación, contabilidad, RRHH, etc.]

\- Stack: \[lenguaje/framework backend, frontend, base de datos, ORM]

\- Particularidades: \[multiempresa/multi-tenant, roles y permisos, integraciones, facturación electrónica, etc.]



\## Método de trabajo

1\. Primero mapea el proyecto: estructura de carpetas, módulos, puntos de entrada, modelos de datos, rutas/endpoints, capa de servicios y dependencias. Resume la arquitectura en 10-15 líneas antes de buscar problemas.

2\. Ejecuta lo que esté disponible sin modificar nada: linter, type-checker, tests y auditoría de dependencias (npm audit / pip-audit / composer audit, según el stack). Incluye los resultados relevantes.

3\. Revisa el código fuente a fondo, no solo por patrones de texto. Sigue los flujos completos de extremo a extremo (UI → API → servicio → base de datos).

4\. Solo reporta hallazgos que puedas respaldar con evidencia concreta (archivo y líneas). Si algo es una sospecha y no puedes confirmarlo, márcalo como "Por verificar". No inventes problemas ni rellenes.



\## Áreas de auditoría



\### 1. Seguridad

\- Autenticación y sesiones: manejo de tokens/JWT, expiración, hash de contraseñas, recuperación de cuenta, fuerza bruta.

\- Autorización: endpoints sin verificación de permisos, IDOR (acceder a registros de otro usuario/empresa cambiando un ID), escalada de privilegios, permisos validados solo en el frontend.

\- Aislamiento multiempresa/multi-tenant: consultas que no filtran por empresa o tenant.

\- Inyección (SQL, NoSQL, comandos, plantillas), XSS, CSRF, SSRF, path traversal, subida de archivos insegura, deserialización.

\- Mass assignment, exposición de datos sensibles en respuestas, logs o mensajes de error.

\- Secretos hardcodeados, configuración insegura (CORS, cabeceras, debug activo), archivos .env versionados.

\- Dependencias vulnerables o abandonadas.



\### 2. Errores en flujos de negocio

Analiza especialmente: cotización → pedido → facturación → cobro; compras → recepción → pago; movimientos de inventario; asientos contables; anulaciones, devoluciones y notas de crédito; cierres de periodo. Busca:

\- Operaciones multi-paso sin transacción (estados inconsistentes si falla a mitad).

\- Condiciones de carrera (doble envío, doble cobro, stock negativo, numeración duplicada de documentos).

\- Errores de redondeo, uso de float para dinero, cálculo incorrecto de impuestos, descuentos o conversión de moneda.

\- Transiciones de estado inválidas (editar o anular documentos ya cerrados/contabilizados).

\- Validaciones ausentes o solo en el cliente, manejo deficiente de nulos, fechas y zonas horarias.

\- Efectos colaterales no revertidos al cancelar o eliminar (borrado en cascada peligroso, falta de soft delete o auditoría).

\- Operaciones masivas o reportes sin paginación, consultas N+1, falta de índices.



\### 3. Consistencia de diseño y buenas prácticas

\- Consistencia de UI/UX: componentes, estilos, espaciados, colores, mensajes y patrones de formularios/tablas repetidos de formas distintas.

\- Consistencia de API: nomenclatura, formato de respuestas y errores, códigos HTTP, paginación y filtros.

\- Convenciones de código: nombres, estructura de carpetas, manejo de errores, logging, validación; identifica dónde se rompe la convención dominante.

\- Principios SOLID, separación de capas (lógica de negocio en controladores o vistas), acoplamiento y dependencias circulares.

\- Cobertura y calidad de pruebas en los flujos críticos.



\### 4. Redundancias y duplicidades

\- Duplicación literal y también semántica: funciones o componentes que hacen lo mismo con nombres o estructuras distintas, validaciones o cálculos (impuestos, totales, permisos) reimplementados en varios sitios.

\- Constantes, enums y reglas de negocio repetidas; consultas casi idénticas; DTOs/modelos solapados.

\- Código muerto, dependencias sin uso, archivos huérfanos, feature flags obsoletos.

\- Para cada grupo de duplicados, propone dónde consolidarlo.



\### 5. Mejoras a gran escala

\- Problemas de arquitectura que frenarán el crecimiento (modularización, límites entre dominios, eventos, colas para tareas pesadas).

\- Rendimiento y escalabilidad, observabilidad (logs, métricas, auditoría de cambios), manejo de configuración y despliegue.

\- Deuda técnica priorizada y un plan de refactor por fases.



\## Formato del informe (AUDITORIA.md)

1\. Resumen ejecutivo: estado general, 5 riesgos principales y puntuación estimada (1-10) por área.

2\. Mapa de arquitectura.

3\. Hallazgos, cada uno con:

&#x20;  - ID (SEC-01, FLJ-01, DIS-01, DUP-01, ARQ-01…)

&#x20;  - Severidad: Crítica / Alta / Media / Baja

&#x20;  - Ubicación: archivo:línea

&#x20;  - Descripción y por qué es un problema (impacto real en el negocio)

&#x20;  - Cómo reproducirlo o comprobarlo

&#x20;  - Corrección recomendada (con ejemplo de código breve cuando ayude)

&#x20;  - Esfuerzo estimado: S / M / L

4\. Lista de duplicidades con propuesta de consolidación.

5\. Plan de acción priorizado: "Hacer ya" (críticos), "Próximo sprint", "Backlog / refactor mayor".

6\. Limitaciones: qué no pudiste revisar o verificar.



Ordena los hallazgos por severidad. Prioriza calidad sobre cantidad: mejor 20 hallazgos reales y verificados que 100 genéricos.

