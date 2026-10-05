<x-auth-split-layout>
    <div class="mb-7">
        <h2 class="text-2xl font-bold text-white tracking-tight">Iniciar sesión</h2>
        <p class="mt-3 text-sm leading-relaxed text-slate-400">
            Accede a tu cuenta para usar el sistema ERP
        </p>
    </div>

    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="space-y-5">
        @csrf

        <div>
            <label for="email" class="ui-label mb-2">
                Correo electrónico
            </label>
            <input
                id="email"
                type="email"
                name="email"
                value="{{ old('email') }}"
                required
                autofocus
                autocomplete="username"
                placeholder="nombre@jhardsystex.com"
                class="ui-input"
            />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div>
            <div class="flex items-center justify-between mb-2 gap-3">
                <label for="password" class="ui-label mb-0">
                    Contraseña
                </label>
                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}" class="text-xs font-medium text-cyan-accent hover:text-white transition-colors focus-visible:outline focus-visible:outline-2 focus-visible:outline-cyan-accent">
                        ¿Olvidaste tu contraseña?
                    </a>
                @endif
            </div>
            <div class="relative">
                <input
                    id="password"
                    type="password"
                    name="password"
                    required
                    autocomplete="current-password"
                    placeholder="••••••••"
                    class="ui-input pr-11"
                />
                <button
                    type="button"
                    id="toggle-password"
                    class="absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400 hover:text-white transition-colors focus-visible:outline focus-visible:outline-2 focus-visible:outline-cyan-accent rounded-md"
                    aria-label="Mostrar contraseña"
                    aria-pressed="false"
                    aria-controls="password"
                >
                    <svg id="eye-open" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    <svg id="eye-closed" class="h-5 w-5 hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88"/>
                    </svg>
                </button>
            </div>
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="flex items-center">
            <input
                id="remember_me"
                type="checkbox"
                name="remember"
                class="ui-check bg-darker-bg text-cyan-primary cursor-pointer"
            />
            <label for="remember_me" class="ms-3 text-sm text-slate-300 cursor-pointer">
                Mantener sesión iniciada
            </label>
        </div>

        <button
            type="submit"
            class="btn-primary w-full py-3 focus-visible:ring-offset-darker-bg"
        >
            Iniciar sesión
        </button>
    </form>

    <script>
        document.getElementById('toggle-password')?.addEventListener('click', function () {
            const input = document.getElementById('password');
            const eyeOpen = document.getElementById('eye-open');
            const eyeClosed = document.getElementById('eye-closed');
            const isHidden = input.type === 'password';

            input.type = isHidden ? 'text' : 'password';
            this.setAttribute('aria-pressed', String(isHidden));
            eyeOpen.classList.toggle('hidden', isHidden);
            eyeClosed.classList.toggle('hidden', !isHidden);
            this.setAttribute('aria-label', isHidden ? 'Ocultar contraseña' : 'Mostrar contraseña');
        });
    </script>
</x-auth-split-layout>
