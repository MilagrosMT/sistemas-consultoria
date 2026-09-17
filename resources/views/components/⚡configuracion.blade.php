<?php

use App\Models\ConfiguracionSistema;
use Livewire\Component;

new class extends Component
{
    public ?ConfiguracionSistema $configuracion = null;

    public string $razon_social = '';
    public string $nombre_comercial = '';
    public string $ruc = '';
    public string $direccion = '';
    public string $telefono = '';
    public string $correo = '';

    public string $serie_factura = 'F001';
    public int $correlativo_factura = 1;

    public string $serie_boleta = 'B001';
    public int $correlativo_boleta = 1;

    public int $digitos_correlativo = 8;
    public string $igv = '18.00';
    public string $moneda = 'PEN';
    public string $simbolo_moneda = 'S/';
    public string $formato_fecha = 'd/m/Y';
    public string $zona_horaria = 'America/Lima';

    public function mount(): void
    {
        $this->configuracion = ConfiguracionSistema::first();

        if ($this->configuracion) {
            $this->razon_social = $this->configuracion->razon_social ?? '';
            $this->nombre_comercial = $this->configuracion->nombre_comercial ?? '';
            $this->ruc = $this->configuracion->ruc ?? '';
            $this->direccion = $this->configuracion->direccion ?? '';
            $this->telefono = $this->configuracion->telefono ?? '';
            $this->correo = $this->configuracion->correo ?? '';

            $this->serie_factura = $this->configuracion->serie_factura;
            $this->correlativo_factura = $this->configuracion->correlativo_factura;

            $this->serie_boleta = $this->configuracion->serie_boleta;
            $this->correlativo_boleta = $this->configuracion->correlativo_boleta;

            $this->digitos_correlativo = $this->configuracion->digitos_correlativo;
            $this->igv = (string) $this->configuracion->igv;
            $this->moneda = $this->configuracion->moneda;
            $this->simbolo_moneda = $this->configuracion->simbolo_moneda;
            $this->formato_fecha = $this->configuracion->formato_fecha;
            $this->zona_horaria = $this->configuracion->zona_horaria;
        }
    }

    public function guardar(): void
    {
        $this->validate([
            'razon_social' => 'required|string|max:255',
            'nombre_comercial' => 'nullable|string|max:255',
            'ruc' => 'required|digits:11',
            'direccion' => 'required|string|max:255',
            'telefono' => 'nullable|string|max:20',
            'correo' => 'nullable|email|max:255',

            'serie_factura' => 'required|string|max:4',
            'correlativo_factura' => 'required|integer|min:1',

            'serie_boleta' => 'required|string|max:4',
            'correlativo_boleta' => 'required|integer|min:1',

            'digitos_correlativo' => 'required|integer|min:1|max:12',
            'igv' => 'required|numeric|min:0|max:100',
            'moneda' => 'required|string|max:3',
            'simbolo_moneda' => 'required|string|max:5',
            'formato_fecha' => 'required|string|max:20',
            'zona_horaria' => 'required|string|max:50',
        ]);

        ConfiguracionSistema::updateOrCreate(
            ['id' => $this->configuracion?->id ?? 0],
            [
                'razon_social' => $this->razon_social,
                'nombre_comercial' => $this->nombre_comercial,
                'ruc' => $this->ruc,
                'direccion' => $this->direccion,
                'telefono' => $this->telefono,
                'correo' => $this->correo,

                'serie_factura' => strtoupper($this->serie_factura),
                'correlativo_factura' => $this->correlativo_factura,

                'serie_boleta' => strtoupper($this->serie_boleta),
                'correlativo_boleta' => $this->correlativo_boleta,

                'digitos_correlativo' => $this->digitos_correlativo,
                'igv' => $this->igv,
                'moneda' => strtoupper($this->moneda),
                'simbolo_moneda' => $this->simbolo_moneda,
                'formato_fecha' => $this->formato_fecha,
                'zona_horaria' => $this->zona_horaria,
            ]
        );

        $this->configuracion = ConfiguracionSistema::first();

        session()->flash('mensaje', 'Configuración guardada correctamente.');
    }
};
?>

<div class="min-h-screen bg-zinc-50 text-zinc-900 transition-colors dark:bg-zinc-950 dark:text-zinc-100">

    <div class="mx-auto max-w-6xl px-6 py-8">

        {{-- Encabezado --}}
        <div class="mb-8">
            <h1 class="text-2xl font-semibold text-zinc-900 dark:text-white">
                Configuración general
            </h1>

            <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">
                Administra los datos de la empresa y los parámetros utilizados por el sistema.
            </p>
        </div>

        {{-- Mensaje --}}
        @if (session('mensaje'))
            <div class="mb-6 rounded-lg border border-emerald-500/20 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-400">
                {{ session('mensaje') }}
            </div>
        @endif

        <form wire:submit="guardar" class="space-y-6">

            {{-- Datos de la empresa --}}
            <section class="rounded-xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900">

                <div class="border-b border-zinc-200 dark:border-zinc-800 px-6 py-5">
                    <div class="flex items-center gap-3">
                        <div class="rounded-lg bg-teal-500/10 p-2 text-teal-400">
                            <svg xmlns="http://www.w3.org/2000/svg"
                                 viewBox="0 0 24 24"
                                 fill="none"
                                 stroke="currentColor"
                                 stroke-width="2"
                                 class="h-5 w-5">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M3 21h18M5 21V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2v16M9 7h1m4 0h1M9 11h1m4 0h1M9 15h1m4 0h1"/>
                            </svg>
                        </div>

                        <div>
                            <h2 class="font-semibold text-zinc-900 dark:text-white">
                                Datos de la empresa
                            </h2>

                            <p class="text-sm text-zinc-600 dark:text-zinc-500">
                                Información que aparecerá en los comprobantes.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="grid gap-5 p-6 md:grid-cols-2">

                    <div>
                        <label class="mb-1.5 block text-sm text-zinc-700 dark:text-zinc-300">
                            Razón social
                        </label>

                        <input
                            type="text"
                            wire:model="razon_social"
                            class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2.5 text-sm text-zinc-900 focus:border-teal-500 focus:ring-teal-500 dark:border-zinc-800 dark:bg-zinc-950 dark:text-white"
                        >

                        @error('razon_social')
                            <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="mb-1.5 block text-sm text-zinc-700 dark:text-zinc-300">
                            Nombre comercial
                        </label>

                        <input
                            type="text"
                            wire:model="nombre_comercial"
                            class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2.5 text-sm text-zinc-900 focus:border-teal-500 focus:ring-teal-500 dark:border-zinc-800 dark:bg-zinc-950 dark:text-white"
                        >
                    </div>

                    <div>
                        <label class="mb-1.5 block text-sm text-zinc-700 dark:text-zinc-300">
                            RUC
                        </label>

                        <input
                            type="text"
                            wire:model="ruc"
                            maxlength="11"
                            class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2.5 text-sm text-zinc-900 focus:border-teal-500 focus:ring-teal-500 dark:border-zinc-800 dark:bg-zinc-950 dark:text-white"
                        >

                        @error('ruc')
                            <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="mb-1.5 block text-sm text-zinc-700 dark:text-zinc-300">
                            Teléfono
                        </label>

                        <input
                            type="text"
                            wire:model="telefono"
                            class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2.5 text-sm text-zinc-900 focus:border-teal-500 focus:ring-teal-500 dark:border-zinc-800 dark:bg-zinc-950 dark:text-white"
                        >
                    </div>

                    <div class="md:col-span-2">
                        <label class="mb-1.5 block text-sm text-zinc-700 dark:text-zinc-300">
                            Dirección fiscal
                        </label>

                        <input
                            type="text"
                            wire:model="direccion"
                            class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2.5 text-sm text-zinc-900 focus:border-teal-500 focus:ring-teal-500 dark:border-zinc-800 dark:bg-zinc-950 dark:text-white"
                        >

                        @error('direccion')
                            <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="md:col-span-2">
                        <label class="mb-1.5 block text-sm text-zinc-700 dark:text-zinc-300">
                            Correo electrónico
                        </label>

                        <input
                            type="email"
                            wire:model="correo"
                            class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2.5 text-sm text-zinc-900 focus:border-teal-500 focus:ring-teal-500 dark:border-zinc-800 dark:bg-zinc-950 dark:text-white"
                        >
                    </div>

                </div>

            </section>

            {{-- Comprobantes --}}
            <section class="rounded-xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900">

                <div class="border-b border-zinc-200 dark:border-zinc-800 px-6 py-5">
                    <h2 class="font-semibold text-zinc-900 dark:text-white">
                        Comprobantes y numeración
                    </h2>

                    <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-500">
                        Define las series y el próximo número que utilizará facturación.
                    </p>
                </div>

                <div class="grid gap-6 p-6 md:grid-cols-2">

                    {{-- Factura --}}
                    <div class="rounded-lg border border-zinc-800 bg-zinc-50 dark:bg-zinc-950 p-5">

                        <h3 class="font-medium text-white">
                            Facturas
                        </h3>

                        <div class="mt-4 grid grid-cols-2 gap-4">

                            <div>
                                <label class="mb-1.5 block text-sm text-zinc-600 dark:text-zinc-400">
                                    Serie
                                </label>

                                <input
                                    type="text"
                                    wire:model="serie_factura"
                                    maxlength="4"
                                    class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2.5 text-sm text-zinc-900 placeholder:text-zinc-400 dark:border-zinc-800 dark:bg-zinc-900 dark:text-white dark:placeholder:text-zinc-500"
                                >
                            </div>

                            <div>
                                <label class="mb-1.5 block text-sm text-zinc-600 dark:text-zinc-400">
                                    Próximo número
                                </label>

                                <input
                                    type="number"
                                    min="1"
                                    wire:model="correlativo_factura"
                                    class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2.5 text-sm text-zinc-900 placeholder:text-zinc-400 dark:border-zinc-800 dark:bg-zinc-900 dark:text-white dark:placeholder:text-zinc-500"
                                >
                            </div>

                        </div>

                        <p class="mt-3 text-xs text-zinc-600">
                            Ejemplo:
                            {{ $serie_factura }}-{{ str_pad($correlativo_factura, $digitos_correlativo, '0', STR_PAD_LEFT) }}
                        </p>

                    </div>

                    {{-- Boleta --}}
                    <div class="rounded-lg border border-zinc-800 bg-zinc-50 dark:bg-zinc-950 p-5">

                        <h3 class="font-medium text-white">
                            Boletas
                        </h3>

                        <div class="mt-4 grid grid-cols-2 gap-4">

                            <div>
                                <label class="mb-1.5 block text-sm text-zinc-600 dark:text-zinc-400">
                                    Serie
                                </label>

                                <input
                                    type="text"
                                    wire:model="serie_boleta"
                                    maxlength="4"
                                    class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2.5 text-sm text-zinc-900 placeholder:text-zinc-400 dark:border-zinc-800 dark:bg-zinc-900 dark:text-white dark:placeholder:text-zinc-500"
                                >
                            </div>

                            <div>
                                <label class="mb-1.5 block text-sm text-zinc-600 dark:text-zinc-400">
                                    Próximo número
                                </label>

                                <input
                                    type="number"
                                    min="1"
                                    wire:model="correlativo_boleta"
                                    class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2.5 text-sm text-zinc-900 placeholder:text-zinc-400 dark:border-zinc-800 dark:bg-zinc-900 dark:text-white dark:placeholder:text-zinc-500"
                                >
                            </div>

                        </div>

                        <p class="mt-3 text-xs text-zinc-600">
                            Ejemplo:
                            {{ $serie_boleta }}-{{ str_pad($correlativo_boleta, $digitos_correlativo, '0', STR_PAD_LEFT) }}
                        </p>

                    </div>

                    <div>
                        <label class="mb-1.5 block text-sm text-zinc-700 dark:text-zinc-300">
                            Dígitos del correlativo
                        </label>

                        <select
                            wire:model="digitos_correlativo"
                            class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2.5 text-sm text-zinc-900 dark:border-zinc-800 dark:bg-zinc-950 dark:text-white"
                        >
                            @for ($i = 4; $i <= 12; $i++)
                                <option value="{{ $i }}">
                                    {{ $i }} dígitos
                                </option>
                            @endfor
                        </select>
                    </div>

                    <div>
                        <label class="mb-1.5 block text-sm text-zinc-700 dark:text-zinc-300">
                            IGV (%)
                        </label>

                        <input
                            type="number"
                            step="0.01"
                            min="0"
                            max="100"
                            wire:model="igv"
                            class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2.5 text-sm text-zinc-900 dark:border-zinc-800 dark:bg-zinc-950 dark:text-white"
                        >
                    </div>

                </div>

            </section>

            {{-- Parámetros generales --}}
            <section class="rounded-xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900">

                <div class="border-b border-zinc-200 dark:border-zinc-800 px-6 py-5">
                    <h2 class="font-semibold text-zinc-900 dark:text-white">
                        Parámetros generales
                    </h2>

                    <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-500">
                        Configuración utilizada por los diferentes módulos del sistema.
                    </p>
                </div>

                <div class="grid gap-5 p-6 md:grid-cols-3">

                    <div>
                        <label class="mb-1.5 block text-sm text-zinc-700 dark:text-zinc-300">
                            Moneda
                        </label>

                        <select
                            wire:model="moneda"
                            class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2.5 text-sm text-zinc-900 dark:border-zinc-800 dark:bg-zinc-950 dark:text-white"
                        >
                            <option value="PEN">PEN - Sol peruano</option>
                            <option value="USD">USD - Dólar estadounidense</option>
                        </select>
                    </div>

                    <div>
                        <label class="mb-1.5 block text-sm text-zinc-700 dark:text-zinc-300">
                            Símbolo monetario
                        </label>

                        <input
                            type="text"
                            wire:model="simbolo_moneda"
                            class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2.5 text-sm text-zinc-900 dark:border-zinc-800 dark:bg-zinc-950 dark:text-white"
                        >
                    </div>

                    <div>
                        <label class="mb-1.5 block text-sm text-zinc-700 dark:text-zinc-300">
                            Formato de fecha
                        </label>

                        <select
                            wire:model="formato_fecha"
                            class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2.5 text-sm text-zinc-900 dark:border-zinc-800 dark:bg-zinc-950 dark:text-white"
                        >
                            <option value="d/m/Y">DD/MM/AAAA</option>
                            <option value="Y-m-d">AAAA-MM-DD</option>
                        </select>
                    </div>

                    <div class="md:col-span-3">
                        <label class="mb-1.5 block text-sm text-zinc-700 dark:text-zinc-300">
                            Zona horaria
                        </label>

                        <input
                            type="text"
                            wire:model="zona_horaria"
                            class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2.5 text-sm text-zinc-900 dark:border-zinc-800 dark:bg-zinc-950 dark:text-white"
                        >
                    </div>

                </div>

            </section>

            {{-- Guardar --}}
            <div class="flex justify-end">

                <button
                    type="submit"
                    class="inline-flex items-center gap-2 rounded-lg bg-teal-500 px-5 py-2.5 text-sm font-semibold text-zinc-950 transition hover:bg-teal-400"
                >
                    <svg xmlns="http://www.w3.org/2000/svg"
                         viewBox="0 0 24 24"
                         fill="none"
                         stroke="currentColor"
                         stroke-width="2"
                         class="h-5 w-5">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M5 12.5 9.5 17 19 7"/>
                    </svg>

                    Guardar configuración
                </button>

            </div>

        </form>

    </div>

</div>
