
<?php

use App\Models\Empleado;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component
{
    use WithPagination;

    public string $buscar = '';
    public string $filtroEstado = '';

    public bool $mostrarFormulario = false;
    public ?int $empleadoEditando = null;

    public string $nombres = '';
    public string $apellidos = '';
    public string $dni = '';
    public string $cargo = '';
    public string $area = '';
    public string $fecha_ingreso = '';
    public string $salario = '';
    public string $estado = 'Activo';

    public function updatingBuscar(): void
    {
        $this->resetPage();
    }

    public function updatingFiltroEstado(): void
    {
        $this->resetPage();
    }

    public function abrirFormulario(): void
    {
        $this->reset([
            'nombres',
            'apellidos',
            'dni',
            'cargo',
            'area',
            'fecha_ingreso',
            'salario',
        ]);

        $this->estado = 'Activo';
        $this->empleadoEditando = null;

        $this->resetValidation();

        $this->mostrarFormulario = true;
    }

    public function cerrarFormulario(): void
    {
        $this->mostrarFormulario = false;

        $this->reset([
            'nombres',
            'apellidos',
            'dni',
            'cargo',
            'area',
            'fecha_ingreso',
            'salario',
        ]);

        $this->estado = 'Activo';
        $this->empleadoEditando = null;

        $this->resetValidation();
    }

    public function editar(int $id): void
    {
        $empleado = Empleado::findOrFail($id);

        $this->empleadoEditando = $empleado->id;

        $this->nombres = $empleado->nombres;
        $this->apellidos = $empleado->apellidos;
        $this->dni = $empleado->dni;
        $this->cargo = $empleado->cargo;
        $this->area = $empleado->area;

        $this->fecha_ingreso = $empleado->fecha_ingreso
            ? $empleado->fecha_ingreso->format('Y-m-d')
            : '';

        $this->salario = (string) $empleado->salario;
        $this->estado = $empleado->estado;

        $this->resetValidation();

        $this->mostrarFormulario = true;
    }

    public function guardar(): void
    {
        $reglas = [
            'nombres' => 'required|string|max:255',
            'apellidos' => 'required|string|max:255',
            'cargo' => 'required|string|max:255',
            'area' => 'required|string|max:255',
            'fecha_ingreso' => 'required|date',
            'salario' => 'required|numeric|min:0',
            'estado' => 'required|in:Activo,Inactivo',
        ];

        $mensajes = [
            'nombres.required' => 'Los nombres son obligatorios.',
            'apellidos.required' => 'Los apellidos son obligatorios.',
            'cargo.required' => 'El cargo es obligatorio.',
            'area.required' => 'El área es obligatoria.',
            'fecha_ingreso.required' => 'La fecha de ingreso es obligatoria.',
            'fecha_ingreso.date' => 'La fecha de ingreso no es válida.',
            'salario.required' => 'El salario es obligatorio.',
            'salario.numeric' => 'El salario debe ser numérico.',
            'salario.min' => 'El salario no puede ser negativo.',
            'estado.required' => 'El estado es obligatorio.',
        ];

        if ($this->empleadoEditando === null) {

            $reglas['dni'] = 'required|string|size:8|unique:empleados,dni';

            $mensajes['dni.required'] = 'El DNI es obligatorio.';
            $mensajes['dni.size'] = 'El DNI debe tener exactamente 8 dígitos.';
            $mensajes['dni.unique'] = 'Este DNI ya está registrado.';

        } else {

            $reglas['dni'] =
                'required|string|size:8|unique:empleados,dni,' .
                $this->empleadoEditando;

            $mensajes['dni.required'] = 'El DNI es obligatorio.';
            $mensajes['dni.size'] = 'El DNI debe tener exactamente 8 dígitos.';
            $mensajes['dni.unique'] = 'Este DNI ya está registrado.';
        }

        $this->validate($reglas, $mensajes);

        if ($this->empleadoEditando === null) {

            Empleado::create([
                'nombres' => $this->nombres,
                'apellidos' => $this->apellidos,
                'dni' => $this->dni,
                'cargo' => $this->cargo,
                'area' => $this->area,
                'fecha_ingreso' => $this->fecha_ingreso,
                'salario' => $this->salario,
                'estado' => $this->estado,
            ]);

            $mensaje = 'Empleado registrado correctamente.';

        } else {

            $empleado = Empleado::findOrFail($this->empleadoEditando);

            $empleado->update([
                'nombres' => $this->nombres,
                'apellidos' => $this->apellidos,
                'dni' => $this->dni,
                'cargo' => $this->cargo,
                'area' => $this->area,
                'fecha_ingreso' => $this->fecha_ingreso,
                'salario' => $this->salario,
                'estado' => $this->estado,
            ]);

            $mensaje = 'Empleado actualizado correctamente.';
        }

        $this->cerrarFormulario();

        session()->flash('mensaje', $mensaje);
    }

    public function cambiarEstado(int $id): void
    {
        $empleado = Empleado::findOrFail($id);

        $empleado->estado =
            $empleado->estado === 'Activo'
                ? 'Inactivo'
                : 'Activo';

        $empleado->save();

        session()->flash(
            'mensaje',
            $empleado->estado === 'Activo'
                ? 'Empleado activado correctamente.'
                : 'Empleado desactivado correctamente.'
        );
    }

    public function eliminar(int $id): void
    {
        $empleado = Empleado::findOrFail($id);

        $empleado->delete();

        session()->flash(
            'mensaje',
            'Empleado eliminado correctamente.'
        );
    }

    public function render()
    {
        $empleados = Empleado::query()
            ->when(
                trim($this->buscar) !== '',
                function ($query) {
                    $buscar = trim($this->buscar);

                    $query->where(function ($query) use ($buscar) {
                        $query
                            ->where('nombres', 'like', '%' . $buscar . '%')
                            ->orWhere('apellidos', 'like', '%' . $buscar . '%')
                            ->orWhere('dni', 'like', '%' . $buscar . '%')
                            ->orWhere('cargo', 'like', '%' . $buscar . '%')
                            ->orWhere('area', 'like', '%' . $buscar . '%');
                    });
                }
            )
            ->when(
                $this->filtroEstado !== '',
                function ($query) {
                    $query->where(
                        'estado',
                        $this->filtroEstado
                    );
                }
            )
            ->latest()
            ->paginate(10);

        return $this->view([
            'empleados' => $empleados,
        ]);
    }
};
?>

<div class="min-h-screen bg-zinc-50 px-4 py-6 dark:bg-zinc-950 sm:px-6 lg:px-8">

    <div class="mx-auto max-w-7xl">

        {{-- ENCABEZADO --}}
        <div class="mb-8 flex items-center justify-between">

            <div>
                <p class="mb-2 text-sm text-zinc-500 dark:text-zinc-400">
                    Administración / Empleados
                </p>

                <h1 class="text-3xl font-bold text-zinc-900 dark:text-white">
                    Empleados
                </h1>

                <p class="mt-1 text-zinc-600 dark:text-zinc-400">
                    Registra y administra el personal de la empresa.
                </p>
            </div>

            <flux:button
                variant="primary"
                icon="plus"
                wire:click="abrirFormulario"
            >
                Nuevo empleado
            </flux:button>

        </div>


        {{-- MENSAJE --}}
        @if (session('mensaje'))

            <div class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm text-emerald-700">
                ✓ {{ session('mensaje') }}
            </div>

        @endif


        {{-- FORMULARIO --}}
        @if ($mostrarFormulario)

            <div class="mb-6 rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">

                <div class="mb-6 flex items-center justify-between">

                    <div>
                        <h2 class="text-xl font-semibold text-zinc-900 dark:text-white">
                            {{ $empleadoEditando ? 'Editar empleado' : 'Nuevo empleado' }}
                        </h2>

                        <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                            {{ $empleadoEditando
                                ? 'Modifica los datos del empleado.'
                                : 'Registra los datos del nuevo empleado.'
                            }}
                        </p>
                    </div>

                    <flux:button
                        variant="ghost"
                        icon="x-mark"
                        wire:click="cerrarFormulario"
                    />

                </div>


                <form wire:submit="guardar">

                    <div class="grid gap-5 md:grid-cols-2">

                        <flux:input
                            wire:model="nombres"
                            label="Nombres"
                            placeholder="Ingrese los nombres"
                        />

                        <flux:input
                            wire:model="apellidos"
                            label="Apellidos"
                            placeholder="Ingrese los apellidos"
                        />

                        <flux:input
                            wire:model="dni"
                            label="DNI"
                            maxlength="8"
                            placeholder="8 dígitos"
                        />

                        <flux:input
                            wire:model="cargo"
                            label="Cargo"
                            placeholder="Ej. Contador"
                        />

                        <flux:input
                            wire:model="area"
                            label="Área"
                            placeholder="Ej. Contabilidad"
                        />

                        <flux:input
                            wire:model="fecha_ingreso"
                            type="date"
                            label="Fecha de ingreso"
                        />

                        <flux:input
                            wire:model="salario"
                            type="number"
                            step="0.01"
                            min="0"
                            label="Salario"
                            placeholder="0.00"
                        />

                        <flux:select
                            wire:model="estado"
                            label="Estado"
                        >
                            <option value="Activo">Activo</option>
                            <option value="Inactivo">Inactivo</option>
                        </flux:select>

                    </div>


                    {{-- ERRORES --}}
                    @if ($errors->any())

                        <div class="mt-5 rounded-lg bg-red-50 p-4 text-sm text-red-700">
                            Revisa los campos marcados antes de guardar.
                        </div>

                    @endif


                    <div class="mt-6 flex justify-end gap-3">

                        <flux:button
                            type="button"
                            variant="ghost"
                            wire:click="cerrarFormulario"
                        >
                            Cancelar
                        </flux:button>

                        <flux:button
                            type="submit"
                            variant="primary"
                        >
                            {{ $empleadoEditando ? 'Actualizar empleado' : 'Guardar empleado' }}
                        </flux:button>

                    </div>

                </form>

            </div>

        @endif


        {{-- TABLA --}}
        <div class="overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900">

            {{-- FILTROS --}}
            <div class="border-b border-zinc-200 p-4 dark:border-zinc-800">

                <div class="grid gap-3 md:grid-cols-2">

                    <flux:input
                        wire:model.live.debounce.300ms="buscar"
                        icon="magnifying-glass"
                        placeholder="Buscar por nombre, DNI, cargo o área..."
                    />

                    <flux:select wire:model.live="filtroEstado">

                        <option value="">
                            Todos los estados
                        </option>

                        <option value="Activo">
                            Activos
                        </option>

                        <option value="Inactivo">
                            Inactivos
                        </option>

                    </flux:select>

                </div>

            </div>


            {{-- TABLA --}}
            <div class="overflow-x-auto">

                <table class="w-full text-left text-sm">

                    <thead class="bg-zinc-50 text-xs uppercase text-zinc-500 dark:bg-zinc-950 dark:text-zinc-400">

                        <tr>

                            <th class="px-6 py-4">
                                DNI
                            </th>

                            <th class="px-6 py-4">
                                Empleado
                            </th>

                            <th class="px-6 py-4">
                                Cargo
                            </th>

                            <th class="px-6 py-4">
                                Área
                            </th>

                            <th class="px-6 py-4">
                                Ingreso
                            </th>

                            <th class="px-6 py-4">
                                Salario
                            </th>

                            <th class="px-6 py-4">
                                Estado
                            </th>

                            <th class="px-6 py-4 text-right">
                                Acciones
                            </th>

                        </tr>

                    </thead>


                    <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">

                        @forelse ($empleados as $empleado)

                            <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50">

                                <td class="px-6 py-4 font-medium text-zinc-900 dark:text-white">
                                    {{ $empleado->dni }}
                                </td>

                                <td class="px-6 py-4 text-zinc-700 dark:text-zinc-300">
                                    {{ $empleado->nombres }}
                                    {{ $empleado->apellidos }}
                                </td>

                                <td class="px-6 py-4 text-zinc-600 dark:text-zinc-300">
                                    {{ $empleado->cargo }}
                                </td>

                                <td class="px-6 py-4 text-zinc-600 dark:text-zinc-300">
                                    {{ $empleado->area }}
                                </td>

                                <td class="px-6 py-4 text-zinc-600 dark:text-zinc-300">
                                    {{ $empleado->fecha_ingreso?->format('d/m/Y') }}
                                </td>

                                <td class="px-6 py-4 font-medium text-zinc-900 dark:text-white">
                                    S/ {{ number_format($empleado->salario, 2) }}
                                </td>

                                <td class="px-6 py-4">

                                    @if ($empleado->estado === 'Activo')

                                        <button
                                            type="button"
                                            wire:click="cambiarEstado({{ $empleado->id }})"
                                            class="inline-flex items-center gap-1.5 text-sm text-emerald-600 hover:text-emerald-700"
                                        >
                                            <span class="size-2 rounded-full bg-emerald-500"></span>
                                            Activo
                                        </button>

                                    @else

                                        <button
                                            type="button"
                                            wire:click="cambiarEstado({{ $empleado->id }})"
                                            class="inline-flex items-center gap-1.5 text-sm text-red-600 hover:text-red-700"
                                        >
                                            <span class="size-2 rounded-full bg-red-500"></span>
                                            Inactivo
                                        </button>

                                    @endif

                                </td>


                                <td class="px-6 py-4 text-right">

                                    <div class="flex justify-end gap-2">

                                        <flux:button
                                            wire:click="editar({{ $empleado->id }})"
                                            variant="ghost"
                                            icon="pencil"
                                        />

                                        <flux:button
                                            wire:click="eliminar({{ $empleado->id }})"
                                            wire:confirm="¿Estás seguro de eliminar este empleado?"
                                            variant="ghost"
                                            icon="trash"
                                        />

                                    </div>

                                </td>

                            </tr>

                        @empty

                            <tr>

                                <td
                                    colspan="8"
                                    class="px-6 py-12 text-center"
                                >
                                    <p class="font-medium text-zinc-900 dark:text-white">
                                        No se encontraron empleados.
                                    </p>

                                    <p class="mt-1 text-sm text-zinc-500">
                                        Intenta modificar los filtros.
                                    </p>
                                </td>

                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>


            {{-- PAGINACIÓN --}}
            @if ($empleados->hasPages())

                <div class="border-t border-zinc-200 px-6 py-4 dark:border-zinc-800">
                    {{ $empleados->links() }}
                </div>

            @endif

        </div>

    </div>

</div>
