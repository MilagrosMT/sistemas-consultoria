<?php

use App\Models\ConfiguracionSistema;
use App\Models\Venta;
use Livewire\Component;

new class extends Component
{
    public Venta $venta;
    public ?ConfiguracionSistema $configuracion = null;

    public function mount(Venta $venta): void
    {
        $this->venta = $venta->load([
            'cliente',
            'servicio',
        ]);

        $this->configuracion = ConfiguracionSistema::first();
    }
};
?>

<div class="min-h-screen bg-zinc-950 p-6 text-zinc-100">
    <div class="mx-auto max-w-4xl">

        {{-- Acciones --}}
        <div class="mb-6 flex items-center justify-between print:hidden">
            <a
                href="{{ route('ventas') }}"
                class="inline-flex items-center gap-2 rounded-lg border border-zinc-800 px-4 py-2 text-sm text-zinc-300 transition hover:bg-zinc-900"
            >
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="m15 18-6-6 6-6"/>
                </svg>
                Volver a Ventas
            </a>

            <button
                type="button"
                onclick="window.print()"
                class="inline-flex items-center gap-2 rounded-lg bg-teal-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-teal-500"
            >
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M6 9V2h12v7"/>
                    <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/>
                    <path d="M6 14h12v8H6z"/>
                </svg>
                Imprimir
            </button>
        </div>

        {{-- Comprobante --}}
        <div class="rounded-xl border border-zinc-800 bg-white p-8 text-zinc-900 shadow-xl">

            {{-- Encabezado --}}
            <div class="flex items-start justify-between gap-8 border-b border-zinc-200 pb-6">

                <div>
                    <h1 class="text-xl font-bold">
                        {{ $configuracion?->razon_social ?? 'Consultoría Contable y Tributaria' }}
                    </h1>

                    @if($configuracion?->nombre_comercial)
                        <p class="mt-1 text-sm text-zinc-600">
                            {{ $configuracion->nombre_comercial }}
                        </p>
                    @endif

                    @if($configuracion?->direccion)
                        <p class="mt-2 text-sm text-zinc-600">
                            {{ $configuracion->direccion }}
                        </p>
                    @endif

                    @if($configuracion?->telefono)
                        <p class="text-sm text-zinc-600">
                            Teléfono: {{ $configuracion->telefono }}
                        </p>
                    @endif

                    @if($configuracion?->correo)
                        <p class="text-sm text-zinc-600">
                            {{ $configuracion->correo }}
                        </p>
                    @endif
                </div>

                <div class="min-w-[220px] rounded-lg border border-zinc-300 p-4 text-center">
                    <p class="text-sm font-semibold uppercase tracking-wide text-zinc-600">
                        {{ $venta->tipo_comprobante }}
                    </p>

                    <p class="mt-2 text-xl font-bold">
                        {{ $venta->numero_comprobante }}
                    </p>

                    @if($configuracion?->ruc)
                        <p class="mt-2 text-sm text-zinc-600">
                            RUC: {{ $configuracion->ruc }}
                        </p>
                    @endif
                </div>
            </div>

            {{-- Datos generales --}}
            <div class="grid gap-6 border-b border-zinc-200 py-6 md:grid-cols-2">

                <div>
                    <p class="text-xs font-semibold uppercase text-zinc-500">
                        Cliente
                    </p>

                    <p class="mt-1 font-semibold">
                        {{ $venta->cliente?->nombre ?? 'Cliente no disponible' }}
                    </p>

                    @if($venta->cliente?->ruc)
                        <p class="text-sm text-zinc-600">
                            RUC: {{ $venta->cliente->ruc }}
                        </p>
                    @endif
                </div>

                <div class="md:text-right">
                    <p class="text-xs font-semibold uppercase text-zinc-500">
                        Fecha de emisión
                    </p>

                    <p class="mt-1 font-semibold">
                        {{ $venta->fecha_emision?->format('d/m/Y') }}
                    </p>

                    <p class="mt-2 text-sm text-zinc-600">
                        Forma de pago:
                        <span class="font-medium text-zinc-900">
                            {{ $venta->forma_pago === 'Credito' ? 'Crédito' : 'Contado' }}
                        </span>
                    </p>

                    @if($venta->forma_pago === 'Credito' && $venta->fecha_vencimiento)
                        <p class="text-sm text-zinc-600">
                            Vencimiento:
                            <span class="font-medium text-zinc-900">
                                {{ $venta->fecha_vencimiento->format('d/m/Y') }}
                            </span>
                        </p>
                    @endif
                </div>
            </div>

            {{-- Detalle --}}
            <div class="py-6">

                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-zinc-300 text-left">
                            <th class="pb-3">Descripción</th>
                            <th class="pb-3 text-center">Cantidad</th>
                            <th class="pb-3 text-right">P. Unitario</th>
                            <th class="pb-3 text-right">Importe</th>
                        </tr>
                    </thead>

                    <tbody>
                        <tr class="border-b border-zinc-200">
                            <td class="py-4">
                                <p class="font-medium">
                                    {{ $venta->servicio?->nombre }}
                                </p>

                                @if($venta->descripcion)
                                    <p class="mt-1 text-xs text-zinc-500">
                                        {{ $venta->descripcion }}
                                    </p>
                                @endif
                            </td>

                            <td class="py-4 text-center">
                                {{ number_format((float) $venta->cantidad, 2) }}
                            </td>

                            <td class="py-4 text-right">
                                S/ {{ number_format((float) $venta->precio_unitario, 2) }}
                            </td>

                            <td class="py-4 text-right font-medium">
                                S/ {{ number_format((float) $venta->base_imponible, 2) }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            {{-- Totales --}}
            <div class="flex justify-end border-t border-zinc-200 pt-6">
                <div class="w-full max-w-sm space-y-3 text-sm">

                    <div class="flex justify-between">
                        <span class="text-zinc-600">Base imponible</span>
                        <span>S/ {{ number_format((float) $venta->base_imponible, 2) }}</span>
                    </div>

                    <div class="flex justify-between">
                        <span class="text-zinc-600">
                            IGV ({{ number_format((float) ($configuracion?->igv ?? 18), 2) }}%)
                        </span>
                        <span>S/ {{ number_format((float) $venta->igv, 2) }}</span>
                    </div>

                    <div class="flex justify-between border-t border-zinc-300 pt-3 text-lg font-bold">
                        <span>Total</span>
                        <span>S/ {{ number_format((float) $venta->total, 2) }}</span>
                    </div>

                    @if($venta->forma_pago === 'Credito')
                        <div class="flex justify-between text-sm">
                            <span class="text-zinc-600">Monto pendiente</span>
                            <span class="font-semibold">
                                S/ {{ number_format((float) $venta->monto_pendiente, 2) }}
                            </span>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Estado y observación --}}
            <div class="mt-8 border-t border-zinc-200 pt-5">

                <div class="flex items-center justify-between">
                    <span class="text-sm text-zinc-600">
                        Estado del comprobante
                    </span>

                    <span class="rounded-full border border-zinc-300 px-3 py-1 text-xs font-semibold">
                        {{ $venta->estado }}
                    </span>
                </div>

                @if($venta->observacion)
                    <div class="mt-4">
                        <p class="text-xs font-semibold uppercase text-zinc-500">
                            Observación
                        </p>

                        <p class="mt-1 text-sm text-zinc-700">
                            {{ $venta->observacion }}
                        </p>
                    </div>
                @endif
            </div>

            {{-- Nota --}}
            <div class="mt-8 border-t border-zinc-200 pt-4 text-center text-xs text-zinc-500">
                Representación referencial generada por el Sistema Integrado Administrativo.
            </div>

        </div>
    </div>

    <style>
        @media print {
            body {
                background: white !important;
            }

            .min-h-screen {
                min-height: auto !important;
                padding: 0 !important;
            }

            @page {
                margin: 12mm;
            }
        }
    </style>
</div>