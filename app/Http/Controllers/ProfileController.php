<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Show the authenticated user's commercial activity.
     */
    public function show(Request $request): View
    {
        $user = $request->user()->loadMissing('rolPersonalizado');
        $secciones = [];

        if ($user->tieneModulo('cotizaciones')) {
            $secciones['cotizaciones'] = 'Cotizaciones';
        }

        if ($user->tieneModulo('ventas')) {
            $secciones['ventas'] = 'Ventas';
        }

        $seccion = $request->query('seccion');
        if (! is_string($seccion) || ! array_key_exists($seccion, $secciones)) {
            $seccion = array_key_first($secciones);
        }

        $totales = [];
        foreach ($secciones as $clave => $etiqueta) {
            $totales[$clave] = $user->{$clave}()->count();
        }

        $registros = $seccion
            ? $user->{$seccion}()->with('cliente')->orderByDesc('fecha')->orderByDesc('id')->paginate(8)->withQueryString()
            : null;

        $partesNombre = preg_split('/\s+/u', trim($user->name)) ?: [];
        $iniciales = collect($partesNombre)
            ->filter()
            ->take(2)
            ->map(fn ($parte) => mb_strtoupper(mb_substr($parte, 0, 1)))
            ->implode('');

        return view('profile.show', [
            'user' => $user,
            'secciones' => $secciones,
            'seccion' => $seccion,
            'totales' => $totales,
            'registros' => $registros,
            'iniciales' => $iniciales,
            'identificador' => $user->username ?: strstr($user->email, '@', true),
            'rutaRegreso' => $user->tieneModulo('dashboard') ? route('dashboard') : route('profile.edit'),
        ]);
    }

    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
