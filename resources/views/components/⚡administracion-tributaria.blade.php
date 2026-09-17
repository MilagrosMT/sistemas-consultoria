<?php

use App\Models\ObligacionTributaria;
use App\Models\Cliente;
use App\Models\Compra;
use App\Models\VentaCliente;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component
{
    use WithPagination;

    public string $buscar = '';
public string $filtroEstado = '';

public string $filtroCliente = '';
public string $filtroPeriodo = '';

    public bool $mostrarFormulario = false;
    public ?int $obligacionEditando = null;

    public string $cliente_id = '';
    public string $tipo = '';
    public string $periodo = '';
    public string $fecha_vencimiento = '';
    public string $monto = '';
    public string $estado = 'Pendiente';

    public function updatingBuscar(): void
    {
        $this->resetPage();
    }

    public function updatingFiltroEstado(): void
    {
        $this->resetPage();
    }

    public function nuevaObligacion(): void
    {
        $this->resetFormulario();
        $this->resetValidation();

        $this->fecha_vencimiento = now()->format('Y-m-d');
        $this->mostrarFormulario = true;
    }

    public function editar(int $id): void
    {
        $obligacion = ObligacionTributaria::findOrFail($id);

        $this->obligacionEditando = $obligacion->id;

        $cliente = Cliente::where('ruc', $obligacion->ruc)->first();

        $this->cliente_id = $cliente ? (string) $cliente->id : '';

        $this->tipo = $obligacion->tipo_obligacion;
        $this->periodo = $obligacion->periodo;

        $this->fecha_vencimiento =
            $obligacion->fecha_vencimiento?->format('Y-m-d') ?? '';

        $this->monto = (string) $obligacion->monto;
        $this->estado = $obligacion->estado;

        $this->resetValidation();
        $this->mostrarFormulario = true;
    }

    public function guardar(): void
    {
        $datos = $this->validate([
            'cliente_id' => 'required|exists:clientes,id',
            'tipo' => 'required|string|max:150',
            'periodo' => 'required|string|max:20',
            'fecha_vencimiento' => 'required|date',
            'monto' => 'required|numeric|min:0',
            'estado' => 'required|in:Pendiente,Pagada,Vencida',
        ]);

        $cliente = Cliente::findOrFail($this->cliente_id);

        $estado = $this->estado;

        if (
            $estado === 'Pendiente' &&
            $this->fecha_vencimiento < now()->format('Y-m-d')
        ) {
            $estado = 'Vencida';
        }

        $datosGuardar = [
            'cliente' => $cliente->razon_social,
            'ruc' => $cliente->ruc,
            'tipo_obligacion' => $this->tipo,
            'periodo' => $this->periodo,
            'fecha_vencimiento' => $this->fecha_vencimiento,
            'monto' => $this->monto,
            'estado' => $estado,
        ];

        if ($this->obligacionEditando) {
            ObligacionTributaria::findOrFail(
                $this->obligacionEditando
            )->update($datosGuardar);

            $mensaje = 'Obligación tributaria actualizada correctamente.';
        } else {
            ObligacionTributaria::create($datosGuardar);

            $mensaje = 'Obligación tributaria registrada correctamente.';
        }

        $this->mostrarFormulario = false;
        $this->resetFormulario();

        session()->flash('mensaje', $mensaje);
    }

    public function eliminar(int $id): void
    {
        ObligacionTributaria::findOrFail($id)->delete();

        session()->flash(
            'mensaje',
            'Obligación tributaria eliminada correctamente.'
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
            'obligacionEditando',
            'cliente_id',
            'tipo',
            'periodo',
            'fecha_vencimiento',
            'monto',
        ]);

        $this->estado = 'Pendiente';
    }

    public function render()
    {
        $consulta = ObligacionTributaria::query();

        if ($this->buscar) {
            $consulta->where(function ($q) {
                $q->where('cliente', 'like', '%' . $this->buscar . '%')
                    ->orWhere('ruc', 'like', '%' . $this->buscar . '%')
                    ->orWhere('tipo_obligacion', 'like', '%' . $this->buscar . '%')
                    ->orWhere('periodo', 'like', '%' . $this->buscar . '%');
            });
        }

        if ($this->filtroEstado) {
            $consulta->where('estado', $this->filtroEstado);
        }
$compras = Compra::query();

$ventas = VentaCliente::query();

if ($this->filtroCliente) {
    $compras->where('cliente_id', $this->filtroCliente);
    $ventas->where('cliente_id', $this->filtroCliente);
}

if ($this->filtroPeriodo) {
    [$anio, $mes] = explode('-', $this->filtroPeriodo);

    $compras->whereYear('fecha_emision', $anio)
        ->whereMonth('fecha_emision', $mes);

    $ventas->whereYear('fecha_emision', $anio)
        ->whereMonth('fecha_emision', $mes);
}

$igvCompras = $compras->sum('igv');

$igvVentas = $ventas->sum('igv');

$igvResultante = $igvVentas - $igvCompras;
        return view('components.⚡administracion-tributaria', [
            'obligaciones' => $consulta
                ->latest()
                ->paginate(10),

            'clientes' => Cliente::where('estado', 'Activo')
                ->orderBy('razon_social')
                ->get(),

            'totalObligaciones' => ObligacionTributaria::count(),

            'totalPendientes' => ObligacionTributaria::where(
                'estado',
                'Pendiente'
            )->count(),

            'totalPagadas' => ObligacionTributaria::where(
                'estado',
                'Pagada'
            )->count(),

            'totalVencidas' => ObligacionTributaria::where(
                'estado',
                'Vencida'
            )->count(),

            'montoPendiente' => ObligacionTributaria::where(
                'estado',
                'Pendiente'
            )->sum('monto'),
'igvCompras' => $igvCompras,
'igvVentas' => $igvVentas,
'igvResultante' => $igvResultante,
        ]);
    }
};
?>
<div class="min-h-full space-y-6 bg-zinc-50 p-1 text-zinc-900 transition-colors dark:bg-zinc-50 dark:bg-black dark:text-white">

    {{-- =========================================================
         ENCABEZADO
         ========================================================= --}}

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
                    <path stroke-linecap="round"
                          stroke-linejoin="round"
                          d="M14 3v5h5"/>
                </svg>

            </div>

            <div>

                <h1 class="text-2xl font-bold tracking-tight text-zinc-900 dark:text-white">
                    Administración tributaria
                </h1>

                <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">
                    Control y seguimiento de las obligaciones tributarias de los clientes.
                </p>

            </div>

        </div>


        <button
            type="button"
            wire:click="nuevaObligacion"
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

            Nueva obligación

        </button>

    </div>


    {{-- =========================================================
         MENSAJE
         ========================================================= --}}

    @if (session('mensaje'))

        <div class="flex items-center gap-3 rounded-xl border border-zinc-800 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-400">

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

    @endif


    {{-- =========================================================
         INDICADORES
         ========================================================= --}}

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-5">

        {{-- TOTAL --}}
        <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-950">

            <p class="text-sm text-zinc-600 dark:text-zinc-400">
                Total obligaciones
            </p>

            <p class="mt-2 text-3xl font-bold text-zinc-900 dark:text-white">
                {{ $totalObligaciones }}
            </p>

            <p class="mt-1 text-xs text-zinc-500">
                Registradas
            </p>

        </div>


        {{-- PENDIENTES --}}
        <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-950">

            <p class="text-sm text-zinc-600 dark:text-zinc-400">
                Pendientes
            </p>

            <p class="mt-2 text-3xl font-bold text-yellow-400">
                {{ $totalPendientes }}
            </p>

            <p class="mt-1 text-xs text-zinc-500">
                Por atender
            </p>

        </div>


        {{-- PAGADAS --}}
        <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-950">

            <p class="text-sm text-zinc-600 dark:text-zinc-400">
                Pagadas
            </p>

            <p class="mt-2 text-3xl font-bold text-green-400">
                {{ $totalPagadas }}
            </p>

            <p class="mt-1 text-xs text-zinc-500">
                Cumplidas
            </p>

        </div>


        {{-- VENCIDAS --}}
        <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-950">

            <p class="text-sm text-zinc-600 dark:text-zinc-400">
                Vencidas
            </p>

            <p class="mt-2 text-3xl font-bold text-red-400">
                {{ $totalVencidas }}
            </p>

            <p class="mt-1 text-xs text-zinc-500">
                Requieren atención
            </p>

        </div>


        {{-- MONTO --}}
        <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-950">

            <p class="text-sm text-zinc-600 dark:text-zinc-400">
                Monto pendiente
            </p>

            <p class="mt-2 text-2xl font-bold text-zinc-900 dark:text-white">
                S/ {{ number_format($montoPendiente, 2) }}
            </p>

            <p class="mt-1 text-xs text-teal-700 dark:text-teal-400">
                Obligaciones pendientes
            </p>

        </div>

    </div>

{{-- =========================================================
     RESUMEN TRIBUTARIO
     ========================================================= --}}

<div class="grid grid-cols-1 gap-4 md:grid-cols-3">

    {{-- IGV VENTAS --}}
    <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-950">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-zinc-600 dark:text-zinc-400">
                    IGV de ventas
                </p>

                <p class="mt-2 text-2xl font-bold text-zinc-900 dark:text-white">
                    S/ {{ number_format($igvVentas, 2) }}
                </p>

                <p class="mt-1 text-xs text-zinc-500">
                    Ventas registradas de clientes
                </p>
            </div>

            <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-teal-500/10 text-teal-400">
                <svg xmlns="http://www.w3.org/2000/svg"
                     class="h-5 w-5"
                     fill="none"
                     viewBox="0 0 24 24"
                     stroke="currentColor"
                     stroke-width="1.8">
                    <path stroke-linecap="round"
                          stroke-linejoin="round"
                          d="M9 14l2 2 4-4m5-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
        </div>
    </div>


    {{-- IGV COMPRAS --}}
    <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-950">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-zinc-600 dark:text-zinc-400">
                    IGV de compras
                </p>

                <p class="mt-2 text-2xl font-bold text-zinc-900 dark:text-white">
                    S/ {{ number_format($igvCompras, 2) }}
                </p>

                <p class="mt-1 text-xs text-zinc-500">
                    Compras registradas de clientes
                </p>
            </div>

            <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-zinc-800 text-zinc-700 dark:text-zinc-300">
                <svg xmlns="http://www.w3.org/2000/svg"
                     class="h-5 w-5"
                     fill="none"
                     viewBox="0 0 24 24"
                     stroke="currentColor"
                     stroke-width="1.8">
                    <path stroke-linecap="round"
                          stroke-linejoin="round"
                          d="M3 7h18M5 7v10a2 2 0 002 2h10a2 2 0 002-2V7M8 11h8"/>
                </svg>
            </div>
        </div>
    </div>


    {{-- IGV RESULTANTE --}}
    <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-950">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-zinc-600 dark:text-zinc-400">
                    IGV resultante
                </p>

                <p class="mt-2 text-2xl font-bold
                    {{ $igvResultante >= 0 ? 'text-yellow-600 dark:text-yellow-400' : 'text-green-600 dark:text-green-400' }}">
                    S/ {{ number_format($igvResultante, 2) }}
                </p>

                <p class="mt-1 text-xs text-zinc-500">
                    IGV ventas − IGV compras
                </p>
            </div>

            <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-yellow-500/10 text-yellow-400">
                <svg xmlns="http://www.w3.org/2000/svg"
                     class="h-5 w-5"
                     fill="none"
                     viewBox="0 0 24 24"
                     stroke="currentColor"
                     stroke-width="1.8">
                    <path stroke-linecap="round"
                          stroke-linejoin="round"
                          d="M9 7h6m-6 4h6m-6 4h4m-7 5h10a2 2 0 002-2V6.5L15.5 3H6a2 2 0 00-2 2v13a2 2 0 002 2z"/>
                </svg>
            </div>
        </div>
    </div>

</div>
{{-- =========================================================
     FILTROS DEL ANÁLISIS TRIBUTARIO
     ========================================================= --}}

<div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-950">

    <div class="mb-4">
        <h2 class="font-semibold text-zinc-900 dark:text-white">
            Análisis tributario
        </h2>

        <p class="mt-1 text-xs text-zinc-500">
            Seleccione el cliente y período para consultar su información tributaria.
        </p>
    </div>

    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">

        {{-- CLIENTE --}}
        <div>
            <label class="mb-2 block text-sm font-semibold text-zinc-800 dark:text-zinc-200">
                Cliente
            </label>

            <select
                wire:model.live="filtroCliente"
                class="h-12 w-full rounded-xl border border-zinc-300 bg-white px-4 text-sm text-zinc-900 outline-none transition focus:border-teal-500 focus:ring-1 focus:ring-teal-500 dark:border-zinc-800 dark:bg-zinc-900 dark:text-white"
            >
                <option value="">
                    Todos los clientes
                </option>

                @foreach ($clientes as $cliente)
                    <option value="{{ $cliente->id }}">
                        {{ $cliente->razon_social }} — RUC {{ $cliente->ruc }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- PERÍODO --}}
        <div>
            <label class="mb-2 block text-sm font-semibold text-zinc-800 dark:text-zinc-200">
                Período
            </label>

            <input
                wire:model.live="filtroPeriodo"
                type="month"
                class="h-12 w-full rounded-xl border border-zinc-300 bg-white px-4 text-sm text-zinc-900 outline-none transition focus:border-teal-500 focus:ring-1 focus:ring-teal-500 dark:border-zinc-800 dark:bg-zinc-900 dark:text-white"
            >

            <p class="mt-1 text-xs text-zinc-500">
                Filtra las compras y ventas del mes seleccionado.
            </p>
        </div>

    </div>

</div>
    {{-- =========================================================
         BUSCADOR Y FILTRO
         ========================================================= --}}

    <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-950">

        <div class="grid grid-cols-1 gap-4 lg:grid-cols-[1fr_280px]">

            <div>

                <label class="mb-2 block text-sm font-semibold text-zinc-800 dark:text-zinc-200">
                    Buscar obligación
                </label>

                <div class="relative">

                    <svg xmlns="http://www.w3.org/2000/svg"
                         class="absolute left-4 top-1/2 h-5 w-5 -translate-y-1/2 text-zinc-500"
                         fill="none"
                         viewBox="0 0 24 24"
                         stroke="currentColor"
                         stroke-width="1.8">
                        <circle cx="11" cy="11" r="7"/>
                        <path stroke-linecap="round"
                              d="M20 20l-4-4"/>
                    </svg>

                    <input
                        wire:model.live.debounce.300ms="buscar"
                        type="text"
                        placeholder="Buscar por cliente, RUC, tipo o periodo..."
                        class="h-12 w-full rounded-xl border border-zinc-300 bg-white pl-12 pr-4 text-sm text-zinc-900 placeholder-zinc-400 outline-none transition focus:border-teal-500 focus:ring-1 focus:ring-teal-500 dark:border-zinc-800 dark:bg-zinc-900 dark:text-white dark:placeholder-zinc-500"
                    >

                </div>

            </div>


            <div>

                <label class="mb-2 block text-sm font-semibold text-zinc-800 dark:text-zinc-200">
                    Filtrar por estado
                </label>

                <select
                    wire:model.live="filtroEstado"
                    class="h-12 w-full rounded-xl border border-zinc-300 bg-white px-4 text-sm text-zinc-900 outline-none transition focus:border-teal-500 focus:ring-1 focus:ring-teal-500 dark:border-zinc-800 dark:bg-zinc-900 dark:text-white"
                >
                    <option value="">Todos los estados</option>
                    <option value="Pendiente">Pendiente</option>
                    <option value="Pagada">Pagada</option>
                    <option value="Vencida">Vencida</option>
                </select>

            </div>

        </div>

    </div>


    {{-- =========================================================
         TABLA
         ========================================================= --}}

    <div class="overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-950">

        <div class="border-b border-zinc-200 px-5 py-4 dark:border-zinc-800">

            <h2 class="font-semibold text-zinc-900 dark:text-white">
                Obligaciones tributarias
            </h2>

            <p class="mt-1 text-xs text-zinc-500">
                Registro y seguimiento de las obligaciones de los clientes.
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
                            Obligación
                        </th>

                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-zinc-600 dark:text-zinc-400">
                            Periodo
                        </th>

                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-zinc-600 dark:text-zinc-400">
                            Vencimiento
                        </th>

                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-zinc-600 dark:text-zinc-400">
                            Monto
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

                    @forelse ($obligaciones as $obligacion)

                        <tr class="transition hover:bg-zinc-50 dark:hover:bg-zinc-900">

                            {{-- CLIENTE --}}
                            <td class="px-5 py-4">

                                <div class="max-w-xs">

                                    <p class="font-semibold text-zinc-900 dark:text-white">
                                        {{ $obligacion->cliente }}
                                    </p>

                                    <p class="mt-1 text-xs text-zinc-500">
                                      RUC {{ $obligacion->ruc }}
                                    </p>

                                </div>

                            </td>


                            {{-- TIPO --}}
                            <td class="px-5 py-4 text-zinc-700 dark:text-zinc-300">
                               {{ $obligacion->tipo_obligacion }}
                            </td>


                            {{-- PERIODO --}}
                            <td class="whitespace-nowrap px-5 py-4 text-zinc-700 dark:text-zinc-300">
                                {{ $obligacion->periodo }}
                            </td>


                            {{-- FECHA --}}
                            <td class="whitespace-nowrap px-5 py-4 text-zinc-600 dark:text-zinc-400">
                                {{ $obligacion->fecha_vencimiento?->format('d/m/Y') }}
                            </td>


                            {{-- MONTO --}}
                            <td class="whitespace-nowrap px-5 py-4 font-semibold text-zinc-900 dark:text-white">
                                S/ {{ number_format($obligacion->monto, 2) }}
                            </td>


                            {{-- ESTADO --}}
                            <td class="px-5 py-4">

                                @if ($obligacion->estado === 'Pagada')

                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-green-500/10 px-3 py-1 text-xs font-semibold text-green-400">
                                        <span class="h-1.5 w-1.5 rounded-full bg-green-500"></span>
                                        Pagada
                                    </span>

                                @elseif ($obligacion->estado === 'Vencida')

                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-red-500/10 px-3 py-1 text-xs font-semibold text-red-400">
                                        <span class="h-1.5 w-1.5 rounded-full bg-red-500"></span>
                                        Vencida
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
                                        wire:click="editar({{ $obligacion->id }})"
                                        title="Editar obligación"
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
                                        wire:click="eliminar({{ $obligacion->id }})"
                                        wire:confirm="¿Estás seguro de eliminar esta obligación tributaria?"
                                        title="Eliminar obligación"
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
                                    No hay obligaciones registradas
                                </p>

                                <p class="mt-1 text-xs text-zinc-500">
                                    Utilice «Nueva obligación» para registrar una.
                                </p>

                            </td>

                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>


        @if ($obligaciones->hasPages())

            <div class="border-t border-zinc-800 px-5 py-4">
                {{ $obligaciones->links() }}
            </div>

        @endif

    </div>


    {{-- =========================================================
         MODAL
         ========================================================= --}}

    @if ($mostrarFormulario)

        <div class="fixed inset-0 z-50 flex items-center justify-center bg-zinc-50 dark:bg-black/80 p-4 backdrop-blur-sm">

            <div class="w-full max-w-3xl overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-950 shadow-2xl">

                {{-- CABECERA --}}
                <div class="flex items-center justify-between border-b border-zinc-200 px-6 py-5 dark:border-zinc-800">

                    <div>

                        <h2 class="text-xl font-semibold text-zinc-900 dark:text-white">
                            {{ $obligacionEditando ? 'Editar obligación tributaria' : 'Registrar obligación tributaria' }}
                        </h2>

                        <p class="mt-1 text-sm text-zinc-500">
                            Registra la información tributaria correspondiente al cliente.
                        </p>

                    </div>


                    <button
                        type="button"
                        wire:click="cancelar"
                        class="flex h-9 w-9 items-center justify-center rounded-lg text-zinc-600 dark:text-zinc-400 transition hover:bg-zinc-800 hover:text-white"
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

                                    <option value="">
                                        Seleccionar cliente
                                    </option>

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


                            {{-- TIPO --}}
                            <div>

                                <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-300">
                                    Tipo de obligación <span class="text-red-500">*</span>
                                </label>

                                <input
                                    wire:model="tipo"
                                    type="text"
                                    placeholder="Ej. IGV mensual"
                                    class="h-11 w-full rounded-xl border border-zinc-300 bg-white px-4 py-0 text-sm text-zinc-900 placeholder-zinc-400 outline-none focus:border-teal-500 focus:ring-1 focus:ring-teal-500 dark:border-zinc-800 dark:bg-zinc-900 dark:text-white dark:placeholder-zinc-500"
                                >

                                @error('tipo')
                                    <span class="mt-1 block text-xs text-red-400">
                                        {{ $message }}
                                    </span>
                                @enderror

                            </div>


                            {{-- PERIODO --}}
                            <div>

                                <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-300">
                                    Periodo <span class="text-red-500">*</span>
                                </label>

                                <input
                                    wire:model="periodo"
                                    type="text"
                                    placeholder="Ej. Agosto 2026"
                                    class="h-11 w-full rounded-xl border border-zinc-300 bg-white px-4 py-0 text-sm text-zinc-900 placeholder-zinc-400 outline-none focus:border-teal-500 focus:ring-1 focus:ring-teal-500 dark:border-zinc-800 dark:bg-zinc-900 dark:text-white dark:placeholder-zinc-500"
                                >

                                @error('periodo')
                                    <span class="mt-1 block text-xs text-red-400">
                                        {{ $message }}
                                    </span>
                                @enderror

                            </div>


                            {{-- VENCIMIENTO --}}
                            <div>

                                <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-300">
                                    Fecha de vencimiento <span class="text-red-500">*</span>
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


                            {{-- MONTO --}}
                            <div>

                                <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-300">
                                    Monto (S/) <span class="text-red-500">*</span>
                                </label>

                                <input
                                    wire:model="monto"
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    placeholder="0.00"
                                    class="h-11 w-full rounded-xl border border-zinc-300 bg-white px-4 py-0 text-sm text-zinc-900 placeholder-zinc-400 outline-none focus:border-teal-500 focus:ring-1 focus:ring-teal-500 dark:border-zinc-800 dark:bg-zinc-900 dark:text-white dark:placeholder-zinc-500"
                                >

                                @error('monto')
                                    <span class="mt-1 block text-xs text-red-400">
                                        {{ $message }}
                                    </span>
                                @enderror

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
                                    <option value="Pendiente">
                                        Pendiente
                                    </option>

                                    <option value="Pagada">
                                        Pagada
                                    </option>

                                    <option value="Vencida">
                                        Vencida
                                    </option>

                                </select>

                            </div>



                        </div>

                    </div>


                    {{-- BOTONES --}}
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
                            {{ $obligacionEditando ? 'Actualizar obligación' : 'Registrar obligación' }}
                        </button>

                    </div>

                </form>

            </div>

        </div>

    @endif

</div>
