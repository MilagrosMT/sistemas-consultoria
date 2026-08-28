<?php

use App\Models\Role;
use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component
{
    use WithPagination;

    public string $buscar = '';

    public string $filtroEstado = '';

    public bool $mostrarFormulario = false;

    public ?int $rolEditando = null;

    public string $nombre = '';

    public string $descripcion = '';

    public bool $activo = true;

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
            'nombre',
            'descripcion',
        ]);

        $this->rolEditando = null;
        $this->activo = true;

        $this->resetValidation();

        $this->mostrarFormulario = true;
    }

    public function cerrarFormulario(): void
    {
        $this->mostrarFormulario = false;

        $this->reset([
            'nombre',
            'descripcion',
        ]);

        $this->rolEditando = null;
        $this->activo = true;

        $this->resetValidation();
    }

    public function guardar(): void
    {
        $reglas = [
            'nombre' => 'required|string|max:100',
            'descripcion' => 'nullable|string|max:255',
        ];

        $mensajes = [
            'nombre.required' => 'El nombre del rol es obligatorio.',
            'nombre.max' => 'El nombre no puede superar los 100 caracteres.',
            'descripcion.max' => 'La descripción no puede superar los 255 caracteres.',
        ];

        if ($this->rolEditando === null) {
            $reglas['nombre'] .= '|unique:roles,nombre';
            $mensajes['nombre.unique'] = 'Ya existe un rol con ese nombre.';
        } else {
            $reglas['nombre'] .= '|unique:roles,nombre,' . $this->rolEditando;
            $mensajes['nombre.unique'] = 'Ya existe otro rol con ese nombre.';
        }

        $this->validate($reglas, $mensajes);

        if ($this->rolEditando === null) {

            Role::create([
                'nombre' => $this->nombre,
                'descripcion' => $this->descripcion ?: null,
                'activo' => $this->activo,
            ]);

            $mensaje = 'Rol creado correctamente.';

        } else {

            $rol = Role::findOrFail($this->rolEditando);

            $rol->nombre = $this->nombre;
            $rol->descripcion = $this->descripcion ?: null;
            $rol->activo = $this->activo;

            $rol->save();

            $mensaje = 'Rol actualizado correctamente.';
        }

        $this->cerrarFormulario();

        session()->flash('success', $mensaje);
    }

    public function editar(int $id): void
    {
        $rol = Role::findOrFail($id);

        $this->rolEditando = $rol->id;
        $this->nombre = $rol->nombre;
        $this->descripcion = $rol->descripcion ?? '';
        $this->activo = (bool) $rol->activo;

        $this->resetValidation();

        $this->mostrarFormulario = true;
    }

    public function cambiarEstado(int $id): void
    {
        $rol = Role::findOrFail($id);

        $rol->activo = ! $rol->activo;
        $rol->save();

        session()->flash(
            'success',
            $rol->activo
                ? 'Rol activado correctamente.'
                : 'Rol desactivado correctamente.'
        );
    }

    public function eliminar(int $id): void
    {
        $rol = Role::findOrFail($id);

        $usuariosAsignados = User::where('role_id', $rol->id)->count();

        if ($usuariosAsignados > 0) {
            session()->flash(
                'error',
                'No puedes eliminar este rol porque tiene usuarios asignados.'
            );

            return;
        }

        $rol->delete();

        session()->flash(
            'success',
            'Rol eliminado correctamente.'
        );
    }

    public function render()
    {
        $roles = Role::query()
            ->withCount('users')
            ->when(
    trim($this->buscar) !== '',
    function ($query) {
        $buscar = trim($this->buscar);

        $query->where(function ($query) use ($buscar) {
            $query
                ->where('nombre', 'like', '%' . $buscar . '%')
                ->orWhere('descripcion', 'like', '%' . $buscar . '%');
        });
    }
)
            ->when(
                $this->filtroEstado !== '',
                function ($query) {
                    $query->where(
                        'activo',
                        $this->filtroEstado === 'activo'
                    );
                }
            )
            ->orderBy('nombre')
            ->paginate(10);

        return $this->view([
            'roles' => $roles,

            'totalRoles' => Role::count(),

            'rolesActivos' => Role::where('activo', true)->count(),

            'rolesInactivos' => Role::where('activo', false)->count(),

            'usuariosAsignados' => User::count(),
        ]);
    }
};
?>

<div class="min-h-screen bg-zinc-50 px-4 py-6 dark:bg-zinc-950 sm:px-6 lg:px-8">

    <div class="mx-auto max-w-7xl">

        {{-- Encabezado --}}
        <div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">

            <div>

                <div class="mb-2 flex items-center gap-2 text-sm text-zinc-500 dark:text-zinc-400">
                    <span>Administración</span>
                    <span>/</span>
                    <span>Roles</span>
                </div>

                <flux:heading size="xl">
                    Roles
                </flux:heading>

                <flux:text class="mt-1">
                    Administra los roles y permisos de acceso del sistema.
                </flux:text>

            </div>

            <flux:button
                variant="primary"
                icon="plus"
                wire:click="abrirFormulario"
            >
                Nuevo rol
            </flux:button>

        </div>

        {{-- Formulario --}}
        @if ($mostrarFormulario)

            <div class="mb-6 rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">

                <div class="mb-6">

                    <flux:heading size="lg">
                        {{ $rolEditando ? 'Editar rol' : 'Nuevo rol' }}
                    </flux:heading>

                    <flux:text class="mt-1">
                        {{ $rolEditando
                            ? 'Modifica la información del rol.'
                            : 'Registra un nuevo rol para el sistema.'
                        }}
                    </flux:text>

                </div>

                <form wire:submit="guardar">

                    <div class="grid gap-5 md:grid-cols-2">

                        <flux:input
                            wire:model="nombre"
                            label="Nombre del rol"
                            placeholder="Ej. Supervisor"
                        />

                        <flux:input
                            wire:model="descripcion"
                            label="Descripción"
                            placeholder="Describe las funciones del rol"
                        />

                    </div>

                    @error('nombre')
                        <p class="mt-2 text-sm text-red-600">
                            {{ $message }}
                        </p>
                    @enderror

                    @error('descripcion')
                        <p class="mt-2 text-sm text-red-600">
                            {{ $message }}
                        </p>
                    @enderror

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
                            {{ $rolEditando ? 'Actualizar rol' : 'Guardar rol' }}
                        </flux:button>

                    </div>

                </form>

            </div>

        @endif

        {{-- Tarjetas --}}
        <div class="mb-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">

            <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">

                <div class="flex items-center justify-between">

                    <div>
                        <p class="text-sm font-medium text-zinc-500 dark:text-zinc-400">
                            Roles registrados
                        </p>

                        <p class="mt-2 text-3xl font-semibold text-zinc-900 dark:text-white">
                            {{ $totalRoles }}
                        </p>
                    </div>

                    <div class="rounded-xl bg-zinc-100 p-3 dark:bg-zinc-800">
                        <flux:icon.shield-check class="size-6 text-zinc-600 dark:text-zinc-300" />
                    </div>

                </div>

            </div>

            <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">

                <div class="flex items-center justify-between">

                    <div>
                        <p class="text-sm font-medium text-zinc-500 dark:text-zinc-400">
                            Roles activos
                        </p>

                        <p class="mt-2 text-3xl font-semibold text-zinc-900 dark:text-white">
                            {{ $rolesActivos }}
                        </p>
                    </div>

                    <div class="rounded-xl bg-emerald-50 p-3 dark:bg-emerald-950/30">
                        <flux:icon.check-circle class="size-6 text-emerald-600 dark:text-emerald-400" />
                    </div>

                </div>

            </div>

            <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">

                <div class="flex items-center justify-between">

                    <div>
                        <p class="text-sm font-medium text-zinc-500 dark:text-zinc-400">
                            Roles inactivos
                        </p>

                        <p class="mt-2 text-3xl font-semibold text-zinc-900 dark:text-white">
                            {{ $rolesInactivos }}
                        </p>
                    </div>

                    <div class="rounded-xl bg-red-50 p-3 dark:bg-red-950/30">
                        <flux:icon.x-circle class="size-6 text-red-600 dark:text-red-400" />
                    </div>

                </div>

            </div>

        </div>

        {{-- Mensajes --}}
        @if (session('success'))

            <div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-5 py-3 text-sm text-emerald-700 dark:border-emerald-900 dark:bg-emerald-950/30 dark:text-emerald-300">
                {{ session('success') }}
            </div>

        @endif

        @if (session('error'))

            <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-5 py-3 text-sm text-red-700 dark:border-red-900 dark:bg-red-950/30 dark:text-red-300">
                {{ session('error') }}
            </div>

        @endif

        {{-- Tabla --}}
        <div class="overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900">

            {{-- Filtros --}}
            <div class="border-b border-zinc-200 p-4 dark:border-zinc-800">

                <div class="grid gap-3 md:grid-cols-2">

                    <flux:input
                        wire:model.live.debounce.300ms="buscar"
                        icon="magnifying-glass"
                        placeholder="Buscar por nombre o descripción..."
                    />

                    <flux:select wire:model.live="filtroEstado">

                        <option value="">
                            Todos los estados
                        </option>

                        <option value="activo">
                            Activo
                        </option>

                        <option value="inactivo">
                            Inactivo
                        </option>

                    </flux:select>

                </div>

            </div>

            {{-- Tabla --}}
            <div class="overflow-x-auto">

                <table class="w-full text-left text-sm">

                    <thead class="bg-zinc-50 text-xs uppercase tracking-wider text-zinc-500 dark:bg-zinc-950 dark:text-zinc-400">

                        <tr>

                            <th class="px-6 py-4 font-medium">
                                Rol
                            </th>

                            <th class="px-6 py-4 font-medium">
                                Descripción
                            </th>

                            <th class="px-6 py-4 font-medium">
                                Usuarios
                            </th>

                            <th class="px-6 py-4 font-medium">
                                Estado
                            </th>

                            <th class="px-6 py-4 text-right font-medium">
                                Acciones
                            </th>

                        </tr>

                    </thead>

                    <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">

                        @forelse ($roles as $rol)

                            <tr class="transition hover:bg-zinc-50 dark:hover:bg-zinc-800/50">

                                <td class="px-6 py-4">

                                    <div class="flex items-center gap-3">

                                        <div class="rounded-xl bg-indigo-50 p-2 dark:bg-indigo-950/40">
                                            <flux:icon.users class="size-5 text-indigo-600 dark:text-indigo-400" />
                                        </div>

                                        <div>
                                            <p class="font-medium text-zinc-900 dark:text-white">
                                                {{ $rol->nombre }}
                                            </p>
                                        </div>

                                    </div>

                                </td>

                                <td class="px-6 py-4 text-zinc-600 dark:text-zinc-300">

                                    {{ $rol->descripcion ?: 'Sin descripción' }}

                                </td>

                                <td class="px-6 py-4">

                                    <span class="font-medium text-zinc-900 dark:text-white">
                                        {{ $rol->users_count }}
                                    </span>

                                </td>

                                <td class="px-6 py-4">

                                    @if ($rol->activo)

                                        <button
                                            type="button"
                                            wire:click="cambiarEstado({{ $rol->id }})"
                                            class="inline-flex items-center gap-1.5 text-sm text-emerald-600 hover:text-emerald-700 dark:text-emerald-400"
                                        >
                                            <span class="size-2 rounded-full bg-emerald-500"></span>
                                            Activo
                                        </button>

                                    @else

                                        <button
                                            type="button"
                                            wire:click="cambiarEstado({{ $rol->id }})"
                                            class="inline-flex items-center gap-1.5 text-sm text-red-600 hover:text-red-700 dark:text-red-400"
                                        >
                                            <span class="size-2 rounded-full bg-red-500"></span>
                                            Inactivo
                                        </button>

                                    @endif

                                </td>

                                <td class="px-6 py-4">

                                    <div class="flex justify-end gap-2">

                                        <flux:button
                                            wire:click="editar({{ $rol->id }})"
                                            variant="ghost"
                                            icon="pencil"
                                        />

                                        <flux:button
                                            wire:click="eliminar({{ $rol->id }})"
                                            wire:confirm="¿Estás seguro de eliminar este rol?"
                                            variant="ghost"
                                            icon="trash"
                                        />

                                    </div>

                                </td>

                            </tr>

                        @empty

                            <tr>

                                <td colspan="5" class="px-6 py-12 text-center">

                                    <div class="flex flex-col items-center">

                                        <div class="mb-3 rounded-full bg-zinc-100 p-4 dark:bg-zinc-800">
                                            <flux:icon.users class="size-7 text-zinc-400" />
                                        </div>

                                        <p class="font-medium text-zinc-900 dark:text-white">
                                            No se encontraron roles
                                        </p>

                                        <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                                            Intenta modificar los filtros de búsqueda.
                                        </p>

                                    </div>

                                </td>

                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>

            {{-- Paginación --}}
            @if ($roles->hasPages())

                <div class="border-t border-zinc-200 px-6 py-4 dark:border-zinc-800">
                    {{ $roles->links() }}
                </div>

            @endif

        </div>

    </div>

</div>