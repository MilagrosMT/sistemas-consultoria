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

    {{-- INICIO --}}
    <flux:sidebar.group heading="Inicio" class="grid">
        <flux:sidebar.item
            icon="home"
            :href="route('dashboard')"
            :current="request()->routeIs('dashboard')"
            wire:navigate
        >
            Dashboard
        </flux:sidebar.item>
    </flux:sidebar.group>


    {{-- GESTIÓN COMERCIAL --}}
    <flux:sidebar.group heading="Gestión Comercial" class="grid">

        <flux:sidebar.item
            icon="megaphone"
            :href="route('marketing')"
            :current="request()->routeIs('marketing')"
            wire:navigate
        >
            Marketing
        </flux:sidebar.item>

        <flux:sidebar.item
            icon="building-office"
            :href="route('clientes')"
            :current="request()->routeIs('clientes')"
            wire:navigate
        >
            Clientes
        </flux:sidebar.item>

        <flux:sidebar.item
            icon="briefcase"
            :href="route('servicios')"
            :current="request()->routeIs('servicios')"
            wire:navigate
        >
            Servicios
        </flux:sidebar.item>

        <flux:sidebar.item
            icon="document-text"
            :href="route('contratos')"
            :current="request()->routeIs('contratos')"
            wire:navigate
        >
            Contratos
        </flux:sidebar.item>

        <flux:sidebar.item
            icon="receipt-percent"
            :href="route('ventas')"
            :current="request()->routeIs('ventas')"
            wire:navigate
        >
            Ventas / Facturación
        </flux:sidebar.item>

    </flux:sidebar.group>


    {{-- GESTIÓN CONTABLE --}}
    <flux:sidebar.group heading="Gestión Contable" class="grid">

        <flux:sidebar.item
            icon="shopping-cart"
            :href="route('compras')"
            :current="request()->routeIs('compras')"
            wire:navigate
        >
            Compras de clientes
        </flux:sidebar.item>

        <flux:sidebar.item
            icon="document-chart-bar"
            :href="route('ventas-clientes')"
            :current="request()->routeIs('ventas-clientes')"
            wire:navigate
        >
            Ventas de clientes
        </flux:sidebar.item>

        <flux:sidebar.item
            icon="banknotes"
            :href="route('caja-clientes')"
            :current="request()->routeIs('caja-clientes')"
            wire:navigate
        >
            Caja de clientes
        </flux:sidebar.item>

        <flux:sidebar.item
            icon="calculator"
            :href="route('procesamiento-contable')"
            :current="request()->routeIs('procesamiento-contable')"
            wire:navigate
        >
            Procesamiento contable
        </flux:sidebar.item>

    </flux:sidebar.group>


    {{-- ADMINISTRACIÓN TRIBUTARIA --}}
    <flux:sidebar.group heading="Administración Tributaria" class="grid">

        <flux:sidebar.item
            icon="document-text"
            :href="route('administracion-tributaria')"
            :current="request()->routeIs('administracion-tributaria')"
            wire:navigate
        >
            Obligaciones tributarias
        </flux:sidebar.item>

    </flux:sidebar.group>


    {{-- GESTIÓN DE PERSONAL --}}
    <flux:sidebar.group heading="Gestión de Personal" class="grid">

        <flux:sidebar.item
            icon="users"
            :href="route('empleados')"
            :current="request()->routeIs('empleados')"
            wire:navigate
        >
            Empleados
        </flux:sidebar.item>

        <flux:sidebar.item
            icon="banknotes"
            :href="route('planillas')"
            :current="request()->routeIs('planillas')"
            wire:navigate
        >
            Planillas
        </flux:sidebar.item>

        <flux:sidebar.item
            icon="user-plus"
            :href="route('reclutamiento')"
            :current="request()->routeIs('reclutamiento')"
            wire:navigate
        >
            Reclutamiento
        </flux:sidebar.item>

    </flux:sidebar.group>


    {{-- OPERACIONES --}}
    <flux:sidebar.group heading="Operaciones" class="grid">

        <flux:sidebar.item
            icon="clipboard-document-list"
            :href="route('operaciones')"
            :current="request()->routeIs('operaciones')"
            wire:navigate
        >
            Servicios en proceso
        </flux:sidebar.item>

        <flux:sidebar.item
            icon="arrow-path"
            disabled
        >
            Seguimiento
        </flux:sidebar.item>

    </flux:sidebar.group>


    {{-- GESTIÓN DOCUMENTARIA --}}
    <flux:sidebar.group heading="Gestión Documentaria" class="grid">

        <flux:sidebar.item
            icon="folder"
            disabled
        >
            Documentos de clientes
        </flux:sidebar.item>

        <flux:sidebar.item
            icon="document-duplicate"
            disabled
        >
            Expedientes
        </flux:sidebar.item>

    </flux:sidebar.group>


    {{-- REPORTES Y GERENCIA --}}
    <flux:sidebar.group heading="Reportes y Gerencia" class="grid">

        <flux:sidebar.item
            icon="chart-bar"
            :href="route('reportes')"
            :current="request()->routeIs('reportes')"
            wire:navigate
        >
            Reportes
        </flux:sidebar.item>

        <flux:sidebar.item
            icon="presentation-chart-line"
            disabled
        >
            SLA / KPI
        </flux:sidebar.item>

    </flux:sidebar.group>


    {{-- ADMINISTRACIÓN DEL SISTEMA --}}
    <flux:sidebar.group heading="Administración del Sistema" class="grid">

        <flux:sidebar.item
            icon="user-circle"
            :href="route('usuarios')"
            :current="request()->routeIs('usuarios')"
            wire:navigate
        >
            Usuarios
        </flux:sidebar.item>

        <flux:sidebar.item
            icon="shield-check"
            :href="route('roles')"
            :current="request()->routeIs('roles')"
            wire:navigate
        >
            Roles y permisos
        </flux:sidebar.item>

        <flux:sidebar.item
            icon="cog-6-tooth"
            :href="route('configuracion')"
            :current="request()->routeIs('configuracion')"
            wire:navigate
        >
            Configuración
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
                Mi Perfil
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
