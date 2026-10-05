<?php

namespace App\Support;

use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ImagenUpload
{
    /**
     * Regla de validación compartida: foto 100% opcional.
     */
    public static function rules(): array
    {
        return ['imagen' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048']];
    }

    /**
     * Resuelve la columna `imagen` en store/update:
     * - archivo nuevo → lo guarda y devuelve su ruta;
     * - flag eliminar_imagen → devuelve null;
     * - sin cambios → conserva el valor actual.
     *
     * @return string|null ruta nueva, null (sin foto o eliminada) o $actual
     */
    public static function guardar(Request $request, string $carpeta, ?string $actual = null): ?string
    {
        if ($request->hasFile('imagen')) {
            return $request->file('imagen')->store($carpeta, 'public');
        }

        if ($request->boolean('eliminar_imagen')) {
            return null;
        }

        return $actual;
    }

    public static function crear(Request $request, string $carpeta, Closure $persistir): mixed
    {
        $ruta = self::guardar($request, $carpeta);
        try {
            return DB::transaction(fn () => $persistir($ruta));
        } catch (\Throwable $e) {
            self::eliminar($ruta);
            throw $e;
        }
    }

    public static function actualizar(Request $request, string $carpeta, Model $modelo, array $datos): void
    {
        $anterior = $modelo->getAttribute('imagen');
        $nuevo = self::guardar($request, $carpeta, $anterior);
        $datos['imagen'] = $nuevo;
        try {
            DB::transaction(fn () => $modelo->update($datos));
        } catch (\Throwable $e) {
            if ($nuevo !== $anterior) {
                self::eliminar($nuevo);
            }
            throw $e;
        }
        if ($nuevo !== $anterior) {
            self::eliminar($anterior);
        }
    }

    public static function eliminarModelo(Model $modelo): void
    {
        $ruta = $modelo->getAttribute('imagen');
        DB::transaction(fn () => $modelo->delete());
        self::eliminar($ruta);
    }

    public static function eliminar(?string $ruta): void
    {
        if ($ruta) {
            Storage::disk('public')->delete($ruta);
        }
    }
}
