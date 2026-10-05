<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <div class="mb-1 flex items-center gap-2 text-xs font-medium text-gray-500 dark:text-gray-400">
                    <span>Cuenta</span>
                    <span>/</span>
                    <a href="{{ route('profile.show') }}" class="transition-colors hover:text-cyan-600 dark:hover:text-cyan-400">Mi perfil</a>
                    <span>/</span>
                    <span class="text-gray-900 dark:text-white">Editar</span>
                </div>
                <h1 class="page-title">Editar mi perfil</h1>
                <p class="page-subtitle">Administra tus datos de acceso y la seguridad de tu cuenta.</p>
            </div>
            <a href="{{ route('profile.show') }}" class="btn-outline self-start sm:self-auto">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7 7-7m-7 7h18" />
                </svg>
                Volver al perfil
            </a>
        </div>
    </x-slot>

    <div class="page-container py-6 sm:py-8">
        <div class="mx-auto max-w-5xl space-y-6">
            <div class="form-card">
                @include('profile.partials.update-profile-information-form')
            </div>

            <div id="password" class="form-card scroll-mt-6">
                @include('profile.partials.update-password-form')
            </div>

            <div class="rounded-2xl border border-rose-200 bg-white p-4 shadow-xs dark:border-rose-900/60 dark:bg-card-bg sm:p-6">
                @include('profile.partials.delete-user-form')
            </div>
        </div>
    </div>
</x-app-layout>
