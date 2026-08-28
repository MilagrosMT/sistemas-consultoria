<x-layouts::app :title="__('Dashboard')">

```
{{-- =========================================================
     DASHBOARD PRINCIPAL
     Sistema Integrado Administrativo
     ========================================================= --}}

<div class="min-h-screen bg-gray-50 p-6">

    {{-- =====================================================
         ENCABEZADO
         ===================================================== --}}
    <div class="mb-8">

        <h1 class="text-3xl font-bold text-gray-900">
            Dashboard
        </h1>

        <p class="mt-1 text-sm text-gray-600">
            Resumen general del Sistema Integrado Administrativo.
        </p>

    </div>


    {{-- =====================================================
         INDICADORES GENERALES
         ===================================================== --}}

    <div class="mb-8 grid grid-cols-1 gap-5 md:grid-cols-2 xl:grid-cols-4">

        {{-- TOTAL EMPLEADOS --}}
        <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">

            <p class="text-sm font-medium text-gray-500">
                Total de empleados
            </p>

            <p class="mt-2 text-3xl font-bold text-gray-900">
                {{ \App\Models\Empleado::count() }}
            </p>

            <p class="mt-1 text-xs text-gray-500">
                Personal registrado
            </p>

        </div>


        {{-- TOTAL POSTULANTES --}}
        <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">

            <p class="text-sm font-medium text-gray-500">
                Total de postulantes
            </p>

            <p class="mt-2 text-3xl font-bold text-gray-900">
                {{ \App\Models\Postulante::count() }}
            </p>

            <p class="mt-1 text-xs text-gray-500">
                Personas registradas en reclutamiento
            </p>

        </div>


        {{-- TOTAL PLANILLAS --}}
        <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">

            <p class="text-sm font-medium text-gray-500">
                Planillas registradas
            </p>

            <p class="mt-2 text-3xl font-bold text-gray-900">
                {{ \App\Models\Planilla::count() }}
            </p>

            <p class="mt-1 text-xs text-gray-500">
                Registros de planilla
            </p>

        </div>


        {{-- OBLIGACIONES TRIBUTARIAS --}}
        <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">

            <p class="text-sm font-medium text-gray-500">
                Obligaciones tributarias
            </p>

            <p class="mt-2 text-3xl font-bold text-gray-900">
                {{ \App\Models\ObligacionTributaria::count() }}
            </p>

            <p class="mt-1 text-xs text-gray-500">
                Obligaciones registradas
            </p>

        </div>

    </div>


    {{-- =====================================================
         SEGUNDO GRUPO DE INDICADORES
         ===================================================== --}}

    <div class="mb-8 grid grid-cols-1 gap-5 md:grid-cols-3">

        {{-- EMPLEADOS ACTIVOS --}}
        <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">

            <p class="text-sm font-medium text-gray-500">
                Empleados activos
            </p>

            <p class="mt-2 text-3xl font-bold text-green-600">
                {{ \App\Models\Empleado::where('estado', 'Activo')->count() }}
            </p>

            <p class="mt-1 text-xs text-gray-500">
                Personal actualmente activo
            </p>

        </div>


        {{-- PLANILLAS PAGADAS --}}
        <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">

            <p class="text-sm font-medium text-gray-500">
                Planillas pagadas
            </p>

            <p class="mt-2 text-3xl font-bold text-green-600">
                {{ \App\Models\Planilla::where('estado', 'Pagada')->count() }}
            </p>

            <p class="mt-1 text-xs text-gray-500">
                Planillas con estado pagado
            </p>

        </div>


        {{-- OBLIGACIONES PENDIENTES --}}
        <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">

            <p class="text-sm font-medium text-gray-500">
                Obligaciones pendientes
            </p>

            <p class="mt-2 text-3xl font-bold text-yellow-600">
                {{ \App\Models\ObligacionTributaria::where('estado', 'Pendiente')->count() }}
            </p>

            <p class="mt-1 text-xs text-gray-500">
                Obligaciones por atender
            </p>

        </div>

    </div>


    {{-- =====================================================
         ACCESOS RÁPIDOS
         ===================================================== --}}

    <div class="mb-8 rounded-xl border border-gray-200 bg-white p-6 shadow-sm">

        <div class="mb-5">

            <h2 class="text-xl font-semibold text-gray-900">
                Accesos rápidos
            </h2>

            <p class="mt-1 text-sm text-gray-500">
                Accede rápidamente a las funciones principales.
            </p>

        </div>


        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">


            {{-- EMPLEADOS --}}
            <a
                href="{{ route('empleados') }}"
                wire:navigate
                class="rounded-lg border border-gray-200 p-5 transition hover:border-gray-400 hover:bg-gray-50"
            >

                <h3 class="font-semibold text-gray-900">
                    Empleados
                </h3>

                <p class="mt-1 text-sm text-gray-500">
                    Gestionar personal
                </p>

            </a>


            {{-- PLANILLAS --}}
            <a
                href="{{ route('planillas') }}"
                wire:navigate
                class="rounded-lg border border-gray-200 p-5 transition hover:border-gray-400 hover:bg-gray-50"
            >

                <h3 class="font-semibold text-gray-900">
                    Planillas
                </h3>

                <p class="mt-1 text-sm text-gray-500">
                    Gestionar planillas y remuneraciones
                </p>

            </a>


            {{-- RECLUTAMIENTO --}}
            <a
                href="{{ route('reclutamiento') }}"
                wire:navigate
                class="rounded-lg border border-gray-200 p-5 transition hover:border-gray-400 hover:bg-gray-50"
            >

                <h3 class="font-semibold text-gray-900">
                    Reclutamiento
                </h3>

                <p class="mt-1 text-sm text-gray-500">
                    Gestionar postulantes
                </p>

            </a>


            {{-- ADMINISTRACIÓN TRIBUTARIA --}}
            <a
                href="{{ route('administracion-tributaria') }}"
                wire:navigate
                class="rounded-lg border border-gray-200 p-5 transition hover:border-gray-400 hover:bg-gray-50"
            >

                <h3 class="font-semibold text-gray-900">
                    Administración tributaria
                </h3>

                <p class="mt-1 text-sm text-gray-500">
                    Gestionar obligaciones tributarias
                </p>

            </a>

        </div>

    </div>


    {{-- =====================================================
         ÚLTIMOS EMPLEADOS
         ===================================================== --}}

    <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">

        <div class="mb-5 flex items-center justify-between">

            <div>

                <h2 class="text-xl font-semibold text-gray-900">
                    Últimos empleados registrados
                </h2>

                <p class="mt-1 text-sm text-gray-500">
                    Personal agregado recientemente.
                </p>

            </div>

            <a
                href="{{ route('empleados') }}"
                wire:navigate
                class="text-sm font-medium text-gray-700 hover:text-gray-900"
            >
                Ver todos →
            </a>

        </div>


        <div class="overflow-x-auto">

            <table class="w-full text-left text-sm">

                <thead>

                    <tr class="border-b border-gray-200 bg-gray-50">

                        <th class="px-4 py-3 font-semibold text-gray-700">
                            DNI
                        </th>

                        <th class="px-4 py-3 font-semibold text-gray-700">
                            Nombre
                        </th>

                        <th class="px-4 py-3 font-semibold text-gray-700">
                            Cargo
                        </th>

                        <th class="px-4 py-3 font-semibold text-gray-700">
                            Área
                        </th>

                        <th class="px-4 py-3 font-semibold text-gray-700">
                            Estado
                        </th>

                    </tr>

                </thead>


                <tbody>

                    @forelse (
                        \App\Models\Empleado::latest()->take(5)->get()
                        as $empleado
                    )

                        <tr class="border-b border-gray-100 hover:bg-gray-50">

                            <td class="px-4 py-3 font-medium text-gray-900">
                                {{ $empleado->dni }}
                            </td>

                            <td class="px-4 py-3 text-gray-700">
                                {{ $empleado->nombres }}
                                {{ $empleado->apellidos }}
                            </td>

                            <td class="px-4 py-3 text-gray-700">
                                {{ $empleado->cargo }}
                            </td>

                            <td class="px-4 py-3 text-gray-700">
                                {{ $empleado->area }}
                            </td>

                            <td class="px-4 py-3">

                                @if ($empleado->estado === 'Activo')

                                    <span class="rounded-full bg-green-100 px-3 py-1 text-xs font-medium text-green-700">
                                        Activo
                                    </span>

                                @else

                                    <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-600">
                                        Inactivo
                                    </span>

                                @endif

                            </td>

                        </tr>

                    @empty

                        <tr>

                            <td
                                colspan="5"
                                class="px-4 py-8 text-center text-gray-500"
                            >
                                Todavía no hay empleados registrados.
                            </td>

                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

    </div>

</div>
```

</x-layouts::app>
