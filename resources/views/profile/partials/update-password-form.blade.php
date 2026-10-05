<section>
    <header class="border-b border-gray-100 pb-3 dark:border-gray-700/60">
        <h2 class="flex items-center gap-2 text-base font-bold text-gray-900 dark:text-white">
            <span class="h-2.5 w-2.5 rounded-full bg-cyan-600 dark:bg-cyan-500"></span>
            Actualizar contraseña
        </h2>
        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Usa una contraseña segura para proteger tu acceso al ERP.</p>
    </header>

    <form method="post" action="{{ route('password.update') }}" class="mt-5 space-y-5">
        @csrf
        @method('put')

        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
            <div class="md:col-span-2">
                <label for="update_password_current_password" class="form-label">Contraseña actual <span class="text-rose-500">*</span></label>
                <input id="update_password_current_password" name="current_password" type="password" class="form-input" required autocomplete="current-password">
                <x-input-error :messages="$errors->updatePassword->get('current_password')" class="mt-1" />
            </div>

            <div>
                <label for="update_password_password" class="form-label">Nueva contraseña <span class="text-rose-500">*</span></label>
                <input id="update_password_password" name="password" type="password" class="form-input" required autocomplete="new-password">
                <x-input-error :messages="$errors->updatePassword->get('password')" class="mt-1" />
            </div>

            <div>
                <label for="update_password_password_confirmation" class="form-label">Confirmar nueva contraseña <span class="text-rose-500">*</span></label>
                <input id="update_password_password_confirmation" name="password_confirmation" type="password" class="form-input" required autocomplete="new-password">
                <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" class="mt-1" />
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-4 border-t border-gray-100 pt-4 dark:border-gray-700/60">
            <button type="submit" class="btn-primary">Actualizar contraseña</button>
            @if (session('status') === 'password-updated')
                <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm font-medium text-emerald-700 dark:text-emerald-300">{{ __('Saved.') }}</p>
            @endif
        </div>
    </form>
</section>
