<?php

use App\Models\Cliente;
use App\Models\MovimientoCaja;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component
{
    use WithPagination;

    public string $buscar = '';
    public bool $mostrarFormulario = false;

    public string $cliente_id = '';
    public string $tipo_movimiento = 'Ingreso';
    public string $concepto = '';
    public string $categoria = '';
    public string $fecha = '';
    public string $forma_pago = 'Efectivo';
    public string $monto = '';
    public string $numero_comprobante = '';
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
            'concepto',
            'categoria',
            'monto',
            'numero_comprobante',
            'observacion',
        ]);

        $this->tipo_movimiento = 'Ingreso';
        $this->fecha = now()->format('Y-m-d');
        $this->forma_pago = 'Efectivo';

        $this->mostrarFormulario = true;
    }

    public function cerrarFormulario(): void
    {
        $this->mostrarFormulario = false;
        $this->resetValidation();
    }

    public function guardarMovimiento(): void
    {
        $datos = $this->validate([
            'cliente_id' => ['required', 'exists:clientes,id'],
            'tipo_movimiento' => ['required', 'in:Ingreso,Egreso'],
            'concepto' => ['required', 'string', 'max:255'],
            'categoria' => ['nullable', 'string', 'max:255'],
            'fecha' => ['required', 'date'],
            'forma_pago' => [
                'required',
                'in:Efectivo,Transferencia,Tarjeta,Yape/Plin,Otro',
            ],
            'monto' => ['required', 'numeric', 'min:0.01'],
            'numero_comprobante' => ['nullable', 'string', 'max:50'],
            'observacion' => ['nullable', 'string'],
        ]);

        MovimientoCaja::create($datos);

        $this->cerrarFormulario();

        session()->flash(
            'success',
            'Movimiento de caja registrado correctamente.'
        );
    }

    public function getMovimientosFiltradosProperty()
    {
        return MovimientoCaja::with('cliente')
            ->when($this->buscar !== '', function ($query) {
                $buscar = '%' . $this->buscar . '%';

                $query->where(function ($q) use ($buscar) {
                    $q->where('concepto', 'like', $buscar)
                        ->orWhere('categoria', 'like', $buscar)
                        ->orWhere('numero_comprobante', 'like', $buscar)
                        ->orWhereHas('cliente', function ($cliente) use ($buscar) {
                            $cliente
                                ->where('razon_social', 'like', $buscar)
                                ->orWhere('ruc', 'like', $buscar);
                        });
                });
            })
            ->latest('fecha')
            ->paginate(10);
    }

    public function render()
    {
        $ingresos = MovimientoCaja::where('tipo_movimiento', 'Ingreso')
            ->sum('monto');

        $egresos = MovimientoCaja::where('tipo_movimiento', 'Egreso')
            ->sum('monto');

        return view('components.⚡caja-clientes', [
            'clientes' => Cliente::orderBy('razon_social')->get(),
            'movimientos' => $this->movimientosFiltrados,
            'totalMovimientos' => MovimientoCaja::count(),
            'ingresos' => $ingresos,
            'egresos' => $egresos,
            'saldo' => $ingresos - $egresos,
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
                    Caja de clientes
                </h1>

                <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">
                    Registro de ingresos y egresos de cada cliente para apoyar el control contable.
                </p>
            </div>

            <button
                type="button"
                wire:click="abrirFormulario"
                class="inline-flex items-center justify-center gap-2 rounded-lg bg-teal-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-teal-500"
            >
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14M5 12h14"/>
                </svg>

                Registrar movimiento
            </button>

        </div>

        {{-- Indicadores --}}
        <div class="mb-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">

            <div class="rounded-xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                <p class="text-sm text-zinc-600 dark:text-zinc-400">
                    Movimientos
                </p>

                <p class="mt-2 text-2xl font-semibold text-zinc-900 dark:text-white">
                    {{ $totalMovimientos }}
                </p>
            </div>

            <div class="rounded-xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                <p class="text-sm text-zinc-600 dark:text-zinc-400">
                    Ingresos
                </p>

                <p class="mt-2 text-2xl font-semibold text-teal-700 dark:text-teal-400">
                    S/ {{ number_format($ingresos, 2) }}
                </p>
            </div>

            <div class="rounded-xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                <p class="text-sm text-zinc-600 dark:text-zinc-400">
                    Egresos
                </p>

                <p class="mt-2 text-2xl font-semibold text-amber-700 dark:text-amber-400">
                    S/ {{ number_format($egresos, 2) }}
                </p>
            </div>

            <div class="rounded-xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                <p class="text-sm text-zinc-600 dark:text-zinc-400">
                    Saldo
                </p>

                <p class="mt-2 text-2xl font-semibold {{ $saldo >= 0 ? 'text-zinc-900 dark:text-white' : 'text-red-600 dark:text-red-400' }}">
                    S/ {{ number_format($saldo, 2) }}
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
                        d="m21 21-4.35-4.35m1.35-5.4a6.75 6.75 0 1 1 13.5 0 6.75 6.75 0 0 1 13.5 0Z"
                    />
                </svg>

                <input
                    type="text"
                    wire:model.live="buscar"
                    placeholder="Buscar por concepto, categoría, comprobante o cliente..."
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
                                Fecha
                            </th>

                            <th class="px-5 py-4 text-xs font-semibold uppercase tracking-wider text-zinc-600 dark:text-zinc-400">
                                Movimiento
                            </th>

                            <th class="px-5 py-4 text-xs font-semibold uppercase tracking-wider text-zinc-600 dark:text-zinc-400">
                                Forma de pago
                            </th>

                            <th class="px-5 py-4 text-xs font-semibold uppercase tracking-wider text-zinc-600 dark:text-zinc-400">
                                Monto
                            </th>

                        </tr>

                    </thead>

                    <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">

                        @forelse ($movimientos as $movimiento)

                            <tr class="transition hover:bg-zinc-50 dark:hover:bg-zinc-800/50">

                                <td class="px-5 py-4">

                                    <div class="font-medium text-zinc-900 dark:text-white">
                                        {{ $movimiento->cliente->razon_social }}
                                    </div>

                                    <div class="mt-1 text-xs text-zinc-500">
                                        RUC {{ $movimiento->cliente->ruc }}
                                    </div>

                                </td>

                                <td class="px-5 py-4 text-sm text-zinc-700 dark:text-zinc-300">
                                    {{ $movimiento->fecha?->format('d/m/Y') }}
                                </td>

                                <td class="px-5 py-4">

                                    <div class="font-medium text-zinc-800 dark:text-zinc-200">
                                        {{ $movimiento->concepto }}
                                    </div>

                                    @if ($movimiento->categoria)
                                        <div class="mt-1 text-xs text-zinc-500">
                                            {{ $movimiento->categoria }}
                                        </div>
                                    @endif

                                </td>

                                <td class="px-5 py-4">

                                    <span class="text-sm text-zinc-300">
                                        {{ $movimiento->forma_pago }}
                                    </span>

                                    @if ($movimiento->numero_comprobante)
                                        <div class="mt-1 text-xs text-zinc-500">
                                            {{ $movimiento->numero_comprobante }}
                                        </div>
                                    @endif

                                </td>

                                <td class="px-5 py-4">

                                    @if ($movimiento->tipo_movimiento === 'Ingreso')

                                        <span class="font-semibold text-teal-700 dark:text-teal-400">
                                            + S/ {{ number_format($movimiento->monto, 2) }}
                                        </span>

                                    @else

                                        <span class="font-semibold text-amber-700 dark:text-amber-400">
                                            - S/ {{ number_format($movimiento->monto, 2) }}
                                        </span>

                                    @endif

                                </td>

                            </tr>

                        @empty

                            <tr>

                                <td
                                    colspan="5"
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
                                                d="M3 10h18M5 6h14a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2Z"
                                            />
                                        </svg>

                                        <p class="text-sm font-medium text-zinc-700 dark:text-zinc-300">
                                            No hay movimientos registrados
                                        </p>

                                        <p class="mt-1 text-xs text-zinc-500">
                                            Registra el primer ingreso o egreso del cliente.
                                        </p>

                                    </div>

                                </td>

                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>

            @if ($movimientos->hasPages())

                <div class="border-t border-zinc-200 px-5 py-4 dark:border-zinc-800">
                    {{ $movimientos->links() }}
                </div>

            @endif

        </div>

    </div>

    {{-- Modal --}}
    @if ($mostrarFormulario)

        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 px-4 py-6">

            <div class="max-h-[90vh] w-full max-w-2xl overflow-y-auto rounded-2xl border border-zinc-200 bg-white text-zinc-900 shadow-2xl dark:border-zinc-800 dark:bg-zinc-950 dark:text-white">

                <div class="sticky top-0 z-10 flex items-center justify-between border-b border-zinc-200 bg-white px-6 py-4 dark:border-zinc-800 dark:bg-zinc-950">

                    <div>
                        <h2 class="text-lg font-semibold text-zinc-900 dark:text-white">
                            Registrar movimiento de caja
                        </h2>

                        <p class="mt-1 text-xs text-zinc-500">
                            Registra un ingreso o egreso del cliente.
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

                <form wire:submit="guardarMovimiento" class="p-6">

                    <div class="grid gap-5 sm:grid-cols-2">

                        {{-- Cliente --}}
                        <div class="sm:col-span-2">

                            <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-300">
                                Cliente
                            </label>

                            <select
                                wire:model="cliente_id"
                                class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2.5 text-sm text-zinc-900 outline-none focus:border-teal-600 dark:border-zinc-800 dark:bg-zinc-900 dark:text-white"
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

                        {{-- Tipo --}}
                        <div>

                            <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-300">
                                Tipo de movimiento
                            </label>

                            <select
                                wire:model="tipo_movimiento"
                                class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2.5 text-sm text-zinc-900 outline-none focus:border-teal-600 dark:border-zinc-800 dark:bg-zinc-900 dark:text-white"
                            >
                                <option value="Ingreso">Ingreso</option>
                                <option value="Egreso">Egreso</option>
                            </select>

                        </div>

                        {{-- Fecha --}}
                        <div>

                            <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-300">
                                Fecha
                            </label>

                            <input
                                type="date"
                                wire:model="fecha"
                                class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2.5 text-sm text-zinc-900 outline-none focus:border-teal-600 dark:border-zinc-800 dark:bg-zinc-900 dark:text-white"
                            >

                            @error('fecha')
                                <p class="mt-1 text-xs text-red-400">{{ $message }}</p>
                            @enderror

                        </div>

                        {{-- Concepto --}}
                        <div class="sm:col-span-2">

                            <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-300">
                                Concepto
                            </label>

                            <input
                                type="text"
                                wire:model="concepto"
                                placeholder="Ej. Cobro de factura, pago a proveedor..."
                                class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2.5 text-sm text-zinc-900 placeholder-zinc-400 outline-none focus:border-teal-600 dark:border-zinc-800 dark:bg-zinc-900 dark:text-white dark:placeholder-zinc-600"
                            >

                            @error('concepto')
                                <p class="mt-1 text-xs text-red-400">{{ $message }}</p>
                            @enderror

                        </div>

                        {{-- Categoría --}}
                        <div>

                            <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-300">
                                Categoría
                            </label>

                            <input
                                type="text"
                                wire:model="categoria"
                                placeholder="Ventas, compras, servicios..."
                                class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2.5 text-sm text-zinc-900 placeholder-zinc-400 outline-none focus:border-teal-600 dark:border-zinc-800 dark:bg-zinc-900 dark:text-white dark:placeholder-zinc-600"
                            >

                        </div>

                        {{-- Forma pago --}}
                        <div>

                            <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-300">
                                Forma de pago
                            </label>

                            <select
                                wire:model="forma_pago"
                                class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2.5 text-sm text-zinc-900 outline-none focus:border-teal-600 dark:border-zinc-800 dark:bg-zinc-900 dark:text-white"
                            >
                                <option value="Efectivo">Efectivo</option>
                                <option value="Transferencia">Transferencia</option>
                                <option value="Tarjeta">Tarjeta</option>
                                <option value="Yape/Plin">Yape/Plin</option>
                                <option value="Otro">Otro</option>
                            </select>

                        </div>

                        {{-- Monto --}}
                        <div>

                            <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-300">
                                Monto
                            </label>

                            <input
                                type="number"
                                min="0.01"
                                step="0.01"
                                wire:model="monto"
                                placeholder="0.00"
                                class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2.5 text-sm text-zinc-900 placeholder-zinc-400 outline-none focus:border-teal-600 dark:border-zinc-800 dark:bg-zinc-900 dark:text-white dark:placeholder-zinc-600"
                            >

                            @error('monto')
                                <p class="mt-1 text-xs text-red-400">{{ $message }}</p>
                            @enderror

                        </div>

                        {{-- Comprobante --}}
                        <div>

                            <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-300">
                                N.º de comprobante
                            </label>

                            <input
                                type="text"
                                wire:model="numero_comprobante"
                                placeholder="Opcional"
                                class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2.5 text-sm text-zinc-900 placeholder-zinc-400 outline-none focus:border-teal-600 dark:border-zinc-800 dark:bg-zinc-900 dark:text-white dark:placeholder-zinc-600"
                            >

                        </div>

                        {{-- Observación --}}
                        <div class="sm:col-span-2">

                            <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-300">
                                Observación
                            </label>

                            <textarea
                                wire:model="observacion"
                                rows="3"
                                placeholder="Observaciones..."
                                class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2.5 text-sm text-zinc-900 placeholder-zinc-400 outline-none focus:border-teal-600 dark:border-zinc-800 dark:bg-zinc-900 dark:text-white dark:placeholder-zinc-600"
                            ></textarea>

                        </div>

                    </div>

                    <div class="mt-6 flex justify-end gap-3 border-t border-zinc-800 pt-5">

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

                            Guardar movimiento
                        </button>

                    </div>

                </form>

            </div>

        </div>

    @endif

</div>
