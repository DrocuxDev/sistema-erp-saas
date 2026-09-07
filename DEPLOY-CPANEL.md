# 🤖 INSTRUCCIONES PARA IA — Despliegue del ERP en cPanel

> **Para la IA asistente:** este documento contiene las indicaciones OBLIGATORIAS para
> desplegar este proyecto Laravel en un VPS con cPanel. Síguelas al pie de la letra y no
> improvises la estructura ni las rutas. Ante cualquier duda, PREGUNTA al usuario en lugar
> de asumir.

## Contexto del proyecto

- Proyecto: **JHardSystex ERP** (Laravel + Tailwind).
- Dominio: `https://jhardsystex.com`
- Se desplegará en un **subdirectorio** del dominio (NO en la raíz).
- En un intento anterior (`/J-STARK/`) falló todo por ignorar `RewriteBase`, las rutas de
  `index.php` y `APP_URL`/`ASSET_URL`. **Estos 3 puntos son obligatorios esta vez.**
- Nombre de la carpeta pública: `___________` ← **preguntar al usuario** (ej: `/erp2/`).
  En este documento se usa **`erp2`** como ejemplo.

## Datos que debes pedirle al usuario ANTES de empezar

1. Nombre exacto del subdirectorio público (reemplaza `erp2` en todo).
2. Usuario de cPanel (para las rutas absolutas `/home/USUARIO/`).
3. Datos de la base de datos MySQL ya creada en cPanel.
4. Si el servidor tiene acceso SSH o solo FTP / Administrador de archivos.
5. Versión de PHP disponible en el servidor.

---

## 1. Estructura en el servidor (público vs privado)

```
/home/USUARIO_CPANEL/
├── erp-app/                        🔒 PRIVADO (todo el proyecto Laravel)
│   ├── app/
│   ├── bootstrap/
│   ├── config/
│   ├── database/
│   ├── resources/
│   ├── routes/
│   ├── storage/                    (permisos 775)
│   ├── vendor/
│   ├── .env
│   └── artisan
│
└── public_html/
    ├── erp2/                       🌐 PÚBLICO (contenido de /public)
    │   ├── index.php               (con rutas corregidas, ver paso 3)
    │   ├── .htaccess               (con RewriteBase, ver paso 4)
    │   ├── favicon.ico
    │   ├── robots.txt
    │   └── imagenes/
    └── (otros sitios existentes)
```

**Regla de oro:** solo lo que está dentro de `public/` se copia a la carpeta pública.
Todo lo demás queda en `erp-app/` (fuera de `public_html`).

**Reglas adicionales para la IA:**
- Nada del proyecto (excepto el contenido de `public/`) puede quedar dentro de `public_html`.
- NO modificar ni borrar otros sitios ya existentes en `public_html` (ej: la carpeta `J-STARK`).

---

## 2. `.env` en el servidor (la IA debe crear/editar este archivo)

Edita `/home/USUARIO_CPANEL/erp-app/.env`:

```env
APP_NAME="JHardSystex ERP"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://jhardsystex.com/erp2

# 🔑 CLAVE para que assets y rutas generen URLs correctas en subcarpeta:
ASSET_URL=${APP_URL}

APP_KEY=base64:...            # copia la APP_KEY de tu .env local (o: php artisan key:generate)
```

> `ASSET_URL` es lo que hace que los `asset()` y Vite carguen desde
> `https://jhardsystex.com/erp2/build/...` y no desde la raíz del dominio.

---

## 3. Modificar `public_html/erp2/index.php` (la IA debe aplicar este cambio)

El `index.php` local apunta con `__DIR__.'/../'`. Al copiarlo a
`public_html/erp2/`, debe subir **dos niveles** hasta llegar a `erp-app/`.

Edita `public_html/erp2/index.php`:

```php
// ❌ Original (local):
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';

// ✅ En el servidor:
require __DIR__.'/../../erp-app/vendor/autoload.php';
$app = require_once __DIR__.'/../../erp-app/bootstrap/app.php';
```

---

## 4. `.htaccess` con `RewriteBase` (la IA debe aplicar este cambio)

⚠️ **SIN ESTO NINGUNA RUTA FUNCIONA.** Apache reescribe las URLs contra la raíz del
dominio y por eso el despliegue anterior en `/J-STARK/` falló por completo.

Crea/edita `public_html/erp2/.htaccess`:

```apache
<IfModule mod_rewrite.c>
    <IfModule mod_negotiation.c>
        Options -MultiViews -Indexes
    </IfModule>

    RewriteEngine On

    # 🔑 OBLIGATORIO en subdirectorio:
    RewriteBase /erp2/

    # Authorization Header
    RewriteCond %{HTTP:Authorization} .
    RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]

    RewriteCond %{HTTP:x-xsrf-token} .
    RewriteRule .* - [E=HTTP_X_XSRF_TOKEN:%{HTTP:X-XSRF-Token}]

    # Redirect Trailing Slashes
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_URI} (.+)/$
    RewriteRule ^ %1 [L,R=301]

    # Front Controller
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^ index.php [L]
</IfModule>
```

Sin `RewriteBase`, Apache reescribe las URLs contra la raíz del dominio y por eso
**ninguna ruta del ERP funciona** al vivir en `/erp2/`.

---

## 5. Cachés y permisos (vía Terminal SSH del cPanel)

```bash
cd /home/USUARIO_CPANEL/erp-app

chmod -R 775 storage bootstrap/cache
composer install --no-dev --optimize-autoloader

php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan storage:link        # si usas storage público

# Cachear para producción:
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

> ⚠️ Si el hosting no da SSH, sube el proyecto ya con `vendor/` incluido y borra
> manualmente el contenido de `bootstrap/cache/*.php` y `storage/framework/*`
> (excepto `.gitignore`) antes del primer acceso.

---

## 6. Checklist de verificación final (la IA debe comprobar y reportar)

- [ ] `https://jhardsystex.com/erp2` redirige al login y responde 200.
- [ ] Login funciona y `/erp2/dashboard` carga.
- [ ] CSS/JS cargan desde `/erp2/build/...` (NO desde la raíz del dominio).
- [ ] Una ruta resource funciona (ej: `/erp2/clientes`).
- [ ] Imágenes de `public/imagenes/` visibles.
- [ ] Permisos 775 en `storage/` y `bootstrap/cache/`.
- [ ] **Reportar al usuario cualquier paso que no pudiste ejecutar y por qué.**

### Síntomas → causa rápida

| Síntoma | Causa | Solución |
|---|---|---|
| 404 en todas las rutas | Falta `RewriteBase` | Paso 4 |
| CSS/JS no cargan | Falta `ASSET_URL` | Paso 2 |
| Error 500 blanco | `index.php` mal apuntado | Paso 3 |
| "Storage link not found" | Falta symlink | Paso 5 |
| Rutas viejas cacheadas | Caché de config/route | Paso 5 (clear) |
