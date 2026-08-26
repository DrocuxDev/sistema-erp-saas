<?php

namespace App\Http\Controllers;

use App\Models\Modulo;
use App\Models\Rol;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::with(['rolPersonalizado', 'modulosExcepciones'])->latest('id');

        // Filtro por nombre o usuario o email
        if ($request->filled('usuario')) {
            $busqueda = trim($request->usuario);
            $query->where(function ($q) use ($busqueda) {
                $q->where('name', 'like', "%{$busqueda}%")
                  ->orWhere('email', 'like', "%{$busqueda}%")
                  ->orWhere('username', 'like', "%{$busqueda}%");
            });
        }

        // Filtro por Rol
        if ($request->filled('rol_id') && $request->rol_id !== 'TODOS') {
            $query->where('rol_id', $request->rol_id);
        }

        // Filtro por Punto de Emisión
        if ($request->filled('punto_emision')) {
            $query->where('punto_emision', $request->punto_emision);
        }

        // Checkbox: Solo Activos
        if ($request->has('solo_activos') && $request->solo_activos == '1') {
            $query->where('activo', true);
        }

        $users = $query->paginate(15)->withQueryString();
        $roles = Rol::orderBy('nombre')->get();
        $puntosEmision = User::select('punto_emision')
            ->whereNotNull('punto_emision')
            ->distinct()
            ->pluck('punto_emision')
            ->filter();

        return view('users.index', compact('users', 'roles', 'puntosEmision'));
    }

    public function create()
    {
        $roles = Rol::orderBy('nombre')->get();
        $modulos = Modulo::where('activo', true)->orderBy('orden')->get()->groupBy('categoria');

        return view('users.create', compact('roles', 'modulos'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'string', 'email', 'max:150', 'unique:users,email'],
            'username' => ['nullable', 'string', 'max:100', 'unique:users,username'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'rol_id' => ['nullable', 'exists:roles,id'],
            'punto_emision' => ['nullable', 'string', 'max:100'],
            'is_superadmin' => ['nullable', 'boolean'],
            'activo' => ['nullable', 'boolean'],
            'modulos' => ['nullable', 'array'],
        ]);

        $rol = !empty($validated['rol_id']) ? Rol::find($validated['rol_id']) : null;
        $esSuperAdmin = !empty($validated['is_superadmin']) && auth()->user()->isSuperAdmin();

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'username' => !empty($validated['username']) ? $validated['username'] : $validated['email'],
            'password' => Hash::make($validated['password']),
            'rol' => ($esSuperAdmin || $rol?->slug === 'superadmin' || $rol?->slug === 'administracion') ? 'administrador' : 'trabajador',
            'rol_id' => $validated['rol_id'] ?? null,
            'punto_emision' => $validated['punto_emision'] ?? 'OFICINA',
            'is_superadmin' => $esSuperAdmin,
            'activo' => $request->has('activo') ? (bool) $request->activo : true,
        ]);

        // Guardar excepciones directas de módulos si se enviaron
        if ($request->has('modulos')) {
            $user->modulosExcepciones()->sync($request->modulos);
        }

        return redirect()->route('users.index')
            ->with('success', "Usuario '{$user->name}' registrado exitosamente.");
    }

    public function edit(User $user)
    {
        // Nadie que no sea SuperAdmin puede editar a un SuperAdmin
        if ($user->isSuperAdmin() && !auth()->user()->isSuperAdmin()) {
            abort(403, 'Solo el Administrador Principal (SuperAdmin) puede modificar esta cuenta.');
        }

        $roles = Rol::orderBy('nombre')->get();
        $modulos = Modulo::where('activo', true)->orderBy('orden')->get()->groupBy('categoria');
        $usuarioModulos = $user->modulosExcepciones->pluck('id')->toArray();

        return view('users.edit', compact('user', 'roles', 'modulos', 'usuarioModulos'));
    }

    public function update(Request $request, User $user)
    {
        // Protección de SuperAdmin Base
        if ($user->isSuperAdmin() && !auth()->user()->isSuperAdmin()) {
            abort(403, 'No tiene permisos para modificar al Administrador Principal.');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'string', 'email', 'max:150', 'unique:users,email,' . $user->id],
            'username' => ['nullable', 'string', 'max:100', 'unique:users,username,' . $user->id],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'rol_id' => ['nullable', 'exists:roles,id'],
            'punto_emision' => ['nullable', 'string', 'max:100'],
            'is_superadmin' => ['nullable', 'boolean'],
            'activo' => ['nullable', 'boolean'],
            'modulos' => ['nullable', 'array'],
        ]);

        $rol = !empty($validated['rol_id']) ? Rol::find($validated['rol_id']) : null;
        
        // Evitar que se desmarque a sí mismo como SuperAdmin si es el único
        $esSuperAdmin = $user->isSuperAdmin();
        if (auth()->user()->isSuperAdmin() && $user->id !== auth()->id() && isset($validated['is_superadmin'])) {
            $esSuperAdmin = (bool) $validated['is_superadmin'];
        }

        $datosActualizar = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'username' => !empty($validated['username']) ? $validated['username'] : $user->username,
            'rol' => ($esSuperAdmin || $rol?->slug === 'superadmin' || $rol?->slug === 'administracion') ? 'administrador' : 'trabajador',
            'rol_id' => $validated['rol_id'] ?? $user->rol_id,
            'punto_emision' => $validated['punto_emision'] ?? ($user->punto_emision ?: 'OFICINA'),
            'is_superadmin' => $esSuperAdmin,
            'activo' => $user->isSuperAdmin() ? true : ($request->has('activo') ? (bool) $request->activo : false),
        ];

        if ($request->filled('password')) {
            $datosActualizar['password'] = Hash::make($validated['password']);
        }

        $user->update($datosActualizar);

        // Actualizar módulos específicos
        if ($request->has('modulos_personalizados')) {
            $user->modulosExcepciones()->sync($request->modulos ?? []);
        }

        return redirect()->route('users.index')
            ->with('success', "Usuario '{$user->name}' actualizado correctamente.");
    }

    public function destroy(User $user)
    {
        // 1. Prohibido eliminar al propio usuario en sesión
        if ($user->id === auth()->id()) {
            return redirect()->route('users.index')
                ->with('error', 'No puedes eliminar tu propia cuenta en sesión.');
        }

        // 2. Prohibido eliminar a un SuperAdmin Base
        if ($user->isSuperAdmin()) {
            return redirect()->route('users.index')
                ->with('error', 'Acceso denegado: El Administrador Principal (SuperAdmin) no puede ser eliminado.');
        }

        // 3. Validar jerarquía
        if (!auth()->user()->puedeGestionarA($user)) {
            return redirect()->route('users.index')
                ->with('error', 'No tiene los privilegios necesarios para eliminar a este usuario.');
        }

        $nombre = $user->name;
        $user->delete();

        return redirect()->route('users.index')
            ->with('success', "Usuario '{$nombre}' eliminado correctamente.");
    }

    /**
     * Alternar estado Activo / Inactivo con AJAX o POST rápido
     */
    public function toggleActivo(User $user)
    {
        if ($user->isSuperAdmin()) {
            return back()->with('error', 'El SuperAdmin principal siempre debe permanecer activo.');
        }

        $user->update(['activo' => ! $user->activo]);

        return back()->with('success', "Estado del usuario {$user->name} actualizado a " . ($user->activo ? 'ACTIVO' : 'INACTIVO'));
    }
}
