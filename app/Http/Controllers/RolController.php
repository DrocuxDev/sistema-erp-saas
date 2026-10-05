<?php

namespace App\Http\Controllers;

use App\Models\Auditoria;
use App\Models\Modulo;
use App\Models\Rol;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RolController extends Controller
{
    public function index()
    {
        $roles = Rol::withCount('users')->with('modulos')->get();

        return view('roles.index', compact('roles'));
    }

    public function create()
    {
        $modulos = Modulo::where('activo', true)->orderBy('orden')->get()->groupBy('categoria');

        return view('roles.create', compact('modulos'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:100', 'unique:roles,nombre'],
            'descripcion' => ['nullable', 'string', 'max:255'],
            'modulos' => ['nullable', 'array'],
            'modulos.*' => ['integer', 'exists:modulos,id'],
        ]);

        $rol = DB::transaction(function () use ($validated) {
            $rol = Rol::create([
                'nombre' => strtoupper(trim($validated['nombre'])),
                'slug' => Str::slug($validated['nombre']),
                'descripcion' => $validated['descripcion'] ?? null,
                'es_sistema' => false,
            ]);

            if (! empty($validated['modulos'])) {
                $rol->modulos()->sync($validated['modulos']);
                Auditoria::create([
                    'tabla' => 'rol_modulo', 'registro_id' => $rol->id, 'usuario_id' => auth()->id(),
                    'accion' => 'edicion', 'detalle' => 'Módulos asignados al rol',
                    'datos_anteriores' => [], 'datos_nuevos' => ['modulos' => $validated['modulos']],
                ]);
            }

            return $rol;
        });

        return redirect()->route('roles.index')
            ->with('success', "Rol '{$rol->nombre}' creado exitosamente.");
    }

    public function edit(Rol $role)
    {
        $rol = $role;
        $modulos = Modulo::where('activo', true)->orderBy('orden')->get()->groupBy('categoria');
        $rolModulos = $rol->modulos->pluck('id')->toArray();

        return view('roles.edit', compact('rol', 'modulos', 'rolModulos'));
    }

    public function update(Request $request, Rol $role)
    {
        $rol = $role;

        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:100', 'unique:roles,nombre,'.$rol->id],
            'descripcion' => ['nullable', 'string', 'max:255'],
            'modulos' => ['nullable', 'array'],
            'modulos.*' => ['integer', 'exists:modulos,id'],
        ]);

        DB::transaction(function () use ($rol, $validated) {
            $rol->update([
                'nombre' => strtoupper(trim($validated['nombre'])),
                'slug' => $rol->es_sistema ? $rol->slug : Str::slug($validated['nombre']),
                'descripcion' => $validated['descripcion'] ?? null,
            ]);
            $anteriores = $rol->modulos()->pluck('modulos.id')->all();
            $rol->modulos()->sync($validated['modulos'] ?? []);
            Auditoria::create([
                'tabla' => 'rol_modulo', 'registro_id' => $rol->id, 'usuario_id' => auth()->id(),
                'accion' => 'edicion', 'detalle' => 'Módulos del rol modificados',
                'datos_anteriores' => ['modulos' => $anteriores],
                'datos_nuevos' => ['modulos' => $validated['modulos'] ?? []],
            ]);
        });

        return redirect()->route('roles.index')
            ->with('success', "Rol '{$rol->nombre}' actualizado exitosamente.");
    }

    public function destroy(Rol $role)
    {
        $rol = $role;

        if ($rol->es_sistema || $rol->slug === 'superadmin') {
            return redirect()->route('roles.index')
                ->with('error', 'Los roles protegidos del sistema no pueden ser eliminados.');
        }

        if ($rol->users()->count() > 0) {
            return redirect()->route('roles.index')
                ->with('error', "No se puede eliminar el rol porque tiene {$rol->users()->count()} usuarios asociados.");
        }

        $nombre = $rol->nombre;
        $rol->delete();

        return redirect()->route('roles.index')
            ->with('success', "Rol '{$nombre}' eliminado correctamente.");
    }
}
