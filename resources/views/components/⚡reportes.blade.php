<?php

use App\Models\Cliente;
use App\Models\Contrato;
use App\Models\Empleado;
use App\Models\Planilla;
use App\Models\Postulante;
use App\Models\Servicio;
use App\Models\ObligacionTributaria;
use Livewire\Component;

new class extends Component
{
    public function with(): array
    {
        return [
            'totalClientes' => Cliente::count(),
            'clientesActivos' => Cliente::where('estado', 'Activo')->count(),

            'totalServicios' => Servicio::count(),
            'serviciosActivos' => Servicio::where('estado', 'Activo')->count(),

            'contratosActivos' => Contrato::where('estado', 'Activo')->count(),
            'contratosFinalizados' => Contrato::where('estado', 'Finalizado')->count(),

            'empleadosActivos' => Empleado::where('estado', 'Activo')->count(),

            'totalPostulantes' => Postulante::count(),

            'totalPlanillas' => Planilla::count(),
            'planillasPagadas' => Planilla::where('estado', 'Pagado')->count(),
'obligacionesTotales' => ObligacionTributaria::count(),

'obligacionesPagadas' => ObligacionTributaria::where(
    'estado',
    'Pagada'
)->count(),

'obligacionesVencidas' => ObligacionTributaria::where(
    'estado',
    'Vencida'
)->count(),

'cumplimientoTributario' => ObligacionTributaria::count() > 0
    ? round(
        (ObligacionTributaria::where('estado', 'Pagada')->count()
            / ObligacionTributaria::count()) * 100,
        1
    )
    : 0,

'cumplimientoPlanillas' => Planilla::count() > 0
    ? round(
        (Planilla::where('estado', 'Pagado')->count()
            / Planilla::count()) * 100,
        1
    )
    : 0,

            'ingresoMensual' => Contrato::where('estado', 'Activo')
                ->sum('monto_mensual'),
        ];
    }
};
?>

<div class="space-y-6 bg-zinc-50 p-6 text-zinc-900 transition-colors dark:bg-zinc-950 dark:text-white">

    {{-- Encabezado --}}
    <div>
        <flux:heading size="xl">
            Reportes
        </flux:heading>

        <flux:text class="mt-1">
            Resumen general de la información registrada en el sistema.
        </flux:text>
    </div>

    {{-- Indicadores principales --}}
    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">

        {{-- Clientes --}}
        <div class="rounded-xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            <div class="flex items-center justify-between">
                <div>
                    <flux:text>Clientes</flux:text>
                    <flux:heading size="xl" class="mt-1">
                        {{ $totalClientes }}
                    </flux:heading>
                    <flux:text class="mt-1 text-sm">
                        {{ $clientesActivos }} activos
                    </flux:text>
                </div>

                <div class="rounded-lg bg-blue-100 p-3 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300">
                    <flux:icon name="building-office" class="size-6" />
                </div>
            </div>
        </div>

        {{-- Servicios --}}
        <div class="rounded-xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            <div class="flex items-center justify-between">
                <div>
                    <flux:text>Servicios</flux:text>
                    <flux:heading size="xl" class="mt-1">
                        {{ $totalServicios }}
                    </flux:heading>
                    <flux:text class="mt-1 text-sm">
                        {{ $serviciosActivos }} activos
                    </flux:text>
                </div>

                <div class="rounded-lg bg-teal-100 p-3 text-teal-700 dark:bg-teal-900/40 dark:text-teal-300">
                    <flux:icon name="briefcase" class="size-6" />
                </div>
            </div>
        </div>

        {{-- Contratos --}}
        <div class="rounded-xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            <div class="flex items-center justify-between">
                <div>
                    <flux:text>Contratos activos</flux:text>
                    <flux:heading size="xl" class="mt-1">
                        {{ $contratosActivos }}
                    </flux:heading>
                    <flux:text class="mt-1 text-sm">
                        {{ $contratosFinalizados }} finalizados
                    </flux:text>
                </div>

                <div class="rounded-lg bg-indigo-100 p-3 text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-300">
                    <flux:icon name="document-text" class="size-6" />
                </div>
            </div>
        </div>

        {{-- Ingreso --}}
        <div class="rounded-xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            <div class="flex items-center justify-between">
                <div>
                    <flux:text>Ingreso mensual</flux:text>
                    <flux:heading size="xl" class="mt-1">
                        S/ {{ number_format($ingresoMensual, 2) }}
                    </flux:heading>
                    <flux:text class="mt-1 text-sm">
                        Contratos activos
                    </flux:text>
                </div>

                <div class="rounded-lg bg-emerald-100 p-3 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300">
                    <flux:icon name="banknotes" class="size-6" />
                </div>
            </div>
        </div>

    </div>

    {{-- Resumen operativo --}}
    <div class="grid gap-6 lg:grid-cols-3">

        <div class="rounded-xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            <flux:heading size="lg">
                Personal
            </flux:heading>

            <div class="mt-5 flex items-center gap-4">
                <div class="rounded-lg bg-blue-100 p-3 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300">
                    <flux:icon name="users" class="size-6" />
                </div>

                <div>
                    <flux:text>Empleados activos</flux:text>
                    <flux:heading size="xl">
                        {{ $empleadosActivos }}
                    </flux:heading>
                </div>
            </div>

            <div class="mt-5 flex items-center gap-4">
                <div class="rounded-lg bg-amber-100 p-3 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300">
                    <flux:icon name="user-plus" class="size-6" />
                </div>

                <div>
                    <flux:text>Postulantes registrados</flux:text>
                    <flux:heading size="xl">
                        {{ $totalPostulantes }}
                    </flux:heading>
                </div>
            </div>
        </div>

        <div class="rounded-xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            <flux:heading size="lg">
                Planillas
            </flux:heading>

            <div class="mt-5 flex items-center gap-4">
                <div class="rounded-lg bg-violet-100 p-3 text-violet-700 dark:bg-violet-900/40 dark:text-violet-300">
                    <flux:icon name="document-currency-dollar" class="size-6" />
                </div>

                <div>
                    <flux:text>Planillas registradas</flux:text>
                    <flux:heading size="xl">
                        {{ $totalPlanillas }}
                    </flux:heading>
                </div>
            </div>

            <div class="mt-5">
                <flux:text class="text-sm">
                    Pagadas: {{ $planillasPagadas }}
                </flux:text>
            </div>
        </div>

        <div class="rounded-xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            <flux:heading size="lg">
                Estado general
            </flux:heading>

            <div class="mt-5 space-y-4">

                <div class="flex items-center justify-between">
                    <flux:text>Clientes activos</flux:text>
                    <flux:badge color="green">
                        {{ $clientesActivos }}
                    </flux:badge>
                </div>

                <div class="flex items-center justify-between">
                    <flux:text>Servicios activos</flux:text>
                    <flux:badge color="green">
                        {{ $serviciosActivos }}
                    </flux:badge>
                </div>

                <div class="flex items-center justify-between">
                    <flux:text>Contratos activos</flux:text>
                    <flux:badge color="green">
                        {{ $contratosActivos }}
                    </flux:badge>
                </div>

            </div>
        </div>

    </div>
{{-- SLA Y KPI --}}
<div class="rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-800 dark:bg-zinc-950">

    <div class="mb-5">
        <flux:heading size="lg">
            SLA y KPI
        </flux:heading>

        <flux:text class="mt-1">
            Indicadores de cumplimiento para evaluar la calidad del servicio.
        </flux:text>
    </div>

    <div class="grid gap-4 md:grid-cols-2">

        {{-- Cumplimiento tributario --}}
        <div class="rounded-xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
            <div class="flex items-start justify-between gap-4">

                <div>
                    <flux:text>
                        Cumplimiento tributario
                    </flux:text>

                    <div class="mt-2 flex items-baseline gap-2">
                        <span class="text-3xl font-bold text-zinc-900 dark:text-white">
                            {{ $cumplimientoTributario }}%
                        </span>

                        <span class="text-sm text-zinc-600 dark:text-zinc-500">
                            / Meta 95%
                        </span>
                    </div>

                    <p class="mt-2 text-xs text-zinc-600 dark:text-zinc-500">
                        Obligaciones pagadas:
                        {{ $obligacionesPagadas }} de {{ $obligacionesTotales }}
                    </p>
                </div>

                @if ($cumplimientoTributario >= 95)
                    <flux:badge color="green">
                        Cumple SLA
                    </flux:badge>
                @else
                    <flux:badge color="red">
                        Requiere atención
                    </flux:badge>
                @endif

            </div>
        </div>


        {{-- Cumplimiento de planillas --}}
        <div class="rounded-xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
            <div class="flex items-start justify-between gap-4">

                <div>
                    <flux:text>
                        Cumplimiento de planillas
                    </flux:text>

                    <div class="mt-2 flex items-baseline gap-2">
                        <span class="text-3xl font-bold text-zinc-900 dark:text-white">
                            {{ $cumplimientoPlanillas }}%
                        </span>

                        <span class="text-sm text-zinc-600 dark:text-zinc-500">
                            / Meta 95%
                        </span>
                    </div>

                    <p class="mt-2 text-xs text-zinc-600 dark:text-zinc-500">
                        Planillas pagadas:
                        {{ $planillasPagadas }} de {{ $totalPlanillas }}
                    </p>
                </div>

                @if ($cumplimientoPlanillas >= 95)
                    <flux:badge color="green">
                        Cumple SLA
                    </flux:badge>
                @else
                    <flux:badge color="red">
                        Requiere atención
                    </flux:badge>
                @endif

            </div>
        </div>

    </div>

</div>
</div>
