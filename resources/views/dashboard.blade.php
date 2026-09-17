<x-layouts::app :title="__('Dashboard')">

<div class="min-h-screen bg-gray-50 px-4 py-6 text-gray-900 transition-colors dark:bg-gray-950 dark:text-white lg:px-6">

    {{-- =========================================================
         ENCABEZADO
         ========================================================= --}}
    <div class="mb-7 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">

        <div class="flex items-center gap-4">

            <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-slate-800 text-white shadow-sm dark:bg-slate-700">

                <svg
                    class="h-6 w-6"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                    stroke-width="1.8"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M3 13h8V3H3v10Zm10 8h8V11h-8v10ZM3 21h8v-6H3v6Zm10-18v6h8V3h-8Z"
                    />
                </svg>

            </div>

            <div>
                <h1 class="text-2xl font-bold tracking-tight text-slate-800 dark:text-white sm:text-3xl">
                    Dashboard
                </h1>

                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Resumen general del Sistema Integrado Administrativo.
                </p>
            </div>

        </div>


        <div class="rounded-xl border border-gray-200 bg-white px-4 py-2 text-sm shadow-sm dark:border-gray-800 dark:bg-gray-900">

            <div class="flex items-center gap-2">

                <span class="h-2 w-2 rounded-full bg-teal-500"></span>

                <span class="text-gray-600 dark:text-gray-300">
                    Sistema operativo
                </span>

            </div>

        </div>

    </div>


    {{-- =========================================================
         INDICADORES PRINCIPALES
         ========================================================= --}}
    <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">


        {{-- CLIENTES --}}
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md dark:border-gray-800 dark:bg-gray-900">

            <div class="flex items-start justify-between">

                <div>

                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">
                        Clientes
                    </p>

                    <p class="mt-2 text-3xl font-bold text-slate-800 dark:text-white">
                        {{ \App\Models\Cliente::count() }}
                    </p>

                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-500">
                        Empresas registradas
                    </p>

                </div>

                <div class="rounded-xl bg-slate-100 p-3 text-slate-700 dark:bg-slate-800 dark:text-slate-300">

                    <svg
                        class="h-5 w-5"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                        stroke-width="1.8"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M16 19h6M19 16v6M12 13a4 4 0 1 0 0-8 4 4 0 0 0 0 8Zm-7 8a7 7 0 0 1 14 0"
                        />
                    </svg>

                </div>

            </div>

        </div>


        {{-- SERVICIOS --}}
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md dark:border-gray-800 dark:bg-gray-900">

            <div class="flex items-start justify-between">

                <div>

                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">
                        Servicios activos
                    </p>

                    <p class="mt-2 text-3xl font-bold text-slate-800 dark:text-white">
                        {{ \App\Models\Servicio::where('estado', 'Activo')->count() }}
                    </p>

                    <p class="mt-1 text-xs text-teal-600 dark:text-teal-400">
                        Disponibles para clientes
                    </p>

                </div>

                <div class="rounded-xl bg-teal-50 p-3 text-teal-700 dark:bg-teal-950 dark:text-teal-400">

                    <svg
                        class="h-5 w-5"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                        stroke-width="1.8"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h7l5 5v11a2 2 0 0 1-2 2Z"
                        />

                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M14 3v5h5"
                        />
                    </svg>

                </div>

            </div>

        </div>


        {{-- CONTRATOS --}}
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md dark:border-gray-800 dark:bg-gray-900">

            <div class="flex items-start justify-between">

                <div>

                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">
                        Contratos activos
                    </p>

                    <p class="mt-2 text-3xl font-bold text-teal-700 dark:text-teal-400">
                        {{ \App\Models\Contrato::where('estado', 'Activo')->count() }}
                    </p>

                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-500">
                        Servicios actualmente vigentes
                    </p>

                </div>

                <div class="rounded-xl bg-teal-50 p-3 text-teal-700 dark:bg-teal-950 dark:text-teal-400">

                    <svg
                        class="h-5 w-5"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                        stroke-width="1.8"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h7l5 5v11a2 2 0 0 1-2 2Z"
                        />

                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M14 3v5h5"
                        />
                    </svg>

                </div>

            </div>

        </div>


        {{-- INGRESO MENSUAL --}}
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md dark:border-gray-800 dark:bg-gray-900">

            <div class="flex items-start justify-between">

                <div>

                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">
                        Ingreso mensual
                    </p>

                    <p class="mt-2 text-2xl font-bold text-slate-800 dark:text-white">
                        S/
                        {{ number_format(
                            \App\Models\Contrato::where('estado', 'Activo')->sum('monto_mensual'),
                            2
                        ) }}
                    </p>

                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-500">
                        Contratos activos
                    </p>

                </div>

                <div class="rounded-xl bg-slate-100 p-3 text-slate-700 dark:bg-slate-800 dark:text-slate-300">

                    <svg
                        class="h-5 w-5"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                        stroke-width="1.8"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M12 8c-2.2 0-4 1.1-4 2.5S9.8 13 12 13s4 1.1 4 2.5S14.2 18 12 18m0-12v2m0 10v2"
                        />

                        <circle
                            cx="12"
                            cy="12"
                            r="9"
                        />
                    </svg>

                </div>

            </div>

        </div>

    </div>


    {{-- =========================================================
         SEGUNDO GRUPO DE INDICADORES
         ========================================================= --}}
    <div class="mb-6 grid grid-cols-1 gap-4 md:grid-cols-3">


        {{-- EMPLEADOS --}}
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900">

            <div class="flex items-center justify-between">

                <div>

                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">
                        Empleados activos
                    </p>

                    <p class="mt-2 text-2xl font-bold text-teal-700 dark:text-teal-400">
                        {{ \App\Models\Empleado::where('estado', 'Activo')->count() }}
                    </p>

                </div>

                <div class="rounded-xl bg-teal-50 p-3 text-teal-700 dark:bg-teal-950 dark:text-teal-400">

                    <svg
                        class="h-5 w-5"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                        stroke-width="1.8"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M17 21v-2a4 4 0 0 0-4-4H7a4 4 0 0 0-4 4v2"
                        />

                        <circle cx="10" cy="7" r="4" />

                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M21 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"
                        />
                    </svg>

                </div>

            </div>

        </div>


        {{-- POSTULANTES --}}
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900">

            <div class="flex items-center justify-between">

                <div>

                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">
                        Postulantes
                    </p>

                    <p class="mt-2 text-2xl font-bold text-slate-800 dark:text-white">
                        {{ \App\Models\Postulante::count() }}
                    </p>

                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-500">
                        Personas registradas
                    </p>

                </div>

                <div class="rounded-xl bg-slate-100 p-3 text-slate-700 dark:bg-slate-800 dark:text-slate-300">

                    <svg
                        class="h-5 w-5"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                        stroke-width="1.8"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M15 19a4 4 0 0 0-8 0"
                        />

                        <circle cx="11" cy="9" r="4" />

                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M18 8v6m3-3h-6"
                        />
                    </svg>

                </div>

            </div>

        </div>


        {{-- OBLIGACIONES --}}
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900">

            <div class="flex items-center justify-between">

                <div>

                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">
                        Obligaciones pendientes
                    </p>

                    <p class="mt-2 text-2xl font-bold text-amber-600 dark:text-amber-400">
                        {{ \App\Models\ObligacionTributaria::where('estado', 'Pendiente')->count() }}
                    </p>

                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-500">
                        Requieren seguimiento
                    </p>

                </div>

                <div class="rounded-xl bg-amber-50 p-3 text-amber-600 dark:bg-amber-950 dark:text-amber-400">

                    <svg
                        class="h-5 w-5"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                        stroke-width="1.8"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M12 9v4"
                        />

                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M12 17h.01"
                        />

                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="m10.3 3.8-8 13.5A2 2 0 0 0 4 20h16a2 2 0 0 0 1.7-2.7l-8-13.5a2 2 0 0 0-3.4 0Z"
                        />
                    </svg>

                </div>

            </div>

        </div>

    </div>


    {{-- =========================================================
         ACCESOS RÁPIDOS
         ========================================================= --}}
    <div class="mb-6 rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900">

        <div class="mb-5">

            <h2 class="text-lg font-bold text-slate-800 dark:text-white">
                Accesos rápidos
            </h2>

            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                Accede rápidamente a las funciones principales.
            </p>

        </div>


        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-4">


            {{-- CLIENTES --}}
            <a
                href="{{ route('clientes') }}"
                wire:navigate
                class="group rounded-xl border border-gray-200 bg-gray-50 p-4 transition hover:border-teal-300 hover:bg-teal-50 dark:border-gray-800 dark:bg-gray-950 dark:hover:border-teal-800 dark:hover:bg-teal-950/30"
            >

                <div class="mb-3 flex h-10 w-10 items-center justify-center rounded-xl bg-slate-100 text-slate-700 group-hover:bg-teal-100 group-hover:text-teal-700 dark:bg-slate-800 dark:text-slate-300 dark:group-hover:bg-teal-950 dark:group-hover:text-teal-400">

                    <svg
                        class="h-5 w-5"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                        stroke-width="1.8"
                    >
                        <circle cx="9" cy="7" r="4" />
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M3 21a6 6 0 0 1 12 0"
                        />
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M16 11a4 4 0 0 1 5 4"
                        />
                    </svg>

                </div>

                <h3 class="font-semibold text-gray-900 dark:text-white">
                    Clientes
                </h3>

                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Gestionar empresas clientes
                </p>

            </a>


            {{-- SERVICIOS --}}
            <a
                href="{{ route('servicios') }}"
                wire:navigate
                class="group rounded-xl border border-gray-200 bg-gray-50 p-4 transition hover:border-teal-300 hover:bg-teal-50 dark:border-gray-800 dark:bg-gray-950 dark:hover:border-teal-800 dark:hover:bg-teal-950/30"
            >

                <div class="mb-3 flex h-10 w-10 items-center justify-center rounded-xl bg-slate-100 text-slate-700 group-hover:bg-teal-100 group-hover:text-teal-700 dark:bg-slate-800 dark:text-slate-300 dark:group-hover:bg-teal-950 dark:group-hover:text-teal-400">

                    <svg
                        class="h-5 w-5"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                        stroke-width="1.8"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h7l5 5v11a2 2 0 0 1-2 2Z"
                        />

                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M14 3v5h5"
                        />
                    </svg>

                </div>

                <h3 class="font-semibold text-gray-900 dark:text-white">
                    Servicios
                </h3>

                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Administrar catálogo de servicios
                </p>

            </a>


            {{-- CONTRATOS --}}
            <a
                href="{{ route('contratos') }}"
                wire:navigate
                class="group rounded-xl border border-gray-200 bg-gray-50 p-4 transition hover:border-teal-300 hover:bg-teal-50 dark:border-gray-800 dark:bg-gray-950 dark:hover:border-teal-800 dark:hover:bg-teal-950/30"
            >

                <div class="mb-3 flex h-10 w-10 items-center justify-center rounded-xl bg-slate-100 text-slate-700 group-hover:bg-teal-100 group-hover:text-teal-700 dark:bg-slate-800 dark:text-slate-300 dark:group-hover:bg-teal-950 dark:group-hover:text-teal-400">

                    <svg
                        class="h-5 w-5"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                        stroke-width="1.8"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h7l5 5v11a2 2 0 0 1-2 2Z"
                        />

                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M14 3v5h5"
                        />
                    </svg>

                </div>

                <h3 class="font-semibold text-gray-900 dark:text-white">
                    Contratos
                </h3>

                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Gestionar servicios contratados
                </p>

            </a>


            {{-- PLANILLAS --}}
            <a
                href="{{ route('planillas') }}"
                wire:navigate
                class="group rounded-xl border border-gray-200 bg-gray-50 p-4 transition hover:border-teal-300 hover:bg-teal-50 dark:border-gray-800 dark:bg-gray-950 dark:hover:border-teal-800 dark:hover:bg-teal-950/30"
            >

                <div class="mb-3 flex h-10 w-10 items-center justify-center rounded-xl bg-slate-100 text-slate-700 group-hover:bg-teal-100 group-hover:text-teal-700 dark:bg-slate-800 dark:text-slate-300 dark:group-hover:bg-teal-950 dark:group-hover:text-teal-400">

                    <svg
                        class="h-5 w-5"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                        stroke-width="1.8"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M12 8c-2.2 0-4 1.1-4 2.5S9.8 13 12 13s4 1.1 4 2.5S14.2 18 12 18"
                        />

                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M12 6v2m0 10v2"
                        />

                        <circle cx="12" cy="12" r="9" />
                    </svg>

                </div>

                <h3 class="font-semibold text-gray-900 dark:text-white">
                    Planillas
                </h3>

                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Gestionar remuneraciones
                </p>

            </a>

        </div>

    </div>


    {{-- =========================================================
         ACTIVIDAD RECIENTE
         ========================================================= --}}
    <div class="grid grid-cols-1 gap-6 xl:grid-cols-2">


        {{-- ÚLTIMOS CLIENTES --}}
        <div class="rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">

            <div class="flex items-center justify-between border-b border-gray-200 px-5 py-4 dark:border-gray-800">

                <div>

                    <h2 class="font-bold text-slate-800 dark:text-white">
                        Últimos clientes
                    </h2>

                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        Empresas registradas recientemente.
                    </p>

                </div>

                <a
                    href="{{ route('clientes') }}"
                    wire:navigate
                    class="text-sm font-semibold text-teal-700 hover:text-teal-800 dark:text-teal-400 dark:hover:text-teal-300"
                >
                    Ver todos →
                </a>

            </div>


            <div class="overflow-x-auto">

                <table class="w-full text-left text-sm">

                    <thead class="bg-gray-50 dark:bg-gray-950">

                        <tr>

                            <th class="px-5 py-3 font-semibold text-gray-600 dark:text-gray-300">
                                Empresa
                            </th>

                            <th class="px-5 py-3 font-semibold text-gray-600 dark:text-gray-300">
                                Servicio
                            </th>

                            <th class="px-5 py-3 font-semibold text-gray-600 dark:text-gray-300">
                                Estado
                            </th>

                        </tr>

                    </thead>


                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">

                        @forelse (\App\Models\Cliente::latest()->take(5)->get() as $cliente)

                            <tr class="transition hover:bg-gray-50 dark:hover:bg-gray-800/50">

                                <td class="px-5 py-4">

                                    <div class="font-semibold text-gray-900 dark:text-white">
                                        {{ $cliente->razon_social }}
                                    </div>

                                    <div class="text-xs text-gray-500 dark:text-gray-400">
                                        RUC {{ $cliente->ruc }}
                                    </div>

                                </td>

                                <td class="px-5 py-4 text-gray-600 dark:text-gray-300">
                                    {{ $cliente->servicio_contratado ?: 'Sin servicio' }}
                                </td>

                                <td class="px-5 py-4">

                                    @if ($cliente->estado === 'Activo')

                                        <span class="inline-flex items-center gap-1.5 rounded-full bg-teal-50 px-3 py-1 text-xs font-semibold text-teal-700 dark:bg-teal-950 dark:text-teal-300">
                                            <span class="h-1.5 w-1.5 rounded-full bg-teal-600"></span>
                                            Activo
                                        </span>

                                    @else

                                        <span class="inline-flex items-center gap-1.5 rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-600 dark:bg-gray-800 dark:text-gray-300">
                                            <span class="h-1.5 w-1.5 rounded-full bg-gray-500"></span>
                                            Inactivo
                                        </span>

                                    @endif

                                </td>

                            </tr>

                        @empty

                            <tr>

                                <td
                                    colspan="3"
                                    class="px-5 py-10 text-center text-sm text-gray-500 dark:text-gray-400"
                                >
                                    Todavía no hay clientes registrados.
                                </td>

                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>

        </div>


        {{-- ÚLTIMOS CONTRATOS --}}
        <div class="rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">

            <div class="flex items-center justify-between border-b border-gray-200 px-5 py-4 dark:border-gray-800">

                <div>

                    <h2 class="font-bold text-slate-800 dark:text-white">
                        Contratos recientes
                    </h2>

                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        Servicios contratados recientemente.
                    </p>

                </div>

                <a
                    href="{{ route('contratos') }}"
                    wire:navigate
                    class="text-sm font-semibold text-teal-700 hover:text-teal-800 dark:text-teal-400 dark:hover:text-teal-300"
                >
                    Ver todos →
                </a>

            </div>


            <div class="overflow-x-auto">

                <table class="w-full text-left text-sm">

                    <thead class="bg-gray-50 dark:bg-gray-950">

                        <tr>

                            <th class="px-5 py-3 font-semibold text-gray-600 dark:text-gray-300">
                                Cliente
                            </th>

                            <th class="px-5 py-3 font-semibold text-gray-600 dark:text-gray-300">
                                Servicio
                            </th>

                            <th class="px-5 py-3 font-semibold text-gray-600 dark:text-gray-300">
                                Monto
                            </th>

                        </tr>

                    </thead>


                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">

                        @forelse (
                            \App\Models\Contrato::with(['cliente', 'servicio'])
                                ->latest()
                                ->take(5)
                                ->get()
                            as $contrato
                        )

                            <tr class="transition hover:bg-gray-50 dark:hover:bg-gray-800/50">

                                <td class="px-5 py-4">

                                    <div class="font-semibold text-gray-900 dark:text-white">
                                        {{ $contrato->cliente->razon_social ?? 'Sin cliente' }}
                                    </div>

                                </td>

                                <td class="px-5 py-4 text-gray-600 dark:text-gray-300">
                                    {{ $contrato->servicio->nombre ?? 'Sin servicio' }}
                                </td>

                                <td class="px-5 py-4 font-semibold text-gray-900 dark:text-white">
                                    S/ {{ number_format($contrato->monto_mensual, 2) }}
                                </td>

                            </tr>

                        @empty

                            <tr>

                                <td
                                    colspan="3"
                                    class="px-5 py-10 text-center text-sm text-gray-500 dark:text-gray-400"
                                >
                                    Todavía no hay contratos registrados.
                                </td>

                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>

        </div>

    </div>


</div>

</x-layouts::app>