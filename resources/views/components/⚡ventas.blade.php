<?php

use App\Models\Cliente;
use App\Models\ConfiguracionSistema;
use App\Models\Servicio;
use App\Models\Venta;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

new class extends Component
{
    public string $buscar = '';
    public bool $mostrarFormulario = false;

    public string $cliente_id = '';
    public string $servicio_id = '';
    public string $tipo_comprobante = 'Factura';
    public string $numero_comprobante = '';
    public string $fecha_emision = '';
    public string $cantidad = '1';
    public string $precio_unitario = '';
    public string $descripcion = '';
    public string $forma_pago = 'Contado';
    public string $fecha_vencimiento = '';
    public string $base_imponible = '';
    public string $igv = '';
    public string $total = '';
    public string $monto_pendiente = '0.00';
    public string $estado = 'Emitida';
    public string $observacion = '';

    public function updatedServicioId(): void
    {
        $servicio = Servicio::find($this->servicio_id);

        if (!$servicio) {
            $this->precio_unitario = '';
            $this->descripcion = '';
            $this->base_imponible = '';
            $this->igv = '';
            $this->total = '';
            return;
        }

        $this->precio_unitario = number_format((float) $servicio->precio, 2, '.', '');
        $this->descripcion = $servicio->descripcion ?: $servicio->nombre;

        $this->calcularTotal();
        $this->actualizarNumeroComprobante();
    }

    public function updatedCantidad(): void
    {
        $this->calcularTotal();
    }

    public function updatedTipoComprobante(): void
    {
        $this->actualizarNumeroComprobante();
    }

    public function updatedFormaPago(): void
    {
        if ($this->forma_pago === 'Contado') {
            $this->fecha_vencimiento = '';
            $this->monto_pendiente = '0.00';
        } else {
            $this->monto_pendiente = $this->total ?: '0.00';

            if ($this->fecha_vencimiento === '') {
                $this->fecha_vencimiento = date('Y-m-d', strtotime('+30 days'));
            }
        }
    }

    public function updatedBaseImponible(): void
    {
        $this->calcularTotal();
    }

    public function calcularTotal(): void
    {
        $cantidad = (float) ($this->cantidad ?: 1);
        $precio = (float) ($this->precio_unitario ?: 0);

        $base = $cantidad * $precio;

        if ($this->precio_unitario === '' && $this->base_imponible !== '') {
            $base = (float) $this->base_imponible;
        }

        $configuracion = ConfiguracionSistema::first();
        $porcentajeIgv = (float) ($configuracion?->igv ?? 18);

        $igv = $base * ($porcentajeIgv / 100);
        $total = $base + $igv;

        $this->base_imponible = number_format($base, 2, '.', '');
        $this->igv = number_format($igv, 2, '.', '');
        $this->total = number_format($total, 2, '.', '');

        if ($this->forma_pago === 'Credito') {
            $this->monto_pendiente = $this->total;
        } else {
            $this->monto_pendiente = '0.00';
        }
    }

    public function actualizarNumeroComprobante(): void
    {
        $configuracion = ConfiguracionSistema::first();

        if (!$configuracion) {
            $this->numero_comprobante = '';
            return;
        }

        if ($this->tipo_comprobante === 'Factura') {
            $serie = $configuracion->serie_factura;
            $correlativo = $configuracion->correlativo_factura;
        } else {
            $serie = $configuracion->serie_boleta;
            $correlativo = $configuracion->correlativo_boleta;
        }

        $digitos = (int) $configuracion->digitos_correlativo;

        $this->numero_comprobante = $serie . '-' .
            str_pad((string) $correlativo, $digitos, '0', STR_PAD_LEFT);
    }

    public function abrirFormulario(): void
    {
        $this->reset([
            'cliente_id',
            'servicio_id',
            'numero_comprobante',
            'cantidad',
            'precio_unitario',
            'descripcion',
            'fecha_vencimiento',
            'base_imponible',
            'igv',
            'total',
            'monto_pendiente',
            'observacion',
        ]);

        $this->tipo_comprobante = 'Factura';
        $this->forma_pago = 'Contado';
        $this->estado = 'Emitida';
        $this->fecha_emision = date('Y-m-d');
        $this->cantidad = '1';

        $this->actualizarNumeroComprobante();

        $this->mostrarFormulario = true;
    }

    public function guardarVenta(): void
    {
        $this->validate([
            'cliente_id' => 'required|exists:clientes,id',
            'servicio_id' => 'required|exists:servicios,id',
            'tipo_comprobante' => 'required|in:Factura,Boleta',
            'fecha_emision' => 'required|date',
            'cantidad' => 'required|numeric|min:0.01',
            'forma_pago' => 'required|in:Contado,Credito',
            'fecha_vencimiento' => 'nullable|date|after_or_equal:fecha_emision',
            'estado' => 'required|in:Emitida,Anulada',
        ]);

        DB::transaction(function () {
            $configuracion = ConfiguracionSistema::query()
                ->lockForUpdate()
                ->first();

            if (!$configuracion) {
                throw new \RuntimeException(
                    'Primero debes completar la configuración del sistema.'
                );
            }

            $servicio = Servicio::findOrFail($this->servicio_id);

            $cantidad = (float) $this->cantidad;
            $precio = (float) $servicio->precio;
            $base = $cantidad * $precio;

            $porcentajeIgv = (float) $configuracion->igv;
            $igv = $base * ($porcentajeIgv / 100);
            $total = $base + $igv;

            if ($this->tipo_comprobante === 'Factura') {
                $serie = $configuracion->serie_factura;
                $correlativo = $configuracion->correlativo_factura;

                $configuracion->increment('correlativo_factura');
            } else {
                $serie = $configuracion->serie_boleta;
                $correlativo = $configuracion->correlativo_boleta;

                $configuracion->increment('correlativo_boleta');
            }

            $digitos = (int) $configuracion->digitos_correlativo;

            $numero = $serie . '-' .
                str_pad((string) $correlativo, $digitos, '0', STR_PAD_LEFT);

            $formaPago = $this->forma_pago;

            Venta::create([
                'cliente_id' => $this->cliente_id,
                'servicio_id' => $this->servicio_id,
                'cantidad' => $cantidad,
                'precio_unitario' => $precio,
                'descripcion' => $this->descripcion ?: $servicio->nombre,
                'tipo_comprobante' => $this->tipo_comprobante,
                'numero_comprobante' => $numero,
                'fecha_emision' => $this->fecha_emision,
                'forma_pago' => $formaPago,
                'fecha_vencimiento' => $formaPago === 'Credito'
                    ? $this->fecha_vencimiento
                    : null,
                'base_imponible' => $base,
                'igv' => $igv,
                'total' => $total,
                'monto_pendiente' => $formaPago === 'Credito'
                    ? $total
                    : 0,
                'estado' => $this->estado,
                'observacion' => $this->observacion ?: null,
            ]);
        });

        $this->reset([
            'cliente_id',
            'servicio_id',
            'numero_comprobante',
            'cantidad',
            'precio_unitario',
            'descripcion',
            'fecha_vencimiento',
            'base_imponible',
            'igv',
            'total',
            'monto_pendiente',
            'observacion',
        ]);

        $this->tipo_comprobante = 'Factura';
        $this->forma_pago = 'Contado';
        $this->estado = 'Emitida';
        $this->fecha_emision = date('Y-m-d');
        $this->cantidad = '1';

        $this->mostrarFormulario = false;
    }

    public function getVentasFiltradasProperty()
    {
        return Venta::with(['cliente', 'servicio'])
            ->when($this->buscar, function ($query) {
                $query->where(function ($q) {
                    $q->where('numero_comprobante', 'like', '%' . $this->buscar . '%')
                        ->orWhereHas('cliente', function ($clienteQuery) {
                            $clienteQuery->where(
                                'razon_social',
                                'like',
                                '%' . $this->buscar . '%'
                            );
                        });
                });
            })
            ->latest()
            ->get();
    }

    public function render()
    {
        return view('components.⚡ventas', [
            'clientes' => Cliente::orderBy('razon_social')->get(),
            'servicios' => Servicio::orderBy('nombre')->get(),
            'configuracion' => ConfiguracionSistema::first(),
        ]);
    }
};
?>
<div class="min-h-screen bg-zinc-50 text-zinc-900 transition-colors dark:bg-zinc-50 dark:bg-zinc-950 dark:text-zinc-100">

    <div class="mx-auto max-w-7xl px-6 py-8">

        {{-- Encabezado --}}
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-zinc-900 dark:text-white">
                    Ventas y Facturación
                </h1>

                <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">
                    Registro y seguimiento de comprobantes por servicios brindados a clientes.
                </p>
            </div>

            <button
                type="button"
                wire:click="abrirFormulario"
                class="inline-flex items-center justify-center gap-2 rounded-lg bg-teal-500 px-4 py-2.5 text-sm font-semibold text-zinc-950 transition hover:bg-teal-400"
            >
                <svg xmlns="http://www.w3.org/2000/svg"
                     viewBox="0 0 24 24"
                     fill="none"
                     stroke="currentColor"
                     stroke-width="2"
                     class="h-5 w-5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14M5 12h14"/>
                </svg>

                Nueva facturación
            </button>
        </div>

        {{-- Indicadores --}}
        <div class="mt-8 grid gap-4 md:grid-cols-4">

            <div class="rounded-xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                <p class="text-sm text-zinc-600 dark:text-zinc-400">Comprobantes</p>
                <p class="mt-2 text-2xl font-semibold text-zinc-900 dark:text-white">
                    {{ $this->ventasFiltradas->count() }}
                </p>
            </div>

            <div class="rounded-xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                <p class="text-sm text-zinc-600 dark:text-zinc-400">Contado</p>
                <p class="mt-2 text-2xl font-semibold text-emerald-700 dark:text-emerald-400">
                    {{ $this->ventasFiltradas->where('forma_pago', 'Contado')->count() }}
                </p>
            </div>

            <div class="rounded-xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                <p class="text-sm text-zinc-600 dark:text-zinc-400">Crédito</p>
                <p class="mt-2 text-2xl font-semibold text-amber-700 dark:text-amber-400">
                    {{ $this->ventasFiltradas->where('forma_pago', 'Credito')->count() }}
                </p>
            </div>

            <div class="rounded-xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                <p class="text-sm text-zinc-600 dark:text-zinc-400">Monto facturado</p>
                <p class="mt-2 text-2xl font-semibold text-teal-700 dark:text-teal-400">
                    {{ $configuracion?->simbolo_moneda ?? 'S/' }}
                    {{ number_format($this->ventasFiltradas->sum('total'), 2) }}
                </p>
            </div>

        </div>

        {{-- Búsqueda --}}
        <div class="mt-6">
            <input
                type="text"
                wire:model.live="buscar"
                placeholder="Buscar por comprobante o cliente..."
                class="w-full rounded-lg border border-zinc-300 bg-white px-4 py-3 text-sm text-zinc-900 placeholder-zinc-400 outline-none transition focus:border-teal-500 focus:ring-1 focus:ring-teal-500 dark:border-zinc-800 dark:bg-zinc-50 dark:bg-zinc-950 dark:text-white dark:placeholder-zinc-500"
            >
        </div>

        {{-- Tabla --}}
        <div class="mt-6 overflow-hidden rounded-xl border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-zinc-100 dark:divide-zinc-800">

                    <thead class="bg-zinc-50 dark:bg-zinc-50 dark:bg-zinc-950">
                        <tr>
                            <th class="px-5 py-4 text-left text-xs font-semibold uppercase tracking-wider text-zinc-600 dark:text-zinc-400">
                                Comprobante
                            </th>

                            <th class="px-5 py-4 text-left text-xs font-semibold uppercase tracking-wider text-zinc-600 dark:text-zinc-400">
                                Cliente
                            </th>

                            <th class="px-5 py-4 text-left text-xs font-semibold uppercase tracking-wider text-zinc-600 dark:text-zinc-400">
                                Servicio
                            </th>

                            <th class="px-5 py-4 text-left text-xs font-semibold uppercase tracking-wider text-zinc-600 dark:text-zinc-400">
                                Fecha
                            </th>

                            <th class="px-5 py-4 text-center text-xs font-semibold uppercase tracking-wider text-zinc-600 dark:text-zinc-400">
                                Pago
                            </th>

                            <th class="px-5 py-4 text-right text-xs font-semibold uppercase tracking-wider text-zinc-600 dark:text-zinc-400">
                                Total
                            </th>

                            <th class="px-5 py-4 text-center text-xs font-semibold uppercase tracking-wider text-zinc-600 dark:text-zinc-400">
                                Estado
                            </th>
<th class="px-5 py-4 text-right text-xs font-semibold uppercase tracking-wider text-zinc-600 dark:text-zinc-400">
    Acciones
</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">

                        @forelse ($this->ventasFiltradas as $venta)

                            <tr class="transition hover:bg-zinc-50 dark:hover:bg-zinc-800/50">

                                <td class="whitespace-nowrap px-5 py-4">
                                    <div class="font-medium text-zinc-900 dark:text-white">
                                        {{ $venta->numero_comprobante }}
                                    </div>

                                    <div class="text-xs text-zinc-500">
                                        {{ $venta->tipo_comprobante }}
                                    </div>
                                </td>

                                <td class="px-5 py-4 text-sm text-zinc-700 dark:text-zinc-300">
                                    {{ $venta->cliente->razon_social ?? 'Sin cliente' }}
                                </td>

                                <td class="px-5 py-4 text-sm text-zinc-700 dark:text-zinc-300">
                                    {{ $venta->servicio->nombre ?? 'Sin servicio' }}
                                </td>

                                <td class="whitespace-nowrap px-5 py-4 text-sm text-zinc-600 dark:text-zinc-400">
                                    {{ $venta->fecha_emision?->format('d/m/Y') }}
                                </td>

                                <td class="px-5 py-4 text-center">
                                    @if ($venta->forma_pago === 'Contado')
                                        <span class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-medium text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400">
                                            Contado
                                        </span>
                                    @else
                                        <span class="rounded-full bg-amber-50 px-3 py-1 text-xs font-medium text-amber-700 dark:bg-amber-500/10 dark:text-amber-400">
                                            Crédito
                                        </span>
                                    @endif
                                </td>

                                <td class="px-5 py-4 text-right font-medium text-zinc-900 dark:text-white">
                                    {{ $configuracion?->simbolo_moneda ?? 'S/' }}
                                    {{ number_format($venta->total, 2) }}
                                </td>

                                <td class="px-5 py-4 text-center">
                                    @if ($venta->estado === 'Emitida')
                                        <span class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-medium text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400">
                                            Emitida
                                        </span>
                                    @else
                                        <span class="rounded-full bg-red-50 px-3 py-1 text-xs font-medium text-red-700 dark:bg-red-500/10 dark:text-red-400">
                                            Anulada
                                        </span>
                                    @endif
                                </td>
<td class="px-5 py-4 text-right">
    <a
        href="{{ route('comprobante', $venta->id) }}"
        target="_blank"
        class="inline-flex items-center gap-2 rounded-lg border border-zinc-200 bg-white px-3 py-2 text-xs font-medium text-zinc-700 transition hover:border-teal-600 hover:bg-teal-50 hover:text-teal-700 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-300 dark:hover:bg-teal-600/10 dark:hover:text-teal-400"
    >
        <svg
            xmlns="http://www.w3.org/2000/svg"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="2"
            class="h-4 w-4"
        >
            <path stroke-linecap="round" stroke-linejoin="round" d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6-10-6-10-6Z"/>
            <circle cx="12" cy="12" r="2.5"/>
        </svg>

        Ver
    </a>
</td>
                            </tr>

                        @empty

                            <tr>
                                <td colspan="8" class="px-5 py-12 text-center">
                                    <p class="text-sm text-zinc-600 dark:text-zinc-400">
                                        No hay comprobantes registrados.
                                    </p>

                                    <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-600">
                                        Registra el primer comprobante utilizando el botón superior.
                                    </p>
                                </td>
                            </tr>

                        @endforelse

                    </tbody>

                </table>
            </div>

        </div>

    </div>

    {{-- Modal --}}
    @if ($mostrarFormulario)

        <div class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-black/60 px-4 py-8">

            <div class="w-full max-w-3xl rounded-xl border border-zinc-200 bg-white text-zinc-900 shadow-2xl dark:border-zinc-800 dark:bg-zinc-900 dark:text-white">

                {{-- Cabecera --}}
                <div class="flex items-center justify-between border-b border-zinc-200 px-6 py-4 dark:border-zinc-800">

                    <div>
                        <h2 class="text-lg font-semibold text-zinc-900 dark:text-white">
                            Nuevo comprobante
                        </h2>

                        <p class="text-sm text-zinc-600 dark:text-zinc-400">
                            Registra la facturación del servicio brindado al cliente.
                        </p>
                    </div>

                    <button
                        type="button"
                        wire:click="$set('mostrarFormulario', false)"
                        class="rounded-lg p-2 text-zinc-500 transition hover:bg-zinc-100 hover:text-zinc-900 dark:text-zinc-400 dark:hover:bg-zinc-800 dark:hover:text-white"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg"
                             viewBox="0 0 24 24"
                             fill="none"
                             stroke="currentColor"
                             stroke-width="2"
                             class="h-5 w-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/>
                        </svg>
                    </button>

                </div>

                <form wire:submit="guardarVenta" class="space-y-6 p-6">

                    {{-- Comprobante --}}
                    <div>
                        <h3 class="mb-4 text-sm font-semibold uppercase tracking-wide text-zinc-600 dark:text-zinc-400">
                            Datos del comprobante
                        </h3>

                        <div class="grid gap-5 md:grid-cols-3">

                            <div>
                                <label class="mb-1.5 block text-sm text-zinc-700 dark:text-zinc-300">
                                    Tipo
                                </label>

                                <select
                                    wire:model.live="tipo_comprobante"
                                    class="w-full rounded-lg border border-zinc-800 bg-zinc-50 dark:bg-zinc-950 px-3 py-2.5 text-sm text-white focus:border-teal-500 focus:ring-teal-500"
                                >
                                    <option value="Factura">Factura</option>
                                    <option value="Boleta">Boleta</option>
                                </select>
                            </div>

                            <div>
                                <label class="mb-1.5 block text-sm text-zinc-700 dark:text-zinc-300">
                                    Número automático
                                </label>

                                <input
                                    type="text"
                                    wire:model="numero_comprobante"
                                    readonly
                                    class="w-full cursor-not-allowed rounded-lg border border-zinc-300 bg-zinc-100 px-3 py-2.5 text-sm font-medium text-teal-700 dark:border-zinc-800 dark:bg-zinc-900 dark:text-teal-400"
                                >
                            </div>

                            <div>
                                <label class="mb-1.5 block text-sm text-zinc-700 dark:text-zinc-300">
                                    Fecha de emisión
                                </label>

                                <input
                                    type="date"
                                    wire:model="fecha_emision"
                                    class="w-full rounded-lg border border-zinc-800 bg-zinc-50 dark:bg-zinc-950 px-3 py-2.5 text-sm text-white focus:border-teal-500 focus:ring-teal-500"
                                >

                                @error('fecha_emision')
                                    <p class="mt-1 text-xs text-red-400">{{ $message }}</p>
                                @enderror
                            </div>

                        </div>
                    </div>

                    {{-- Cliente --}}
                    <div>
                        <h3 class="mb-4 text-sm font-semibold uppercase tracking-wide text-zinc-600 dark:text-zinc-400">
                            Cliente
                        </h3>

                        <select
                            wire:model="cliente_id"
                            class="w-full rounded-lg border border-zinc-800 bg-zinc-50 dark:bg-zinc-950 px-3 py-2.5 text-sm text-white focus:border-teal-500 focus:ring-teal-500"
                        >
                            <option value="">Seleccionar cliente</option>

                            @foreach ($clientes as $cliente)
                                <option value="{{ $cliente->id }}">
                                    {{ $cliente->razon_social }}
                                    @if ($cliente->ruc)
                                        — RUC {{ $cliente->ruc }}
                                    @endif
                                </option>
                            @endforeach
                        </select>

                        @error('cliente_id')
                            <p class="mt-1 text-xs text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Servicio --}}
                    <div>
                        <h3 class="mb-4 text-sm font-semibold uppercase tracking-wide text-zinc-600 dark:text-zinc-400">
                            Detalle del servicio
                        </h3>

                        <div class="grid gap-5 md:grid-cols-2">

                            <div>
                                <label class="mb-1.5 block text-sm text-zinc-700 dark:text-zinc-300">
                                    Servicio
                                </label>

                                <select
                                    wire:model.live="servicio_id"
                                    class="w-full rounded-lg border border-zinc-800 bg-zinc-50 dark:bg-zinc-950 px-3 py-2.5 text-sm text-white focus:border-teal-500 focus:ring-teal-500"
                                >
                                    <option value="">Seleccionar servicio</option>

                                    @foreach ($servicios as $servicio)
                                        <option value="{{ $servicio->id }}">
                                            {{ $servicio->nombre }}
                                            — {{ $configuracion?->simbolo_moneda ?? 'S/' }}
                                            {{ number_format($servicio->precio, 2) }}
                                        </option>
                                    @endforeach
                                </select>

                                @error('servicio_id')
                                    <p class="mt-1 text-xs text-red-400">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="mb-1.5 block text-sm text-zinc-700 dark:text-zinc-300">
                                    Cantidad
                                </label>

                                <input
                                    type="number"
                                    step="0.01"
                                    min="0.01"
                                    wire:model.live="cantidad"
                                    class="w-full rounded-lg border border-zinc-800 bg-zinc-50 dark:bg-zinc-950 px-3 py-2.5 text-sm text-white focus:border-teal-500 focus:ring-teal-500"
                                >

                                @error('cantidad')
                                    <p class="mt-1 text-xs text-red-400">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="mb-1.5 block text-sm text-zinc-700 dark:text-zinc-300">
                                    Precio unitario
                                </label>

                                <div class="relative">
                                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-sm text-zinc-500">
                                        {{ $configuracion?->simbolo_moneda ?? 'S/' }}
                                    </span>

                                    <input
                                        type="text"
                                        value="{{ $precio_unitario }}"
                                        readonly
                                        class="w-full cursor-not-allowed rounded-lg border border-zinc-800 bg-zinc-900 py-2.5 pl-10 pr-3 text-sm text-zinc-700 dark:text-zinc-300"
                                    >
                                </div>
                            </div>

                            <div>
                                <label class="mb-1.5 block text-sm text-zinc-700 dark:text-zinc-300">
                                    Descripción
                                </label>

                                <input
                                    type="text"
                                    wire:model="descripcion"
                                    class="w-full rounded-lg border border-zinc-800 bg-zinc-50 dark:bg-zinc-950 px-3 py-2.5 text-sm text-white focus:border-teal-500 focus:ring-teal-500"
                                >
                            </div>

                        </div>
                    </div>

                    {{-- Forma de pago --}}
                    <div>
                        <h3 class="mb-4 text-sm font-semibold uppercase tracking-wide text-zinc-600 dark:text-zinc-400">
                            Condición de pago
                        </h3>

                        <div class="grid gap-5 md:grid-cols-2">

                            <div>
                                <label class="mb-1.5 block text-sm text-zinc-700 dark:text-zinc-300">
                                    Forma de pago
                                </label>

                                <select
                                    wire:model.live="forma_pago"
                                    class="w-full rounded-lg border border-zinc-800 bg-zinc-50 dark:bg-zinc-950 px-3 py-2.5 text-sm text-white focus:border-teal-500 focus:ring-teal-500"
                                >
                                    <option value="Contado">Contado</option>
                                    <option value="Credito">Crédito</option>
                                </select>

                                @error('forma_pago')
                                    <p class="mt-1 text-xs text-red-400">{{ $message }}</p>
                                @enderror
                            </div>

                            @if ($forma_pago === 'Credito')
                                <div>
                                    <label class="mb-1.5 block text-sm text-zinc-700 dark:text-zinc-300">
                                        Fecha de vencimiento
                                    </label>

                                    <input
                                        type="date"
                                        wire:model="fecha_vencimiento"
                                        class="w-full rounded-lg border border-zinc-800 bg-zinc-50 dark:bg-zinc-950 px-3 py-2.5 text-sm text-white focus:border-teal-500 focus:ring-teal-500"
                                    >

                                    @error('fecha_vencimiento')
                                        <p class="mt-1 text-xs text-red-400">{{ $message }}</p>
                                    @enderror
                                </div>
                            @endif

                        </div>
                    </div>

                    {{-- Resumen --}}
                    <div class="rounded-xl border border-zinc-800 bg-zinc-50 dark:bg-zinc-950 p-5">

                        <div class="grid gap-5 md:grid-cols-4">

                            <div>
                                <p class="text-xs text-zinc-500">Base imponible</p>
                                <p class="mt-1 font-medium text-zinc-900 dark:text-white">
                                    {{ $configuracion?->simbolo_moneda ?? 'S/' }}
                                    {{ number_format((float) $base_imponible, 2) }}
                                </p>
                            </div>

                            <div>
                                <p class="text-xs text-zinc-500">
                                    IGV ({{ number_format((float) ($configuracion?->igv ?? 18), 2) }}%)
                                </p>

                                <p class="mt-1 font-medium text-teal-400">
                                    {{ $configuracion?->simbolo_moneda ?? 'S/' }}
                                    {{ number_format((float) $igv, 2) }}
                                </p>
                            </div>

                            <div>
                                <p class="text-xs text-zinc-500">Total</p>

                                <p class="mt-1 text-lg font-semibold text-zinc-900 dark:text-white">
                                    {{ $configuracion?->simbolo_moneda ?? 'S/' }}
                                    {{ number_format((float) $total, 2) }}
                                </p>
                            </div>

                            <div>
                                <p class="text-xs text-zinc-500">Monto pendiente</p>

                                <p class="mt-1 font-medium {{ $forma_pago === 'Credito' ? 'text-amber-400' : 'text-zinc-500' }}">
                                    {{ $configuracion?->simbolo_moneda ?? 'S/' }}
                                    {{ number_format((float) $monto_pendiente, 2) }}
                                </p>
                            </div>

                        </div>

                    </div>

                    {{-- Estado y observación --}}
                    <div class="grid gap-5 md:grid-cols-2">

                        <div>
                            <label class="mb-1.5 block text-sm text-zinc-700 dark:text-zinc-300">
                                Estado
                            </label>

                            <select
                                wire:model="estado"
                                class="w-full rounded-lg border border-zinc-800 bg-zinc-50 dark:bg-zinc-950 px-3 py-2.5 text-sm text-white"
                            >
                                <option value="Emitida">Emitida</option>
                                <option value="Anulada">Anulada</option>
                            </select>
                        </div>

                        <div>
                            <label class="mb-1.5 block text-sm text-zinc-700 dark:text-zinc-300">
                                Observación
                            </label>

                            <input
                                type="text"
                                wire:model="observacion"
                                placeholder="Observación opcional..."
                                class="w-full rounded-lg border border-zinc-800 bg-zinc-50 dark:bg-zinc-950 px-3 py-2.5 text-sm text-white placeholder-zinc-600"
                            >
                        </div>

                    </div>

                    {{-- Acciones --}}
                    <div class="flex justify-end gap-3 border-t border-zinc-200 pt-5 dark:border-zinc-800">

                        <button
                            type="button"
                            wire:click="$set('mostrarFormulario', false)"
                            class="rounded-lg border border-zinc-200 bg-white px-4 py-2.5 text-sm font-medium text-zinc-700 transition hover:bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-300 dark:hover:bg-zinc-800"
                        >
                            Cancelar
                        </button>

                        <button
                            type="submit"
                            class="rounded-lg bg-teal-500 px-5 py-2.5 text-sm font-semibold text-zinc-950 transition hover:bg-teal-400"
                        >
                            Guardar comprobante
                        </button>

                    </div>

                </form>

            </div>

        </div>

    @endif

</div>
