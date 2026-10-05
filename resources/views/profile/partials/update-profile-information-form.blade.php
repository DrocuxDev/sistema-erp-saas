<section>
    <header class="border-b border-gray-100 pb-3 dark:border-gray-700/60">
        <h2 class="flex items-center gap-2 text-base font-bold text-gray-900 dark:text-white">
            <span class="h-2.5 w-2.5 rounded-full bg-cyan-600 dark:bg-cyan-500"></span>
            Información del perfil
        </h2>
        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Actualiza tu nombre y el correo electrónico con el que ingresas al sistema.</p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="mt-5 space-y-5">
        @csrf
        @method('patch')

        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
            <div>
                <label for="name" class="form-label">Nombre completo <span class="text-rose-500">*</span></label>
                <input id="name" name="name" type="text" class="form-input" value="{{ old('name', $user->name) }}" required autofocus autocomplete="name">
                <x-input-error class="mt-1" :messages="$errors->get('name')" />
            </div>

            <div>
                <label for="email" class="form-label">Correo electrónico <span class="text-rose-500">*</span></label>
                <input id="email" name="email" type="email" class="form-input" value="{{ old('email', $user->email) }}" required autocomplete="username">
                <x-input-error class="mt-1" :messages="$errors->get('email')" />
            </div>
        </div>

        @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
            <div class="rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800 dark:border-amber-800 dark:bg-amber-950/40 dark:text-amber-300">
                <p>{{ __('Your email address is unverified.') }}</p>
                <button form="send-verification" type="submit" class="mt-1 font-semibold underline underline-offset-2 hover:text-amber-600 dark:hover:text-amber-200">
                    {{ __('Click here to re-send the verification email.') }}
                </button>
                @if (session('status') === 'verification-link-sent')
                    <p class="mt-2 font-medium text-emerald-700 dark:text-emerald-300">{{ __('A new verification link has been sent to your email address.') }}</p>
                @endif
            </div>
        @endif

        <div class="flex flex-wrap items-center gap-4 border-t border-gray-100 pt-4 dark:border-gray-700/60">
            <button type="submit" class="btn-primary">Guardar cambios</button>
            @if (session('status') === 'profile-updated')
                <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm font-medium text-emerald-700 dark:text-emerald-300">{{ __('Saved.') }}</p>
            @endif
        </div>
    </form>
</section>
