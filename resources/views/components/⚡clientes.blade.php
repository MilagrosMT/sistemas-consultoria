<?php

use App\Models\Cliente;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component
{
    use WithPagination;

    public string $buscar = '';

    public bool $mostrarFormulario = false;

    public ?int $clienteEditando = null;

    public string $ruc = '';
    public string $razon_social = '';
    public string $nombre_comercial = '';
    public string $direccion = '';
    public string $telefono = '';
    public string $correo = '';
    public string $servicio_contratado = '';
public string $servicio_id = '';
    public string $estado = 'Activo';

    public function updatedBuscar(): void
    {
        $this->resetPage();
    }

    public function nuevoCliente(): void
    {
        $this->resetFormulario();
        $this->mostrarFormulario = true;
    }

    public function editarCliente(int $id): void
    {
        $cliente = Cliente::findOrFail($id);

        $this->clienteEditando = $cliente->id;
        $this->ruc = $cliente->ruc;
        $this->razon_social = $cliente->razon_social;
        $this->nombre_comercial = $cliente->nombre_comercial ?? '';
        $this->direccion = $cliente->direccion ?? '';
        $this->telefono = $cliente->telefono ?? '';
        $this->correo = $cliente->correo ?? '';
        $this->servicio_contratado = $cliente->servicio_contratado;
$this->servicio_id = $cliente->servicio_id ? (string) $cliente->servicio_id : '';
$this->estado = $cliente->estado;

        $this->mostrarFormulario = true;
    }

public function guardarCliente(): void
{
    $datos = $this->validate([
        'ruc' => 'required|digits:11|unique:clientes,ruc,' . ($this->clienteEditando ?? 'NULL'),
        'razon_social' => 'required|string|max:255',
        'nombre_comercial' => 'nullable|string|max:255',
        'direccion' => 'nullable|string|max:255',
        'telefono' => 'nullable|string|max:20',
        'correo' => 'nullable|email|max:255',
        'servicio_id' => 'required|exists:servicios,id',
        'estado' => 'required|in:Activo,Inactivo',
    ]);

    $servicio = \App\Models\Servicio::findOrFail($this->servicio_id);

    $datos['servicio_contratado'] = $servicio->nombre;

    if ($this->clienteEditando) {
        Cliente::findOrFail($this->clienteEditando)->update($datos);
    } else {
        Cliente::create($datos);
    }

    $this->resetFormulario();
    $this->mostrarFormulario = false;
}

    public function cambiarEstado(int $id): void
    {
        $cliente = Cliente::findOrFail($id);

        $cliente->update([
            'estado' => $cliente->estado === 'Activo'
                ? 'Inactivo'
                : 'Activo',
        ]);
    }

    public function cancelar(): void
    {
        $this->resetFormulario();
        $this->mostrarFormulario = false;
    }

    private function resetFormulario(): void
    {
        $this->reset([
            'clienteEditando',
            'ruc',
            'razon_social',
            'nombre_comercial',
            'direccion',
            'telefono',
            'correo',
            'servicio_contratado',
'servicio_id',
        ]);

        $this->estado = 'Activo';
    }

    public function with(): array
    {
        $consulta = Cliente::query();

        if ($this->buscar) {
            $consulta->where(function ($q) {
                $q->where('ruc', 'like', '%' . $this->buscar . '%')
                    ->orWhere('razon_social', 'like', '%' . $this->buscar . '%')
                    ->orWhere('nombre_comercial', 'like', '%' . $this->buscar . '%');
            });
        }

return [
    'clientes' => $consulta->latest()->paginate(10),

    'totalClientes' => Cliente::count(),

    'clientesActivos' => Cliente::where('estado', 'Activo')->count(),

    'clientesInactivos' => Cliente::where('estado', 'Inactivo')->count(),

    'servicios' => Cliente::whereNotNull('servicio_contratado')
        ->distinct('servicio_contratado')
        ->count('servicio_contratado'),

    'serviciosDisponibles' => \App\Models\Servicio::where('estado', 'Activo')
        ->orderBy('nombre')
        ->get(),
];
    }
};
?>

<div class="min-h-screen bg-zinc-50 px-4 py-6 text-zinc-900 transition-colors dark:bg-zinc-950 dark:text-white sm:px-6 lg:px-8">

    {{-- ENCABEZADO --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">

        <div class="flex items-center gap-4">

            <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-zinc-100 text-zinc-700 shadow-sm dark:bg-zinc-800 dark:text-white dark:shadow-black/20">
                <svg xmlns="http://www.w3.org/2000/svg"
                     class="h-6 w-6"
                     fill="none"
                     viewBox="0 0 24 24"
                     stroke="currentColor"
                     stroke-width="1.8">
                    <path stroke-linecap="round"
                          stroke-linejoin="round"
                          d="M3 21h18M5 21V7l7-4 7 4v14M9 21v-4h6v4M8 9h.01M12 9h.01M16 9h.01M8 13h.01M12 13h.01M16 13h.01"/>
                </svg>
            </div>

            <div>
                <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">
                    Clientes
                </h1>

                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                    Gestión de empresas clientes de la consultora
                </p>
            </div>

        </div>


        <button
            wire:click="nuevoCliente"
            type="button"
            class="inline-flex items-center justify-center gap-2 rounded-xl bg-teal-500 px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-teal-500/20 transition hover:bg-teal-600 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:ring-offset-2 dark:focus:ring-offset-black"
        >

            <svg xmlns="http://www.w3.org/2000/svg"
                 class="h-5 w-5"
                 fill="none"
                 viewBox="0 0 24 24"
                 stroke="currentColor"
                 stroke-width="2">
                <path stroke-linecap="round"
                      stroke-linejoin="round"
                      d="M12 4v16m8-8H4"/>
            </svg>

            Nuevo cliente

        </button>

    </div>


    {{-- TARJETAS DE INDICADORES --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">

        {{-- TOTAL --}}
        <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">

            <div class="flex items-center justify-between">

                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">
                        Total clientes
                    </p>

                    <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">
                        {{ $totalClientes }}
                    </p>

                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-500">
                        Empresas registradas
                    </p>
                </div>

                <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-zinc-100 text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300">

                    <svg xmlns="http://www.w3.org/2000/svg"
                         class="h-6 w-6"
                         fill="none"
                         viewBox="0 0 24 24"
                         stroke="currentColor"
                         stroke-width="1.8">
                        <path stroke-linecap="round"
                              stroke-linejoin="round"
                              d="M3 21h18M5 21V7l7-4 7 4v14M9 21v-4h6v4"/>
                    </svg>

                </div>

            </div>

        </div>


        {{-- ACTIVOS --}}
        <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">

            <div class="flex items-center justify-between">

                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">
                        Clientes activos
                    </p>

                    <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">
                        {{ $clientesActivos }}
                    </p>

                    <p class="mt-1 text-xs text-green-600 dark:text-green-400">
                        Actualmente atendidos
                    </p>
                </div>

                <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-green-100 text-green-600 dark:bg-green-500/10 dark:text-green-400">

                    <svg xmlns="http://www.w3.org/2000/svg"
                         class="h-6 w-6"
                         fill="none"
                         viewBox="0 0 24 24"
                         stroke="currentColor"
                         stroke-width="2">
                        <path stroke-linecap="round"
                              stroke-linejoin="round"
                              d="M5 13l4 4L19 7"/>
                    </svg>

                </div>

            </div>

        </div>


        {{-- INACTIVOS --}}
        <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">

            <div class="flex items-center justify-between">

                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">
                        Clientes inactivos
                    </p>

                    <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">
                        {{ $clientesInactivos }}
                    </p>

                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        Sin atención activa
                    </p>
                </div>

                <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400">

                    <svg xmlns="http://www.w3.org/2000/svg"
                         class="h-6 w-6"
                         fill="none"
                         viewBox="0 0 24 24"
                         stroke="currentColor"
                         stroke-width="1.8">
                        <path stroke-linecap="round"
                              stroke-linejoin="round"
                              d="M18.364 5.636A9 9 0 115.636 18.364 9 9 0 0118.364 5.636z"/>
                        <path stroke-linecap="round"
                              stroke-linejoin="round"
                              d="M9 9l6 6M15 9l-6 6"/>
                    </svg>

                </div>

            </div>

        </div>


        {{-- SERVICIOS --}}
        <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">

            <div class="flex items-center justify-between">

                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">
                        Servicios
                    </p>

                    <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">
                        {{ $servicios }}
                    </p>

                    <p class="mt-1 text-xs text-teal-700 dark:text-teal-400">
                        Tipos contratados
                    </p>
                </div>

                <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-teal-50 text-teal-700 dark:bg-teal-500/10 dark:text-teal-400">

                    <svg xmlns="http://www.w3.org/2000/svg"
                         class="h-6 w-6"
                         fill="none"
                         viewBox="0 0 24 24"
                         stroke="currentColor"
                         stroke-width="1.8">
                        <path stroke-linecap="round"
                              stroke-linejoin="round"
                              d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h7l5 5v11a2 2 0 01-2 2z"/>
                        <path stroke-linecap="round"
                              stroke-linejoin="round"
                              d="M14 3v5h5"/>
                    </svg>

                </div>

            </div>

        </div>

    </div>


    {{-- BUSCADOR --}}
    <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">

        <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">

            <div>
                <h2 class="font-semibold text-gray-900 dark:text-white">
                    Empresas clientes
                </h2>

                <p class="text-xs text-gray-500 dark:text-gray-400">
                    Busque y gestione la información de sus clientes.
                </p>
            </div>

            <div class="relative w-full md:max-w-2xl">

                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">

                    <svg xmlns="http://www.w3.org/2000/svg"
                         class="h-5 w-5"
                         fill="none"
                         viewBox="0 0 24 24"
                         stroke="currentColor"
                         stroke-width="2">
                        <path stroke-linecap="round"
                              stroke-linejoin="round"
                              d="M21 21l-4.35-4.35m2.35-5.65a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>

                </div>

                <input
                    type="text"
                    wire:model.live="buscar"
                    placeholder="Buscar por RUC o razón social..."
                    aria-label="Buscar clientes"
                    class="w-full rounded-xl border border-zinc-300 bg-white py-3 pl-10 pr-4 text-sm text-zinc-900 outline-none transition placeholder:text-zinc-400 focus:border-teal-500 focus:ring-2 focus:ring-teal-500 dark:border-zinc-700 dark:bg-zinc-950 dark:text-white dark:placeholder:text-zinc-500 dark:focus:border-teal-500"
                >

            </div>

        </div>

    </div>


    {{-- FORMULARIO --}}
    @if ($mostrarFormulario)

        <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">

            <div class="mb-6 flex items-start gap-3 border-b border-gray-200 pb-5 dark:border-gray-800">

                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-teal-500/10 text-teal-400 dark:bg-teal-500/10 dark:text-teal-400">

                    <svg xmlns="http://www.w3.org/2000/svg"
                         class="h-5 w-5"
                         fill="none"
                         viewBox="0 0 24 24"
                         stroke="currentColor"
                         stroke-width="1.8">
                        <path stroke-linecap="round"
                              stroke-linejoin="round"
                              d="M12 4v16m8-8H4"/>
                    </svg>

                </div>

                <div>

                    <h2 class="text-lg font-bold text-gray-900 dark:text-white">
                        {{ $clienteEditando ? 'Editar cliente' : 'Registrar nuevo cliente' }}
                    </h2>

                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                        Complete la información de la empresa cliente.
                    </p>

                </div>

            </div>


            <div class="grid grid-cols-1 gap-5 md:grid-cols-2">

                {{-- RUC --}}
                <div>

                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200">
                        RUC <span class="text-red-500">*</span>
                    </label>

                    <input
                        type="text"
                        wire:model="ruc"
                        maxlength="11"
                        placeholder="Ej. 20123456789"
                        aria-label="RUC"
                        class="mt-1.5 w-full rounded-xl border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900 placeholder:text-gray-400 outline-none focus:border-teal-500 focus:ring-2 focus:ring-teal-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white dark:placeholder:text-gray-500"
                    >

                    @error('ruc')
                        <span class="mt-1 block text-xs font-medium text-red-600 dark:text-red-400">
                            {{ $message }}
                        </span>
                    @enderror

                </div>


                {{-- RAZÃ“N SOCIAL --}}
                <div>

                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200">
                        RazÃ³n social <span class="text-red-500">*</span>
                    </label>

                    <input
                        type="text"
                        wire:model="razon_social"
                        placeholder="Ej. Empresa S.A.C."
                        aria-label="RazÃ³n social"
                        class="mt-1.5 w-full rounded-xl border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900 placeholder:text-gray-400 outline-none focus:border-teal-500 focus:ring-2 focus:ring-teal-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white dark:placeholder:text-gray-500"
                    >

                    @error('razon_social')
                        <span class="mt-1 block text-xs font-medium text-red-600 dark:text-red-400">
                            {{ $message }}
                        </span>
                    @enderror

                </div>


                {{-- NOMBRE COMERCIAL --}}
                <div>

                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200">
                        Nombre comercial
                    </label>

                    <input
                        type="text"
                        wire:model="nombre_comercial"
                        placeholder="Nombre comercial"
                        aria-label="Nombre comercial"
                        class="mt-1.5 w-full rounded-xl border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900 placeholder:text-gray-400 outline-none focus:border-teal-500 focus:ring-2 focus:ring-teal-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white dark:placeholder:text-gray-500"
                    >

                </div>


                {{-- TELÃ‰FONO --}}
                <div>

                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200">
                        Teléfono
                    </label>

                    <input
                        type="text"
                        wire:model="telefono"
                        placeholder="Ej. 987654321"
                        aria-label="Teléfono"
                        class="mt-1.5 w-full rounded-xl border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900 placeholder:text-gray-400 outline-none focus:border-teal-500 focus:ring-2 focus:ring-teal-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white dark:placeholder:text-gray-500"
                    >

                </div>


                {{-- CORREO --}}
                <div>

                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200">
                        Correo electrónico
                    </label>

                    <input
                        type="email"
                        wire:model="correo"
                        placeholder="contacto@empresa.com"
                        aria-label="Correo electrónico"
                        class="mt-1.5 w-full rounded-xl border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900 placeholder:text-gray-400 outline-none focus:border-teal-500 focus:ring-2 focus:ring-teal-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white dark:placeholder:text-gray-500"
                    >

                    @error('correo')
                        <span class="mt-1 block text-xs font-medium text-red-600 dark:text-red-400">
                            {{ $message }}
                        </span>
                    @enderror

                </div>


{{-- SERVICIO --}}
<div>

    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200">
        Servicio contratado <span class="text-red-500">*</span>
    </label>

    <select
        wire:model="servicio_id"
        aria-label="Servicio contratado"
        class="mt-1.5 w-full rounded-xl border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900 outline-none focus:border-teal-500 focus:ring-2 focus:ring-teal-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
    >
        <option value="">Seleccione un servicio</option>

        @foreach ($serviciosDisponibles as $servicio)
            <option value="{{ $servicio->id }}">
                {{ $servicio->nombre }} — S/ {{ number_format($servicio->precio, 2) }}
            </option>
        @endforeach

    </select>

    @error('servicio_id')
        <span class="mt-1 block text-xs font-medium text-red-600 dark:text-red-400">
            {{ $message }}
        </span>
    @enderror

</div>


                {{-- DIRECCIÃ“N --}}
                <div class="md:col-span-2">

                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200">
                        Dirección
                    </label>

                    <input
                        type="text"
                        wire:model="direccion"
                        placeholder="Dirección de la empresa"
                        aria-label="Dirección"
                        class="mt-1.5 w-full rounded-xl border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900 placeholder:text-gray-400 outline-none focus:border-teal-500 focus:ring-2 focus:ring-teal-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white dark:placeholder:text-gray-500"
                    >

                </div>


                {{-- ESTADO --}}
                <div>

                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200">
                        Estado
                    </label>

                    <select
                        wire:model="estado"
                        aria-label="Estado"
                        class="mt-1.5 w-full rounded-xl border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900 outline-none focus:border-teal-500 focus:ring-2 focus:ring-teal-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                    >
                        <option value="Activo">Activo</option>
                        <option value="Inactivo">Inactivo</option>
                    </select>

                </div>

            </div>


            {{-- BOTONES --}}
            <div class="mt-7 flex flex-col-reverse gap-3 border-t border-gray-200 pt-5 sm:flex-row sm:justify-end dark:border-gray-800">

                <button
                    wire:click="cancelar"
                    type="button"
                    class="inline-flex items-center justify-center gap-2 rounded-xl border border-zinc-200 bg-white px-5 py-2.5 text-sm font-semibold text-zinc-700 transition hover:bg-zinc-50 focus:outline-none focus:ring-2 focus:ring-zinc-400 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-200 dark:hover:bg-zinc-800"
                >

                    <svg xmlns="http://www.w3.org/2000/svg"
                         class="h-4 w-4"
                         fill="none"
                         viewBox="0 0 24 24"
                         stroke="currentColor"
                         stroke-width="2">
                        <path stroke-linecap="round"
                              stroke-linejoin="round"
                              d="M6 18L18 6M6 6l12 12"/>
                    </svg>

                    Cancelar

                </button>


                <button
                    wire:click="guardarCliente"
                    type="button"
                    class="inline-flex items-center justify-center gap-2 rounded-xl bg-teal-500 px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-teal-500/20 transition hover:bg-teal-600 focus:outline-none focus:ring-2 focus:ring-teal-500"
                >

                    <svg xmlns="http://www.w3.org/2000/svg"
                         class="h-4 w-4"
                         fill="none"
                         viewBox="0 0 24 24"
                         stroke="currentColor"
                         stroke-width="2">
                        <path stroke-linecap="round"
                              stroke-linejoin="round"
                              d="M5 13l4 4L19 7"/>
                    </svg>

                    {{ $clienteEditando ? 'Actualizar cliente' : 'Guardar cliente' }}

                </button>

            </div>

        </div>

    @endif


    {{-- TABLA --}}
    <div class="overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900">

        <div class="border-b border-gray-200 px-5 py-4 dark:border-gray-800">

            <div class="flex items-center gap-3">

                <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-teal-500/10 text-teal-400 dark:bg-teal-500/10 dark:text-teal-400">

                    <svg xmlns="http://www.w3.org/2000/svg"
                         class="h-5 w-5"
                         fill="none"
                         viewBox="0 0 24 24"
                         stroke="currentColor"
                         stroke-width="1.8">
                        <path stroke-linecap="round"
                              stroke-linejoin="round"
                              d="M3 21h18M5 21V7l7-4 7 4v14"/>
                    </svg>

                </div>

                <div>

                    <h2 class="font-semibold text-gray-900 dark:text-white">
                        Listado de clientes
                    </h2>

                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        Empresas atendidas por la consultora.
                    </p>

                </div>

            </div>

        </div>


        <div class="overflow-x-auto">

            <table class="min-w-full text-sm">

                <thead class="border-b border-zinc-200 bg-zinc-50 dark:border-zinc-800 dark:bg-zinc-950">

                    <tr>

                        <th class="px-5 py-3 text-left font-semibold text-gray-700 dark:text-gray-300">
                            Empresa
                        </th>

                        <th class="px-5 py-3 text-left font-semibold text-gray-700 dark:text-gray-300">
                            RUC
                        </th>

                        <th class="px-5 py-3 text-left font-semibold text-gray-700 dark:text-gray-300">
                            Servicio
                        </th>

                        <th class="px-5 py-3 text-left font-semibold text-gray-700 dark:text-gray-300">
                            Contacto
                        </th>

                        <th class="px-5 py-3 text-left font-semibold text-gray-700 dark:text-gray-300">
                            Estado
                        </th>

                        <th class="px-5 py-3 text-right font-semibold text-gray-700 dark:text-gray-300">
                            Acciones
                        </th>

                    </tr>

                </thead>


                <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">

                    @forelse ($clientes as $cliente)

                        <tr class="transition hover:bg-zinc-50 dark:hover:bg-zinc-800/50">

                            {{-- EMPRESA --}}
                            <td class="px-5 py-4">

                                <div class="flex items-center gap-3">

                                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-300">

                                        <svg xmlns="http://www.w3.org/2000/svg"
                                             class="h-5 w-5"
                                             fill="none"
                                             viewBox="0 0 24 24"
                                             stroke="currentColor"
                                             stroke-width="1.8">
                                            <path stroke-linecap="round"
                                                  stroke-linejoin="round"
                                                  d="M3 21h18M5 21V7l7-4 7 4v14M9 21v-4h6v4"/>
                                        </svg>

                                    </div>

                                    <div>

                                        <div class="font-semibold text-gray-900 dark:text-white">
                                            {{ $cliente->razon_social }}
                                        </div>

                                        @if ($cliente->nombre_comercial)

                                            <div class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">
                                                {{ $cliente->nombre_comercial }}
                                            </div>

                                        @endif

                                    </div>

                                </div>

                            </td>


                            {{-- RUC --}}
                            <td class="whitespace-nowrap px-5 py-4 font-medium text-gray-700 dark:text-gray-300">
                                {{ $cliente->ruc }}
                            </td>


                            {{-- SERVICIO --}}
                            <td class="px-5 py-4">

                                <span class="inline-flex items-center rounded-lg bg-teal-50 px-2.5 py-1 text-xs font-semibold text-teal-700 dark:bg-teal-950/40 dark:text-teal-300">
                                    {{ $cliente->servicio_contratado }}
                                </span>

                            </td>


                            {{-- CONTACTO --}}
                            <td class="px-5 py-4">

                                <div class="flex items-center gap-2 text-gray-700 dark:text-gray-300">

                                    <svg xmlns="http://www.w3.org/2000/svg"
                                         class="h-4 w-4 text-gray-400"
                                         fill="none"
                                         viewBox="0 0 24 24"
                                         stroke="currentColor"
                                         stroke-width="1.8">
                                        <path stroke-linecap="round"
                                              stroke-linejoin="round"
                                              d="M3 5a2 2 0 012-2h3.28a2 2 0 011.897 1.368L11.1 7.5a2 2 0 01-.45 2.11l-1.27 1.27a16 16 0 006.74 6.74l1.27-1.27a2 2 0 012.11-.45l3.132.923A2 2 0 0121 18.72V22a2 2 0 01-2 2h-1C9.163 24 0 14.837 0 3V2a2 2 0 012-2h3z"/>
                                    </svg>

                                    {{ $cliente->telefono ?: 'Sin teléfono' }}

                                </div>

                                <div class="mt-1 flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400">

                                    <svg xmlns="http://www.w3.org/2000/svg"
                                         class="h-3.5 w-3.5"
                                         fill="none"
                                         viewBox="0 0 24 24"
                                         stroke="currentColor"
                                         stroke-width="1.8">
                                        <path stroke-linecap="round"
                                              stroke-linejoin="round"
                                              d="M3 8l9 6 9-6M5 4h14a2 2 0 012 2v12a2 2 0 01-2 2H5a2 2 0 01-2-2V6a2 2 0 012-2z"/>
                                    </svg>

                                    {{ $cliente->correo ?: 'Sin correo' }}

                                </div>

                            </td>


                            {{-- ESTADO --}}
                            <td class="px-5 py-4">

                                <button
                                    wire:click="cambiarEstado({{ $cliente->id }})"
                                    type="button"
                                    title="Cambiar estado"
                                    class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-semibold transition
                                    {{ $cliente->estado === 'Activo'
                                        ? 'bg-green-100 text-green-700 hover:bg-green-200 dark:bg-green-500/10 dark:text-green-400 dark:hover:bg-green-500/20'
                                        : 'bg-gray-100 text-gray-600 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-700' }}"
                                >

                                    <span class="h-1.5 w-1.5 rounded-full {{ $cliente->estado === 'Activo' ? 'bg-green-500' : 'bg-gray-400' }}"></span>

                                    {{ $cliente->estado }}

                                </button>

                            </td>


                            {{-- ACCIONES --}}
                            <td class="px-5 py-4 text-right">

                                <button
                                    wire:click="editarCliente({{ $cliente->id }})"
                                    type="button"
                                    title="Editar cliente"
                                    class="inline-flex h-9 w-9 items-center justify-center rounded-lg text-gray-500 transition hover:bg-red-50 hover:text-red-600 focus:outline-none focus:ring-2 focus:ring-teal-500 dark:text-gray-400 dark:hover:bg-red-500/10 dark:hover:text-red-400"
                                >

                                    <svg xmlns="http://www.w3.org/2000/svg"
                                         class="h-5 w-5"
                                         fill="none"
                                         viewBox="0 0 24 24"
                                         stroke="currentColor"
                                         stroke-width="1.8">
                                        <path stroke-linecap="round"
                                              stroke-linejoin="round"
                                              d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/>
                                    </svg>

                                    <span class="sr-only">
                                        Editar cliente
                                    </span>

                                </button>

                            </td>

                        </tr>

                    @empty

                        <tr>

                            <td colspan="6" class="px-5 py-14 text-center">

                                <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-gray-100 text-gray-400 dark:bg-gray-800 dark:text-gray-500">

                                    <svg xmlns="http://www.w3.org/2000/svg"
                                         class="h-7 w-7"
                                         fill="none"
                                         viewBox="0 0 24 24"
                                         stroke="currentColor"
                                         stroke-width="1.5">
                                        <path stroke-linecap="round"
                                              stroke-linejoin="round"
                                              d="M3 21h18M5 21V7l7-4 7 4v14"/>
                                    </svg>

                                </div>

                                <div class="mt-4 text-sm font-semibold text-gray-700 dark:text-gray-300">
                                    No hay clientes registrados
                                </div>

                                <div class="mt-1 text-xs text-gray-500 dark:text-gray-500">
                                    Utilice «Nuevo cliente» para registrar una empresa.
                                </div>

                            </td>

                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>


        {{-- PAGINACIÃ“N --}}
        @if ($clientes->hasPages())

            <div class="border-t border-gray-200 px-5 py-4 dark:border-gray-800">
                {{ $clientes->links() }}
            </div>

        @endif

    </div>

</div>

