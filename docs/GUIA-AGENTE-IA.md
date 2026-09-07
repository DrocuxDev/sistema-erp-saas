# 🤖 Guía para el Agente IA — Despliegue de Proyectos Laravel en cPanel/VPS

> **Documento de retroalimentación y aprendizaje.**
> Dirigido a cualquier agente IA que realice despliegues de aplicaciones Laravel
> (o similares) en servidores cPanel/VPS. Léelo COMPLETO antes de ejecutar cualquier acción.

---

## 1. Principio fundamental: separación público / privado

Todo proyecto web tiene dos tipos de archivos:

| Tipo | Qué contiene | Dónde vive | Accesible por URL |
|---|---|---|---|
| **Público** | `index.php`, `.htaccess`, assets compilados (CSS/JS), imágenes, favicon | `public_html/` (o subcarpeta de ella) | ✅ Sí, cualquiera |
| **Privado** | Código fuente, `.env` (credenciales), `vendor/`, `storage/`, configuración | FUERA de `public_html` | ❌ Nunca |

**Regla de oro:** si un archivo contiene credenciales, lógica de negocio o puede
ejecutarse en el servidor, NO debe ser descargable por URL.

Un error clásico es subir TODO el proyecto dentro de `public_html/`. Eso expone el
`.env` (usuario y contraseña de la base de datos) a cualquiera que escriba
`dominio.com/.env` en el navegador.

---

## 2. Estructura correcta de despliegue (plantilla estándar)

```
/home/USUARIO_CPANEL/
├── mi-app/                          🔒 PRIVADO — todo el proyecto
│   ├── app/                         Código PHP de la aplicación
│   ├── bootstrap/                   Arranque del framework (+ cache/, con permisos 775)
│   ├── config/                      Configuración del framework
│   ├── database/                    Migraciones y seeders
│   ├── resources/                   Vistas Blade, fuentes JS/CSS sin compilar
│   ├── routes/                      Definición de rutas
│   ├── storage/                     Logs, cachés, uploads (permisos 775)
│   ├── vendor/                      Dependencias de Composer
│   ├── .env                         🔐 Credenciales — permisos 640
│   └── artisan                      CLI del framework
│
└── public_html/
    └── mi-app-publico/              🌐 PÚBLICO — SOLO el contenido de /public
        ├── index.php                Front controller (con rutas ajustadas)
        ├── .htaccess                Reglas de reescritura (con RewriteBase)
        ├── favicon.ico
        └── assets/  imagenes/ ...
```

Variantes válidas según el hosting:
- **Subcarpeta del dominio** (`dominio.com/erp/`): la más común en cPanel compartido.
- **Dominio/subdominio propio**: `public_html/` apunta directamente a `public/`,
  ideal si el ERP tiene su propio subdominio (ej: `erp.jhardsystex.com`).
- **Symlink**: si el hosting lo permite, `public_html/erp` → `~/mi-app/public`.
  Es lo más limpio, pero no todos los hostings compartidos lo permiten.

---

## 3. Los 3 puntos que rompen un despliegue en subdirectorio

Experiencia real (proyecto JHardSystex ERP, intento `/J-STARK/`): al subir el proyecto,
**ninguna ruta funcionaba**. La causa fue ignorar estos 3 puntos:

### 3.1 `RewriteBase` en `.htaccess`
Sin esto, Apache reescribe las URLs contra la raíz del dominio
(`dominio.com/ruta` en vez de `dominio.com/erp/ruta`) → **404 en todas las rutas**.

```apache
RewriteBase /erp/
```

### 3.2 Rutas del `index.php`
El `index.php` de `public/` apunta a `__DIR__.'/../vendor/...'`. Al copiarlo dentro de
`public_html/erp/` ya no encuentra el proyecto. Debe apuntar a la carpeta privada:

```php
require __DIR__.'/../../mi-app/vendor/autoload.php';
$app = require_once __DIR__.'/../../mi-app/bootstrap/app.php';
```

### 3.3 `APP_URL` y `ASSET_URL` en `.env`
Sin el subdirectorio en la URL base, todos los links y assets se generan apuntando a la
raíz del dominio → CSS/JS rotos y links que salen del ERP.

```env
APP_URL=https://dominio.com/erp
ASSET_URL=${APP_URL}
```

---

## 4. Protocolo de trabajo del agente (paso a paso)

1. **Preguntar antes de actuar:**
   - ¿Subdirectorio o subdominio? ¿Nombre exacto de la carpeta?
   - ¿Usuario cPanel (rutas absolutas)? ¿Hay SSH o solo FTP?
   - ¿Versión de PHP? ¿Datos de la base de datos?
2. **Mapear el destino:** verificar qué existe ya en `public_html` y NO tocarlo.
3. **Subir el código privado** fuera de `public_html`.
4. **Copiar solo `public/`** a la carpeta pública.
5. **Aplicar los 3 puntos críticos** (sección 3).
6. **Permisos:** `storage/` y `bootstrap/cache/` → 775; `.env` → 640.
7. **Dependencias:** `composer install --no-dev` (o subir `vendor/` si no hay SSH).
8. **Limpiar cachés locales** (`config:clear`, `route:clear`, `view:clear`) — un
   `.env` cacheado con URLs locales es un fallo silencioso muy común.
9. **Migraciones** solo si el usuario lo confirma (pueden destruir datos).
10. **Verificar en el navegador** y reportar resultados honestos.

---

## 5. Checklist de verificación final

- [ ] El sitio responde 200 en su URL base y redirige al login.
- [ ] Login funciona y la página principal carga con datos.
- [ ] CSS/JS cargan desde la URL correcta (con el subdirectorio).
- [ ] Una ruta CRUD completa funciona (listar + crear).
- [ ] Imágenes y archivos estáticos visibles.
- [ ] `dominio.com/.env`, `dominio.com/erp-app/` → NO accesibles (403/404).
- [ ] Reportar al usuario cualquier paso no ejecutado y por qué.

---

## 6. Errores típicos y su solución rápida

| Síntoma | Causa | Solución |
|---|---|---|
| 404 en todas las rutas | Falta `RewriteBase` | Sección 3.1 |
| Error 500 pantalla blanca | `index.php` mal apuntado | Sección 3.2 |
| CSS/JS no cargan | Falta `ASSET_URL` | Sección 3.3 |
| "Page expired" al enviar forms | `APP_URL` no coincide con la URL real (CSRF) | Sección 3.3 |
| 500 por permisos | `storage/` sin 775 | Paso 6 del protocolo |
| Config vieja / URLs locales | Caché de config no limpiada | Paso 8 del protocolo |
| Proyecto expuesto | Todo subido dentro de `public_html` | Sección 1 — reestructurar |

---

## 7. Lecciones aprendidas (retroalimentación)

1. **Nunca asumir la estructura del servidor**: preguntar y verificar.
2. **El `.htaccess` de Laravel no sirve tal cual en subdirectorios**: siempre añadir `RewriteBase`.
3. **Las rutas relativas de `index.php` dependen de dónde se copie el archivo**: recalcularlas.
4. **Las URLs absolutas viven en `.env`**: cualquier cambio de ubicación exige actualizar `APP_URL`/`ASSET_URL`.
5. **Probar en el navegador, no dar por bueno el "ya está subido"**: un despliegue sin verificación no está terminado.
6. **Seguridad primero**: verificar que el `.env` y el código privado no sean accesibles por URL.
