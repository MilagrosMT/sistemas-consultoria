
<?php

use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component
{
    use WithPagination;

    public string $buscar = '';

    public string $filtroRol = '';

    public string $filtroEstado = '';

    public bool $mostrarFormulario = false;

    public ?int $usuarioEditando = null;

    public string $nombre = '';

    public string $email = '';

    public string $password = '';

    public string $role_id = '';

    public function updatingBuscar(): void
    {
        $this->resetPage();
    }

    public function updatingFiltroRol(): void
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
            'email',
            'password',
            'role_id',
        ]);

        $this->usuarioEditando = null;

        $this->resetValidation();

        $this->mostrarFormulario = true;
    }

    public function cerrarFormulario(): void
    {
        $this->mostrarFormulario = false;

        $this->reset([
            'nombre',
            'email',
            'password',
            'role_id',
        ]);

        $this->usuarioEditando = null;

        $this->resetValidation();
    }

    public function editar(int $id): void
    {
        $usuario = User::findOrFail($id);

        $this->usuarioEditando = $usuario->id;
        $this->nombre = $usuario->name;
        $this->email = $usuario->email;
        $this->role_id = (string) $usuario->role_id;
        $this->password = '';

        $this->resetValidation();

        $this->mostrarFormulario = true;
    }

    public function guardar(): void
    {
        $reglas = [
            'nombre' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'role_id' => 'required|exists:roles,id',
        ];

        $mensajes = [
            'nombre.required' => 'El nombre es obligatorio.',
            'nombre.max' => 'El nombre no puede superar los 255 caracteres.',
            'email.required' => 'El correo es obligatorio.',
            'email.email' => 'Ingresa un correo válido.',
            'role_id.required' => 'Selecciona un rol.',
            'role_id.exists' => 'El rol seleccionado no es válido.',
        ];

        if ($this->usuarioEditando === null) {
            $reglas['email'] .= '|unique:users,email';
            $reglas['password'] = 'required|string|min:8';

            $mensajes['email.unique'] = 'Este correo ya está registrado.';
            $mensajes['password.required'] = 'La contraseña es obligatoria.';
            $mensajes['password.min'] = 'La contraseña debe tener al menos 8 caracteres.';
        } else {
            $reglas['email'] .= '|unique:users,email,' . $this->usuarioEditando;
            $reglas['password'] = 'nullable|string|min:8';

            $mensajes['email.unique'] = 'Este correo ya está registrado.';
            $mensajes['password.min'] = 'La contraseña debe tener al menos 8 caracteres.';
        }

        $this->validate($reglas, $mensajes);

        if ($this->usuarioEditando === null) {

            User::create([
                'name' => $this->nombre,
                'email' => $this->email,
                'password' => Hash::make($this->password),
                'role_id' => $this->role_id,
                'activo' => true,
            ]);

            $mensaje = 'Usuario creado correctamente.';
        } else {

            $usuario = User::findOrFail($this->usuarioEditando);

            $usuario->name = $this->nombre;
            $usuario->email = $this->email;
            $usuario->role_id = $this->role_id;

            if ($this->password !== '') {
                $usuario->password = Hash::make($this->password);
            }

            $usuario->save();

            $mensaje = 'Usuario actualizado correctamente.';
        }

        $this->cerrarFormulario();

        session()->flash('success', $mensaje);
    }

    public function cambiarEstado(int $id): void
    {
        $usuario = User::findOrFail($id);

        if ($usuario->id === auth()->id()) {
            session()->flash(
                'error',
                'No puedes desactivar tu propio usuario.'
            );

            return;
        }

        $usuario->activo = ! $usuario->activo;
        $usuario->save();

        session()->flash(
            'success',
            $usuario->activo
                ? 'Usuario activado correctamente.'
                : 'Usuario desactivado correctamente.'
        );
    }

    public function eliminar(int $id): void
    {
        $usuario = User::findOrFail($id);

        if ($usuario->id === auth()->id()) {
            session()->flash(
                'error',
                'No puedes eliminar tu propio usuario.'
            );

            return;
        }

        $usuario->delete();

        session()->flash(
            'success',
            'Usuario eliminado correctamente.'
        );
    }

    public function render()
    {
        $usuarios = User::query()
            ->with('role')
            ->when(
                trim($this->buscar) !== '',
                function ($query) {
                    $buscar = trim($this->buscar);

                    $query->where(function ($query) use ($buscar) {
                        $query
                            ->where('name', 'like', '%' . $buscar . '%')
                            ->orWhere('email', 'like', '%' . $buscar . '%');
                    });
                }
            )
            ->when(
                $this->filtroRol !== '',
                function ($query) {
                    $query->where('role_id', $this->filtroRol);
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
            ->latest()
            ->paginate(10);

        return $this->view([
            'usuarios' => $usuarios,

            'roles' => Role::query()
                ->where('activo', true)
                ->orderBy('nombre')
                ->get(),

            'totalUsuarios' => User::count(),

            'usuariosActivos' => User::where('activo', true)->count(),

            'totalRoles' => Role::query()
                ->where('activo', true)
                ->count(),
        ]);
    }
};
?>

<div class="min-h-screen bg-zinc-50 px-4 py-6 dark:bg-zinc-950 sm:px-6 lg:px-8">

    <div class="mx-auto max-w-7xl">

        {{-- Encabezado --}}
        <div class="mb-8 flex items-center justify-between">

            <div>
                <p class="mb-2 text-sm text-zinc-500 dark:text-zinc-400">
                    Administración / Usuarios
                </p>

                <h1 class="text-3xl font-bold text-zinc-900 dark:text-white">
                    Usuarios
                </h1>

                <p class="mt-1 text-zinc-600 dark:text-zinc-400">
                    Administra los usuarios y sus roles de acceso.
                </p>
            </div>

            <flux:button
    variant="primary"
    icon="plus"
    wire:click="abrirFormulario"
>
    Nuevo usuario
</flux:button>

        </div>
{{-- Formulario nuevo usuario --}}
@if ($mostrarFormulario)

    <div class="mb-6 rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">

        <div class="mb-6 flex items-center justify-between">

            <div>
                <h2 class="text-xl font-semibold text-zinc-900 dark:text-white">
    {{ $usuarioEditando ? 'Editar usuario' : 'Nuevo usuario' }}
</h2>

               <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
    {{ $usuarioEditando
        ? 'Modifica los datos y permisos del usuario.'
        : 'Registra un nuevo usuario y asigna su rol.'
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

                {{-- Nombre --}}
                <div>
                    <flux:input
                        wire:model="nombre"
                        label="Nombre completo"
                        placeholder="Ej. Juan Pérez"
                    />

                    @error('nombre')
                        <p class="mt-1 text-sm text-red-600">
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                {{-- Correo --}}
                <div>
                    <flux:input
                        wire:model="email"
                        type="email"
                        label="Correo electrónico"
                        placeholder="usuario@correo.com"
                    />

                    @error('email')
                        <p class="mt-1 text-sm text-red-600">
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                {{-- Contraseña --}}
                <div>
                    <flux:input
                        wire:model="password"
                        type="password"
                        label="Contraseña"
                        placeholder="Mínimo 8 caracteres"
                    />

                    @error('password')
                        <p class="mt-1 text-sm text-red-600">
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                {{-- Rol --}}
                <div>

                    <flux:select
                        wire:model="role_id"
                        label="Rol"
                    >

                        <option value="">
                            Selecciona un rol
                        </option>

                        @foreach ($roles as $rol)

                            <option value="{{ $rol->id }}">
                                {{ $rol->nombre }}
                            </option>

                        @endforeach

                    </flux:select>

                    @error('role_id')
                        <p class="mt-1 text-sm text-red-600">
                            {{ $message }}
                        </p>
                    @enderror

                </div>

            </div>

            {{-- Botones --}}
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
    {{ $usuarioEditando ? 'Actualizar usuario' : 'Guardar usuario' }}
</flux:button>

            </div>

        </form>

    </div>

@endif
        {{-- Resumen --}}
        <div class="mb-6 grid gap-4 md:grid-cols-3">

            <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                <p class="text-sm text-zinc-500 dark:text-zinc-400">
                    Usuarios registrados
                </p>

                <p class="mt-2 text-3xl font-bold text-zinc-900 dark:text-white">
                    {{ $totalUsuarios }}
                </p>
            </div>

            <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                <p class="text-sm text-zinc-500 dark:text-zinc-400">
                    Usuarios activos
                </p>

                <p class="mt-2 text-3xl font-bold text-emerald-600">
                    {{ $usuariosActivos }}
                </p>
            </div>

            <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                <p class="text-sm text-zinc-500 dark:text-zinc-400">
                    Roles disponibles
                </p>

                <p class="mt-2 text-3xl font-bold text-indigo-600">
                    {{ $totalRoles }}
                </p>
            </div>

        </div>

        {{-- Tabla --}}
        <div class="overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900">

            {{-- Filtros --}}
            <div class="border-b border-zinc-200 p-4 dark:border-zinc-800">

                <div class="grid gap-3 md:grid-cols-3">

                    <flux:input
                        wire:model.live.debounce.300ms="buscar"
                        icon="magnifying-glass"
                        placeholder="Buscar por nombre o correo..."
                    />

                    <flux:select wire:model.live="filtroRol">
                        <option value="">Todos los roles</option>

                        @foreach ($roles as $rol)
                            <option value="{{ $rol->id }}">
                                {{ $rol->nombre }}
                            </option>
                        @endforeach
                    </flux:select>
<flux:select wire:model.live="filtroEstado">
    <option value="">Todos los estados</option>
    <option value="activo">Activo</option>
    <option value="inactivo">Inactivo</option>
</flux:select>
                </div>

            </div>

            {{-- Mensajes --}}
            @if (session('success'))
                <div class="border-b border-emerald-200 bg-emerald-50 px-5 py-3 text-sm text-emerald-700">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="border-b border-red-200 bg-red-50 px-5 py-3 text-sm text-red-700">
                    {{ session('error') }}
                </div>
            @endif

            {{-- Tabla --}}
            <div class="overflow-x-auto">

                <table class="w-full text-left text-sm">

                    <thead class="bg-zinc-50 text-xs uppercase text-zinc-500 dark:bg-zinc-950 dark:text-zinc-400">

                        <tr>
                            <th class="px-6 py-4">
                                Usuario
                            </th>

                            <th class="px-6 py-4">
                                Correo
                            </th>

                            <th class="px-6 py-4">
                                Rol
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

                        @forelse ($usuarios as $usuario)

                            <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50">

                                {{-- Usuario --}}
                                <td class="px-6 py-4">

                                    <div class="flex items-center gap-3">

                                        <flux:avatar
                                            :name="$usuario->name"
                                            :initials="$usuario->initials()"
                                        />

                                        <div>

                                            <p class="font-medium text-zinc-900 dark:text-white">
                                                {{ $usuario->name }}
                                            </p>

                                            @if ($usuario->id === auth()->id())
                                                <p class="text-xs text-zinc-500">
                                                    Usuario actual
                                                </p>
                                            @endif

                                        </div>

                                    </div>

                                </td>

                                {{-- Correo --}}
                                <td class="px-6 py-4 text-zinc-600 dark:text-zinc-300">
                                    {{ $usuario->email }}
                                </td>

                                {{-- Rol --}}
                                <td class="px-6 py-4">

                                    @if ($usuario->role)

                                        <span class="rounded-full bg-indigo-50 px-3 py-1 text-xs font-medium text-indigo-700 dark:bg-indigo-950/40 dark:text-indigo-300">
                                            {{ $usuario->role->nombre }}
                                        </span>

                                    @else

                                        <span class="text-xs text-zinc-400">
                                            Sin rol
                                        </span>

                                    @endif

                                </td>

                                {{-- Estado --}}
                               <td class="px-6 py-4">

    @if ($usuario->activo)

        <button
            type="button"
            wire:click="cambiarEstado({{ $usuario->id }})"
            class="inline-flex items-center gap-1.5 text-sm text-emerald-600 hover:text-emerald-700 dark:text-emerald-400 dark:hover:text-emerald-300"
        >
            <span class="size-2 rounded-full bg-emerald-500"></span>
            Activo
        </button>

    @else

        <button
            type="button"
            wire:click="cambiarEstado({{ $usuario->id }})"
            class="inline-flex items-center gap-1.5 text-sm text-red-600 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300"
        >
            <span class="size-2 rounded-full bg-red-500"></span>
            Inactivo
        </button>

    @endif

</td>

                                {{-- Acciones --}}
                                <td class="px-6 py-4 text-right">

    @if ($usuario->id !== auth()->id())

        <div class="flex justify-end gap-2">

            <flux:button
                wire:click="editar({{ $usuario->id }})"
                variant="ghost"
                icon="pencil"
            />

            <flux:button
                wire:click="eliminar({{ $usuario->id }})"
                wire:confirm="¿Estás seguro de eliminar este usuario?"
                variant="ghost"
                icon="trash"
            />

        </div>

    @else

        <span class="text-xs text-zinc-400">
            Cuenta actual
        </span>

    @endif

</td>

                            </tr>

                        @empty

                            <tr>

                                <td colspan="5" class="px-6 py-12 text-center">

                                    <p class="font-medium text-zinc-900 dark:text-white">
                                        No se encontraron usuarios
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

            {{-- Paginación --}}
            @if ($usuarios->hasPages())

                <div class="border-t border-zinc-200 px-6 py-4 dark:border-zinc-800">
                    {{ $usuarios->links() }}
                </div>

            @endif

        </div>

    </div>

</div>