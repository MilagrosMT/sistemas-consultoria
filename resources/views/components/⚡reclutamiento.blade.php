<?php

use App\Models\Postulante;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component
{
    use WithPagination;

    public string $buscar = '';
    public string $filtroEstado = '';

    public bool $mostrarFormulario = false;
    public ?int $postulanteEditando = null;

    public string $nombres = '';
    public string $apellidos = '';
    public string $dni = '';
    public string $telefono = '';
    public string $email = '';
    public string $puesto = '';
    public string $fecha_postulacion = '';
    public string $estado = 'Postulando';

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
        $postulante = Postulante::findOrFail($id);

        $this->postulanteEditando = $postulante->id;
        $this->nombres = $postulante->nombres;
        $this->apellidos = $postulante->apellidos;
        $this->dni = $postulante->dni;
        $this->telefono = $postulante->telefono ?? '';
        $this->email = $postulante->email ?? '';
        $this->puesto = $postulante->puesto;
        $this->fecha_postulacion =
            $postulante->fecha_postulacion?->format('Y-m-d') ?? '';
        $this->estado = $postulante->estado;

        $this->resetValidation();

        $this->mostrarFormulario = true;
    }

    public function eliminar(int $id): void
    {
        $postulante = Postulante::findOrFail($id);

        $postulante->delete();

        session()->flash(
            'mensaje',
            'Postulante eliminado correctamente.'
        );
    }

   public function guardar(): void
{
    $this->validate([
        'nombres' => 'required|string|max:100',
        'apellidos' => 'required|string|max:100',
        'dni' => 'required|digits:8',
        'telefono' => 'nullable|digits:9',
        'email' => 'nullable|email|max:255',
        'puesto' => 'required|string|max:150',
        'fecha_postulacion' => 'required|date',
        'estado' => 'required|in:Postulando,Entrevista,Seleccionado,Rechazado',
    ], [
        'nombres.required' => 'Los nombres son obligatorios.',
        'apellidos.required' => 'Los apellidos son obligatorios.',
        'dni.required' => 'El DNI es obligatorio.',
        'dni.digits' => 'El DNI debe tener 8 dígitos.',
        'telefono.digits' => 'El teléfono debe tener 9 dígitos.',
        'email.email' => 'Ingresa un correo válido.',
        'puesto.required' => 'El puesto es obligatorio.',
        'fecha_postulacion.required' => 'La fecha de postulación es obligatoria.',
        'fecha_postulacion.date' => 'Ingresa una fecha válida.',
        'estado.required' => 'El estado es obligatorio.',
    ]);

    // Validar el flujo del proceso de selección
    if ($this->postulanteEditando) {
        $postulanteActual = Postulante::find($this->postulanteEditando);

        if ($postulanteActual) {
            $estadoAnterior = $postulanteActual->estado;
            $estadoNuevo = $this->estado;

            $transicionesPermitidas = [
                'Postulando' => ['Postulando', 'Entrevista', 'Rechazado'],
                'Entrevista' => ['Entrevista', 'Seleccionado', 'Rechazado'],
                'Seleccionado' => ['Seleccionado'],
                'Rechazado' => ['Rechazado', 'Postulando'],
            ];

            if (
                !in_array(
                    $estadoNuevo,
                    $transicionesPermitidas[$estadoAnterior] ?? []
                )
            ) {
                $this->addError(
                    'estado',
                    'El cambio de estado no corresponde al flujo del proceso de selección.'
                );

                return;
            }
        }
    }

    if ($this->postulanteEditando) {
        $postulante = Postulante::findOrFail(
            $this->postulanteEditando
        );

        $postulante->update([
            'nombres' => $this->nombres,
            'apellidos' => $this->apellidos,
            'dni' => $this->dni,
            'telefono' => $this->telefono ?: null,
            'email' => $this->email ?: null,
            'puesto' => $this->puesto,
            'fecha_postulacion' => $this->fecha_postulacion,
            'estado' => $this->estado,
        ]);

        $mensaje = 'Postulante actualizado correctamente.';
    } else {
        Postulante::create([
            'nombres' => $this->nombres,
            'apellidos' => $this->apellidos,
            'dni' => $this->dni,
            'telefono' => $this->telefono ?: null,
            'email' => $this->email ?: null,
            'puesto' => $this->puesto,
            'fecha_postulacion' => $this->fecha_postulacion,
            'estado' => 'Postulando',
        ]);

        $mensaje = 'Postulante registrado correctamente.';
    }

    $this->mostrarFormulario = false;
    $this->postulanteEditando = null;

    $this->reset([
        'nombres',
        'apellidos',
        'dni',
        'telefono',
        'email',
        'puesto',
        'fecha_postulacion',
    ]);

    $this->estado = 'Postulando';

    session()->flash('mensaje', $mensaje);
}
public function render()
{
    $totalPostulantes = Postulante::count();

    $totalPostulando = Postulante::where('estado', 'Postulando')->count();

    $totalEntrevista = Postulante::where('estado', 'Entrevista')->count();

    $totalSeleccionados = Postulante::where('estado', 'Seleccionado')->count();

    $totalRechazados = Postulante::where('estado', 'Rechazado')->count();

    $postulantes = Postulante::query()
            ->when($this->buscar, function ($query) {
                $query->where(function ($query) {
                    $query->where(
                        'nombres',
                        'like',
                        '%' . $this->buscar . '%'
                    )
                    ->orWhere(
                        'apellidos',
                        'like',
                        '%' . $this->buscar . '%'
                    )
                    ->orWhere(
                        'dni',
                        'like',
                        '%' . $this->buscar . '%'
                    )
                    ->orWhere(
                        'puesto',
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

        return view('components.⚡reclutamiento', [
    'postulantes' => $postulantes,
    'totalPostulantes' => $totalPostulantes,
    'totalPostulando' => $totalPostulando,
    'totalEntrevista' => $totalEntrevista,
    'totalSeleccionados' => $totalSeleccionados,
    'totalRechazados' => $totalRechazados,
]);
    }
};
?>

<div class="min-h-screen bg-gray-50 p-6">

    {{-- ENCABEZADO --}}
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">
            Gestión de Reclutamiento
        </h1>

        <p class="mt-1 text-sm text-gray-600">
            Registra y gestiona los postulantes y su proceso de selección.
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
                {{ $postulanteEditando ? 'Editar postulante' : 'Registrar postulante' }}
            </h2>

            <p class="mt-1 text-sm text-gray-500">
                Ingresa los datos del postulante.
            </p>
        </div>

        <form wire:submit="guardar">

            <div class="grid grid-cols-1 gap-5 md:grid-cols-2">

                {{-- NOMBRES --}}
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">
                        Nombres
                    </label>

                    <input
                        wire:model="nombres"
                        type="text"
                        placeholder="Nombres del postulante"
                        class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-gray-900 shadow-sm"
                    >

                    @error('nombres')
                        <span class="mt-1 block text-sm text-red-600">
                            {{ $message }}
                        </span>
                    @enderror
                </div>

                {{-- APELLIDOS --}}
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">
                        Apellidos
                    </label>

                    <input
                        wire:model="apellidos"
                        type="text"
                        placeholder="Apellidos del postulante"
                        class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-gray-900 shadow-sm"
                    >

                    @error('apellidos')
                        <span class="mt-1 block text-sm text-red-600">
                            {{ $message }}
                        </span>
                    @enderror
                </div>

                {{-- DNI --}}
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">
                        DNI
                    </label>

                    <input
                        wire:model="dni"
                        type="text"
                        maxlength="8"
                        placeholder="00000000"
                        class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-gray-900 shadow-sm"
                    >

                    @error('dni')
                        <span class="mt-1 block text-sm text-red-600">
                            {{ $message }}
                        </span>
                    @enderror
                </div>

                {{-- TELÉFONO --}}
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">
                        Teléfono
                    </label>

                    <input
                        wire:model="telefono"
                        type="text"
                        maxlength="9"
                        placeholder="000000000"
                        class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-gray-900 shadow-sm"
                    >

                    @error('telefono')
                        <span class="mt-1 block text-sm text-red-600">
                            {{ $message }}
                        </span>
                    @enderror
                </div>

                {{-- EMAIL --}}
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">
                        Correo electrónico
                    </label>

                    <input
                        wire:model="email"
                        type="email"
                        placeholder="correo@ejemplo.com"
                        class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-gray-900 shadow-sm"
                    >

                    @error('email')
                        <span class="mt-1 block text-sm text-red-600">
                            {{ $message }}
                        </span>
                    @enderror
                </div>

                {{-- PUESTO --}}
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">
                        Puesto al que postula
                    </label>

                    <input
                        wire:model="puesto"
                        type="text"
                        placeholder="Ej. Asistente contable"
                        class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-gray-900 shadow-sm"
                    >

                    @error('puesto')
                        <span class="mt-1 block text-sm text-red-600">
                            {{ $message }}
                        </span>
                    @enderror
                </div>

                {{-- FECHA --}}
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">
                        Fecha de postulación
                    </label>

                    <input
                        wire:model="fecha_postulacion"
                        type="date"
                        class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-gray-900 shadow-sm"
                    >

                    @error('fecha_postulacion')
                        <span class="mt-1 block text-sm text-red-600">
                            {{ $message }}
                        </span>
                    @enderror
                </div>

                {{-- ESTADO --}}
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">
                        Estado del proceso
                    </label>

                    <select
                        wire:model="estado"
                        class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-gray-900 shadow-sm"
                    >
                        <option value="Postulando">Postulando</option>
                        <option value="Entrevista">Entrevista</option>
                        <option value="Seleccionado">Seleccionado</option>
                        <option value="Rechazado">Rechazado</option>
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
                    {{ $postulanteEditando ? 'Actualizar postulante' : 'Registrar postulante' }}
                </button>

            </div>

        </form>

    </div>
{{-- INDICADORES DE RECLUTAMIENTO --}}
<div class="mb-8 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-5">

    <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
        <p class="text-sm font-medium text-gray-500">
            Total postulantes
        </p>
        <p class="mt-2 text-3xl font-bold text-gray-900">
            {{ $totalPostulantes }}
        </p>
    </div>

    <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
        <p class="text-sm font-medium text-gray-500">
            Postulando
        </p>
        <p class="mt-2 text-3xl font-bold text-gray-900">
            {{ $totalPostulando }}
        </p>
    </div>

    <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
        <p class="text-sm font-medium text-gray-500">
            En entrevista
        </p>
        <p class="mt-2 text-3xl font-bold text-gray-900">
            {{ $totalEntrevista }}
        </p>
    </div>

    <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
        <p class="text-sm font-medium text-gray-500">
            Seleccionados
        </p>
        <p class="mt-2 text-3xl font-bold text-gray-900">
            {{ $totalSeleccionados }}
        </p>
    </div>

    <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
        <p class="text-sm font-medium text-gray-500">
            Rechazados
        </p>
        <p class="mt-2 text-3xl font-bold text-gray-900">
            {{ $totalRechazados }}
        </p>
    </div>

</div>
    {{-- TABLA --}}
    <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">

        <div class="mb-5 border-b border-gray-200 pb-4">

            <h2 class="text-xl font-semibold text-gray-900">
                Postulantes registrados
            </h2>

            <p class="mt-1 text-sm text-gray-500">
                Historial de postulantes y procesos de selección.
            </p>

        </div>

        {{-- BÚSQUEDA Y FILTRO --}}
        <div class="mb-5 grid grid-cols-1 gap-3 border-b border-gray-200 pb-5 md:grid-cols-2">

            <input
                wire:model.live.debounce.300ms="buscar"
                type="text"
                placeholder="Buscar por nombre, DNI o puesto..."
                class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900 shadow-sm outline-none focus:border-gray-500 focus:ring-2 focus:ring-gray-200"
            >

            <select
                wire:model.live="filtroEstado"
                class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900 shadow-sm"
            >
                <option value="">Todos los estados</option>
                <option value="Postulando">Postulando</option>
                <option value="Entrevista">Entrevista</option>
                <option value="Seleccionado">Seleccionado</option>
                <option value="Rechazado">Rechazado</option>
            </select>

        </div>

        <div class="overflow-x-auto">

            <table class="w-full text-left text-sm">

                <thead>
                    <tr class="border-b border-gray-200 bg-gray-50">

                        <th class="px-4 py-3 font-semibold text-gray-700">
                            Postulante
                        </th>

                        <th class="px-4 py-3 font-semibold text-gray-700">
                            DNI
                        </th>

                        <th class="px-4 py-3 font-semibold text-gray-700">
                            Puesto
                        </th>

                        <th class="px-4 py-3 font-semibold text-gray-700">
                            Fecha
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

                    @forelse ($postulantes as $postulante)

                        <tr class="border-b border-gray-100 hover:bg-gray-50">

                            <td class="px-4 py-3 font-medium text-gray-900">
                                {{ $postulante->apellidos }},
                                {{ $postulante->nombres }}
                            </td>

                            <td class="px-4 py-3 text-gray-700">
                                {{ $postulante->dni }}
                            </td>

                            <td class="px-4 py-3 text-gray-700">
                                {{ $postulante->puesto }}
                            </td>

                            <td class="px-4 py-3 text-gray-700">
                                {{ $postulante->fecha_postulacion?->format('d/m/Y') }}
                            </td>

                            {{-- ESTADO --}}
                            <td class="px-4 py-3">

                                @if ($postulante->estado === 'Seleccionado')

                                    <span class="inline-flex rounded-full bg-green-100 px-3 py-1 text-xs font-medium text-green-700">
                                        Seleccionado
                                    </span>

                                @elseif ($postulante->estado === 'Rechazado')

                                    <span class="inline-flex rounded-full bg-red-100 px-3 py-1 text-xs font-medium text-red-700">
                                        Rechazado
                                    </span>

                                @elseif ($postulante->estado === 'Entrevista')

                                    <span class="inline-flex rounded-full bg-blue-100 px-3 py-1 text-xs font-medium text-blue-700">
                                        Entrevista
                                    </span>

                                @else

                                    <span class="inline-flex rounded-full bg-yellow-100 px-3 py-1 text-xs font-medium text-yellow-700">
                                        Postulando
                                    </span>

                                @endif

                            </td>

                            {{-- ACCIONES --}}
                            <td class="px-4 py-3 text-right">

                                <div class="flex justify-end gap-2">

                                    <button
                                        type="button"
                                        wire:click="editar({{ $postulante->id }})"
                                        class="rounded-lg border border-gray-300 px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100"
                                    >
                                        Editar
                                    </button>

                                    <button
                                        type="button"
                                        wire:click="eliminar({{ $postulante->id }})"
                                        wire:confirm="¿Estás seguro de eliminar este postulante?"
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
                                colspan="6"
                                class="px-4 py-8 text-center text-gray-500"
                            >
                                No hay postulantes registrados.
                            </td>
                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

        {{-- PAGINACIÓN --}}
        <div class="mt-5">
            {{ $postulantes->links() }}
        </div>

    </div>

</div>