```blade
<!DOCTYPE html>

<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">

<head>
    {{-- Carga los estilos, fuentes y configuraciones principales --}}
    @include('partials.head')
</head>

<body class="min-h-screen bg-white dark:bg-zinc-800">

    {{-- =========================================================
         MENÚ LATERAL PRINCIPAL
         Este menú será compartido por todas las pantallas.
         ========================================================= --}}
    <flux:sidebar
        sticky
        collapsible="mobile"
        class="border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900"
    >

        {{-- =====================================================
             ENCABEZADO DEL MENÚ
             Logo + botón para ocultar/mostrar el menú
             ===================================================== --}}
        <flux:sidebar.header>

            <x-app-logo
                :sidebar="true"
                href="{{ route('dashboard') }}"
                wire:navigate
            />

            <flux:sidebar.collapse class="lg:hidden" />

        </flux:sidebar.header>


        {{-- =====================================================
             NAVEGACIÓN PRINCIPAL
             ===================================================== --}}
        <flux:sidebar.nav>


            {{-- =================================================
                 INICIO
                 ================================================= --}}
            <flux:sidebar.group
                heading="Inicio"
                class="grid"
            >

                <flux:sidebar.item
                    icon="home"
                    :href="route('dashboard')"
                    :current="request()->routeIs('dashboard')"
                    wire:navigate
                >
                    Dashboard
                </flux:sidebar.item>

            </flux:sidebar.group>


            {{-- =================================================
                 GESTIÓN DEL PERSONAL
                 ================================================= --}}
            <flux:sidebar.group
                heading="Gestión del personal"
                class="grid"
            >

                {{-- Empleados --}}
                <flux:sidebar.item
                    icon="users"
                    :href="route('empleados')"
                    :current="request()->routeIs('empleados')"
                    wire:navigate
                >
                    Empleados
                </flux:sidebar.item>


                {{-- Reclutamiento
                     Esta ruta la crearemos posteriormente.
                     Por ahora NO colocar href para evitar errores.
                --}}
               <flux:sidebar.item
    icon="user-plus"
    :href="route('reclutamiento')"
    :current="request()->routeIs('reclutamiento')"
    wire:navigate
>
    Reclutamiento
</flux:sidebar.item>


                {{-- Planillas
                     La ruta será creada posteriormente.
                --}}
               <flux:sidebar.item
    icon="banknotes"
    :href="route('planillas')"
    :current="request()->routeIs('planillas')"
    wire:navigate
>
    Planillas
</flux:sidebar.item>

            </flux:sidebar.group>


            {{-- =================================================
                 CONTABILIDAD Y TRIBUTACIÓN
                 ================================================= --}}
            <flux:sidebar.group
                heading="Contabilidad y tributación"
                class="grid"
            >

{{-- Administración tributaria --}}
<flux:sidebar.item
    icon="document-text"
    :href="route('administracion-tributaria')"
    :current="request()->routeIs('administracion-tributaria')"
    wire:navigate
>
    Administración tributaria
</flux:sidebar.item>


                {{-- Reportes --}}
                <flux:sidebar.item
                    icon="chart-bar"
                >
                    Reportes
                </flux:sidebar.item>

            </flux:sidebar.group>


            {{-- =================================================
                 ADMINISTRACIÓN DEL SISTEMA
                 ================================================= --}}
            <flux:sidebar.group
                heading="Administración"
                class="grid"
            >

{{-- Usuarios --}}
<flux:sidebar.item
    icon="user-circle"
    :href="route('usuarios')"
    :current="request()->routeIs('usuarios')"
    wire:navigate
>
    Usuarios
</flux:sidebar.item>


{{-- Roles y permisos --}}
<flux:sidebar.item
    icon="shield-check"
    :href="route('roles')"
    :current="request()->routeIs('roles')"
    wire:navigate
>
    Roles y permisos
</flux:sidebar.item>

            </flux:sidebar.group>

        </flux:sidebar.nav>


        {{-- =====================================================
             ESPACIO FLEXIBLE
             Empuja las opciones inferiores hacia abajo.
             ===================================================== --}}
        <flux:spacer />


        {{-- =====================================================
             OPCIONES INFERIORES
             ===================================================== --}}
        <flux:sidebar.nav>

            {{-- Configuración del perfil --}}
            <flux:sidebar.item
                icon="cog-6-tooth"
                :href="route('profile.edit')"
                :current="request()->routeIs('profile.edit')"
                wire:navigate
            >
                Configuración
            </flux:sidebar.item>

        </flux:sidebar.nav>


        {{-- =====================================================
             USUARIO ACTUAL
             Aparece en la parte inferior del menú.
             ===================================================== --}}
        <x-desktop-user-menu
            class="hidden lg:block"
            :name="auth()->user()->name"
        />

    </flux:sidebar>



    {{-- =========================================================
         ENCABEZADO PARA DISPOSITIVOS MÓVILES
         ========================================================= --}}
    <flux:header class="lg:hidden">

        {{-- Botón para abrir el menú --}}
        <flux:sidebar.toggle
            class="lg:hidden"
            icon="bars-2"
            inset="left"
        />

        <flux:spacer />


        {{-- Menú del usuario --}}
        <flux:dropdown
            position="top"
            align="end"
        >

            <flux:profile
                :initials="auth()->user()->initials()"
                icon-trailing="chevron-down"
            />


            <flux:menu>

                {{-- Información del usuario --}}
                <flux:menu.radio.group>

                    <div class="p-0 text-sm font-normal">

                        <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">

                            <flux:avatar
                                :name="auth()->user()->name"
                                :initials="auth()->user()->initials()"
                            />

                            <div class="grid flex-1 text-start text-sm leading-tight">

                                <flux:heading class="truncate">
                                    {{ auth()->user()->name }}
                                </flux:heading>

                                <flux:text class="truncate">
                                    {{ auth()->user()->email }}
                                </flux:text>

                            </div>

                        </div>

                    </div>

                </flux:menu.radio.group>


                <flux:menu.separator />


                {{-- Configuración --}}
                <flux:menu.radio.group>

                    <flux:menu.item
                        :href="route('profile.edit')"
                        icon="cog"
                        wire:navigate
                    >
                        Configuración
                    </flux:menu.item>

                </flux:menu.radio.group>


                <flux:menu.separator />


                {{-- Cerrar sesión --}}
                <form
                    method="POST"
                    action="{{ route('logout') }}"
                    class="w-full"
                >

                    @csrf

                    <flux:menu.item
                        as="button"
                        type="submit"
                        icon="arrow-right-start-on-rectangle"
                        class="w-full cursor-pointer"
                        data-test="logout-button"
                    >
                        Cerrar sesión
                    </flux:menu.item>

                </form>

            </flux:menu>

        </flux:dropdown>

    </flux:header>



    {{-- =========================================================
         CONTENIDO DE CADA PANTALLA
         Aquí aparecerá Empleados, Dashboard, Planillas, etc.
         ========================================================= --}}
    {{ $slot }}



    {{-- =========================================================
         NOTIFICACIONES / TOAST
         ========================================================= --}}
    @persist('toast')

        <flux:toast.group>

            <flux:toast />

        </flux:toast.group>

    @endpersist


    {{-- Scripts de Flux --}}
    @fluxScripts

</body>

</html>
```
