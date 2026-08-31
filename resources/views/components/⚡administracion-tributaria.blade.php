<?php

use App\Models\ObligacionTributaria;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component
{
    use WithPagination;

    public string $buscar = '';
    public string $filtroEstado = '';

    public bool $mostrarFormulario = false;
    public ?int $obligacionEditando = null;

    public string $cliente = '';
    public string $ruc = '';
    public string $tipo_obligacion = '';
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

    public function editar(int $id): void
    {
        $obligacion = ObligacionTributaria::findOrFail($id);

        $this->obligacionEditando = $obligacion->id;
        $this->cliente = $obligacion->cliente;
        $this->ruc = $obligacion->ruc;
        $this->tipo_obligacion = $obligacion->tipo_obligacion;
        $this->periodo = $obligacion->periodo;
        $this->fecha_vencimiento =
            $obligacion->fecha_vencimiento?->format('Y-m-d') ?? '';
        $this->monto = $obligacion->monto;
        $this->estado = $obligacion->estado;

        $this->resetValidation();

        $this->mostrarFormulario = true;
    }

    public function eliminar(int $id): void
    {
        $obligacion = ObligacionTributaria::findOrFail($id);

        $obligacion->delete();

        session()->flash(
            'mensaje',
            'Obligación tributaria eliminada correctamente.'
        );
    }

    public function guardar(): void
    {
        $this->validate([
            'cliente' => 'required|string|max:150',
            'ruc' => 'required|digits:11',
            'tipo_obligacion' => 'required|string|max:100',
            'periodo' => 'required|string|max:20',
            'fecha_vencimiento' => 'required|date',
            'monto' => 'required|numeric|min:0',
            'estado' => 'required|in:Pendiente,Pagada,Vencida',
        ], [
            'cliente.required' =>
                'El cliente es obligatorio.',
            'ruc.required' =>
                'El RUC es obligatorio.',
            'ruc.digits' =>
                'El RUC debe tener 11 dígitos.',
            'tipo_obligacion.required' =>
                'El tipo de obligación es obligatorio.',
            'periodo.required' =>
                'El periodo es obligatorio.',
            'fecha_vencimiento.required' =>
                'La fecha de vencimiento es obligatoria.',
            'fecha_vencimiento.date' =>
                'Ingresa una fecha válida.',
            'monto.required' =>
                'El monto es obligatorio.',
            'monto.numeric' =>
                'Ingresa un monto válido.',
            'monto.min' =>
                'El monto no puede ser negativo.',
            'estado.required' =>
                'El estado es obligatorio.',
        ]);

        if ($this->obligacionEditando) {

            $obligacion = ObligacionTributaria::findOrFail(
                $this->obligacionEditando
            );

            $obligacion->update([
                'cliente' => $this->cliente,
                'ruc' => $this->ruc,
                'tipo_obligacion' => $this->tipo_obligacion,
                'periodo' => $this->periodo,
                'fecha_vencimiento' => $this->fecha_vencimiento,
                'monto' => $this->monto,
                'estado' => $this->estado,
            ]);

            $mensaje =
                'Obligación tributaria actualizada correctamente.';

        } else {

            ObligacionTributaria::create([
                'cliente' => $this->cliente,
                'ruc' => $this->ruc,
                'tipo_obligacion' => $this->tipo_obligacion,
                'periodo' => $this->periodo,
                'fecha_vencimiento' => $this->fecha_vencimiento,
                'monto' => $this->monto,
                'estado' => 'Pendiente',
            ]);

            $mensaje =
                'Obligación tributaria registrada correctamente.';
        }

        $this->mostrarFormulario = false;
        $this->obligacionEditando = null;

        $this->reset([
            'cliente',
            'ruc',
            'tipo_obligacion',
            'periodo',
            'fecha_vencimiento',
            'monto',
        ]);

        $this->estado = 'Pendiente';

        session()->flash('mensaje', $mensaje);
    }

public function render()
{
    $hoy = now()->startOfDay();
    $limite = now()->addDays(7)->endOfDay();

    // Actualiza automáticamente las obligaciones pendientes
    // cuya fecha de vencimiento ya pasó.
    ObligacionTributaria::where('estado', 'Pendiente')
        ->whereDate('fecha_vencimiento', '<', $hoy->toDateString())
        ->update([
            'estado' => 'Vencida',
        ]);

    $obligaciones = ObligacionTributaria::query()
            ->when($this->buscar, function ($query) {
                $query->where(function ($query) {
                    $query->where(
                        'cliente',
                        'like',
                        '%' . $this->buscar . '%'
                    )
                    ->orWhere(
                        'ruc',
                        'like',
                        '%' . $this->buscar . '%'
                    )
                    ->orWhere(
                        'tipo_obligacion',
                        'like',
                        '%' . $this->buscar . '%'
                    );
                });
            })
            ->when($this->filtroEstado, function ($query) {
                $query->where(
                    'estado',
                    $this->filtroEstado
                );
            })
            ->latest()
->paginate(10);

$totalObligaciones = ObligacionTributaria::count();

$totalPendientes = ObligacionTributaria::where('estado', 'Pendiente')
    ->count();

$totalVencidas = ObligacionTributaria::where('estado', 'Vencida')
    ->count();

$totalPagadas = ObligacionTributaria::where('estado', 'Pagada')
    ->count();

$totalProximasVencer = ObligacionTributaria::where('estado', 'Pendiente')
    ->whereDate('fecha_vencimiento', '>=', $hoy->toDateString())
    ->whereDate('fecha_vencimiento', '<=', $limite->toDateString())
    ->count();

return view('components.⚡administracion-tributaria', [
    'obligaciones' => $obligaciones,
    'totalObligaciones' => $totalObligaciones,
    'totalPendientes' => $totalPendientes,
    'totalVencidas' => $totalVencidas,
    'totalPagadas' => $totalPagadas,
    'totalProximasVencer' => $totalProximasVencer,
]);
    }
};
?>

<div class="min-h-screen bg-gray-50 p-6">

    {{-- ENCABEZADO --}}
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">
            Administración Tributaria
        </h1>

        <p class="mt-1 text-sm text-gray-600">
            Registra y gestiona las obligaciones tributarias de los clientes.
        </p>
    </div>

    {{-- MENSAJE --}}
    @if (session('mensaje'))
        <div class="mb-6 rounded-lg border border-green-200 bg-green-50 p-4">
            <span class="font-medium text-green-800">
                ✓ {{ session('mensaje') }}
            </span>
        </div>
    @endif

    {{-- FORMULARIO --}}
    <div class="mb-8 rounded-xl border border-gray-200 bg-white p-6 shadow-sm">

        <div class="mb-6 border-b border-gray-200 pb-4">
            <h2 class="text-xl font-semibold text-gray-900">
                {{ $obligacionEditando ? 'Editar obligación' : 'Registrar obligación' }}
            </h2>

            <p class="mt-1 text-sm text-gray-500">
                Ingresa los datos de la obligación tributaria.
            </p>
        </div>

        <form wire:submit="guardar">

            <div class="grid grid-cols-1 gap-5 md:grid-cols-2">

                {{-- CLIENTE --}}
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">
                        Cliente
                    </label>

                    <input
                        wire:model="cliente"
                        type="text"
                        placeholder="Nombre del cliente"
                        class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-gray-900 shadow-sm"
                    >

                    @error('cliente')
                        <span class="mt-1 block text-sm text-red-600">
                            {{ $message }}
                        </span>
                    @enderror
                </div>

                {{-- RUC --}}
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">
                        RUC
                    </label>

                    <input
                        wire:model="ruc"
                        type="text"
                        maxlength="11"
                        placeholder="20123456789"
                        class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-gray-900 shadow-sm"
                    >

                    @error('ruc')
                        <span class="mt-1 block text-sm text-red-600">
                            {{ $message }}
                        </span>
                    @enderror
                </div>

                {{-- TIPO --}}
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">
                        Tipo de obligación
                    </label>

                    <select
                        wire:model="tipo_obligacion"
                        class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-gray-900 shadow-sm"
                    >
                        <option value="">
                            Selecciona una obligación
                        </option>
                        <option value="IGV">
                            IGV
                        </option>
                        <option value="Impuesto a la Renta">
                            Impuesto a la Renta
                        </option>
                        <option value="PDT">
                            PDT
                        </option>
                        <option value="Declaración mensual">
                            Declaración mensual
                        </option>
                        <option value="Declaración anual">
                            Declaración anual
                        </option>
                        <option value="Otra">
                            Otra
                        </option>
                    </select>

                    @error('tipo_obligacion')
                        <span class="mt-1 block text-sm text-red-600">
                            {{ $message }}
                        </span>
                    @enderror
                </div>

                {{-- PERIODO --}}
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">
                        Periodo
                    </label>

                    <input
                        wire:model="periodo"
                        type="month"
                        class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-gray-900 shadow-sm"
                    >

                    @error('periodo')
                        <span class="mt-1 block text-sm text-red-600">
                            {{ $message }}
                        </span>
                    @enderror
                </div>

                {{-- VENCIMIENTO --}}
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">
                        Fecha de vencimiento
                    </label>

                    <input
                        wire:model="fecha_vencimiento"
                        type="date"
                        class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-gray-900 shadow-sm"
                    >

                    @error('fecha_vencimiento')
                        <span class="mt-1 block text-sm text-red-600">
                            {{ $message }}
                        </span>
                    @enderror
                </div>

                {{-- MONTO --}}
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">
                        Monto
                    </label>

                    <div class="relative">
                        <span class="absolute left-3 top-2.5 text-sm text-gray-500">
                            S/
                        </span>

                        <input
                            wire:model="monto"
                            type="number"
                            step="0.01"
                            min="0"
                            placeholder="0.00"
                            class="w-full rounded-lg border border-gray-300 bg-white py-2.5 pl-10 pr-3 text-gray-900 shadow-sm"
                        >
                    </div>

                    @error('monto')
                        <span class="mt-1 block text-sm text-red-600">
                            {{ $message }}
                        </span>
                    @enderror
                </div>

                {{-- ESTADO --}}
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">
                        Estado
                    </label>

                    <select
                        wire:model="estado"
                        class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-gray-900 shadow-sm"
                    >
                        <option value="Pendiente">Pendiente</option>
                        <option value="Pagada">Pagada</option>
                        <option value="Vencida">Vencida</option>
                    </select>

                    @error('estado')
                        <span class="mt-1 block text-sm text-red-600">
                            {{ $message }}
                        </span>
                    @enderror
                </div>

            </div>

            {{-- BOTÓN --}}
            <div class="mt-7 border-t border-gray-200 pt-5">

                <button
                    type="submit"
                    class="rounded-lg bg-gray-900 px-6 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-gray-700"
                >
                    {{ $obligacionEditando ? 'Actualizar obligación' : 'Registrar obligación' }}
                </button>

            </div>

        </form>

    </div>
{{-- INDICADORES --}}
<div class="mb-8 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">

    <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
        <p class="text-sm font-medium text-gray-500">
            Total de obligaciones
        </p>
        <p class="mt-2 text-3xl font-bold text-gray-900">
            {{ $totalObligaciones }}
        </p>
    </div>

    <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
        <p class="text-sm font-medium text-gray-500">
            Pendientes
        </p>
        <p class="mt-2 text-3xl font-bold text-gray-900">
            {{ $totalPendientes }}
        </p>
    </div>

    <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
        <p class="text-sm font-medium text-gray-500">
            Próximas a vencer
        </p>
        <p class="mt-2 text-3xl font-bold text-gray-900">
            {{ $totalProximasVencer }}
        </p>
        <p class="mt-1 text-xs text-gray-500">
            Próximos 7 días
        </p>
    </div>

    <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
        <p class="text-sm font-medium text-gray-500">
            Vencidas
        </p>
        <p class="mt-2 text-3xl font-bold text-gray-900">
            {{ $totalVencidas }}
        </p>
    </div>

</div>
    {{-- TABLA --}}
    <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">

        <div class="mb-5 border-b border-gray-200 pb-4">

            <h2 class="text-xl font-semibold text-gray-900">
                Obligaciones tributarias registradas
            </h2>

            <p class="mt-1 text-sm text-gray-500">
                Historial de obligaciones tributarias.
            </p>

        </div>

        {{-- BÚSQUEDA Y FILTRO --}}
        <div class="mb-5 grid grid-cols-1 gap-3 border-b border-gray-200 pb-5 md:grid-cols-2">

            <input
                wire:model.live.debounce.300ms="buscar"
                type="text"
                placeholder="Buscar por cliente, RUC o obligación..."
                class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900 shadow-sm outline-none focus:border-gray-500 focus:ring-2 focus:ring-gray-200"
            >

            <select
                wire:model.live="filtroEstado"
                class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900 shadow-sm"
            >
                <option value="">
                    Todos los estados
                </option>
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

        <div class="overflow-x-auto">

            <table class="w-full text-left text-sm">

                <thead>
                    <tr class="border-b border-gray-200 bg-gray-50">

                        <th class="px-4 py-3 font-semibold text-gray-700">
                            Cliente
                        </th>

                        <th class="px-4 py-3 font-semibold text-gray-700">
                            RUC
                        </th>

                        <th class="px-4 py-3 font-semibold text-gray-700">
                            Obligación
                        </th>

                        <th class="px-4 py-3 font-semibold text-gray-700">
                            Periodo
                        </th>

                        <th class="px-4 py-3 font-semibold text-gray-700">
                            Vencimiento
                        </th>

                        <th class="px-4 py-3 font-semibold text-gray-700">
                            Monto
                        </th>

                        <th class="px-4 py-3 font-semibold text-gray-700">
                            Estado
                        </th>

                        <th class="px-4 py-3 text-right font-semibold text-gray-700">
                            Acciones
                        </th>

                    </tr>
                </thead>

                <tbody>

                    @forelse ($obligaciones as $obligacion)

                        <tr class="border-b border-gray-100 hover:bg-gray-50">

                            <td class="px-4 py-3 font-medium text-gray-900">
                                {{ $obligacion->cliente }}
                            </td>

                            <td class="px-4 py-3 text-gray-700">
                                {{ $obligacion->ruc }}
                            </td>

                            <td class="px-4 py-3 text-gray-700">
                                {{ $obligacion->tipo_obligacion }}
                            </td>

                            <td class="px-4 py-3 text-gray-700">
                                {{ $obligacion->periodo }}
                            </td>

                            <td class="px-4 py-3 text-gray-700">
                                {{ $obligacion->fecha_vencimiento?->format('d/m/Y') }}
                            </td>

                            <td class="px-4 py-3 font-medium text-gray-900">
                                S/ {{ number_format($obligacion->monto, 2) }}
                            </td>

                            {{-- ESTADO --}}
                            <td class="px-4 py-3">

                                @if ($obligacion->estado === 'Pagada')

                                    <span class="inline-flex rounded-full bg-green-100 px-3 py-1 text-xs font-medium text-green-700">
                                        Pagada
                                    </span>

                                @elseif ($obligacion->estado === 'Vencida')

                                    <span class="inline-flex rounded-full bg-red-100 px-3 py-1 text-xs font-medium text-red-700">
                                        Vencida
                                    </span>

                                @else

                                    <span class="inline-flex rounded-full bg-yellow-100 px-3 py-1 text-xs font-medium text-yellow-700">
                                        Pendiente
                                    </span>

                                @endif

                            </td>

                            {{-- ACCIONES --}}
                            <td class="px-4 py-3 text-right">

                                <div class="flex justify-end gap-2">

                                    <button
                                        type="button"
                                        wire:click="editar({{ $obligacion->id }})"
                                        class="rounded-lg border border-gray-300 px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100"
                                    >
                                        Editar
                                    </button>

                                    <button
                                        type="button"
                                        wire:click="eliminar({{ $obligacion->id }})"
                                        wire:confirm="¿Estás seguro de eliminar esta obligación?"
                                        class="rounded-lg border border-red-200 px-3 py-2 text-sm font-medium text-red-600 hover:bg-red-50"
                                    >
                                        Eliminar
                                    </button>

                                </div>

                            </td>

                        </tr>

                    @empty

                        <tr>
                            <td
                                colspan="8"
                                class="px-4 py-8 text-center text-gray-500"
                            >
                                No hay obligaciones tributarias registradas.
                            </td>
                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

        {{-- PAGINACIÓN --}}
        <div class="mt-5">
            {{ $obligaciones->links() }}
        </div>

    </div>

</div>