<?php

use App\Models\Cliente;
use App\Models\Contrato;
use App\Models\Empleado;
use App\Models\Servicio;
use App\Models\ServicioProceso;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component
{
    use WithPagination;

    public string $buscar = '';
    public string $filtroEstado = '';

    public bool $mostrarFormulario = false;
    public ?int $procesoEditando = null;

    public string $cliente_id = '';
    public string $contrato_id = '';
    public string $servicio_id = '';
    public string $empleado_id = '';
    public string $periodo = '';
    public string $fecha_inicio = '';
    public string $fecha_vencimiento = '';
    public string $prioridad = 'Media';
    public string $estado = 'Pendiente';
    public string $observacion = '';

    public function updatingBuscar(): void
    {
        $this->resetPage();
    }

    public function updatingFiltroEstado(): void
    {
        $this->resetPage();
    }

    public function nuevoProceso(): void
    {
        $this->resetFormulario();
        $this->resetValidation();

        $this->fecha_inicio = now()->format('Y-m-d');
        $this->periodo = now()->format('m/Y');

        $this->mostrarFormulario = true;
    }

    public function editar(int $id): void
    {
        $proceso = ServicioProceso::findOrFail($id);

        $this->procesoEditando = $proceso->id;
        $this->cliente_id = (string) $proceso->cliente_id;
        $this->contrato_id = $proceso->contrato_id
            ? (string) $proceso->contrato_id
            : '';
        $this->servicio_id = (string) $proceso->servicio_id;
        $this->empleado_id = $proceso->empleado_id
            ? (string) $proceso->empleado_id
            : '';
        $this->periodo = $proceso->periodo;
        $this->fecha_inicio = $proceso->fecha_inicio?->format('Y-m-d') ?? '';
        $this->fecha_vencimiento = $proceso->fecha_vencimiento?->format('Y-m-d') ?? '';
        $this->prioridad = $proceso->prioridad;
        $this->estado = $proceso->estado;
        $this->observacion = $proceso->observacion ?? '';

        $this->resetValidation();
        $this->mostrarFormulario = true;
    }

public function guardar(): void
{
    $datos = $this->validate([
        'cliente_id' => 'required|exists:clientes,id',
        'contrato_id' => 'nullable|exists:contratos,id',
        'servicio_id' => 'required|exists:servicios,id',
        'empleado_id' => 'nullable|exists:empleados,id',
        'periodo' => 'required|string|max:20',
        'fecha_inicio' => 'required|date',
        'fecha_vencimiento' => 'nullable|date|after_or_equal:fecha_inicio',
        'prioridad' => 'required|in:Baja,Media,Alta',
        'estado' => 'required|in:Pendiente,En proceso,Completado,Observado',
        'observacion' => 'nullable|string|max:1000',
    ]);

    $datos['contrato_id'] = $this->contrato_id !== ''
        ? $this->contrato_id
        : null;

    $datos['empleado_id'] = $this->empleado_id !== ''
        ? $this->empleado_id
        : null;

    if ($this->procesoEditando) {
        ServicioProceso::findOrFail($this->procesoEditando)
            ->update($datos);

        $mensaje = 'Servicio en proceso actualizado correctamente.';
    } else {
        ServicioProceso::create($datos);

        $mensaje = 'Servicio en proceso registrado correctamente.';
    }

    $this->mostrarFormulario = false;
    $this->resetFormulario();

    session()->flash('mensaje', $mensaje);
}
    public function eliminar(int $id): void
    {
        ServicioProceso::findOrFail($id)->delete();

        session()->flash(
            'mensaje',
            'Servicio en proceso eliminado correctamente.'
        );
    }

    public function cancelar(): void
    {
        $this->mostrarFormulario = false;
        $this->resetFormulario();
        $this->resetValidation();
    }

    private function resetFormulario(): void
    {
        $this->reset([
            'procesoEditando',
            'cliente_id',
            'contrato_id',
            'servicio_id',
            'empleado_id',
            'periodo',
            'fecha_inicio',
            'fecha_vencimiento',
            'observacion',
        ]);

        $this->prioridad = 'Media';
        $this->estado = 'Pendiente';
    }

    public function render()
    {
        $consulta = ServicioProceso::with([
            'cliente',
            'contrato',
            'servicio',
            'empleado',
        ]);

        if ($this->buscar) {
            $consulta->where(function ($q) {
                $q->whereHas('cliente', function ($cliente) {
                    $cliente->where(
                        'razon_social',
                        'like',
                        '%' . $this->buscar . '%'
                    );
                })
                ->orWhereHas('servicio', function ($servicio) {
                    $servicio->where(
                        'nombre',
                        'like',
                        '%' . $this->buscar . '%'
                    );
                })
                ->orWhere('periodo', 'like', '%' . $this->buscar . '%')
                ->orWhere('estado', 'like', '%' . $this->buscar . '%');
            });
        }

        if ($this->filtroEstado) {
            $consulta->where('estado', $this->filtroEstado);
        }

        return view('components.⚡operaciones', [
            'procesos' => $consulta
                ->latest()
                ->paginate(10),

            'clientes' => Cliente::where('estado', 'Activo')
                ->orderBy('razon_social')
                ->get(),

            'contratos' => Contrato::with(['cliente', 'servicio'])
                ->latest()
                ->get(),

            'servicios' => Servicio::orderBy('nombre')->get(),

            'empleados' => Empleado::where('estado', 'Activo')
                ->orderBy('apellidos')
                ->orderBy('nombres')
                ->get(),

            'totalProcesos' => ServicioProceso::count(),

            'pendientes' => ServicioProceso::where(
                'estado',
                'Pendiente'
            )->count(),

            'enProceso' => ServicioProceso::where(
                'estado',
                'En proceso'
            )->count(),

            'completados' => ServicioProceso::where(
                'estado',
                'Completado'
            )->count(),

            'observados' => ServicioProceso::where(
                'estado',
                'Observado'
            )->count(),
        ]);
    }
};
?>

<div class="min-h-full space-y-6 bg-zinc-50 p-1 text-zinc-900 transition-colors dark:bg-zinc-50 dark:bg-black dark:text-white">

    {{-- ENCABEZADO --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">

        <div class="flex items-center gap-4">

            <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-zinc-200 text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300">
                <svg xmlns="http://www.w3.org/2000/svg"
                     class="h-6 w-6"
                     fill="none"
                     viewBox="0 0 24 24"
                     stroke="currentColor"
                     stroke-width="1.8">
                    <path stroke-linecap="round"
                          stroke-linejoin="round"
                          d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h7l5 5v11a2 2 0 01-2 2z"/>
                </svg>
            </div>

            <div>
                <h1 class="text-2xl font-bold tracking-tight text-zinc-900 dark:text-white">
                    Operaciones
                </h1>

                <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">
                    Seguimiento de los servicios contratados por los clientes.
                </p>
            </div>

        </div>

        <button
            type="button"
            wire:click="nuevoProceso"
            class="inline-flex items-center justify-center gap-2 rounded-xl bg-teal-500 px-5 py-2.5 text-sm font-semibold text-zinc-900 dark:text-white shadow-lg shadow-teal-500/20 transition hover:bg-teal-600"
        >
            <svg xmlns="http://www.w3.org/2000/svg"
                 class="h-5 w-5"
                 fill="none"
                 viewBox="0 0 24 24"
                 stroke="currentColor"
                 stroke-width="2">
                <path stroke-linecap="round"
                      stroke-linejoin="round"
                      d="M12 4v16m8-8H4"/>
            </svg>

            Nuevo proceso
        </button>

    </div>


    {{-- MENSAJE --}}
    @if (session('mensaje'))

        <div class="flex items-center gap-3 rounded-xl border border-zinc-800 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-400">

            <svg xmlns="http://www.w3.org/2000/svg"
                 class="h-5 w-5"
                 fill="none"
                 viewBox="0 0 24 24"
                 stroke="currentColor"
                 stroke-width="2">
                <path stroke-linecap="round"
                      stroke-linejoin="round"
                      d="M5 13l4 4L19 7"/>
            </svg>

            {{ session('mensaje') }}

        </div>

    @endif


    {{-- INDICADORES --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-5">

        <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-950">
            <p class="text-sm text-zinc-600 dark:text-zinc-400">Total procesos</p>
            <p class="mt-2 text-3xl font-bold text-zinc-900 dark:text-white">
                {{ $totalProcesos }}
            </p>
            <p class="mt-1 text-xs text-zinc-500">Servicios registrados</p>
        </div>

        <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-950">
            <p class="text-sm text-zinc-600 dark:text-zinc-400">Pendientes</p>
            <p class="mt-2 text-3xl font-bold text-yellow-400">
                {{ $pendientes }}
            </p>
            <p class="mt-1 text-xs text-zinc-500">Por iniciar</p>
        </div>

        <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-950">
            <p class="text-sm text-zinc-600 dark:text-zinc-400">En proceso</p>
            <p class="mt-2 text-3xl font-bold text-teal-400">
                {{ $enProceso }}
            </p>
            <p class="mt-1 text-xs text-zinc-500">En ejecución</p>
        </div>

        <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-950">
            <p class="text-sm text-zinc-600 dark:text-zinc-400">Completados</p>
            <p class="mt-2 text-3xl font-bold text-green-400">
                {{ $completados }}
            </p>
            <p class="mt-1 text-xs text-zinc-500">Finalizados</p>
        </div>

        <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-950">
            <p class="text-sm text-zinc-600 dark:text-zinc-400">Observados</p>
            <p class="mt-2 text-3xl font-bold text-red-400">
                {{ $observados }}
            </p>
            <p class="mt-1 text-xs text-zinc-500">Requieren revisión</p>
        </div>

    </div>


    {{-- BUSCADOR --}}
    <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-950">

        <div class="grid grid-cols-1 gap-4 lg:grid-cols-[1fr_280px]">

            <div>

                <label class="mb-2 block text-sm font-semibold text-zinc-800 dark:text-zinc-200">
                    Buscar proceso
                </label>

                <input
                    wire:model.live.debounce.300ms="buscar"
                    type="text"
                    placeholder="Buscar por cliente, servicio, período o estado..."
                    class="h-12 w-full rounded-xl border border-zinc-300 bg-white px-4 text-sm text-zinc-900 placeholder-zinc-400 outline-none focus:border-teal-500 focus:ring-1 focus:ring-teal-500 dark:border-zinc-800 dark:bg-zinc-900 dark:text-white dark:placeholder-zinc-500"
                >

            </div>

            <div>

                <label class="mb-2 block text-sm font-semibold text-zinc-800 dark:text-zinc-200">
                    Filtrar por estado
                </label>

                <select
                    wire:model.live="filtroEstado"
                    class="h-12 w-full rounded-xl border border-zinc-300 bg-white px-4 text-sm text-zinc-900 outline-none focus:border-teal-500 focus:ring-1 focus:ring-teal-500 dark:border-zinc-800 dark:bg-zinc-900 dark:text-white"
                >
                    <option value="">Todos los estados</option>
                    <option value="Pendiente">Pendiente</option>
                    <option value="En proceso">En proceso</option>
                    <option value="Completado">Completado</option>
                    <option value="Observado">Observado</option>
                </select>

            </div>

        </div>

    </div>


    {{-- TABLA --}}
    <div class="overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-950">

        <div class="border-b border-zinc-200 px-5 py-4 dark:border-zinc-800">

            <h2 class="font-semibold text-zinc-900 dark:text-white">
                Servicios en proceso
            </h2>

            <p class="mt-1 text-xs text-zinc-500">
                Control de ejecución y seguimiento de los servicios contratados.
            </p>

        </div>

        <div class="overflow-x-auto">

            <table class="min-w-full text-sm">

                <thead class="bg-zinc-50 dark:bg-black">

                    <tr>

                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-zinc-600 dark:text-zinc-400">
                            Cliente
                        </th>

                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-zinc-600 dark:text-zinc-400">
                            Servicio
                        </th>

                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-zinc-600 dark:text-zinc-400">
                            Responsable
                        </th>

                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-zinc-600 dark:text-zinc-400">
                            Período
                        </th>

                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-zinc-600 dark:text-zinc-400">
                            Prioridad
                        </th>

                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-zinc-600 dark:text-zinc-400">
                            Estado
                        </th>

                        <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-zinc-600 dark:text-zinc-400">
                            Acciones
                        </th>

                    </tr>

                </thead>

                <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">

                    @forelse ($procesos as $proceso)

                        <tr class="transition hover:bg-zinc-50 dark:hover:bg-zinc-900">

                            <td class="px-5 py-4">
                                <p class="font-semibold text-zinc-900 dark:text-white">
                                    {{ $proceso->cliente?->razon_social ?? 'Sin cliente' }}
                                </p>

                                <p class="mt-1 text-xs text-zinc-500">
                                    {{ $proceso->contrato ? 'Contrato asignado' : 'Sin contrato' }}
                                </p>
                            </td>

                            <td class="px-5 py-4 text-zinc-700 dark:text-zinc-300">
                                {{ $proceso->servicio?->nombre ?? 'Sin servicio' }}
                            </td>

                            <td class="px-5 py-4 text-zinc-700 dark:text-zinc-300">
                                @if ($proceso->empleado)
                                    {{ $proceso->empleado->nombres }}
                                    {{ $proceso->empleado->apellidos }}
                                @else
                                    <span class="text-zinc-500">
                                        Sin asignar
                                    </span>
                                @endif
                            </td>

                            <td class="whitespace-nowrap px-5 py-4 text-zinc-700 dark:text-zinc-300">
                                {{ $proceso->periodo }}
                            </td>

                            <td class="px-5 py-4">

                                @if ($proceso->prioridad === 'Alta')

                                    <span class="rounded-full bg-red-500/10 px-3 py-1 text-xs font-semibold text-red-400">
                                        Alta
                                    </span>

                                @elseif ($proceso->prioridad === 'Media')

                                    <span class="rounded-full bg-yellow-500/10 px-3 py-1 text-xs font-semibold text-yellow-400">
                                        Media
                                    </span>

                                @else

                                    <span class="rounded-full bg-zinc-800 px-3 py-1 text-xs font-semibold text-zinc-600 dark:text-zinc-400">
                                        Baja
                                    </span>

                                @endif

                            </td>

                            <td class="px-5 py-4">

                                @if ($proceso->estado === 'Completado')

                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-green-500/10 px-3 py-1 text-xs font-semibold text-green-400">
                                        <span class="h-1.5 w-1.5 rounded-full bg-green-500"></span>
                                        Completado
                                    </span>

                                @elseif ($proceso->estado === 'En proceso')

                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-teal-500/10 px-3 py-1 text-xs font-semibold text-teal-400">
                                        <span class="h-1.5 w-1.5 rounded-full bg-teal-500"></span>
                                        En proceso
                                    </span>

                                @elseif ($proceso->estado === 'Observado')

                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-red-500/10 px-3 py-1 text-xs font-semibold text-red-400">
                                        <span class="h-1.5 w-1.5 rounded-full bg-red-500"></span>
                                        Observado
                                    </span>

                                @else

                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-yellow-500/10 px-3 py-1 text-xs font-semibold text-yellow-400">
                                        <span class="h-1.5 w-1.5 rounded-full bg-yellow-500"></span>
                                        Pendiente
                                    </span>

                                @endif

                            </td>

                            <td class="px-5 py-4 text-right">

                                <div class="flex justify-end gap-1">

                                    <button
                                        type="button"
                                        wire:click="editar({{ $proceso->id }})"
                                        title="Editar proceso"
                                        class="inline-flex h-9 w-9 items-center justify-center rounded-lg text-zinc-600 dark:text-zinc-400 transition hover:bg-teal-500/10 hover:text-teal-400"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg"
                                             class="h-5 w-5"
                                             fill="none"
                                             viewBox="0 0 24 24"
                                             stroke="currentColor"
                                             stroke-width="1.8">
                                            <path stroke-linecap="round"
                                                  stroke-linejoin="round"
                                                  d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/>
                                        </svg>
                                    </button>

                                    <button
                                        type="button"
                                        wire:click="eliminar({{ $proceso->id }})"
                                        wire:confirm="¿Estás seguro de eliminar este proceso?"
                                        title="Eliminar proceso"
                                        class="inline-flex h-9 w-9 items-center justify-center rounded-lg text-zinc-600 dark:text-zinc-400 transition hover:bg-red-500/10 hover:text-red-400"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg"
                                             class="h-5 w-5"
                                             fill="none"
                                             viewBox="0 0 24 24"
                                             stroke="currentColor"
                                             stroke-width="1.8">
                                            <path stroke-linecap="round"
                                                  stroke-linejoin="round"
                                                  d="M6 7h12m-9 0V4h6v3m-8 0l1 14h6l1-14M10 11v6m4-6v6"/>
                                        </svg>
                                    </button>

                                </div>

                            </td>

                        </tr>

                    @empty

                        <tr>
                            <td colspan="7" class="px-5 py-14 text-center">

                                <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-zinc-800 text-zinc-500">
                                    <svg xmlns="http://www.w3.org/2000/svg"
                                         class="h-7 w-7"
                                         fill="none"
                                         viewBox="0 0 24 24"
                                         stroke="currentColor"
                                         stroke-width="1.5">
                                        <path stroke-linecap="round"
                                              stroke-linejoin="round"
                                              d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h7l5 5v11a2 2 0 01-2 2z"/>
                                    </svg>
                                </div>

                                <p class="mt-4 text-sm font-semibold text-zinc-700 dark:text-zinc-300">
                                    No hay servicios en proceso
                                </p>

                                <p class="mt-1 text-xs text-zinc-500">
                                    Utilice «Nuevo proceso» para registrar uno.
                                </p>

                            </td>
                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

        @if ($procesos->hasPages())

            <div class="border-t border-zinc-800 px-5 py-4">
                {{ $procesos->links() }}
            </div>

        @endif

    </div>


    {{-- MODAL --}}
    @if ($mostrarFormulario)

        <div class="fixed inset-0 z-50 flex items-center justify-center bg-zinc-50 dark:bg-black/80 p-4 backdrop-blur-sm">

            <div class="w-full max-w-4xl overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-950 shadow-2xl">

                <div class="flex items-center justify-between border-b border-zinc-200 px-6 py-5 dark:border-zinc-800">

                    <div>
                        <h2 class="text-xl font-semibold text-zinc-900 dark:text-white">
                            {{ $procesoEditando ? 'Editar servicio en proceso' : 'Registrar servicio en proceso' }}
                        </h2>

                        <p class="mt-1 text-sm text-zinc-500">
                            Registra y asigna un servicio contratado para su seguimiento.
                        </p>
                    </div>

                    <button
                        type="button"
                        wire:click="cancelar"
                        class="flex h-9 w-9 items-center justify-center rounded-lg text-zinc-600 dark:text-zinc-500 transition hover:bg-zinc-100 hover:text-zinc-900 dark:text-zinc-400 dark:hover:bg-zinc-800 dark:hover:text-white"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg"
                             class="h-5 w-5"
                             fill="none"
                             viewBox="0 0 24 24"
                             stroke="currentColor"
                             stroke-width="1.8">
                            <path stroke-linecap="round"
                                  d="M6 6l12 12M18 6L6 18"/>
                        </svg>
                    </button>

                </div>


                <form wire:submit="guardar">

                    <div class="max-h-[70vh] overflow-y-auto px-6 py-6">

                        <div class="grid grid-cols-1 gap-5 md:grid-cols-2">

                            {{-- CLIENTE --}}
                            <div>
                                <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-300">
                                    Cliente <span class="text-red-500">*</span>
                                </label>

                                <select
                                    wire:model="cliente_id"
                                    class="h-11 w-full rounded-xl border border-zinc-300 bg-white px-4 text-sm text-zinc-900 outline-none focus:border-teal-500 focus:ring-1 focus:ring-teal-500 dark:border-zinc-800 dark:bg-zinc-900 dark:text-white"
                                >
                                    <option value="">Seleccionar cliente</option>

                                    @foreach ($clientes as $cliente)
                                        <option value="{{ $cliente->id }}">
                                            {{ $cliente->razon_social }}
                                        </option>
                                    @endforeach
                                </select>

                                @error('cliente_id')
                                    <span class="mt-1 block text-xs text-red-400">
                                        {{ $message }}
                                    </span>
                                @enderror
                            </div>


                            {{-- SERVICIO --}}
                            <div>
                                <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-300">
                                    Servicio <span class="text-red-500">*</span>
                                </label>

                                <select
                                    wire:model="servicio_id"
                                    class="h-11 w-full rounded-xl border border-zinc-300 bg-white px-4 text-sm text-zinc-900 outline-none focus:border-teal-500 focus:ring-1 focus:ring-teal-500 dark:border-zinc-800 dark:bg-zinc-900 dark:text-white"
                                >
                                    <option value="">Seleccionar servicio</option>

                                    @foreach ($servicios as $servicio)
                                        <option value="{{ $servicio->id }}">
                                            {{ $servicio->nombre }}
                                        </option>
                                    @endforeach
                                </select>

                                @error('servicio_id')
                                    <span class="mt-1 block text-xs text-red-400">
                                        {{ $message }}
                                    </span>
                                @enderror
                            </div>


                            {{-- CONTRATO --}}
                            <div>
                                <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-300">
                                    Contrato
                                </label>

                                <select
                                    wire:model="contrato_id"
                                    class="h-11 w-full rounded-xl border border-zinc-300 bg-white px-4 text-sm text-zinc-900 outline-none focus:border-teal-500 focus:ring-1 focus:ring-teal-500 dark:border-zinc-800 dark:bg-zinc-900 dark:text-white"
                                >
                                    <option value="">Sin contrato</option>

                                    @foreach ($contratos as $contrato)
                                        <option value="{{ $contrato->id }}">
                                            {{ $contrato->cliente?->razon_social }}
                                            — {{ $contrato->servicio?->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>


                            {{-- RESPONSABLE --}}
                            <div>
                                <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-300">
                                    Responsable
                                </label>

                                <select
                                    wire:model="empleado_id"
                                    class="h-11 w-full rounded-xl border border-zinc-300 bg-white px-4 text-sm text-zinc-900 outline-none focus:border-teal-500 focus:ring-1 focus:ring-teal-500 dark:border-zinc-800 dark:bg-zinc-900 dark:text-white"
                                >
                                    <option value="">Sin asignar</option>

                                    @foreach ($empleados as $empleado)
                                        <option value="{{ $empleado->id }}">
                                            {{ $empleado->nombres }}
                                            {{ $empleado->apellidos }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>


                            {{-- PERÍODO --}}
                            <div>
                                <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-300">
                                    Período <span class="text-red-500">*</span>
                                </label>

                                <input
                                    wire:model="periodo"
                                    type="text"
                                    placeholder="Ej. 09/2026"
                                    class="h-11 w-full rounded-xl border border-zinc-300 bg-white px-4 text-sm text-zinc-900 placeholder-zinc-400 outline-none focus:border-teal-500 focus:ring-1 focus:ring-teal-500 dark:border-zinc-800 dark:bg-zinc-900 dark:text-white dark:placeholder-zinc-500"
                                >

                                @error('periodo')
                                    <span class="mt-1 block text-xs text-red-400">
                                        {{ $message }}
                                    </span>
                                @enderror
                            </div>


                            {{-- FECHA INICIO --}}
                            <div>
                                <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-300">
                                    Fecha de inicio <span class="text-red-500">*</span>
                                </label>

                                <input
                                    wire:model="fecha_inicio"
                                    type="date"
                                    class="h-11 w-full rounded-xl border border-zinc-300 bg-white px-4 text-sm text-zinc-900 outline-none focus:border-teal-500 focus:ring-1 focus:ring-teal-500 dark:border-zinc-800 dark:bg-zinc-900 dark:text-white"
                                >

                                @error('fecha_inicio')
                                    <span class="mt-1 block text-xs text-red-400">
                                        {{ $message }}
                                    </span>
                                @enderror
                            </div>


                            {{-- VENCIMIENTO --}}
                            <div>
                                <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-300">
                                    Fecha de vencimiento
                                </label>

                                <input
                                    wire:model="fecha_vencimiento"
                                    type="date"
                                    class="h-11 w-full rounded-xl border border-zinc-300 bg-white px-4 text-sm text-zinc-900 outline-none focus:border-teal-500 focus:ring-1 focus:ring-teal-500 dark:border-zinc-800 dark:bg-zinc-900 dark:text-white"
                                >

                                @error('fecha_vencimiento')
                                    <span class="mt-1 block text-xs text-red-400">
                                        {{ $message }}
                                    </span>
                                @enderror
                            </div>


                            {{-- PRIORIDAD --}}
                            <div>
                                <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-300">
                                    Prioridad
                                </label>

                                <select
                                    wire:model="prioridad"
                                    class="h-11 w-full rounded-xl border border-zinc-300 bg-white px-4 text-sm text-zinc-900 outline-none focus:border-teal-500 focus:ring-1 focus:ring-teal-500 dark:border-zinc-800 dark:bg-zinc-900 dark:text-white"
                                >
                                    <option value="Baja">Baja</option>
                                    <option value="Media">Media</option>
                                    <option value="Alta">Alta</option>
                                </select>
                            </div>


                            {{-- ESTADO --}}
                            <div>
                                <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-300">
                                    Estado
                                </label>

                                <select
                                    wire:model="estado"
                                    class="h-11 w-full rounded-xl border border-zinc-300 bg-white px-4 text-sm text-zinc-900 outline-none focus:border-teal-500 focus:ring-1 focus:ring-teal-500 dark:border-zinc-800 dark:bg-zinc-900 dark:text-white"
                                >
                                    <option value="Pendiente">Pendiente</option>
                                    <option value="En proceso">En proceso</option>
                                    <option value="Completado">Completado</option>
                                    <option value="Observado">Observado</option>
                                </select>
                            </div>


                            {{-- OBSERVACIÓN --}}
                            <div class="md:col-span-2">

                                <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-300">
                                    Observación
                                </label>

                                <textarea
                                    wire:model="observacion"
                                    rows="3"
                                    placeholder="Ingrese alguna observación o seguimiento..."
                                    class="w-full rounded-xl border border-zinc-300 bg-white px-4 py-3 text-sm text-zinc-900 placeholder-zinc-400 outline-none focus:border-teal-500 focus:ring-1 focus:ring-teal-500 dark:border-zinc-800 dark:bg-zinc-900 dark:text-white dark:placeholder-zinc-500"
                                ></textarea>

                                @error('observacion')
                                    <span class="mt-1 block text-xs text-red-400">
                                        {{ $message }}
                                    </span>
                                @enderror

                            </div>

                        </div>

                    </div>


                    <div class="flex flex-col-reverse gap-3 border-t border-zinc-200 px-6 py-4 dark:border-zinc-800 sm:flex-row sm:justify-end">

                        <button
                            type="button"
                            wire:click="cancelar"
                            class="rounded-xl border border-zinc-800 bg-zinc-900 px-5 py-2.5 text-sm font-semibold text-zinc-700 dark:text-zinc-300 transition hover:bg-zinc-800 hover:text-white"
                        >
                            Cancelar
                        </button>

                        <button
                            type="submit"
                            class="rounded-xl bg-teal-500 px-5 py-2.5 text-sm font-semibold text-zinc-900 dark:text-white shadow-lg shadow-teal-500/20 transition hover:bg-teal-600"
                        >
                            {{ $procesoEditando ? 'Actualizar proceso' : 'Registrar proceso' }}
                        </button>

                    </div>

                </form>

            </div>

        </div>

    @endif

</div>
