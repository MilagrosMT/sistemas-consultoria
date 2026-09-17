<?php

use App\Models\Cliente;
use App\Models\Compra;
use App\Models\MovimientoCaja;
use App\Models\VentaCliente;
use Livewire\Component;

new class extends Component
{
    public string $cliente_id = '';
    public string $periodo = '';

    public function mount(): void
    {
        $this->periodo = now()->format('Y-m');
    }

    public function render()
    {
        $comprasQuery = Compra::query();
        $ventasQuery = VentaCliente::query();
        $cajaQuery = MovimientoCaja::query();

        if ($this->cliente_id !== '') {
            $comprasQuery->where('cliente_id', $this->cliente_id);
            $ventasQuery->where('cliente_id', $this->cliente_id);
            $cajaQuery->where('cliente_id', $this->cliente_id);
        }

        if ($this->periodo !== '') {
            $inicio = $this->periodo . '-01';
            $fin = date('Y-m-t', strtotime($inicio));

            $comprasQuery->whereBetween('fecha_emision', [$inicio, $fin]);
            $ventasQuery->whereBetween('fecha_emision', [$inicio, $fin]);
            $cajaQuery->whereBetween('fecha', [$inicio, $fin]);
        }

        $totalCompras = (float) $comprasQuery->sum('total');
        $igvCompras = (float) $comprasQuery->sum('igv');

        $totalVentas = (float) $ventasQuery->sum('total');
        $igvVentas = (float) $ventasQuery->sum('igv');

        $ingresosCaja = (float) (clone $cajaQuery)
            ->where('tipo_movimiento', 'Ingreso')
            ->sum('monto');

        $egresosCaja = (float) (clone $cajaQuery)
            ->where('tipo_movimiento', 'Egreso')
            ->sum('monto');

        $saldoCaja = $ingresosCaja - $egresosCaja;

        $compras = $comprasQuery
            ->with('cliente')
            ->latest('fecha_emision')
            ->get();

        $ventas = $ventasQuery
            ->with('cliente')
            ->latest('fecha_emision')
            ->get();

        $movimientos = $cajaQuery
            ->with('cliente')
            ->latest('fecha')
            ->get();

        return view('components.⚡procesamiento-contable', [
            'clientes' => Cliente::orderBy('razon_social')->get(),
            'compras' => $compras,
            'ventas' => $ventas,
            'movimientos' => $movimientos,
            'totalCompras' => $totalCompras,
            'igvCompras' => $igvCompras,
            'totalVentas' => $totalVentas,
            'igvVentas' => $igvVentas,
            'ingresosCaja' => $ingresosCaja,
            'egresosCaja' => $egresosCaja,
            'saldoCaja' => $saldoCaja,
        ]);
    }
};
?>

<div class="min-h-screen bg-zinc-50 text-zinc-900 transition-colors dark:bg-zinc-950 dark:text-zinc-100">

    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">

        {{-- Encabezado --}}
        <div class="mb-8">

            <p class="text-sm font-medium text-teal-700 dark:text-teal-400">
                Gestión Contable
            </p>

            <h1 class="mt-1 text-2xl font-semibold tracking-tight text-zinc-900 dark:text-white">
                Procesamiento contable
            </h1>

            <p class="mt-1 max-w-3xl text-sm text-zinc-600 dark:text-zinc-400">
                Integración de compras, ventas y movimientos de caja del cliente
                para generar información de apoyo al procesamiento contable.
            </p>

        </div>

        {{-- Filtros --}}
        <div class="mb-6 rounded-xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">

            <div class="grid gap-4 sm:grid-cols-2">

                <div>

                    <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-300">
                        Cliente
                    </label>

                    <select
                        wire:model.live="cliente_id"
                        class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2.5 text-sm text-zinc-900 outline-none focus:border-teal-600 focus:ring-1 focus:ring-teal-600 dark:border-zinc-800 dark:bg-zinc-900 dark:text-white"
                    >
                        <option value="">Todos los clientes</option>

                        @foreach ($clientes as $cliente)

                            <option value="{{ $cliente->id }}">
                                {{ $cliente->razon_social }} — RUC {{ $cliente->ruc }}
                            </option>

                        @endforeach

                    </select>

                </div>

                <div>

                    <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-300">
                        Período
                    </label>

                    <input
                        type="month"
                        wire:model.live="periodo"
                        class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2.5 text-sm text-zinc-900 outline-none focus:border-teal-600 focus:ring-1 focus:ring-teal-600 dark:border-zinc-800 dark:bg-zinc-900 dark:text-white"
                    >

                </div>

            </div>

        </div>

        {{-- Resumen --}}
        <div class="mb-8">

            <h2 class="mb-4 text-base font-semibold text-zinc-900 dark:text-white">
                Resumen del período
            </h2>

            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">

                <div class="rounded-xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                    <p class="text-sm text-zinc-400">
                        Compras
                    </p>

                    <p class="mt-2 text-2xl font-semibold text-zinc-900 dark:text-white">
                        S/ {{ number_format($totalCompras, 2) }}
                    </p>

                    <p class="mt-1 text-xs text-zinc-500">
                        IGV: S/ {{ number_format($igvCompras, 2) }}
                    </p>
                </div>

                <div class="rounded-xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                    <p class="text-sm text-zinc-400">
                        Ventas
                    </p>

                    <p class="mt-2 text-2xl font-semibold text-zinc-900 dark:text-white">
                        S/ {{ number_format($totalVentas, 2) }}
                    </p>

                    <p class="mt-1 text-xs text-zinc-500">
                        IGV: S/ {{ number_format($igvVentas, 2) }}
                    </p>
                </div>

                <div class="rounded-xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                    <p class="text-sm text-zinc-400">
                        Ingresos de caja
                    </p>

                    <p class="mt-2 text-2xl font-semibold text-teal-700 dark:text-teal-400">
                        S/ {{ number_format($ingresosCaja, 2) }}
                    </p>
                </div>

                <div class="rounded-xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                    <p class="text-sm text-zinc-400">
                        Saldo de caja
                    </p>

                    <p class="mt-2 text-2xl font-semibold {{ $saldoCaja >= 0 ? 'text-zinc-900 dark:text-white' : 'text-red-600 dark:text-red-400' }}">
                        S/ {{ number_format($saldoCaja, 2) }}
                    </p>

                    <p class="mt-1 text-xs text-zinc-500">
                        Egresos: S/ {{ number_format($egresosCaja, 2) }}
                    </p>
                </div>

            </div>

        </div>

        {{-- Flujo contable --}}
        <div class="mb-8 rounded-xl border border-zinc-800 bg-zinc-900/70 p-6">

            <h2 class="text-base font-semibold text-zinc-900 dark:text-white">
                Flujo de procesamiento
            </h2>

            <p class="mt-1 text-sm text-zinc-500">
                La información registrada se organiza para apoyar la elaboración
                de reportes y el procesamiento contable del cliente.
            </p>

            <div class="mt-6 grid gap-4 md:grid-cols-4">

                <div class="rounded-xl border border-zinc-200 bg-zinc-50 p-4 dark:border-zinc-800 dark:bg-zinc-950">
                    <div class="text-sm font-semibold text-teal-400">
                        01
                    </div>

                    <p class="mt-2 font-medium text-zinc-900 dark:text-white">
                        Recopilación
                    </p>

                    <p class="mt-1 text-xs leading-5 text-zinc-500">
                        Compras, ventas y movimientos de caja.
                    </p>
                </div>

                <div class="rounded-xl border border-zinc-200 bg-zinc-50 p-4 dark:border-zinc-800 dark:bg-zinc-950">
                    <div class="text-sm font-semibold text-teal-400">
                        02
                    </div>

                    <p class="mt-2 font-medium text-zinc-900 dark:text-white">
                        Validación
                    </p>

                    <p class="mt-1 text-xs leading-5 text-zinc-500">
                        Revisión de datos y comprobantes registrados.
                    </p>
                </div>

                <div class="rounded-xl border border-zinc-200 bg-zinc-50 p-4 dark:border-zinc-800 dark:bg-zinc-950">
                    <div class="text-sm font-semibold text-teal-400">
                        03
                    </div>

                    <p class="mt-2 font-medium text-zinc-900 dark:text-white">
                        Procesamiento
                    </p>

                    <p class="mt-1 text-xs leading-5 text-zinc-500">
                        Consolidación de la información del período.
                    </p>
                </div>

                <div class="rounded-xl border border-zinc-200 bg-zinc-50 p-4 dark:border-zinc-800 dark:bg-zinc-950">
                    <div class="text-sm font-semibold text-teal-400">
                        04
                    </div>

                    <p class="mt-2 font-medium text-zinc-900 dark:text-white">
                        Reportes
                    </p>

                    <p class="mt-1 text-xs leading-5 text-zinc-500">
                        Información preparada para análisis contable.
                    </p>
                </div>

            </div>

        </div>

        {{-- Compras --}}
        <div class="mb-8 overflow-hidden rounded-xl border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">

            <div class="border-b border-zinc-200 px-5 py-4 dark:border-zinc-800">

                <h2 class="font-semibold text-zinc-900 dark:text-white">
                    Compras del período
                </h2>

            </div>

            <div class="overflow-x-auto">

                <table class="min-w-full text-left">

                    <thead class="border-b border-zinc-200 bg-zinc-50 dark:border-zinc-800 dark:bg-zinc-950">

                        <tr>

                            <th class="px-5 py-4 text-xs font-semibold uppercase tracking-wider text-zinc-600 dark:text-zinc-400">
                                Cliente
                            </th>

                            <th class="px-5 py-4 text-xs font-semibold uppercase tracking-wider text-zinc-600 dark:text-zinc-400">
                                Proveedor
                            </th>

                            <th class="px-5 py-4 text-xs font-semibold uppercase tracking-wider text-zinc-600 dark:text-zinc-400">
                                Comprobante
                            </th>

                            <th class="px-5 py-4 text-xs font-semibold uppercase tracking-wider text-zinc-600 dark:text-zinc-400">
                                Total
                            </th>

                        </tr>

                    </thead>

                    <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">

                        @forelse ($compras as $compra)

                            <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/40">

                                <td class="px-5 py-4 text-sm text-zinc-800 dark:text-zinc-200">
                                    {{ $compra->cliente->razon_social }}
                                </td>

                                <td class="px-5 py-4 text-sm text-zinc-700 dark:text-zinc-300">
                                    {{ $compra->proveedor }}
                                </td>

                                <td class="px-5 py-4 text-sm text-zinc-700 dark:text-zinc-300">
                                    {{ $compra->serie }}-{{ $compra->numero }}
                                </td>

                                <td class="px-5 py-4 text-sm font-medium text-zinc-900 dark:text-white">
                                    S/ {{ number_format($compra->total, 2) }}
                                </td>

                            </tr>

                        @empty

                            <tr>
                                <td colspan="4" class="px-5 py-8 text-center text-sm text-zinc-500">
                                    No hay compras para el período seleccionado.
                                </td>
                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>

        </div>

        {{-- Ventas --}}
        <div class="mb-8 overflow-hidden rounded-xl border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">

            <div class="border-b border-zinc-200 px-5 py-4 dark:border-zinc-800">

                <h2 class="font-semibold text-zinc-900 dark:text-white">
                    Ventas del período
                </h2>

            </div>

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
                                Total
                            </th>

                        </tr>

                    </thead>

                    <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">

                        @forelse ($ventas as $venta)

                            <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/40">

                                <td class="px-5 py-4 text-sm text-zinc-800 dark:text-zinc-200">
                                    {{ $venta->cliente->razon_social }}
                                </td>

                                <td class="px-5 py-4 text-sm text-zinc-700 dark:text-zinc-300">
                                    {{ $venta->comprador }}
                                </td>

                                <td class="px-5 py-4 text-sm text-zinc-700 dark:text-zinc-300">
                                    {{ $venta->serie }}-{{ $venta->numero }}
                                </td>

                                <td class="px-5 py-4 text-sm font-medium text-zinc-900 dark:text-white">
                                    S/ {{ number_format($venta->total, 2) }}
                                </td>

                            </tr>

                        @empty

                            <tr>
                                <td colspan="4" class="px-5 py-8 text-center text-sm text-zinc-500">
                                    No hay ventas para el período seleccionado.
                                </td>
                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>

        </div>

        {{-- Caja --}}
        <div class="overflow-hidden rounded-xl border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">

            <div class="border-b border-zinc-200 px-5 py-4 dark:border-zinc-800">

                <h2 class="font-semibold text-zinc-900 dark:text-white">
                    Movimientos de caja del período
                </h2>

            </div>

            <div class="overflow-x-auto">

                <table class="min-w-full text-left">

                    <thead class="border-b border-zinc-200 bg-zinc-50 dark:border-zinc-800 dark:bg-zinc-950">

                        <tr>

                            <th class="px-5 py-4 text-xs font-semibold uppercase tracking-wider text-zinc-600 dark:text-zinc-400">
                                Cliente
                            </th>

                            <th class="px-5 py-4 text-xs font-semibold uppercase tracking-wider text-zinc-600 dark:text-zinc-400">
                                Fecha
                            </th>

                            <th class="px-5 py-4 text-xs font-semibold uppercase tracking-wider text-zinc-600 dark:text-zinc-400">
                                Concepto
                            </th>

                            <th class="px-5 py-4 text-xs font-semibold uppercase tracking-wider text-zinc-600 dark:text-zinc-400">
                                Tipo
                            </th>

                            <th class="px-5 py-4 text-xs font-semibold uppercase tracking-wider text-zinc-600 dark:text-zinc-400">
                                Monto
                            </th>

                        </tr>

                    </thead>

                    <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">

                        @forelse ($movimientos as $movimiento)

                            <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/40">

                                <td class="px-5 py-4 text-sm text-zinc-800 dark:text-zinc-200">
                                    {{ $movimiento->cliente->razon_social }}
                                </td>

                                <td class="px-5 py-4 text-sm text-zinc-700 dark:text-zinc-300">
                                    {{ $movimiento->fecha?->format('d/m/Y') }}
                                </td>

                                <td class="px-5 py-4 text-sm text-zinc-700 dark:text-zinc-300">
                                    {{ $movimiento->concepto }}
                                </td>

                                <td class="px-5 py-4">

                                    @if ($movimiento->tipo_movimiento === 'Ingreso')

                                        <span class="text-sm text-teal-700 dark:text-teal-400">
                                            Ingreso
                                        </span>

                                    @else

                                        <span class="text-sm text-amber-700 dark:text-amber-400">
                                            Egreso
                                        </span>

                                    @endif

                                </td>

                                <td class="px-5 py-4 font-medium text-zinc-900 dark:text-white">
                                    S/ {{ number_format($movimiento->monto, 2) }}
                                </td>

                            </tr>

                        @empty

                            <tr>
                                <td colspan="5" class="px-5 py-8 text-center text-sm text-zinc-500">
                                    No hay movimientos para el período seleccionado.
                                </td>
                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>

        </div>

    </div>

</div>
