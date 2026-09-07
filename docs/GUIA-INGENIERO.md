# 🛠️ Guía para el Ingeniero — Despliegue de Proyectos Laravel en cPanel/VPS

> **Documento de retroalimentación y aprendizaje.**
> Resumen de la experiencia real desplegando el JHardSystex ERP en un VPS con cPanel,
> con la teoría y estructura correcta para futuros despliegues.

---

## 1. El concepto clave: público vs privado

Toda aplicación web (no solo Laravel) separa:

- **Capa pública**: lo que el servidor web sirve directamente. En Laravel es la
  carpeta `public/`: front controller (`index.php`), `.htaccess`, assets compilados
  e imágenes. **Esto es lo único que debe ser accesible por HTTP.**
- **Capa privada**: código, configuración y credenciales. Debe vivir fuera del
  document root del servidor web.

**Por qué importa (seguridad):** si subes el proyecto completo a `public_html/`:

- `https://dominio.com/.env` → expone credenciales de la BD, SMTP, APIs.
- `https://dominio.com/storage/logs/laravel.log` → expone rutas internas, datos y errores.
- Cualquier archivo PHP mal protegido puede ejecutarse o descargarse.

Es la vulnerabilidad más común y más grave en despliegues mal hechos.

---

## 2. Estructura recomendada en cPanel

```
/home/USUARIO/
├── erp-app/                          🔒 Privado (fuera de public_html)
│   ├── app/  bootstrap/  config/  database/
│   ├── resources/  routes/  storage/  vendor/
│   ├── .env                          (640)
│   └── artisan
│
└── public_html/
    └── erp/                          🌐 Público = contenido de /public
        ├── index.php                 (ajustado, ver §3)
        ├── .htaccess                 (con RewriteBase, ver §4)
        ├── favicon.ico  robots.txt
        └── imagenes/
```

Alternativas:
| Escenario | Solución |
|---|---|
| El ERP necesita su propio dominio/subdominio | Crear subdominio en cPanel y apuntar su document root a `~/erp-app/public` |
| El hosting no deja salir de `public_html` | Subir el proyecto a `public_html/erp/` completo pero **proteger** con `.htaccess`: `Require all denied` en cada carpeta sensible y servir `public/` vía rewrite |
| El hosting soporta symlinks | `ln -s ~/erp-app/public ~/public_html/erp` (lo más limpio) |

---

## 3. Ajuste de `index.php`

El front controller debe encontrar al framework. Original en local:

```php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
```

En el servidor (publicación en subcarpeta):

```php
require __DIR__.'/../../erp-app/vendor/autoload.php';
$app = require_once __DIR__.'/../../erp-app/bootstrap/app.php';
```

Cuenta los niveles: `public_html/erp/` → `erp-app/` = subir 2 (`../../`) + entrar a `erp-app/`.

---

## 4. `.htaccess` y `RewriteBase`

En un subdirectorio, Apache necesita saber contra qué base reescribir:

```apache
RewriteEngine On
RewriteBase /erp/
```

**Caso real:** el primer despliegue del ERP (`jhardsystex.com/J-STARK/`) falló con
404 en *todas* las rutas porque el `.htaccess` venía sin `RewriteBase`. Las URLs se
reescribían contra la raíz del dominio, que no corresponde al proyecto.

---

## 5. Variables de entorno (`APP_URL` / `ASSET_URL`)

```env
APP_URL=https://jhardsystex.com/erp
ASSET_URL=${APP_URL}
APP_ENV=production
APP_DEBUG=false        # nunca true en producción: expone stack traces y credenciales
```

- `APP_URL` mal configurado → los redirects y las rutas firmadas apuntan a otro lado,
  y CSRF da "Page expired" en los formularios.
- `ASSET_URL` mal configurado → CSS/JS piden `dominio.com/build/...` en vez de
  `dominio.com/erp/build/...` → sitio sin estilos.

---

## 6. Permisos correctos

```
chmod -R 775 storage bootstrap/cache    # el servidor web necesita escribir ahí
chmod 640 .env                          # solo el dueño y el grupo lo leen
```

El grupo debe incluir al usuario que ejecuta PHP (en cPanel suele ser el mismo
usuario de la cuenta; en VPS con PHP-FPM propio puede ser `www-data`).

---

## 7. Cachés: limpiar SIEMPRE tras desplegar

Un despliegue arrastra cachés de desarrollo (`bootstrap/cache/config.php`,
`routes-v7.php`, vistas compiladas). Si no se limpian, el servidor sirve URLs y
rutas del entorno anterior aunque el `.env` esté perfecto:

```bash
php artisan config:clear && php artisan route:clear && php artisan view:clear
# luego cachear para producción:
php artisan config:cache && php artisan route:cache && php artisan view:cache
```

---

## 8. Checklist de despliegue (imprimible)

**Antes:**
- [ ] Definir subdirectorio/subdominio y nombre de carpetas.
- [ ] BD MySQL creada en cPanel + usuario con privilegios.
- [ ] Versión de PHP del servidor ≥ la que exige el proyecto.
- [ ] Saber si hay SSH o solo FTP/Administrador de archivos.

**Durante:**
- [ ] Proyecto completo fuera de `public_html`.
- [ ] Solo `public/` copiado a la carpeta pública.
- [ ] `index.php` apuntando a la carpeta privada.
- [ ] `.htaccess` con `RewriteBase`.
- [ ] `.env` con `APP_URL` + `ASSET_URL` correctos y `APP_DEBUG=false`.
- [ ] Permisos 775 / 640 aplicados.
- [ ] `composer install --no-dev` (o `vendor/` incluido).
- [ ] Cachés limpiadas y regeneradas.

**Después (verificación):**
- [ ] URL base responde y redirige al login.
- [ ] Login + dashboard funcionan.
- [ ] CSS/JS cargan desde la URL con subdirectorio.
- [ ] Un CRUD completo funciona.
- [ ] `.env` y código privado NO accesibles por URL.

---

## 9. Diagnóstico rápido de fallos

| Síntoma | Probable causa |
|---|---|
| 404 en todas las rutas | Falta `RewriteBase` o `mod_rewrite` desactivado |
| 500 blanco | Rutas de `index.php` incorrectas / versión de PHP |
| Sitio sin estilos | `ASSET_URL`/`APP_URL` sin el subdirectorio |
| "Page expired" en formularios | `APP_URL` no coincide con la URL real |
| Error de permisos al loguear | `storage/` sin permisos de escritura |
| Cambios no se reflejan | Caché de config/route/vista sin limpiar |

---

## 10. Conclusiones para próximos despliegues

1. Diseñar el despliegue **antes** de subir: estructura, rutas y nombres definidos.
2. La separación público/privado no es opcional: es la base de la seguridad.
3. En subcarpetas, los 3 ajustes obligatorios son `RewriteBase`, `index.php` y
   `APP_URL`/`ASSET_URL`. Si falta uno, el despliegue falla o queda inseguro.
4. Documentar cada despliegue (este archivo) para convertir errores en proceso.
5. Verificar siempre en el navegador antes de dar por terminado el trabajo.
