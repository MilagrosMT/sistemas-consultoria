<x-layouts::auth :title="__('Iniciar sesión')">

    <div class="flex flex-col gap-6">

        <div class="text-center">

            <div class="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-xl bg-teal-500/10">
                <svg
                    class="h-6 w-6 text-teal-400"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                    stroke-width="1.8"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M12 6v12m6-6H6"
                    />
                </svg>
            </div>

            <h1 class="text-2xl font-semibold tracking-tight text-zinc-900 dark:text-white">
                Sistema Integrado Administrativo
            </h1>

            <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">
                Servicios administrativos y contables
            </p>

            <p class="mt-4 text-sm text-zinc-600 dark:text-zinc-400">
                Ingresa tus credenciales para acceder al sistema.
            </p>

        </div>

        <x-auth-session-status
            class="text-center"
            :status="session('status')"
        />

        <x-passkey-verify />

        <form
            method="POST"
            action="{{ route('login.store') }}"
            class="flex flex-col gap-5"
        >
            @csrf

            <flux:input
                name="email"
                label="Correo electrónico"
                :value="old('email')"
                type="email"
                required
                autofocus
                autocomplete="email"
                placeholder="correo@empresa.com"
            />

            <div class="relative">

                <flux:input
                    name="password"
                    label="Contraseña"
                    type="password"
                    required
                    autocomplete="current-password"
                    placeholder="Ingresa tu contraseña"
                    viewable
                />

                @if (Route::has('password.request'))
                    <flux:link
                        class="absolute top-0 text-sm end-0"
                        :href="route('password.request')"
                        wire:navigate
                    >
                        ¿Olvidaste tu contraseña?
                    </flux:link>
                @endif

            </div>

            <flux:checkbox
                name="remember"
                label="Recordarme"
                :checked="old('remember')"
            />

            <flux:button
                variant="primary"
                type="submit"
                class="w-full !bg-teal-500 !text-white hover:!bg-teal-600"
                data-test="login-button"
            >
                Iniciar sesión
            </flux:button>

        </form>

        @if (Route::has('register'))
            <div class="text-center text-sm text-zinc-600 dark:text-zinc-400">

                <span>¿No tienes una cuenta?</span>

                <flux:link
                    :href="route('register')"
                    wire:navigate
                >
                    Crear cuenta
                </flux:link>

            </div>
        @endif

    </div>

</x-layouts::auth>