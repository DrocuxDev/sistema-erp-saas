<section class="space-y-5">
    <header class="border-b border-rose-100 pb-3 dark:border-rose-900/60">
        <h2 class="flex items-center gap-2 text-base font-bold text-rose-700 dark:text-rose-300">
            <span class="h-2.5 w-2.5 rounded-full bg-rose-600"></span>
            Eliminar cuenta
        </h2>
        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Esta acción elimina tu acceso y los datos asociados de forma permanente.</p>
    </header>

    <button type="button" class="btn-danger-soft" x-data="" x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')">
        Eliminar cuenta
    </button>

    <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
        <form method="post" action="{{ route('profile.destroy') }}" class="rounded-lg bg-white p-6 dark:bg-card-bg">
            @csrf
            @method('delete')

            <h2 class="text-lg font-bold text-gray-900 dark:text-white">¿Seguro que deseas eliminar tu cuenta?</h2>
            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                Esta acción es permanente. Ingresa tu contraseña para confirmar la eliminación.
            </p>

            <div class="mt-6">
                <label for="password" class="form-label">Contraseña</label>
                <input id="password" name="password" type="password" class="form-input" autocomplete="current-password">
                <x-input-error :messages="$errors->userDeletion->get('password')" class="mt-1" />
            </div>

            <div class="mt-6 flex flex-wrap justify-end gap-3">
                <x-secondary-button x-on:click="$dispatch('close')">Cancelar</x-secondary-button>
                <x-danger-button>Eliminar cuenta</x-danger-button>
            </div>
        </form>
    </x-modal>
</section>
