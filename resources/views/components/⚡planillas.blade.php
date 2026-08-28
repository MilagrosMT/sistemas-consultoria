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
        'empleado_id.exists' => 'El empleado seleccionado no es válido.',
        'periodo.required' => 'El periodo es obligatorio.',
        'periodo.date' => 'Ingresa un periodo válido.',
        'sueldo_base.required' => 'El sueldo base es obligatorio.',
        'sueldo_base.numeric' => 'Ingresa un monto válido.',
        'bonificaciones.numeric' => 'Ingresa un monto válido.',
        'descuentos.numeric' => 'Ingresa un monto válido.',
    ]);

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
        return [
            'empleados' => Empleado::orderBy('apellidos')
                ->orderBy('nombres')
                ->get(),

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

<div class="min-h-screen bg-gray-50 p-6">

    {{-- ENCABEZADO --}}
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">
            Gestión de Planillas
        </h1>

        <p class="mt-1 text-sm text-gray-600">
            Registra y consulta las planillas de los empleados.
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
    {{ $planillaEditando ? 'Editar planilla' : 'Registrar planilla' }}
</h2>

            <p class="mt-1 text-sm text-gray-500">
                Ingresa los datos de la planilla del empleado.
            </p>
        </div>

        <form wire:submit="guardar">

            <div class="grid grid-cols-1 gap-5 md:grid-cols-2">

                {{-- EMPLEADO --}}
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">
                        Empleado
                    </label>

                    <select
                        wire:model="empleado_id"
                        class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-gray-900 shadow-sm"
                    >
                        <option value="">
                            Selecciona un empleado
                        </option>

                        @foreach ($empleados as $empleado)
                            <option value="{{ $empleado->id }}">
                                {{ $empleado->apellidos }}, {{ $empleado->nombres }}
                                - DNI {{ $empleado->dni }}
                            </option>
                        @endforeach
                    </select>

                    @error('empleado_id')
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

                {{-- SUELDO --}}
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">
                        Sueldo básico
                    </label>

                    <div class="relative">
                        <span class="absolute left-3 top-2.5 text-sm text-gray-500">
                            S/
                        </span>

                        <input
                            wire:model="sueldo_base"
                            type="number"
                            step="0.01"
                            placeholder="0.00"
                            class="w-full rounded-lg border border-gray-300 bg-white py-2.5 pl-10 pr-3 text-gray-900 shadow-sm"
                        >
                    </div>

                    @error('sueldo_base')
                        <span class="mt-1 block text-sm text-red-600">
                            {{ $message }}
                        </span>
                    @enderror
                </div>

                {{-- BONIFICACIONES --}}
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">
                        Bonificaciones
                    </label>

                    <div class="relative">
                        <span class="absolute left-3 top-2.5 text-sm text-gray-500">
                            S/
                        </span>

                        <input
                            wire:model="bonificaciones"
                            type="number"
                            step="0.01"
                            min="0"
                            placeholder="0.00"
                            class="w-full rounded-lg border border-gray-300 bg-white py-2.5 pl-10 pr-3 text-gray-900 shadow-sm"
                        >
                    </div>

                    @error('bonificaciones')
                        <span class="mt-1 block text-sm text-red-600">
                            {{ $message }}
                        </span>
                    @enderror
                </div>

                {{-- DESCUENTOS --}}
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">
                        Descuentos
                    </label>

                    <div class="relative">
                        <span class="absolute left-3 top-2.5 text-sm text-gray-500">
                            S/
                        </span>

                        <input
                            wire:model="descuentos"
                            type="number"
                            step="0.01"
                            min="0"
                            placeholder="0.00"
                            class="w-full rounded-lg border border-gray-300 bg-white py-2.5 pl-10 pr-3 text-gray-900 shadow-sm"
                        >
                    </div>

                    @error('descuentos')
                        <span class="mt-1 block text-sm text-red-600">
                            {{ $message }}
                        </span>
                    @enderror
                </div>

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
        <option value="Anulada">Anulada</option>
    </select>

    @error('estado')
        <span class="mt-1 block text-sm text-red-600">
            {{ $message }}
        </span>
    @enderror
</div>
            {{-- BOTÓN --}}
            <div class="mt-7 border-t border-gray-200 pt-5">

                <button
                    type="submit"
                    class="rounded-lg bg-gray-900 px-6 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-gray-700"
                >
                   {{ $planillaEditando ? 'Actualizar planilla' : 'Registrar planilla' }}
                </button>

            </div>

        </form>

    </div>

    {{-- TABLA --}}
    <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">

        <div class="mb-5 border-b border-gray-200 pb-4">

            <h2 class="text-xl font-semibold text-gray-900">
                Planillas registradas
            </h2>

            <p class="mt-1 text-sm text-gray-500">
                Historial de planillas generadas.
            </p>

        </div>
<div class="mb-5 grid grid-cols-1 gap-3 border-b border-gray-200 pb-5 md:grid-cols-2">

    <input
        wire:model.live.debounce.300ms="buscar"
        type="text"
        placeholder="Buscar por empleado o DNI..."
        class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900 shadow-sm outline-none focus:border-gray-500 focus:ring-2 focus:ring-gray-200"
    >

    <select
        wire:model.live="filtroEstado"
        class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900 shadow-sm"
    >
        <option value="">Todos los estados</option>
        <option value="Pendiente">Pendiente</option>
        <option value="Pagada">Pagada</option>
        <option value="Anulada">Anulada</option>
    </select>

</div>
        <div class="overflow-x-auto">

            <table class="w-full text-left text-sm">

                <thead>
                    <tr class="border-b border-gray-200 bg-gray-50">

                        <th class="px-4 py-3 font-semibold text-gray-700">
                            Empleado
                        </th>

                        <th class="px-4 py-3 font-semibold text-gray-700">
                            Periodo
                        </th>

                        <th class="px-4 py-3 font-semibold text-gray-700">
                            Sueldo básico
                        </th>

                        <th class="px-4 py-3 font-semibold text-gray-700">
                            Bonificaciones
                        </th>

                        <th class="px-4 py-3 font-semibold text-gray-700">
                            Descuentos
                        </th>

                        <th class="px-4 py-3 font-semibold text-gray-700">
                            Sueldo neto
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

                    @forelse ($planillas as $planilla)

                        <tr class="border-b border-gray-100 hover:bg-gray-50">

                            <td class="px-4 py-3 font-medium text-gray-900">
                                {{ $planilla->empleado?->nombres }}
                                {{ $planilla->empleado?->apellidos }}
                            </td>

                            <td class="px-4 py-3 text-gray-700">
    {{ $planilla->periodo?->translatedFormat('F Y') }}
</td>

                            <td class="px-4 py-3 text-gray-700">
                                S/ {{ number_format($planilla->sueldo_base, 2) }}
                            </td>

                            <td class="px-4 py-3 text-gray-700">
                                S/ {{ number_format($planilla->bonificaciones, 2) }}
                            </td>

                            <td class="px-4 py-3 text-gray-700">
                                S/ {{ number_format($planilla->descuentos, 2) }}
                            </td>

                           <td class="px-4 py-3 font-semibold text-gray-900">
    S/ {{ number_format($planilla->sueldo_neto, 2) }}
</td>
{{-- ESTADO --}}
<td class="px-4 py-3">
    @if ($planilla->estado === 'Pagada')

        <span class="inline-flex rounded-full bg-green-100 px-3 py-1 text-xs font-medium text-green-700">
            Pagada
        </span>

    @elseif ($planilla->estado === 'Anulada')

        <span class="inline-flex rounded-full bg-red-100 px-3 py-1 text-xs font-medium text-red-700">
            Anulada
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
            wire:click="editar({{ $planilla->id }})"
            class="rounded-lg border border-gray-300 px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100"
        >
            Editar
        </button>

        <button
            type="button"
            wire:click="eliminar({{ $planilla->id }})"
            wire:confirm="¿Estás seguro de eliminar esta planilla?"
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
                                No hay planillas registradas.
                            </td>
                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

    </div>

</div>