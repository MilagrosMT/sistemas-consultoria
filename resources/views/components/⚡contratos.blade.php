<?php

use App\Models\Cliente;
use App\Models\Contrato;
use App\Models\Servicio;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component
{
    use WithPagination;

    public string $buscar = '';

    public bool $mostrarFormulario = false;

    public ?int $contratoEditando = null;

    public string $cliente_id = '';
    public string $servicio_id = '';
    public string $fecha_inicio = '';
    public string $fecha_fin = '';
    public string $monto_mensual = '';
    public string $estado = 'Activo';
    public string $observaciones = '';

    public function updatedBuscar(): void
    {
        $this->resetPage();
    }

    public function nuevoContrato(): void
    {
        $this->resetFormulario();

        $this->fecha_inicio = now()->format('Y-m-d');
        $this->mostrarFormulario = true;
    }

    public function editarContrato(int $id): void
    {
        $contrato = Contrato::findOrFail($id);

        $this->contratoEditando = $contrato->id;
        $this->cliente_id = (string) $contrato->cliente_id;
        $this->servicio_id = (string) $contrato->servicio_id;
        $this->fecha_inicio = $contrato->fecha_inicio?->format('Y-m-d') ?? '';
        $this->fecha_fin = $contrato->fecha_fin?->format('Y-m-d') ?? '';
        $this->monto_mensual = (string) $contrato->monto_mensual;
        $this->estado = $contrato->estado;
        $this->observaciones = $contrato->observaciones ?? '';

        $this->resetValidation();
        $this->mostrarFormulario = true;
    }

    public function guardarContrato(): void
    {
        $datos = $this->validate([
            'cliente_id' => 'required|exists:clientes,id',
            'servicio_id' => 'required|exists:servicios,id',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'nullable|date|after_or_equal:fecha_inicio',
            'monto_mensual' => 'required|numeric|min:0',
            'estado' => 'required|in:Activo,Finalizado,Suspendido',
            'observaciones' => 'nullable|string|max:1000',
        ]);

        $datos['fecha_fin'] = $this->fecha_fin !== ''
            ? $this->fecha_fin
            : null;

        if ($this->contratoEditando) {
            Contrato::findOrFail($this->contratoEditando)->update($datos);

            session()->flash(
                'mensaje',
                'Contrato actualizado correctamente.'
            );
        } else {
            Contrato::create($datos);

            session()->flash(
                'mensaje',
                'Contrato registrado correctamente.'
            );
        }

        $this->resetFormulario();
        $this->mostrarFormulario = false;
    }

    public function cambiarEstado(int $id, string $nuevoEstado): void
    {
        $contrato = Contrato::findOrFail($id);

        $contrato->update([
            'estado' => $nuevoEstado,
        ]);

        session()->flash(
            'mensaje',
            'Estado del contrato actualizado correctamente.'
        );
    }

    public function eliminarContrato(int $id): void
    {
        Contrato::findOrFail($id)->delete();

        session()->flash(
            'mensaje',
            'Contrato eliminado correctamente.'
        );
    }

    public function cancelarFormulario(): void
    {
        $this->resetFormulario();
        $this->mostrarFormulario = false;
    }

    private function resetFormulario(): void
    {
        $this->reset([
            'contratoEditando',
            'cliente_id',
            'servicio_id',
            'fecha_inicio',
            'fecha_fin',
            'monto_mensual',
            'observaciones',
        ]);

        $this->estado = 'Activo';
        $this->resetValidation();
    }

    public function with(): array
    {
        $consulta = Contrato::with(['cliente', 'servicio']);

        if ($this->buscar) {
            $consulta->where(function ($query) {
                $query->whereHas('cliente', function ($q) {
                    $q->where('razon_social', 'like', '%' . $this->buscar . '%')
                        ->orWhere('ruc', 'like', '%' . $this->buscar . '%');
                })->orWhereHas('servicio', function ($q) {
                    $q->where('nombre', 'like', '%' . $this->buscar . '%');
                });
            });
        }

        return [
            'contratos' => $consulta->latest()->paginate(10),

            'totalContratos' => Contrato::count(),

            'contratosActivos' => Contrato::where(
                'estado',
                'Activo'
            )->count(),

            'contratosSuspendidos' => Contrato::where(
                'estado',
                'Suspendido'
            )->count(),

            'ingresosMensuales' => Contrato::where(
                'estado',
                'Activo'
            )->sum('monto_mensual'),

            'clientesDisponibles' => Cliente::orderBy(
                'razon_social'
            )->get(),

            'serviciosDisponibles' => Servicio::where(
                'estado',
                'Activo'
            )->orderBy('nombre')->get(),
        ];
    }
};
?>

<div class="min-h-screen bg-zinc-50 p-4 text-zinc-900 transition-colors dark:bg-zinc-950 dark:text-zinc-100 sm:p-6">

    <div class="mx-auto max-w-7xl space-y-6">

        {{-- =====================================================
             ENCABEZADO
             ===================================================== --}}
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">

            <div>

                <div class="mb-2 flex items-center gap-2 text-sm text-zinc-600 dark:text-zinc-400">

                    <svg
                        class="size-4"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="1.8"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h7l5 5v11a2 2 0 0 1-2 2Z"
                        />
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="1.8"
                            d="M14 3v5h5"
                        />
                    </svg>

                    <span>Gestión de clientes</span>

                    <span>/</span>

                    <span>Contratos</span>

                </div>

                <h1 class="text-3xl font-bold tracking-tight text-zinc-900 dark:text-white">
                    Contratos
                </h1>

                <p class="mt-1 text-zinc-700 dark:text-zinc-400">
                    Gestiona los servicios contratados por los clientes.
                </p>

            </div>

            <button
                type="button"
                wire:click="nuevoContrato"
                class="inline-flex items-center justify-center gap-2 rounded-xl bg-teal-600 px-5 py-3 text-sm font-semibold text-zinc-900 dark:text-white shadow-sm transition hover:bg-teal-500 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:ring-offset-2 focus:ring-offset-zinc-50 dark:focus:ring-offset-zinc-950"
            >

                <svg
                    class="size-5"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M12 5v14M5 12h14"
                    />
                </svg>

                Nuevo contrato

            </button>

        </div>


        {{-- =====================================================
             MENSAJE
             ===================================================== --}}
        @if (session('mensaje'))

            <div class="flex items-center gap-3 rounded-xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm text-emerald-700 dark:border-emerald-900/70 dark:bg-emerald-950/40 dark:text-emerald-300">

                <svg
                    class="size-5 shrink-0"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="m5 12 4 4L19 7"
                    />
                </svg>

                <span>{{ session('mensaje') }}</span>

            </div>

        @endif


        {{-- =====================================================
             TARJETAS DE RESUMEN
             ===================================================== --}}
        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">

            {{-- Total --}}
            <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">

                <div class="flex items-center justify-between">

                    <div>

                        <p class="text-sm font-medium text-zinc-600 dark:text-zinc-400">
                            Total contratos
                        </p>

                        <p class="mt-2 text-3xl font-bold text-zinc-900 dark:text-white">
                            {{ $totalContratos }}
                        </p>

                    </div>

                    <div class="rounded-xl bg-zinc-100 p-3 text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300">

                        <svg
                            class="size-6"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="1.8"
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h7l5 5v11a2 2 0 0 1-2 2Z"
                            />
                        </svg>

                    </div>

                </div>

            </div>


            {{-- Activos --}}
            <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">

                <div class="flex items-center justify-between">

                    <div>

                        <p class="text-sm font-medium text-zinc-600 dark:text-zinc-400">
                            Contratos activos
                        </p>

                        <p class="mt-2 text-3xl font-bold text-emerald-700 dark:text-emerald-400">
                            {{ $contratosActivos }}
                        </p>

                    </div>

                    <div class="rounded-xl bg-emerald-50 p-3 text-emerald-700 dark:bg-emerald-950/70 dark:text-emerald-400">

                        <svg
                            class="size-6"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="m5 12 4 4L19 6"
                            />
                        </svg>

                    </div>

                </div>

            </div>


            {{-- Suspendidos --}}
            <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">

                <div class="flex items-center justify-between">

                    <div>

                        <p class="text-sm font-medium text-zinc-600 dark:text-zinc-400">
                            Suspendidos
                        </p>

                        <p class="mt-2 text-3xl font-bold text-amber-700 dark:text-amber-400">
                            {{ $contratosSuspendidos }}
                        </p>

                    </div>

                    <div class="rounded-xl bg-amber-50 p-3 text-amber-700 dark:bg-amber-950/70 dark:text-amber-400">

                        <svg
                            class="size-6"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="1.8"
                                d="M12 8v4l2.5 2.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"
                            />
                        </svg>

                    </div>

                </div>

            </div>


            {{-- Ingreso --}}
            <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">

                <div class="flex items-center justify-between">

                    <div>

                        <p class="text-sm font-medium text-zinc-600 dark:text-zinc-400">
                            Ingreso mensual
                        </p>

                        <p class="mt-2 text-3xl font-bold text-zinc-900 dark:text-white">
                            S/ {{ number_format($ingresosMensuales, 2) }}
                        </p>

                    </div>

                    <div class="rounded-xl bg-zinc-100 p-3 text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300">

                        <svg
                            class="size-6"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <circle
                                cx="12"
                                cy="12"
                                r="8"
                                stroke-width="1.8"
                            />

                            <path
                                stroke-linecap="round"
                                stroke-width="1.8"
                                d="M9.5 10.5c0-1 1-1.5 2.5-1.5s2.5.5 2.5 1.5-1 1.5-2.5 1.5-2.5.5-2.5 1.5 1 1.5 2.5 1.5 2.5-.5 2.5-1.5"
                            />
                        </svg>

                    </div>

                </div>

            </div>

        </div>


        {{-- =====================================================
             BUSCADOR
             ===================================================== --}}
        <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">

            <label class="mb-2 block text-sm font-semibold text-zinc-700 dark:text-zinc-200">
                Buscar contrato
            </label>

            <div class="relative">

                <svg
                    class="pointer-events-none absolute left-4 top-1/2 size-5 -translate-y-1/2 text-zinc-500"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="1.8"
                        d="m21 21-4.3-4.3m2.3-5.2a7.5 7.5 0 1 1-15 0 7.5 7.5 0 0 1 15 0Z"
                    />
                </svg>

                <input
                    type="text"
                    wire:model.live="buscar"
                    placeholder="Buscar por cliente, RUC o servicio..."
                    class="w-full rounded-xl border border-zinc-700/70 bg-zinc-900 py-3.5 pl-12 pr-4 text-sm text-white outline-none transition placeholder:text-zinc-500 focus:border-teal-500 focus:ring-2 focus:ring-teal-500/20"
                >

            </div>

        </div>


        {{-- =====================================================
             TABLA
             ===================================================== --}}
        <div class="overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900">

            <div class="overflow-x-auto">

                <table class="min-w-full text-left text-sm">

                    <thead class="border-b border-zinc-200 bg-zinc-50 dark:border-zinc-800/60 dark:bg-zinc-950">

                        <tr>

                            <th class="whitespace-nowrap px-6 py-4 text-xs font-semibold uppercase tracking-wide text-zinc-600 dark:text-zinc-400">
                                Cliente
                            </th>

                            <th class="whitespace-nowrap px-6 py-4 text-xs font-semibold uppercase tracking-wide text-zinc-600 dark:text-zinc-400">
                                Servicio
                            </th>

                            <th class="whitespace-nowrap px-6 py-4 text-xs font-semibold uppercase tracking-wide text-zinc-600 dark:text-zinc-400">
                                Inicio
                            </th>

                            <th class="whitespace-nowrap px-6 py-4 text-xs font-semibold uppercase tracking-wide text-zinc-600 dark:text-zinc-400">
                                Monto
                            </th>

                            <th class="whitespace-nowrap px-6 py-4 text-xs font-semibold uppercase tracking-wide text-zinc-600 dark:text-zinc-400">
                                Estado
                            </th>

                            <th class="whitespace-nowrap px-6 py-4 text-right text-xs font-semibold uppercase tracking-wide text-zinc-600 dark:text-zinc-400">
                                Acciones
                            </th>

                        </tr>

                    </thead>


                    <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">

                        @forelse ($contratos as $contrato)

                            <tr class="transition hover:bg-zinc-50 dark:hover:bg-zinc-800/50">

                                {{-- Cliente --}}
                                <td class="px-6 py-5">

                                    <div class="flex items-center gap-3">

                                        <div class="flex size-10 shrink-0 items-center justify-center rounded-full bg-zinc-100 text-sm font-bold text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300">
                                            {{ strtoupper(substr($contrato->cliente->razon_social ?? 'SC', 0, 2)) }}
                                        </div>

                                        <div>

                                            <p class="font-semibold text-zinc-900 dark:text-white">
                                                {{ $contrato->cliente->razon_social ?? 'Sin cliente' }}
                                            </p>

                                            <p class="text-xs text-zinc-500">
                                                RUC:
                                                {{ $contrato->cliente->ruc ?? '—' }}
                                            </p>

                                        </div>

                                    </div>

                                </td>


                                {{-- Servicio --}}
                                <td class="px-6 py-5">

                                    <p class="font-medium text-zinc-800 dark:text-zinc-200">
                                        {{ $contrato->servicio->nombre ?? 'Sin servicio' }}
                                    </p>

                                </td>


                                {{-- Inicio --}}
                                <td class="whitespace-nowrap px-6 py-5 text-zinc-700 dark:text-zinc-400">
                                    {{ $contrato->fecha_inicio?->format('d/m/Y') }}
                                </td>


                                {{-- Monto --}}
                                <td class="whitespace-nowrap px-6 py-5 font-semibold text-zinc-900 dark:text-white">
                                    S/ {{ number_format($contrato->monto_mensual, 2) }}
                                </td>


                                {{-- Estado --}}
                                <td class="px-6 py-5">

                                    @if ($contrato->estado === 'Activo')

                                        <span class="inline-flex items-center gap-2 rounded-full bg-emerald-50 px-3 py-1.5 text-xs font-semibold text-emerald-700 dark:bg-emerald-950/70 dark:text-emerald-300">

                                            <span class="size-2 rounded-full bg-emerald-500"></span>

                                            Activo

                                        </span>

                                    @elseif ($contrato->estado === 'Suspendido')

                                        <span class="inline-flex items-center gap-2 rounded-full bg-amber-50 px-3 py-1.5 text-xs font-semibold text-amber-700 dark:bg-amber-950/70 dark:text-amber-300">

                                            <span class="size-2 rounded-full bg-amber-500"></span>

                                            Suspendido

                                        </span>

                                    @else

                                        <span class="inline-flex items-center gap-2 rounded-full bg-zinc-100 px-3 py-1.5 text-xs font-semibold text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300">

                                            <span class="size-2 rounded-full bg-zinc-500"></span>

                                            Finalizado

                                        </span>

                                    @endif

                                </td>


                                {{-- Acciones --}}
                                <td class="px-6 py-5">

                                    <div class="flex justify-end gap-1">

                                        <button
                                            type="button"
                                            wire:click="editarContrato({{ $contrato->id }})"
                                            title="Editar contrato"
                                            class="rounded-lg p-2 text-zinc-500 transition hover:bg-zinc-100 hover:text-zinc-900 dark:text-zinc-400 dark:hover:bg-zinc-800 dark:hover:text-white"
                                        >

                                            <svg
                                                class="size-5"
                                                fill="none"
                                                stroke="currentColor"
                                                viewBox="0 0 24 24"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="1.8"
                                                    d="m16.5 3.5 4 4M5 19l2.2-.5L19.5 6.2a1.4 1.4 0 0 0-2-2L5.2 16.5 5 19Z"
                                                />
                                            </svg>

                                        </button>


                                        <button
                                            type="button"
                                            wire:click="eliminarContrato({{ $contrato->id }})"
                                            wire:confirm="¿Está seguro de eliminar este contrato?"
                                            title="Eliminar contrato"
                                            class="rounded-lg p-2 text-zinc-500 transition hover:bg-red-50 hover:text-red-600 dark:hover:bg-red-950/60 dark:hover:text-red-400"
                                        >

                                            <svg
                                                class="size-5"
                                                fill="none"
                                                stroke="currentColor"
                                                viewBox="0 0 24 24"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="1.8"
                                                    d="m6 7 1 13h10l1-13M9 7V4h6v3m-8 0h10"
                                                />
                                            </svg>

                                        </button>

                                    </div>

                                </td>

                            </tr>

                        @empty

                            <tr>

                                <td
                                    colspan="6"
                                    class="px-6 py-16 text-center"
                                >

                                    <div class="mx-auto flex max-w-sm flex-col items-center">

                                        <div class="mb-4 rounded-full bg-zinc-100 p-4 text-zinc-500 dark:bg-zinc-800 dark:text-zinc-500">

                                            <svg
                                                class="size-8"
                                                fill="none"
                                                stroke="currentColor"
                                                viewBox="0 0 24 24"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="1.8"
                                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h7l5 5v11a2 2 0 0 1-2 2Z"
                                                />
                                            </svg>

                                        </div>

                                        <p class="font-semibold text-zinc-900 dark:text-white">
                                            No hay contratos registrados
                                        </p>

                                        <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-500">
                                            Registra el primer contrato para comenzar.
                                        </p>

                                    </div>

                                </td>

                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>


            {{-- PAGINACIÓN --}}
            @if ($contratos->hasPages())

                <div class="border-t border-zinc-200 px-6 py-4 dark:border-zinc-800">
                    {{ $contratos->links() }}
                </div>

            @endif

        </div>


        {{-- =====================================================
             FORMULARIO / MODAL
             ===================================================== --}}
        @if ($mostrarFormulario)

            <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4 backdrop-blur-sm">

                <div class="max-h-[92vh] w-full max-w-3xl overflow-y-auto rounded-2xl border border-zinc-200 bg-white text-zinc-900 shadow-2xl dark:border-zinc-700 dark:bg-zinc-900 dark:text-white">

                    {{-- Encabezado --}}
                    <div class="flex items-center justify-between border-b border-zinc-200 px-6 py-5 dark:border-zinc-800">

                        <div class="flex items-center gap-3">

                            <div class="rounded-xl bg-teal-50 p-3 text-teal-700 dark:bg-teal-950/70 dark:text-teal-400">

                                <svg
                                    class="size-5"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="1.8"
                                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h7l5 5v11a2 2 0 0 1-2 2Z"
                                    />
                                </svg>

                            </div>

                            <div>

                                <h2 class="text-xl font-bold text-zinc-900 dark:text-white">
                                    {{ $contratoEditando ? 'Editar contrato' : 'Nuevo contrato' }}
                                </h2>

                                <p class="text-sm text-zinc-700 dark:text-zinc-400">
                                    Registra el servicio contratado por el cliente.
                                </p>

                            </div>

                        </div>


                        <button
                            type="button"
                            wire:click="cancelarFormulario"
                            title="Cerrar"
                            class="rounded-lg p-2 text-zinc-500 transition hover:bg-zinc-100 hover:text-zinc-900 dark:hover:bg-zinc-800 dark:hover:text-white"
                        >

                            <svg
                                class="size-6"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M6 18 18 6M6 6l12 12"
                                />
                            </svg>

                        </button>

                    </div>


                    {{-- Formulario --}}
                    <form
                        wire:submit="guardarContrato"
                        class="space-y-5 p-6"
                    >

                        <div class="grid gap-5 md:grid-cols-2">

                            {{-- Cliente --}}
                            <div class="md:col-span-2">

                                <label class="mb-2 block text-sm font-semibold text-zinc-700 dark:text-zinc-200">
                                    Cliente
                                    <span class="text-red-400">*</span>
                                </label>

                                <select
                                    wire:model="cliente_id"
                                    class="w-full rounded-xl border border-zinc-300 bg-white px-4 py-3 text-sm text-zinc-900 outline-none transition focus:border-teal-500 focus:ring-2 focus:ring-teal-500/20 dark:border-zinc-700/70 dark:bg-zinc-950 dark:text-white"
                                >

                                    <option value="">
                                        Seleccione un cliente
                                    </option>

                                    @foreach ($clientesDisponibles as $cliente)

                                        <option value="{{ $cliente->id }}">
                                            {{ $cliente->razon_social }} — RUC {{ $cliente->ruc }}
                                        </option>

                                    @endforeach

                                </select>

                                @error('cliente_id')
                                    <span class="mt-1 block text-xs text-red-400">
                                        {{ $message }}
                                    </span>
                                @enderror

                            </div>


                            {{-- Servicio --}}
                            <div class="md:col-span-2">

                                <label class="mb-2 block text-sm font-semibold text-zinc-700 dark:text-zinc-200">
                                    Servicio contratado
                                    <span class="text-red-400">*</span>
                                </label>

                                <select
                                    wire:model="servicio_id"
                                    class="w-full rounded-xl border border-zinc-300 bg-white px-4 py-3 text-sm text-zinc-900 outline-none transition focus:border-teal-500 focus:ring-2 focus:ring-teal-500/20 dark:border-zinc-700/70 dark:bg-zinc-950 dark:text-white"
                                >

                                    <option value="">
                                        Seleccione un servicio
                                    </option>

                                    @foreach ($serviciosDisponibles as $servicio)

                                        <option value="{{ $servicio->id }}">
                                            {{ $servicio->nombre }} — S/ {{ number_format($servicio->precio, 2) }}
                                        </option>

                                    @endforeach

                                </select>

                                @error('servicio_id')
                                    <span class="mt-1 block text-xs text-red-400">
                                        {{ $message }}
                                    </span>
                                @enderror

                            </div>


                            {{-- Fecha inicio --}}
                            <div>

                                <label class="mb-2 block text-sm font-semibold text-zinc-700 dark:text-zinc-200">
                                    Fecha de inicio
                                    <span class="text-red-400">*</span>
                                </label>

                                <input
                                    type="date"
                                    wire:model="fecha_inicio"
                                    class="w-full rounded-xl border border-zinc-300 bg-white px-4 py-3 text-sm text-zinc-900 outline-none transition focus:border-teal-500 focus:ring-2 focus:ring-teal-500/20 dark:border-zinc-700/70 dark:bg-zinc-950 dark:text-white"
                                >

                                @error('fecha_inicio')
                                    <span class="mt-1 block text-xs text-red-400">
                                        {{ $message }}
                                    </span>
                                @enderror

                            </div>


                            {{-- Fecha fin --}}
                            <div>

                                <label class="mb-2 block text-sm font-semibold text-zinc-700 dark:text-zinc-200">
                                    Fecha de finalización
                                </label>

                                <input
                                    type="date"
                                    wire:model="fecha_fin"
                                    class="w-full rounded-xl border border-zinc-300 bg-white px-4 py-3 text-sm text-zinc-900 outline-none transition focus:border-teal-500 focus:ring-2 focus:ring-teal-500/20 dark:border-zinc-700/70 dark:bg-zinc-950 dark:text-white"
                                >

                                @error('fecha_fin')
                                    <span class="mt-1 block text-xs text-red-400">
                                        {{ $message }}
                                    </span>
                                @enderror

                            </div>


                            {{-- Monto --}}
                            <div>

                                <label class="mb-2 block text-sm font-semibold text-zinc-700 dark:text-zinc-200">
                                    Monto mensual
                                    <span class="text-red-400">*</span>
                                </label>

                                <div class="relative">

                                    <span class="absolute left-4 top-1/2 -translate-y-1/2 text-sm font-semibold text-zinc-500">
                                        S/
                                    </span>

                                    <input
                                        type="number"
                                        step="0.01"
                                        min="0"
                                        wire:model="monto_mensual"
                                        placeholder="0.00"
                                        class="w-full rounded-xl border border-zinc-300 bg-white py-3 pl-11 pr-4 text-sm text-zinc-900 outline-none transition placeholder:text-zinc-400 focus:border-teal-500 focus:ring-2 focus:ring-teal-500/20 dark:border-zinc-700/70 dark:bg-zinc-950 dark:text-white dark:placeholder:text-zinc-500"
                                    >

                                </div>

                                @error('monto_mensual')
                                    <span class="mt-1 block text-xs text-red-400">
                                        {{ $message }}
                                    </span>
                                @enderror

                            </div>


                            {{-- Estado --}}
                            <div>

                                <label class="mb-2 block text-sm font-semibold text-zinc-700 dark:text-zinc-200">
                                    Estado
                                    <span class="text-red-400">*</span>
                                </label>

                                <select
                                    wire:model="estado"
                                    class="w-full rounded-xl border border-zinc-300 bg-white px-4 py-3 text-sm text-zinc-900 outline-none transition focus:border-teal-500 focus:ring-2 focus:ring-teal-500/20 dark:border-zinc-700/70 dark:bg-zinc-950 dark:text-white"
                                >

                                    <option value="Activo">
                                        Activo
                                    </option>

                                    <option value="Suspendido">
                                        Suspendido
                                    </option>

                                    <option value="Finalizado">
                                        Finalizado
                                    </option>

                                </select>

                                @error('estado')
                                    <span class="mt-1 block text-xs text-red-400">
                                        {{ $message }}
                                    </span>
                                @enderror

                            </div>


                            {{-- Observaciones --}}
                            <div class="md:col-span-2">

                                <label class="mb-2 block text-sm font-semibold text-zinc-700 dark:text-zinc-200">
                                    Observaciones
                                </label>

                                <textarea
                                    wire:model="observaciones"
                                    rows="3"
                                    placeholder="Observaciones adicionales del contrato..."
                                    class="w-full resize-none rounded-xl border border-zinc-300 bg-white px-4 py-3 text-sm text-zinc-900 outline-none transition placeholder:text-zinc-400 focus:border-teal-500 focus:ring-2 focus:ring-teal-500/20 dark:border-zinc-700/70 dark:bg-zinc-950 dark:text-white dark:placeholder:text-zinc-500"
                                ></textarea>

                                @error('observaciones')
                                    <span class="mt-1 block text-xs text-red-400">
                                        {{ $message }}
                                    </span>
                                @enderror

                            </div>

                        </div>


                        {{-- Botones --}}
                        <div class="flex flex-col-reverse gap-3 border-t border-zinc-200 pt-5 dark:border-zinc-800 sm:flex-row sm:justify-end">

                            <button
                                type="button"
                                wire:click="cancelarFormulario"
                                class="rounded-xl border border-zinc-200 bg-white px-5 py-2.5 text-sm font-semibold text-zinc-700 transition hover:bg-zinc-50 hover:text-zinc-900 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-300 dark:hover:bg-zinc-800 dark:hover:text-white"
                            >
                                Cancelar
                            </button>

                            <button
                                type="submit"
                                class="inline-flex items-center justify-center gap-2 rounded-xl bg-teal-600 px-5 py-2.5 text-sm font-semibold text-zinc-900 dark:text-white shadow-sm transition hover:bg-teal-500 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:ring-offset-2 focus:ring-offset-white dark:focus:ring-offset-zinc-900"
                            >

                                <svg
                                    class="size-5"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="m5 12.5 4.5 4.5L19 7"
                                    />
                                </svg>

                                {{ $contratoEditando ? 'Actualizar contrato' : 'Guardar contrato' }}

                            </button>

                        </div>

                    </form>

                </div>

            </div>

        @endif

    </div>

</div>

