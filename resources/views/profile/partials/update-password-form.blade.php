<section>
    <header>
        <h2 class="text-lg font-bold text-gray-900 dark:text-gray-100">
            🔒 Cambiar Contraseña
        </h2>

        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            Asegúrate de que tu cuenta esté utilizando una contraseña segura y difícil de adivinar para mantenerte protegido.
        </p>
    </header>

    <form method="post" action="{{ route('password.update') }}" class="mt-6 space-y-6">
        @csrf
        @method('put')

        <div>
            <x-input-label for="update_password_current_password" :value="'Contraseña Actual'" class="font-bold text-gray-700 dark:text-gray-300" />
            <x-text-input id="update_password_current_password" name="current_password" type="password" class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-gray-700 dark:text-gray-300" autocomplete="current-password" />
            <x-input-error :messages="$errors->updatePassword->get('current_password')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="update_password_password" :value="'Nueva Contraseña'" class="font-bold text-gray-700 dark:text-gray-300" />
            <x-text-input id="update_password_password" name="password" type="password" class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-gray-700 dark:text-gray-300" autocomplete="new-password" />
            <x-input-error :messages="$errors->updatePassword->get('password')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="update_password_password_confirmation" :value="'Confirmar Contraseña'" class="font-bold text-gray-700 dark:text-gray-300" />
            <x-text-input id="update_password_password_confirmation" name="password_confirmation" type="password" class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-gray-700 dark:text-gray-300" autocomplete="new-password" />
            <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center gap-4">
            <button type="submit" class="px-5 py-2.5 rounded-xl text-white font-bold text-sm bg-blue-600 hover:bg-blue-700 transition-colors shadow-md shadow-blue-600/20">
                🔒 Actualizar Contraseña
            </button>

            @if (session('status') === 'password-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 3000)"
                    class="text-sm text-green-600 dark:text-green-400 font-bold"
                >¡Contraseña actualizada correctamente!</p>
            @endif
        </div>
    </form>
</section>
