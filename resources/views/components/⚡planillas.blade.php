<?php

use App\Models\Empleado;
use App\Models\Planilla;
use Livewire\Component;

new class extends Component
{
    public $empleado_id = '';
    public $periodo = '';
    public $sueldo_base = '';
    public $bonificaciones = 0;
    public $descuentos = 0;
public bool $mostrarFormulario = false;
public ?int $planillaEditando = null;
 public $estado = 'Pendiente';
public string $buscar = '';
public string $filtroEstado = '';


public function updatingBuscar(): void
{
    //
}

public function updatingFiltroEstado(): void
{
    //
}
public function editar(int $id): void
{
    $planilla = Planilla::findOrFail($id);

    $this->planillaEditando = $planilla->id;
    $this->empleado_id = (string) $planilla->empleado_id;
    $this->periodo = $planilla->periodo?->format('Y-m');
    $this->sueldo_base = $planilla->sueldo_base;
    $this->bonificaciones = $planilla->bonificaciones;
    $this->descuentos = $planilla->descuentos;

$this->estado = $planilla->estado;

    $this->resetValidation();

    $this->mostrarFormulario = true;
}

public function eliminar(int $id): void
{
    $planilla = Planilla::findOrFail($id);

    $planilla->delete();

    session()->flash(
        'mensaje',
        'Planilla eliminada correctamente.'
    );
}
public function guardar(): void
{
    $this->validate([
        'empleado_id' => 'required|exists:empleados,id',
        'periodo' => 'required|date',
        'sueldo_base' => 'required|numeric|min:0',
        'bonificaciones' => 'required|numeric|min:0',
        'descuentos' => 'required|numeric|min:0',
'estado' => 'required|in:Pendiente,Pagada,Anulada',
    ], [
        'empleado_id.required' => 'Selecciona un empleado.',
        'empleado_id.exists' => 'El empleado seleccionado no es vÃ¡lido.',
        'periodo.required' => 'El periodo es obligatorio.',
        'periodo.date' => 'Ingresa un periodo vÃ¡lido.',
        'sueldo_base.required' => 'El sueldo base es obligatorio.',
        'sueldo_base.numeric' => 'Ingresa un monto vÃ¡lido.',
        'bonificaciones.numeric' => 'Ingresa un monto vÃ¡lido.',
        'descuentos.numeric' => 'Ingresa un monto vÃ¡lido.',
    ]);
    $duplicado = Planilla::where('empleado_id', $this->empleado_id)
        ->whereDate('periodo', $this->periodo)
        ->when($this->planillaEditando, function ($query) {
            $query->where('id', '!=', $this->planillaEditando);
        })
        ->exists();

    if ($duplicado) {
        $this->addError(
            'periodo',
            'Ya existe una planilla registrada para este empleado en el periodo seleccionado.'
        );

        return;
    }
    $sueldoNeto =
        (float) $this->sueldo_base
        + (float) $this->bonificaciones
        - (float) $this->descuentos;

    if ($this->planillaEditando) {

        $planilla = Planilla::findOrFail($this->planillaEditando);

        $planilla->update([
            'empleado_id' => $this->empleado_id,
            'periodo' => $this->periodo,
            'sueldo_base' => $this->sueldo_base,
            'bonificaciones' => $this->bonificaciones,
            'descuentos' => $this->descuentos,
'sueldo_neto' => $sueldoNeto,
'estado' => $this->estado,
        ]);

        $mensaje = 'Planilla actualizada correctamente.';

    } else {

        Planilla::create([
            'empleado_id' => $this->empleado_id,
            'periodo' => $this->periodo,
            'sueldo_base' => $this->sueldo_base,
            'bonificaciones' => $this->bonificaciones,
            'descuentos' => $this->descuentos,
            'sueldo_neto' => $sueldoNeto,
            'estado' => 'Pendiente',
        ]);

        $mensaje = 'Planilla registrada correctamente.';
    }

    $this->mostrarFormulario = false;
    $this->planillaEditando = null;

    $this->reset([
        'empleado_id',
        'periodo',
        'sueldo_base',
        'bonificaciones',
        'descuentos',
    ]);

    $this->bonificaciones = 0;
    $this->descuentos = 0;

    session()->flash('mensaje', $mensaje);
}
  public function with(): array
    {
$totalPlanillas = Planilla::count();

$totalPendientes = Planilla::where('estado', 'Pendiente')->count();

$totalPagadas = Planilla::where('estado', 'Pagada')->count();

$totalAnuladas = Planilla::where('estado', 'Anulada')->count();

$totalNeto = Planilla::where('estado', '!=', 'Anulada')
    ->sum('sueldo_neto');
        return [
            'empleados' => Empleado::orderBy('apellidos')
                ->orderBy('nombres')
                ->get(),
'totalPlanillas' => $totalPlanillas,
'totalPendientes' => $totalPendientes,
'totalPagadas' => $totalPagadas,
'totalAnuladas' => $totalAnuladas,
'totalNeto' => $totalNeto,
            'planillas' => Planilla::with('empleado')
    ->when($this->buscar, function ($query) {
        $query->whereHas('empleado', function ($query) {
            $query->where('nombres', 'like', '%' . $this->buscar . '%')
                ->orWhere('apellidos', 'like', '%' . $this->buscar . '%')
                ->orWhere('dni', 'like', '%' . $this->buscar . '%');
        });
    })
    ->when($this->filtroEstado, function ($query) {
        $query->where('estado', $this->filtroEstado);
    })
    ->latest()
    ->get(),
        ];
    }
};
?>

<div class="min-h-full space-y-6 bg-zinc-50 p-1 text-zinc-900 transition-colors dark:bg-zinc-50 dark:bg-black dark:text-white">

    {{-- =========================================================
         ENCABEZADO
         ========================================================= --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">

        <div class="flex items-center gap-4">

            <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-zinc-200 text-zinc-600 dark:bg-zinc-800 dark:text-zinc-200">
                <svg xmlns="http://www.w3.org/2000/svg"
                     class="h-6 w-6"
                     fill="none"
                     viewBox="0 0 24 24"
                     stroke="currentColor"
                     stroke-width="1.8">
                    <path stroke-linecap="round"
                          stroke-linejoin="round"
                          d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h7l5 5v11a2 2 0 01-2 2z"/>
                    <path stroke-linecap="round"
                          stroke-linejoin="round"
                          d="M14 3v5h5"/>
                </svg>
            </div>

            <div>
                <h1 class="text-2xl font-bold tracking-tight text-zinc-900 dark:text-white">
                    Planillas
                </h1>

                <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">
                    Gestiona las remuneraciones de los empleados de la empresa.
                </p>
            </div>

        </div>

        <button
            type="button"
            wire:click="$set('mostrarFormulario', true)"
            class="inline-flex items-center justify-center gap-2 rounded-xl bg-teal-500 px-5 py-2.5 text-sm font-semibold text-zinc-900 dark:text-white shadow-lg shadow-teal-500/20 transition hover:bg-teal-600 focus:outline-none focus:ring-2 focus:ring-teal-500"
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

            Nueva planilla
        </button>

    </div>


    {{-- =========================================================
         INDICADORES
         ========================================================= --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-5">

        {{-- TOTAL --}}
        <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-800/60 dark:bg-zinc-950">

            <div class="flex items-center justify-between">

                <div>
                    <p class="text-sm font-medium text-zinc-600 dark:text-zinc-400">
                        Total planillas
                    </p>

                    <p class="mt-2 text-3xl font-bold text-zinc-900 dark:text-white">
                        {{ $totalPlanillas }}
                    </p>

                    <p class="mt-1 text-xs text-zinc-500">
                        Registros generados
                    </p>
                </div>

                <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-zinc-200 text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300">
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

            </div>

        </div>


        {{-- PENDIENTES --}}
        <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-800/60 dark:bg-zinc-950">

            <div class="flex items-center justify-between">

                <div>
                    <p class="text-sm font-medium text-zinc-600 dark:text-zinc-400">
                        Pendientes
                    </p>

                    <p class="mt-2 text-3xl font-bold text-yellow-400">
                        {{ $totalPendientes }}
                    </p>

                    <p class="mt-1 text-xs text-yellow-500/80">
                        Requieren pago
                    </p>
                </div>

                <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-yellow-500/10 text-yellow-400">
                    <svg xmlns="http://www.w3.org/2000/svg"
                         class="h-6 w-6"
                         fill="none"
                         viewBox="0 0 24 24"
                         stroke="currentColor"
                         stroke-width="1.8">
                        <path stroke-linecap="round"
                              stroke-linejoin="round"
                              d="M12 8v4l3 2m6-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>

            </div>

        </div>


        {{-- PAGADAS --}}
        <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-800/60 dark:bg-zinc-950">

            <div class="flex items-center justify-between">

                <div>
                    <p class="text-sm font-medium text-zinc-600 dark:text-zinc-400">
                        Pagadas
                    </p>

                    <p class="mt-2 text-3xl font-bold text-green-400">
                        {{ $totalPagadas }}
                    </p>

                    <p class="mt-1 text-xs text-green-500/80">
                        Remuneraciones pagadas
                    </p>
                </div>

                <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-green-500/10 text-green-400">
                    <svg xmlns="http://www.w3.org/2000/svg"
                         class="h-6 w-6"
                         fill="none"
                         viewBox="0 0 24 24"
                         stroke="currentColor"
                         stroke-width="1.8">
                        <path stroke-linecap="round"
                              stroke-linejoin="round"
                              d="M5 13l4 4L19 7"/>
                    </svg>
                </div>

            </div>

        </div>


        {{-- ANULADAS --}}
        <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-800/60 dark:bg-zinc-950">

            <div class="flex items-center justify-between">

                <div>
                    <p class="text-sm font-medium text-zinc-600 dark:text-zinc-400">
                        Anuladas
                    </p>

                    <p class="mt-2 text-3xl font-bold text-red-400">
                        {{ $totalAnuladas }}
                    </p>

                    <p class="mt-1 text-xs text-red-500/80">
                        Registros anulados
                    </p>
                </div>

                <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-red-500/10 text-red-400">
                    <svg xmlns="http://www.w3.org/2000/svg"
                         class="h-6 w-6"
                         fill="none"
                         viewBox="0 0 24 24"
                         stroke="currentColor"
                         stroke-width="1.8">
                        <path stroke-linecap="round"
                              stroke-linejoin="round"
                              d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </div>

            </div>

        </div>


        {{-- NETO --}}
        <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-800/60 dark:bg-zinc-950">

            <div class="flex items-center justify-between">

                <div>
                    <p class="text-sm font-medium text-zinc-600 dark:text-zinc-400">
                        Neto registrado
                    </p>

                    <p class="mt-2 text-2xl font-bold text-zinc-900 dark:text-white">
                        S/ {{ number_format($totalNeto, 2) }}
                    </p>

                    <p class="mt-1 text-xs text-teal-400">
                        Total no periodo
                    </p>
                </div>

                <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-zinc-200 text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300">
                    <svg xmlns="http://www.w3.org/2000/svg"
                         class="h-6 w-6"
                         fill="none"
                         viewBox="0 0 24 24"
                         stroke="currentColor"
                         stroke-width="1.8">
                        <path stroke-linecap="round"
                              stroke-linejoin="round"
                              d="M12 8c-2.21 0-4 1.343-4 3s1.79 3 4 3 4 1.343 4 3-1.79 3-4 3m0-12V5m0 14v-2"/>
                    </svg>
                </div>

            </div>

        </div>

    </div>


    {{-- =========================================================
         MENSAJE
         ========================================================= --}}
    @if (session('mensaje'))

        <div class="rounded-xl border border-green-500/20 bg-green-500/10 px-4 py-3 text-sm text-green-400">
            <div class="flex items-center gap-2">

                <svg xmlns="http://www.w3.org/2000/svg"
                     class="h-5 w-5 shrink-0"
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
        </div>

    @endif


    {{-- =========================================================
         BUSCADOR Y FILTRO
         ========================================================= --}}
    <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-800/60 dark:bg-zinc-950">

        <div class="flex flex-col gap-4 lg:flex-row lg:items-end">

            <div class="flex-1">

                <label class="mb-2 block text-sm font-semibold text-zinc-900 dark:text-white">
                    Buscar empleado
                </label>

                <div class="relative">

                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-zinc-500">

                        <svg xmlns="http://www.w3.org/2000/svg"
                             class="h-5 w-5"
                             fill="none"
                             viewBox="0 0 24 24"
                             stroke="currentColor"
                             stroke-width="2">
                            <path stroke-linecap="round"
                                  stroke-linejoin="round"
                                  d="M21 21l-4.35-4.35m2.35-5.65a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>

                    </div>

                    <input
                        wire:model.live.debounce.300ms="buscar"
                        type="text"
                        placeholder="Buscar por nombre, apellido o DNI..."
                        class="w-full rounded-xl border border-zinc-300 bg-white py-3 pl-10 pr-4 text-sm text-zinc-900 outline-none placeholder:text-zinc-400 focus:border-teal-500 focus:ring-2 focus:ring-teal-500 dark:border-zinc-700 dark:bg-zinc-900 dark:text-white dark:placeholder:text-zinc-500"
                    >

                </div>

            </div>


            <div class="w-full lg:w-72">

                <label class="mb-2 block text-sm font-semibold text-zinc-900 dark:text-white">
                    Filtrar por estado
                </label>

                <select
                    wire:model.live="filtroEstado"
                    class="w-full rounded-xl border border-zinc-300 bg-white px-3 py-3 text-sm text-zinc-900 outline-none focus:border-teal-500 focus:ring-2 focus:ring-teal-500 dark:border-zinc-700 dark:bg-zinc-900 dark:text-white"
                >
                    <option value="">Todos los estados</option>
                    <option value="Pendiente">Pendiente</option>
                    <option value="Pagada">Pagada</option>
                    <option value="Anulada">Anulada</option>
                </select>

            </div>

        </div>

    </div>


    {{-- =========================================================
         FORMULARIO
         ========================================================= --}}
    @if ($mostrarFormulario)

        <div class="rounded-2xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-800/60 dark:bg-zinc-950 p-6">

            <div class="mb-6 flex items-start justify-between border-b border-zinc-200 pb-5 dark:border-zinc-800">

                <div class="flex items-center gap-3">

                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-teal-500/10 text-teal-400">

                        <svg xmlns="http://www.w3.org/2000/svg"
                             class="h-5 w-5"
                             fill="none"
                             viewBox="0 0 24 24"
                             stroke="currentColor"
                             stroke-width="1.8">
                            <path stroke-linecap="round"
                                  stroke-linejoin="round"
                                  d="M12 4v16m8-8H4"/>
                        </svg>

                    </div>

                    <div>

                        <h2 class="text-lg font-bold text-zinc-900 dark:text-white">
                            {{ $planillaEditando ? 'Editar planilla' : 'Registrar nueva planilla' }}
                        </h2>

                        <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">
                            Complete la información de remuneración del empleado.
                        </p>

                    </div>

                </div>

                <button
                    type="button"
                    wire:click="$set('mostrarFormulario', false)"
                    class="text-zinc-500 transition hover:text-zinc-900 dark:text-white"
                    title="Cerrar"
                >
                    <svg xmlns="http://www.w3.org/2000/svg"
                         class="h-5 w-5"
                         fill="none"
                         viewBox="0 0 24 24"
                         stroke="currentColor"
                         stroke-width="2">
                        <path stroke-linecap="round"
                              stroke-linejoin="round"
                              d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>

            </div>


            <form wire:submit="guardar">

                <div class="grid grid-cols-1 gap-5 md:grid-cols-2">

                    {{-- EMPLEADO --}}
                    <div>

                        <label class="block text-sm font-semibold text-zinc-800 dark:text-zinc-200">
                            Empleado <span class="text-red-500">*</span>
                        </label>

                        <select
                            wire:model="empleado_id"
                            class="mt-1.5 w-full rounded-xl border border-zinc-700 bg-zinc-900 px-3 py-2.5 text-sm text-zinc-900 dark:text-white outline-none focus:border-teal-500 focus:ring-2 focus:ring-teal-500"
                        >
                            <option value="">
                                Selecciona un empleado
                            </option>

                            @foreach ($empleados as $empleado)
                                <option value="{{ $empleado->id }}">
                                    {{ $empleado->apellidos }}, {{ $empleado->nombres }} - DNI {{ $empleado->dni }}
                                </option>
                            @endforeach

                        </select>

                        @error('empleado_id')
                            <span class="mt-1 block text-xs text-red-400">
                                {{ $message }}
                            </span>
                        @enderror

                    </div>


                    {{-- PERIODO --}}
                    <div>

                        <label class="block text-sm font-semibold text-zinc-800 dark:text-zinc-200">
                            Periodo <span class="text-red-500">*</span>
                        </label>

                        <input
                            wire:model="periodo"
                            type="month"
                            class="mt-1.5 w-full rounded-xl border border-zinc-700 bg-zinc-900 px-3 py-2.5 text-sm text-zinc-900 dark:text-white outline-none focus:border-teal-500 focus:ring-2 focus:ring-teal-500"
                        >

                        @error('periodo')
                            <span class="mt-1 block text-xs text-red-400">
                                {{ $message }}
                            </span>
                        @enderror

                    </div>


                    {{-- SUELDO BÁSICO --}}
                    <div>

                        <label class="block text-sm font-semibold text-zinc-800 dark:text-zinc-200">
                            Sueldo básico <span class="text-red-500">*</span>
                        </label>

                        <div class="relative mt-1.5">

                            <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-sm text-zinc-500">
                                S/
                            </span>

                            <input
                                wire:model="sueldo_base"
                                type="number"
                                step="0.01"
                                min="0"
                                placeholder="0.00"
                                class="w-full rounded-xl border border-zinc-700 bg-zinc-900 py-2.5 pl-10 pr-3 text-sm text-zinc-900 dark:text-white outline-none placeholder:text-zinc-500 focus:border-teal-500 focus:ring-2 focus:ring-teal-500"
                            >

                        </div>

                        @error('sueldo_base')
                            <span class="mt-1 block text-xs text-red-400">
                                {{ $message }}
                            </span>
                        @enderror

                    </div>


                    {{-- BONIFICACIONES --}}
                    <div>

                        <label class="block text-sm font-semibold text-zinc-800 dark:text-zinc-200">
                            Bonificaciones
                        </label>

                        <div class="relative mt-1.5">

                            <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-sm text-zinc-500">
                                S/
                            </span>

                            <input
                                wire:model="bonificaciones"
                                type="number"
                                step="0.01"
                                min="0"
                                placeholder="0.00"
                                class="w-full rounded-xl border border-zinc-700 bg-zinc-900 py-2.5 pl-10 pr-3 text-sm text-zinc-900 dark:text-white outline-none placeholder:text-zinc-500 focus:border-teal-500 focus:ring-2 focus:ring-teal-500"
                            >

                        </div>

                        @error('bonificaciones')
                            <span class="mt-1 block text-xs text-red-400">
                                {{ $message }}
                            </span>
                        @enderror

                    </div>


                    {{-- DESCUENTOS --}}
                    <div>

                        <label class="block text-sm font-semibold text-zinc-800 dark:text-zinc-200">
                            Descuentos
                        </label>

                        <div class="relative mt-1.5">

                            <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-sm text-zinc-500">
                                S/
                            </span>

                            <input
                                wire:model="descuentos"
                                type="number"
                                step="0.01"
                                min="0"
                                placeholder="0.00"
                                class="w-full rounded-xl border border-zinc-700 bg-zinc-900 py-2.5 pl-10 pr-3 text-sm text-zinc-900 dark:text-white outline-none placeholder:text-zinc-500 focus:border-teal-500 focus:ring-2 focus:ring-teal-500"
                            >

                        </div>

                        @error('descuentos')
                            <span class="mt-1 block text-xs text-red-400">
                                {{ $message }}
                            </span>
                        @enderror

                    </div>


                    {{-- ESTADO --}}
                    <div>

                        <label class="block text-sm font-semibold text-zinc-800 dark:text-zinc-200">
                            Estado
                        </label>

                        <select
                            wire:model="estado"
                            class="mt-1.5 w-full rounded-xl border border-zinc-700 bg-zinc-900 px-3 py-2.5 text-sm text-zinc-900 dark:text-white outline-none focus:border-teal-500 focus:ring-2 focus:ring-teal-500"
                        >
                            <option value="Pendiente">Pendiente</option>
                            <option value="Pagada">Pagada</option>
                            <option value="Anulada">Anulada</option>
                        </select>

                    </div>

                </div>


                {{-- BOTONES --}}
                <div class="mt-7 flex flex-col-reverse gap-3 border-t border-zinc-200 pt-5 dark:border-zinc-800 sm:flex-row sm:justify-end">

                    <button
                        type="button"
                        wire:click="$set('mostrarFormulario', false)"
                        class="rounded-xl border border-zinc-700 bg-zinc-900 px-5 py-2.5 text-sm font-semibold text-zinc-800 dark:text-zinc-200 transition hover:bg-zinc-800"
                    >
                        Cancelar
                    </button>

                    <button
                        type="submit"
                        class="inline-flex items-center justify-center gap-2 rounded-xl bg-teal-500 px-5 py-2.5 text-sm font-semibold text-zinc-900 dark:text-white shadow-lg shadow-teal-500/20 transition hover:bg-teal-600"
                    >

                        <svg xmlns="http://www.w3.org/2000/svg"
                             class="h-4 w-4"
                             fill="none"
                             viewBox="0 0 24 24"
                             stroke="currentColor"
                             stroke-width="2">
                            <path stroke-linecap="round"
                                  stroke-linejoin="round"
                                  d="M5 13l4 4L19 7"/>
                        </svg>

                        {{ $planillaEditando ? 'Actualizar planilla' : 'Registrar planilla' }}

                    </button>

                </div>

            </form>

        </div>

    @endif


    {{-- =========================================================
         TABLA
         ========================================================= --}}
    <div class="overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-800/60 dark:bg-zinc-950">

        <div class="border-b border-zinc-200 px-5 py-4 dark:border-zinc-800">

            <div class="flex items-center gap-3">

                <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-teal-500/10 text-teal-400">

                    <svg xmlns="http://www.w3.org/2000/svg"
                         class="h-5 w-5"
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

                    <h2 class="font-semibold text-zinc-900 dark:text-white">
                        Planillas registradas
                    </h2>

                    <p class="text-xs text-zinc-500">
                        Historial de remuneraciones de los empleados.
                    </p>

                </div>

            </div>

        </div>


        <div class="overflow-x-auto">

            <table class="min-w-full text-sm">

                <thead class="bg-zinc-50 dark:bg-black">

                    <tr>

                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-zinc-600 dark:text-zinc-400">
                            Empleado
                        </th>

                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-zinc-600 dark:text-zinc-400">
                            Periodo
                        </th>

                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-zinc-600 dark:text-zinc-400">
                            Básico
                        </th>

                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-zinc-600 dark:text-zinc-400">
                            Bonificaciones
                        </th>

                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-zinc-600 dark:text-zinc-400">
                            Descuentos
                        </th>

                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-zinc-600 dark:text-zinc-400">
                            Neto
                        </th>

                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-zinc-600 dark:text-zinc-400">
                            Estado
                        </th>

                        <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-zinc-600 dark:text-zinc-400">
                            Acciones
                        </th>

                    </tr>

                </thead>


                <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800/70">

                    @forelse ($planillas as $planilla)

                        <tr class="transition hover:bg-zinc-50 dark:hover:bg-zinc-900">

                            {{-- EMPLEADO --}}
                            <td class="px-5 py-4">

                                <div class="flex items-center gap-3">

                                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-zinc-800 text-xs font-semibold text-zinc-700 dark:text-zinc-300">

                                        {{ strtoupper(substr($planilla->empleado?->nombres ?? 'E', 0, 1)) }}{{ strtoupper(substr($planilla->empleado?->apellidos ?? 'M', 0, 1)) }}

                                    </div>

                                    <div>

                                        <div class="font-semibold text-zinc-900 dark:text-white">
                                            {{ $planilla->empleado?->nombres }}
                                            {{ $planilla->empleado?->apellidos }}
                                        </div>

                                        <div class="text-xs text-zinc-500">
                                            DNI {{ $planilla->empleado?->dni }}
                                        </div>

                                    </div>

                                </div>

                            </td>


                            {{-- PERIODO --}}
                            <td class="whitespace-nowrap px-5 py-4 text-zinc-700 dark:text-zinc-300">
                                {{ $planilla->periodo?->translatedFormat('F Y') }}
                            </td>


                            {{-- BÁSICO --}}
                            <td class="whitespace-nowrap px-5 py-4 text-zinc-700 dark:text-zinc-300">
                                S/ {{ number_format($planilla->sueldo_base, 2) }}
                            </td>


                            {{-- BONIFICACIONES --}}
                            <td class="whitespace-nowrap px-5 py-4 text-zinc-700 dark:text-zinc-300">
                                S/ {{ number_format($planilla->bonificaciones, 2) }}
                            </td>


                            {{-- DESCUENTOS --}}
                            <td class="whitespace-nowrap px-5 py-4 text-zinc-700 dark:text-zinc-300">
                                S/ {{ number_format($planilla->descuentos, 2) }}
                            </td>


                            {{-- NETO --}}
                            <td class="whitespace-nowrap px-5 py-4 font-bold text-zinc-900 dark:text-white">
                                S/ {{ number_format($planilla->sueldo_neto, 2) }}
                            </td>


                            {{-- ESTADO --}}
                            <td class="px-5 py-4">

                                @if ($planilla->estado === 'Pagada')

                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-green-500/10 px-3 py-1 text-xs font-semibold text-green-400">
                                        <span class="h-1.5 w-1.5 rounded-full bg-green-500"></span>
                                        Pagada
                                    </span>

                                @elseif ($planilla->estado === 'Anulada')

                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-red-500/10 px-3 py-1 text-xs font-semibold text-red-400">
                                        <span class="h-1.5 w-1.5 rounded-full bg-red-500"></span>
                                        Anulada
                                    </span>

                                @else

                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-yellow-500/10 px-3 py-1 text-xs font-semibold text-yellow-400">
                                        <span class="h-1.5 w-1.5 rounded-full bg-yellow-500"></span>
                                        Pendiente
                                    </span>

                                @endif

                            </td>


                            {{-- ACCIONES --}}
                            <td class="px-5 py-4 text-right">

                                <div class="flex justify-end gap-1">

                                    <button
                                        type="button"
                                        wire:click="editar({{ $planilla->id }})"
                                        title="Editar planilla"
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

                                        <span class="sr-only">
                                            Editar
                                        </span>

                                    </button>


                                    <button
                                        type="button"
                                        wire:click="eliminar({{ $planilla->id }})"
                                        wire:confirm="¿Estás seguro de eliminar esta planilla?"
                                        title="Eliminar planilla"
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

                                        <span class="sr-only">
                                            Eliminar
                                        </span>

                                    </button>

                                </div>

                            </td>

                        </tr>

                    @empty

                        <tr>

                            <td colspan="8" class="px-5 py-14 text-center">

                                <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-zinc-100 text-zinc-500 dark:bg-zinc-800 dark:text-zinc-500">

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
                                    No hay planillas registradas
                                </p>

                                <p class="mt-1 text-xs text-zinc-500">
                                    Utilice «Nueva planilla» para registrar una remuneración.
                                </p>

                            </td>

                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

    </div>

</div>
