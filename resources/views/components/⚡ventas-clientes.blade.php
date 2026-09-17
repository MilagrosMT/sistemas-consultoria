<?php

use App\Exports\VentasClientesTemplateExport;
use App\Imports\VentasClientesImport;
use App\Models\Cliente;
use App\Models\VentaCliente;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;

new class extends Component
{
    use WithPagination, WithFileUploads;

    public string $buscar = '';

    public bool $mostrarFormulario = false;
    public bool $mostrarImportacion = false;

    public $archivoExcel;

    public int $ventasImportadas = 0;
    public array $erroresImportacion = [];

    public string $cliente_id = '';
    public string $comprador = '';
    public string $ruc_comprador = '';
    public string $tipo_comprobante = 'Factura';
    public string $serie = 'F001';
    public string $numero = '';
    public string $fecha_emision = '';
    public string $base_imponible = '';
    public string $igv = '';
    public string $total = '';
    public string $forma_pago = 'Contado';
    public string $estado = 'Registrada';
    public string $observacion = '';

    public function updatedBuscar(): void
    {
        $this->resetPage();
    }

    public function abrirFormulario(): void
    {
        $this->resetValidation();

        $this->reset([
            'cliente_id',
            'comprador',
            'ruc_comprador',
            'numero',
            'base_imponible',
            'igv',
            'total',
            'observacion',
        ]);

        $this->tipo_comprobante = 'Factura';
        $this->serie = 'F001';
        $this->fecha_emision = now()->format('Y-m-d');
        $this->forma_pago = 'Contado';
        $this->estado = 'Registrada';

        $this->mostrarFormulario = true;
    }

    public function cerrarFormulario(): void
    {
        $this->mostrarFormulario = false;
        $this->resetValidation();
    }

    public function calcularTotal(): void
    {
        $base = (float) ($this->base_imponible ?: 0);

        $this->igv = number_format($base * 0.18, 2, '.', '');

        $this->total = number_format(
            $base + (float) $this->igv,
            2,
            '.',
            ''
        );
    }

    public function updatedBaseImponible(): void
    {
        $this->calcularTotal();
    }

    public function guardarVentaCliente(): void
    {
        $this->calcularTotal();

        $datos = $this->validate([
            'cliente_id' => ['required', 'exists:clientes,id'],
            'comprador' => ['required', 'string', 'max:255'],
            'ruc_comprador' => ['nullable', 'digits:11'],
            'tipo_comprobante' => [
                'required',
                'in:Factura,Boleta,Nota de crédito,Nota de débito',
            ],
            'serie' => ['required', 'string', 'max:10'],
            'numero' => ['required', 'string', 'max:20'],
            'fecha_emision' => ['required', 'date'],
            'base_imponible' => ['required', 'numeric', 'min:0'],
            'igv' => ['required', 'numeric', 'min:0'],
            'total' => ['required', 'numeric', 'min:0'],
            'forma_pago' => ['required', 'in:Contado,Credito'],
            'estado' => [
                'required',
                'in:Registrada,Observada,Anulada',
            ],
            'observacion' => ['nullable', 'string'],
        ]);

        $existe = VentaCliente::where('cliente_id', $datos['cliente_id'])
            ->where('tipo_comprobante', $datos['tipo_comprobante'])
            ->where('serie', $datos['serie'])
            ->where('numero', $datos['numero'])
            ->exists();

        if ($existe) {
            $this->addError(
                'numero',
                'Este comprobante ya está registrado para el cliente.'
            );

            return;
        }

        VentaCliente::create($datos);

        $this->cerrarFormulario();

        session()->flash(
            'success',
            'Venta del cliente registrada correctamente.'
        );
    }

    public function descargarPlantilla()
    {
        return Excel::download(
            new VentasClientesTemplateExport(),
            'plantilla_ventas_clientes.xlsx'
        );
    }

    public function abrirImportacion(): void
    {
        $this->resetValidation();

        $this->archivoExcel = null;
        $this->ventasImportadas = 0;
        $this->erroresImportacion = [];

        $this->mostrarImportacion = true;
    }

    public function cerrarImportacion(): void
    {
        $this->mostrarImportacion = false;
        $this->resetValidation();

        $this->archivoExcel = null;
    }

    public function importarExcel(): void
    {
        $this->validate([
            'archivoExcel' => [
                'required',
                'file',
                'mimes:xlsx,xls,csv',
                'max:5120',
            ],
        ], [
            'archivoExcel.required' => 'Selecciona un archivo Excel.',
            'archivoExcel.file' => 'El archivo seleccionado no es válido.',
            'archivoExcel.mimes' => 'El archivo debe ser XLSX, XLS o CSV.',
            'archivoExcel.max' => 'El archivo no debe superar los 5 MB.',
        ]);

        $import = new VentasClientesImport();

        Excel::import(
            $import,
            $this->archivoExcel->getRealPath()
        );

        $this->ventasImportadas = $import->importadas;
        $this->erroresImportacion = $import->errores;

        $this->archivoExcel = null;

        session()->flash(
            'success',
            "{$this->ventasImportadas} venta(s) importada(s) correctamente."
        );
    }

    public function getVentasFiltradasProperty()
    {
        return VentaCliente::with('cliente')
            ->when($this->buscar !== '', function ($query) {
                $buscar = '%' . $this->buscar . '%';

                $query->where(function ($q) use ($buscar) {
                    $q->where('comprador', 'like', $buscar)
                        ->orWhere('ruc_comprador', 'like', $buscar)
                        ->orWhere('serie', 'like', $buscar)
                        ->orWhere('numero', 'like', $buscar)
                        ->orWhereHas('cliente', function ($cliente) use ($buscar) {
                            $cliente
                                ->where('razon_social', 'like', $buscar)
                                ->orWhere('ruc', 'like', $buscar);
                        });
                });
            })
            ->latest('fecha_emision')
            ->paginate(10);
    }

    public function render()
    {
        return view('components.⚡ventas-clientes', [
            'clientes' => Cliente::orderBy('razon_social')->get(),
            'ventasClientes' => $this->ventasFiltradas,
            'totalVentasClientes' => VentaCliente::count(),
            'montoVentasClientes' => VentaCliente::sum('total'),
            'igvVentasClientes' => VentaCliente::sum('igv'),
        ]);
    }
};
?>

<div class="min-h-screen bg-zinc-50 text-zinc-900 transition-colors dark:bg-zinc-950 dark:text-zinc-100">

    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">

        {{-- Encabezado --}}
        <div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">

            <div>
                <p class="text-sm font-medium text-teal-700 dark:text-teal-400">
                    Gestión Contable
                </p>

                <h1 class="mt-1 text-2xl font-semibold tracking-tight text-zinc-900 dark:text-white">
                    Ventas de clientes
                </h1>

                <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">
                    Registro de las ventas de cada cliente para su procesamiento contable.
                </p>
            </div>

            <div class="flex flex-wrap gap-2">

                <button
                    type="button"
                    wire:click="descargarPlantilla"
                    class="inline-flex items-center justify-center gap-2 rounded-lg border border-zinc-200 bg-white px-4 py-2.5 text-sm font-medium text-zinc-700 transition hover:border-teal-600 hover:bg-teal-50 hover:text-teal-700 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-300 dark:hover:bg-teal-600/10 dark:hover:text-teal-400"
                >
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M12 3v12m0 0 4-4m-4 4-4-4M5 21h14"/>
                    </svg>

                    Plantilla Excel
                </button>

                <button
                    type="button"
                    wire:click="abrirImportacion"
                    class="inline-flex items-center justify-center gap-2 rounded-lg border border-zinc-200 bg-white px-4 py-2.5 text-sm font-medium text-zinc-700 transition hover:border-teal-600 hover:bg-teal-50 hover:text-teal-700 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-300 dark:hover:bg-teal-600/10 dark:hover:text-teal-400"
                >
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M12 16V4m0 0-4 4m4-4 4 4M5 20h14"/>
                    </svg>

                    Importar Excel
                </button>

                <button
                    type="button"
                    wire:click="abrirFormulario"
                    class="inline-flex items-center justify-center gap-2 rounded-lg bg-teal-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-teal-500"
                >
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14M5 12h14"/>
                    </svg>

                    Registrar venta
                </button>

            </div>

        </div>

        {{-- Indicadores --}}
        <div class="mb-6 grid gap-4 sm:grid-cols-3">

            <div class="rounded-xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                <p class="text-sm text-zinc-600 dark:text-zinc-400">
                    Ventas registradas
                </p>

                <p class="mt-2 text-2xl font-semibold text-zinc-900 dark:text-white">
                    {{ $totalVentasClientes }}
                </p>
            </div>

            <div class="rounded-xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                <p class="text-sm text-zinc-600 dark:text-zinc-400">
                    Total ventas
                </p>

                <p class="mt-2 text-2xl font-semibold text-zinc-900 dark:text-white">
                    S/ {{ number_format($montoVentasClientes, 2) }}
                </p>
            </div>

            <div class="rounded-xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                <p class="text-sm text-zinc-600 dark:text-zinc-400">
                    IGV registrado
                </p>

                <p class="mt-2 text-2xl font-semibold text-teal-700 dark:text-teal-400">
                    S/ {{ number_format($igvVentasClientes, 2) }}
                </p>
            </div>

        </div>

        {{-- Búsqueda --}}
        <div class="mb-5">

            <div class="relative">

                <svg
                    class="pointer-events-none absolute left-4 top-1/2 h-5 w-5 -translate-y-1/2 text-zinc-500"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="m21 21-4.35-4.35m1.35-5.4a6.75 6.75 0 1 1-13.5 0 6.75 6.75 0 0 1 13.5 0Z"
                    />
                </svg>

                <input
                    type="text"
                    wire:model.live="buscar"
                    placeholder="Buscar por comprador, RUC, comprobante o cliente..."
                    class="w-full rounded-xl border border-zinc-300 bg-white py-3 pl-12 pr-4 text-sm text-zinc-900 placeholder-zinc-400 outline-none transition focus:border-teal-600 focus:ring-1 focus:ring-teal-600 dark:border-zinc-800 dark:bg-zinc-950 dark:text-white dark:placeholder-zinc-500"
                >

            </div>

        </div>

        {{-- Tabla --}}
        <div class="overflow-hidden rounded-xl border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">

            <div class="overflow-x-auto">

                <table class="min-w-full text-left">

                    <thead class="border-b border-zinc-200 bg-zinc-50 dark:border-zinc-800 dark:bg-zinc-950">

                        <tr>

                            <th class="px-5 py-4 text-xs font-semibold uppercase tracking-wider text-zinc-600 dark:text-zinc-400">
                                Cliente
                            </th>

                            <th class="px-5 py-4 text-xs font-semibold uppercase tracking-wider text-zinc-600 dark:text-zinc-400">
                                Comprador
                            </th>

                            <th class="px-5 py-4 text-xs font-semibold uppercase tracking-wider text-zinc-600 dark:text-zinc-400">
                                Comprobante
                            </th>

                            <th class="px-5 py-4 text-xs font-semibold uppercase tracking-wider text-zinc-600 dark:text-zinc-400">
                                Fecha
                            </th>

                            <th class="px-5 py-4 text-xs font-semibold uppercase tracking-wider text-zinc-600 dark:text-zinc-400">
                                Total
                            </th>

                            <th class="px-5 py-4 text-xs font-semibold uppercase tracking-wider text-zinc-600 dark:text-zinc-400">
                                Pago
                            </th>

                            <th class="px-5 py-4 text-xs font-semibold uppercase tracking-wider text-zinc-600 dark:text-zinc-400">
                                Estado
                            </th>

                        </tr>

                    </thead>

                    <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">

                        @forelse ($ventasClientes as $venta)

                            <tr class="transition hover:bg-zinc-50 dark:hover:bg-zinc-800/50">

                                <td class="px-5 py-4">

                                    <div class="font-medium text-zinc-900 dark:text-white">
                                        {{ $venta->cliente->razon_social }}
                                    </div>

                                    <div class="mt-1 text-xs text-zinc-500">
                                        RUC {{ $venta->cliente->ruc }}
                                    </div>

                                </td>

                                <td class="px-5 py-4">

                                    <div class="font-medium text-zinc-800 dark:text-zinc-200">
                                        {{ $venta->comprador }}
                                    </div>

                                    @if ($venta->ruc_comprador)
                                        <div class="mt-1 text-xs text-zinc-500">
                                            {{ $venta->ruc_comprador }}
                                        </div>
                                    @endif

                                </td>

                                <td class="px-5 py-4">

                                    <span class="text-sm text-zinc-800 dark:text-zinc-200">
                                        {{ $venta->tipo_comprobante }}
                                    </span>

                                    <div class="mt-1 text-xs text-zinc-500">
                                        {{ $venta->serie }}-{{ $venta->numero }}
                                    </div>

                                </td>

                                <td class="px-5 py-4 text-sm text-zinc-700 dark:text-zinc-300">
                                    {{ $venta->fecha_emision?->format('d/m/Y') }}
                                </td>

                                <td class="px-5 py-4 font-medium text-zinc-900 dark:text-white">
                                    S/ {{ number_format($venta->total, 2) }}
                                </td>

                                <td class="px-5 py-4">

                                    @if ($venta->forma_pago === 'Contado')

                                        <span class="text-sm text-teal-700 dark:text-teal-400">
                                            Contado
                                        </span>

                                    @else

                                        <span class="text-sm text-amber-700 dark:text-amber-400">
                                            Crédito
                                        </span>

                                    @endif

                                </td>

                                <td class="px-5 py-4">

                                    @if ($venta->estado === 'Registrada')

                                        <span class="inline-flex rounded-full bg-teal-50 px-2.5 py-1 text-xs font-medium text-teal-700 dark:bg-teal-500/10 dark:text-teal-400">
                                            Registrada
                                        </span>

                                    @elseif ($venta->estado === 'Observada')

                                        <span class="inline-flex rounded-full bg-amber-50 px-2.5 py-1 text-xs font-medium text-amber-700 dark:bg-amber-500/10 dark:text-amber-400">
                                            Observada
                                        </span>

                                    @else

                                        <span class="inline-flex rounded-full bg-red-50 px-2.5 py-1 text-xs font-medium text-red-700 dark:bg-red-500/10 dark:text-red-400">
                                            Anulada
                                        </span>

                                    @endif

                                </td>

                            </tr>

                        @empty

                            <tr>

                                <td
                                    colspan="7"
                                    class="px-5 py-14 text-center"
                                >

                                    <div class="flex flex-col items-center">

                                        <svg
                                            class="mb-3 h-10 w-10 text-zinc-700"
                                            viewBox="0 0 24 24"
                                            fill="none"
                                            stroke="currentColor"
                                            stroke-width="1.5"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                d="M9 14.25h6m-6 3h6M9 6.75h6M6.75 3h10.5A1.75 1.75 0 0 1 19 4.75v14.5A1.75 1.75 0 0 1 17.25 21H6.75A1.75 1.75 0 0 1 5 19.25V4.75A1.75 1.75 0 0 1 6.75 3Z"
                                            />
                                        </svg>

                                        <p class="text-sm font-medium text-zinc-700 dark:text-zinc-300">
                                            No hay ventas registradas
                                        </p>

                                        <p class="mt-1 text-xs text-zinc-500">
                                            Registra o importa las ventas del cliente.
                                        </p>

                                    </div>

                                </td>

                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>

            @if ($ventasClientes->hasPages())

                <div class="border-t border-zinc-200 px-5 py-4 dark:border-zinc-800">
                    {{ $ventasClientes->links() }}
                </div>

            @endif

        </div>

    </div>

    {{-- Modal formulario --}}
    @if ($mostrarFormulario)

        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 px-4 py-6">

            <div class="max-h-[90vh] w-full max-w-3xl overflow-y-auto rounded-2xl border border-zinc-200 bg-white text-zinc-900 shadow-2xl dark:border-zinc-800 dark:bg-zinc-950 dark:text-white">

                <div class="sticky top-0 z-10 flex items-center justify-between border-b border-zinc-200 bg-white px-6 py-4 dark:border-zinc-800 dark:bg-zinc-950">

                    <div>
                        <h2 class="text-lg font-semibold text-zinc-900 dark:text-white">
                            Registrar venta del cliente
                        </h2>

                        <p class="mt-1 text-xs text-zinc-500">
                            Ingresa la información del comprobante de venta.
                        </p>
                    </div>

                    <button
                        type="button"
                        wire:click="cerrarFormulario"
                        class="rounded-lg p-2 text-zinc-500 transition hover:bg-zinc-100 hover:text-zinc-900 dark:hover:bg-zinc-800 dark:hover:text-white"
                    >
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/>
                        </svg>
                    </button>

                </div>

                <form wire:submit="guardarVentaCliente" class="p-6">

                    <div class="grid gap-5 sm:grid-cols-2">

                        {{-- Cliente --}}
                        <div class="sm:col-span-2">

                            <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-300">
                                Cliente
                            </label>

                            <select
                                wire:model="cliente_id"
                                class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2.5 text-sm text-zinc-900 outline-none focus:border-teal-600 focus:ring-1 focus:ring-teal-600 dark:border-zinc-800 dark:bg-zinc-900 dark:text-white"
                            >
                                <option value="">Seleccionar cliente</option>

                                @foreach ($clientes as $cliente)
                                    <option value="{{ $cliente->id }}">
                                        {{ $cliente->razon_social }} — RUC {{ $cliente->ruc }}
                                    </option>
                                @endforeach

                            </select>

                            @error('cliente_id')
                                <p class="mt-1 text-xs text-red-400">{{ $message }}</p>
                            @enderror

                        </div>

                        {{-- Comprador --}}
                        <div>

                            <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-300">
                                Comprador
                            </label>

                            <input
                                type="text"
                                wire:model="comprador"
                                placeholder="Nombre o razón social"
                                class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2.5 text-sm text-zinc-900 placeholder-zinc-400 outline-none focus:border-teal-600 focus:ring-1 focus:ring-teal-600 dark:border-zinc-800 dark:bg-zinc-900 dark:text-white dark:placeholder-zinc-600"
                            >

                            @error('comprador')
                                <p class="mt-1 text-xs text-red-400">{{ $message }}</p>
                            @enderror

                        </div>

                        {{-- RUC comprador --}}
                        <div>

                            <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-300">
                                RUC comprador
                            </label>

                            <input
                                type="text"
                                wire:model="ruc_comprador"
                                maxlength="11"
                                placeholder="Opcional"
                                class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2.5 text-sm text-zinc-900 placeholder-zinc-400 outline-none focus:border-teal-600 focus:ring-1 focus:ring-teal-600 dark:border-zinc-800 dark:bg-zinc-900 dark:text-white dark:placeholder-zinc-600"
                            >

                            @error('ruc_comprador')
                                <p class="mt-1 text-xs text-red-400">{{ $message }}</p>
                            @enderror

                        </div>

                        {{-- Tipo --}}
                        <div>

                            <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-300">
                                Tipo de comprobante
                            </label>

                            <select
                                wire:model="tipo_comprobante"
                                class="w-full rounded-lg border border-zinc-800 bg-zinc-900 px-3 py-2.5 text-sm text-white outline-none focus:border-teal-600"
                            >
                                <option>Factura</option>
                                <option>Boleta</option>
                                <option>Nota de crédito</option>
                                <option>Nota de débito</option>
                            </select>

                        </div>

                        {{-- Serie --}}
                        <div>

                            <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-300">
                                Serie
                            </label>

                            <input
                                type="text"
                                wire:model="serie"
                                maxlength="10"
                                placeholder="F001"
                                class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2.5 text-sm uppercase text-zinc-900 placeholder-zinc-400 outline-none focus:border-teal-600 dark:border-zinc-800 dark:bg-zinc-900 dark:text-white dark:placeholder-zinc-600"
                            >

                        </div>

                        {{-- Número --}}
                        <div>

                            <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-300">
                                Número
                            </label>

                            <input
                                type="text"
                                wire:model="numero"
                                placeholder="00000001"
                                class="w-full rounded-lg border border-zinc-800 bg-zinc-900 px-3 py-2.5 text-sm text-white placeholder-zinc-600 outline-none focus:border-teal-600"
                            >

                            @error('numero')
                                <p class="mt-1 text-xs text-red-400">{{ $message }}</p>
                            @enderror

                        </div>

                        {{-- Fecha --}}
                        <div>

                            <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-300">
                                Fecha de emisión
                            </label>

                            <input
                                type="date"
                                wire:model="fecha_emision"
                                class="w-full rounded-lg border border-zinc-800 bg-zinc-900 px-3 py-2.5 text-sm text-white outline-none focus:border-teal-600"
                            >

                        </div>

                        {{-- Forma de pago --}}
                        <div>

                            <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-300">
                                Forma de pago
                            </label>

                            <select
                                wire:model="forma_pago"
                                class="w-full rounded-lg border border-zinc-800 bg-zinc-900 px-3 py-2.5 text-sm text-white outline-none focus:border-teal-600"
                            >
                                <option value="Contado">Contado</option>
                                <option value="Credito">Crédito</option>
                            </select>

                        </div>

                        {{-- Base --}}
                        <div>

                            <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-300">
                                Base imponible
                            </label>

                            <input
                                type="number"
                                step="0.01"
                                min="0"
                                wire:model.live="base_imponible"
                                placeholder="0.00"
                                class="w-full rounded-lg border border-zinc-800 bg-zinc-900 px-3 py-2.5 text-sm text-white placeholder-zinc-600 outline-none focus:border-teal-600"
                            >

                        </div>

                        {{-- IGV --}}
                        <div>

                            <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-300">
                                IGV
                            </label>

                            <input
                                type="number"
                                step="0.01"
                                wire:model="igv"
                                readonly
                                class="w-full rounded-lg border border-zinc-800 bg-zinc-900/60 px-3 py-2.5 text-sm text-zinc-600 dark:text-zinc-400 outline-none"
                            >

                        </div>

                        {{-- Total --}}
                        <div>

                            <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-300">
                                Total
                            </label>

                            <input
                                type="number"
                                step="0.01"
                                wire:model="total"
                                readonly
                                class="w-full rounded-lg border border-zinc-300 bg-zinc-100 px-3 py-2.5 text-sm font-semibold text-teal-700 outline-none dark:border-zinc-800 dark:bg-zinc-900/60 dark:text-teal-400"
                            >

                        </div>

                        {{-- Estado --}}
                        <div>

                            <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-300">
                                Estado
                            </label>

                            <select
                                wire:model="estado"
                                class="w-full rounded-lg border border-zinc-800 bg-zinc-900 px-3 py-2.5 text-sm text-white outline-none focus:border-teal-600"
                            >
                                <option value="Registrada">Registrada</option>
                                <option value="Observada">Observada</option>
                                <option value="Anulada">Anulada</option>
                            </select>

                        </div>

                        {{-- Observación --}}
                        <div class="sm:col-span-2">

                            <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-300">
                                Observación
                            </label>

                            <textarea
                                wire:model="observacion"
                                rows="3"
                                placeholder="Observaciones de la venta..."
                                class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2.5 text-sm text-zinc-900 placeholder-zinc-400 outline-none focus:border-teal-600 focus:ring-1 focus:ring-teal-600 dark:border-zinc-800 dark:bg-zinc-900 dark:text-white dark:placeholder-zinc-600"
                            ></textarea>

                        </div>

                    </div>

                    <div class="mt-6 flex justify-end gap-3 border-t border-zinc-200 pt-5 dark:border-zinc-800">

                        <button
                            type="button"
                            wire:click="cerrarFormulario"
                            class="rounded-lg border border-zinc-700 px-4 py-2.5 text-sm font-medium text-zinc-700 dark:text-zinc-300 transition hover:bg-zinc-800 hover:text-white"
                        >
                            Cancelar
                        </button>

                        <button
                            type="submit"
                            class="inline-flex items-center gap-2 rounded-lg bg-teal-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-teal-500"
                        >
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                            </svg>

                            Guardar venta
                        </button>

                    </div>

                </form>

            </div>

        </div>

    @endif

    {{-- Modal importación --}}
    @if ($mostrarImportacion)

        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 px-4 py-6">

            <div class="w-full max-w-xl rounded-2xl border border-zinc-800 bg-zinc-950 shadow-2xl">

                <div class="flex items-center justify-between border-b border-zinc-800 px-6 py-4">

                    <div>
                        <h2 class="text-lg font-semibold text-zinc-900 dark:text-white">
                            Importar ventas desde Excel
                        </h2>

                        <p class="mt-1 text-xs text-zinc-500">
                            Carga la plantilla completada con las ventas del cliente.
                        </p>
                    </div>

                    <button
                        type="button"
                        wire:click="cerrarImportacion"
                        class="rounded-lg p-2 text-zinc-500 transition hover:bg-zinc-100 hover:text-zinc-900 dark:hover:bg-zinc-800 dark:hover:text-white"
                    >
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/>
                        </svg>
                    </button>

                </div>

                <div class="p-6">

                    <div class="rounded-xl border border-zinc-200 bg-zinc-50 p-5 dark:border-zinc-800 dark:bg-zinc-950">

                        <p class="text-sm font-medium text-zinc-900 dark:text-white">
                            Antes de importar
                        </p>

                        <p class="mt-2 text-sm leading-6 text-zinc-400">
                            Descarga la plantilla, completa los datos y conserva
                            los nombres de las columnas.
                        </p>

                        <button
                            type="button"
                            wire:click="descargarPlantilla"
                            class="mt-4 text-sm font-medium text-teal-700 dark:text-teal-400 hover:text-teal-300"
                        >
                            Descargar plantilla Excel
                        </button>

                    </div>

                    <div class="mt-5">

                        <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-300">
                            Archivo Excel
                        </label>

                        <input
                            type="file"
                            wire:model="archivoExcel"
                            accept=".xlsx,.xls,.csv"
                            class="block w-full rounded-lg border border-zinc-300 bg-white p-3 text-sm text-zinc-700 file:mr-4 file:rounded-lg file:border-0 file:bg-teal-600 file:px-4 file:py-2 file:text-sm file:font-medium file:text-white hover:file:bg-teal-500 dark:border-zinc-800 dark:bg-zinc-950 dark:text-zinc-300"
                        >

                        @error('archivoExcel')
                            <p class="mt-2 text-xs text-red-400">
                                {{ $message }}
                            </p>
                        @enderror

                        <div wire:loading wire:target="archivoExcel" class="mt-2 text-xs text-zinc-500">
                            Cargando archivo...
                        </div>

                    </div>

                    @if ($ventasImportadas > 0 || count($erroresImportacion) > 0)

                        <div class="mt-5 rounded-xl border border-zinc-200 bg-zinc-50 p-4 dark:border-zinc-800 dark:bg-zinc-950">

                            @if ($ventasImportadas > 0)
                                <p class="text-sm text-teal-700 dark:text-teal-400">
                                    {{ $ventasImportadas }} venta(s) importada(s) correctamente.
                                </p>
                            @endif

                            @if (count($erroresImportacion) > 0)

                                <div class="mt-3">

                                    <p class="text-sm font-medium text-amber-400">
                                        Registros con observaciones:
                                    </p>

                                    <div class="mt-2 max-h-40 space-y-2 overflow-y-auto">

                                        @foreach ($erroresImportacion as $error)

                                            <div class="rounded-lg bg-zinc-100 px-3 py-2 text-xs text-zinc-600 dark:bg-zinc-950 dark:text-zinc-400">

                                                <span class="font-medium text-zinc-700 dark:text-zinc-300">
                                                    Fila {{ $error['fila'] }}:
                                                </span>

                                                {{ $error['mensaje'] }}

                                            </div>

                                        @endforeach

                                    </div>

                                </div>

                            @endif

                        </div>

                    @endif

                    <div class="mt-6 flex justify-end gap-3 border-t border-zinc-200 pt-5 dark:border-zinc-800">

                        <button
                            type="button"
                            wire:click="cerrarImportacion"
                            class="rounded-lg border border-zinc-700 px-4 py-2.5 text-sm font-medium text-zinc-700 dark:text-zinc-300 transition hover:bg-zinc-800 hover:text-white"
                        >
                            Cerrar
                        </button>

                        <button
                            type="button"
                            wire:click="importarExcel"
                            wire:loading.attr="disabled"
                            wire:target="importarExcel"
                            class="inline-flex items-center gap-2 rounded-lg bg-teal-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-teal-500 disabled:cursor-not-allowed disabled:opacity-50"
                        >
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M4 16v2a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-2M8 8l4-4 4 4m-4-4v12"/>
                            </svg>

                            <span wire:loading.remove wire:target="importarExcel">
                                Importar ventas
                            </span>

                            <span wire:loading wire:target="importarExcel">
                                Procesando...
                            </span>
                        </button>

                    </div>

                </div>

            </div>

        </div>

    @endif

</div>
