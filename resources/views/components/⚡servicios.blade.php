<?php

use App\Models\Servicio;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component
{
    use WithPagination;

    public string $buscar = '';

    public bool $mostrarFormulario = false;

    public ?int $servicioEditando = null;

    public string $nombre = '';
    public string $descripcion = '';
    public string $precio = '';
    public string $estado = 'Activo';

    public function updatedBuscar(): void
    {
        $this->resetPage();
    }

    public function nuevoServicio(): void
    {
        $this->resetFormulario();
        $this->mostrarFormulario = true;
    }

    public function editarServicio(int $id): void
    {
        $servicio = Servicio::findOrFail($id);

        $this->servicioEditando = $servicio->id;
        $this->nombre = $servicio->nombre;
        $this->descripcion = $servicio->descripcion ?? '';
        $this->precio = (string) $servicio->precio;
        $this->estado = $servicio->estado;

        $this->mostrarFormulario = true;
    }

    public function guardarServicio(): void
    {
        $datos = $this->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string|max:1000',
            'precio' => 'required|numeric|min:0',
            'estado' => 'required|in:Activo,Inactivo',
        ]);

        if ($this->servicioEditando) {
            Servicio::findOrFail($this->servicioEditando)->update($datos);
        } else {
            Servicio::create($datos);
        }

        $this->resetFormulario();
        $this->mostrarFormulario = false;
    }

    public function cambiarEstado(int $id): void
    {
        $servicio = Servicio::findOrFail($id);

        $servicio->update([
            'estado' => $servicio->estado === 'Activo'
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
            'servicioEditando',
            'nombre',
            'descripcion',
            'precio',
        ]);

        $this->estado = 'Activo';
    }

    public function with(): array
    {
        $consulta = Servicio::query();

        if ($this->buscar) {
            $consulta->where(function ($q) {
                $q->where('nombre', 'like', '%' . $this->buscar . '%')
                    ->orWhere('descripcion', 'like', '%' . $this->buscar . '%');
            });
        }

        return [
            'servicios' => $consulta->latest()->paginate(10),

            'totalServicios' => Servicio::count(),

            'serviciosActivos' => Servicio::where('estado', 'Activo')->count(),

            'serviciosInactivos' => Servicio::where('estado', 'Inactivo')->count(),

            'precioPromedio' => Servicio::avg('precio') ?? 0,
        ];
    }
};
?>

<div class="min-h-screen space-y-6 bg-zinc-50 p-1 text-zinc-900 transition-colors dark:bg-zinc-950 dark:text-white">

    {{-- =========================================================
         ENCABEZADO
         ========================================================= --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">

        <div class="flex items-center gap-4">

            <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-zinc-100 text-zinc-700 dark:bg-zinc-800 dark:text-zinc-200">
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

            <div>
                <h1 class="text-2xl font-bold tracking-tight text-zinc-900 dark:text-white">
                    Servicios
                </h1>

                <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">
                    Catálogo de servicios ofrecidos por la consultora
                </p>
            </div>

        </div>

        <button
            wire:click="nuevoServicio"
            type="button"
            class="inline-flex items-center justify-center gap-2 rounded-xl bg-teal-500 px-5 py-2.5 text-sm font-semibold text-zinc-900 dark:text-white shadow-lg shadow-teal-500/20 transition hover:bg-teal-600 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:ring-offset-2 focus:ring-offset-zinc-50 dark:focus:ring-offset-zinc-950"
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

            Nuevo servicio
        </button>

    </div>


    {{-- =========================================================
         INDICADORES
         ========================================================= --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">

        {{-- TOTAL --}}
        <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">

            <div class="flex items-center justify-between">

                <div>
                    <p class="text-sm font-medium text-zinc-600 dark:text-zinc-400">
                        Total servicios
                    </p>

                    <p class="mt-2 text-3xl font-bold text-zinc-900 dark:text-white">
                        {{ $totalServicios }}
                    </p>

                    <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-500">
                        Registrados en el catálogo
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
                              d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h7l5 5v11a2 2 0 01-2 2z"/>
                    </svg>
                </div>

            </div>

        </div>


        {{-- ACTIVOS --}}
        <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">

            <div class="flex items-center justify-between">

                <div>
                    <p class="text-sm font-medium text-zinc-600 dark:text-zinc-400">
                        Servicios activos
                    </p>

                    <p class="mt-2 text-3xl font-bold text-teal-700 dark:text-teal-400">
                        {{ $serviciosActivos }}
                    </p>

                    <p class="mt-1 text-xs text-green-700 dark:text-green-400">
                        Disponibles para clientes
                    </p>
                </div>

                <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-teal-50 text-teal-700 dark:bg-teal-500/10 dark:text-teal-400">
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
                    <p class="text-sm font-medium text-zinc-600 dark:text-zinc-400">
                        Servicios inactivos
                    </p>

                    <p class="mt-2 text-3xl font-bold text-zinc-900 dark:text-white">
                        {{ $serviciosInactivos }}
                    </p>

                    <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-500">
                        No disponibles actualmente
                    </p>
                </div>

                <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-zinc-800 text-zinc-400">
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


        {{-- TARIFA PROMEDIO --}}
        <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">

            <div class="flex items-center justify-between">

                <div>
                    <p class="text-sm font-medium text-zinc-600 dark:text-zinc-400">
                        Tarifa promedio
                    </p>

                    <p class="mt-2 text-3xl font-bold text-zinc-900 dark:text-white">
                        S/ {{ number_format($precioPromedio, 2) }}
                    </p>

                    <p class="mt-1 text-xs text-teal-400">
                        Precio referencial
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
                              d="M12 8c-2.21 0-4 1.343-4 3s1.79 3 4 3 4 1.343 4 3-1.79 3-4 3m0-12V5m0 14v-2m0-12a4 4 0 014 4"/>
                    </svg>
                </div>

            </div>

        </div>

    </div>


    {{-- =========================================================
         BUSCADOR
         ========================================================= --}}
    <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">

        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">

            <div>
                <h2 class="font-semibold text-zinc-900 dark:text-white">
                    Catálogo de servicios
                </h2>

                <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-500">
                    Administre los servicios que ofrece la consultora.
                </p>
            </div>

            <div class="relative w-full md:max-w-2xl">

                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-zinc-500">
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
                    placeholder="Buscar servicio..."
                    aria-label="Buscar servicios"
                    class="w-full rounded-xl border border-zinc-300 bg-white py-3 pl-10 pr-4 text-sm text-zinc-900 outline-none transition placeholder:text-zinc-400 focus:border-teal-500 focus:ring-2 focus:ring-teal-500 dark:border-zinc-700 dark:bg-zinc-950 dark:text-white dark:placeholder:text-zinc-500"
                >

            </div>

        </div>

    </div>


    {{-- =========================================================
         FORMULARIO
         ========================================================= --}}
    @if ($mostrarFormulario)

        <div class="rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">

            <div class="mb-6 flex items-start gap-3 border-b border-zinc-200 pb-5 dark:border-zinc-800">

                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-teal-500/10 text-teal-400">

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

                    <h2 class="text-lg font-bold text-zinc-900 dark:text-white">
                        {{ $servicioEditando ? 'Editar servicio' : 'Registrar nuevo servicio' }}
                    </h2>

                    <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">
                        Ingrese la información del servicio ofrecido.
                    </p>

                </div>

            </div>


            <div class="grid grid-cols-1 gap-5 md:grid-cols-2">

                {{-- NOMBRE --}}
                <div>

                    <label class="block text-sm font-semibold text-zinc-700 dark:text-zinc-200">
                        Nombre del servicio <span class="text-red-500">*</span>
                    </label>

                    <input
                        type="text"
                        wire:model="nombre"
                        placeholder="Ej. Contabilidad"
                        aria-label="Nombre del servicio"
                        class="mt-1.5 w-full rounded-xl border border-zinc-300 bg-white px-3 py-2.5 text-sm text-zinc-900 placeholder:text-zinc-400 outline-none transition focus:border-teal-500 focus:ring-2 focus:ring-teal-500 dark:border-zinc-700 dark:bg-zinc-950 dark:text-white dark:placeholder:text-zinc-500"
                    >

                    @error('nombre')
                        <span class="mt-1 block text-xs font-medium text-red-400">
                            {{ $message }}
                        </span>
                    @enderror

                </div>


                {{-- PRECIO --}}
                <div>

                    <label class="block text-sm font-semibold text-zinc-700 dark:text-zinc-200">
                        Tarifa mensual (S/) <span class="text-red-500">*</span>
                    </label>

                    <div class="relative mt-1.5">

                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-sm font-medium text-zinc-500 dark:text-zinc-500">
                            S/
                        </span>

                        <input
                            type="number"
                            step="0.01"
                            min="0"
                            wire:model="precio"
                            placeholder="0.00"
                            aria-label="Tarifa mensual"
                            class="w-full rounded-xl border border-zinc-700 bg-zinc-900 py-2.5 pl-10 pr-3 text-sm text-white placeholder:text-zinc-500 outline-none focus:border-teal-500 focus:ring-2 focus:ring-teal-500"
                        >

                    </div>

                    @error('precio')
                        <span class="mt-1 block text-xs font-medium text-red-400">
                            {{ $message }}
                        </span>
                    @enderror

                </div>


                {{-- DESCRIPCIÓN --}}
                <div class="md:col-span-2">

                    <label class="block text-sm font-semibold text-zinc-700 dark:text-zinc-200">
                        Descripción
                    </label>

                    <textarea
                        wire:model="descripcion"
                        rows="4"
                        placeholder="Describa brevemente qué incluye este servicio..."
                        aria-label="Descripción del servicio"
                        class="mt-1.5 w-full resize-none rounded-xl border border-zinc-700 bg-zinc-900 px-3 py-2.5 text-sm text-white placeholder:text-zinc-500 outline-none focus:border-teal-500 focus:ring-2 focus:ring-teal-500"
                    ></textarea>

                    @error('descripcion')
                        <span class="mt-1 block text-xs font-medium text-red-400">
                            {{ $message }}
                        </span>
                    @enderror

                </div>


                {{-- ESTADO --}}
                <div>

                    <label class="block text-sm font-semibold text-zinc-700 dark:text-zinc-200">
                        Estado
                    </label>

                    <select
                        wire:model="estado"
                        aria-label="Estado del servicio"
                        class="mt-1.5 w-full rounded-xl border border-zinc-300 bg-white px-3 py-2.5 text-sm text-zinc-900 outline-none transition focus:border-teal-500 focus:ring-2 focus:ring-teal-500 dark:border-zinc-700 dark:bg-zinc-950 dark:text-white"
                    >
                        <option value="Activo">Activo</option>
                        <option value="Inactivo">Inactivo</option>
                    </select>

                </div>

            </div>


            {{-- BOTONES --}}
            <div class="mt-7 flex flex-col-reverse gap-3 border-t border-zinc-200 pt-5 dark:border-zinc-800 sm:flex-row sm:justify-end">

                <button
                    wire:click="cancelar"
                    type="button"
                    class="inline-flex items-center justify-center gap-2 rounded-xl border border-zinc-200 bg-white px-5 py-2.5 text-sm font-semibold text-zinc-700 transition hover:bg-zinc-50 focus:outline-none focus:ring-2 focus:ring-zinc-500 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-200 dark:hover:bg-zinc-800"
                >
                    Cancelar
                </button>

                <button
                    wire:click="guardarServicio"
                    type="button"
                    class="inline-flex items-center justify-center gap-2 rounded-xl bg-teal-500 px-5 py-2.5 text-sm font-semibold text-zinc-900 dark:text-white shadow-lg shadow-teal-500/20 transition hover:bg-teal-600 focus:outline-none focus:ring-2 focus:ring-teal-500"
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

                    {{ $servicioEditando ? 'Actualizar servicio' : 'Guardar servicio' }}

                </button>

            </div>

        </div>

    @endif


    {{-- =========================================================
         TABLA
         ========================================================= --}}
    <div class="overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900">

        <div class="border-b border-zinc-200 px-5 py-4 dark:border-zinc-800">

            <div class="flex items-center gap-3">

                <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-teal-500/10 text-teal-400">

                    <svg xmlns="http://www.w3.org/2000/svg"
                         class="h-5 w-5"
                         fill="none"
                         viewBox="0 0 24 24"
                         stroke="currentColor"
                         stroke-width="1.8">
                        <path stroke-linecap="round"
                              stroke-linejoin="round"
                              d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h7l5 5v11a2 2 0 01-2-2z"/>
                    </svg>

                </div>

                <div>

                    <h2 class="font-semibold text-zinc-900 dark:text-white">
                        Servicios disponibles
                    </h2>

                    <p class="text-xs text-zinc-500 dark:text-zinc-500">
                        Catálogo de servicios de la consultora.
                    </p>

                </div>

            </div>

        </div>


        <div class="overflow-x-auto">

            <table class="min-w-full text-sm">

                <thead class="bg-zinc-50 dark:bg-zinc-950">

                    <tr>

                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-zinc-600 dark:text-zinc-400">
                            Servicio
                        </th>

                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-zinc-600 dark:text-zinc-400">
                            Descripción
                        </th>

                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-zinc-600 dark:text-zinc-400">
                            Tarifa
                        </th>

                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-zinc-600 dark:text-zinc-400">
                            Estado
                        </th>

                        <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-zinc-400">
                            Acciones
                        </th>

                    </tr>

                </thead>


                <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">

                    @forelse ($servicios as $servicio)

                        <tr class="transition hover:bg-zinc-50 dark:hover:bg-zinc-800/50">

                            {{-- SERVICIO --}}
                            <td class="px-5 py-4">

                                <div class="flex items-center gap-3">

                                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-zinc-100 text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300">

                                        <svg xmlns="http://www.w3.org/2000/svg"
                                             class="h-5 w-5"
                                             fill="none"
                                             viewBox="0 0 24 24"
                                             stroke="currentColor"
                                             stroke-width="1.8">
                                            <path stroke-linecap="round"
                                                  stroke-linejoin="round"
                                                  d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h7l5 5v11a2 2 0 01-2 2z"/>
                                        </svg>

                                    </div>

                                    <div class="font-semibold text-zinc-900 dark:text-white">
                                        {{ $servicio->nombre }}
                                    </div>

                                </div>

                            </td>


                            {{-- DESCRIPCIÓN --}}
                            <td class="max-w-md px-5 py-4 text-zinc-700 dark:text-zinc-400">
                                {{ $servicio->descripcion ?: 'Sin descripción registrada' }}
                            </td>


                            {{-- PRECIO --}}
                            <td class="whitespace-nowrap px-5 py-4 font-semibold text-zinc-900 dark:text-white">
                                S/ {{ number_format($servicio->precio, 2) }}
                            </td>


                            {{-- ESTADO --}}
                            <td class="px-5 py-4">

                                <button
                                    wire:click="cambiarEstado({{ $servicio->id }})"
                                    type="button"
                                    title="Cambiar estado"
                                    class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-semibold transition
                                    {{ $servicio->estado === 'Activo'
                                        ? 'bg-green-50 text-green-700 hover:bg-green-100 dark:bg-green-500/10 dark:text-green-400 dark:hover:bg-green-500/20'
                                        : 'bg-zinc-100 text-zinc-600 hover:bg-zinc-200 dark:bg-zinc-800 dark:text-zinc-400 dark:hover:bg-zinc-700' }}"
                                >

                                    <span class="h-1.5 w-1.5 rounded-full {{ $servicio->estado === 'Activo' ? 'bg-green-500' : 'bg-zinc-500' }}"></span>

                                    {{ $servicio->estado }}

                                </button>

                            </td>


                            {{-- ACCIONES --}}
                            <td class="px-5 py-4 text-right">

                                <button
                                    wire:click="editarServicio({{ $servicio->id }})"
                                    type="button"
                                    title="Editar servicio"
                                    class="inline-flex h-9 w-9 items-center justify-center rounded-lg text-zinc-400 transition hover:bg-teal-500/10 hover:text-teal-400 focus:outline-none focus:ring-2 focus:ring-teal-500"
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
                                        Editar servicio
                                    </span>

                                </button>

                            </td>

                        </tr>

                    @empty

                        <tr>

                            <td colspan="5" class="px-5 py-14 text-center">

                                <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-zinc-100 text-zinc-500 dark:bg-zinc-800 dark:text-zinc-500">

                                    <svg xmlns="http://www.w3.org/2000/svg"
                                         class="h-7 w-7"
                                         fill="none"
                                         viewBox="0 0 24 24"
                                         stroke="currentColor"
                                         stroke-width="1.5">
                                        <path stroke-linecap="round"
                                              stroke-linejoin="round"
                                              d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h7l5 5v11a2 2 0 01-2-2z"/>
                                    </svg>

                                </div>

                                <div class="mt-4 text-sm font-semibold text-zinc-300">
                                    No hay servicios registrados
                                </div>

                                <div class="mt-1 text-xs text-zinc-500 dark:text-zinc-500">
                                    Utilice «Nuevo servicio» para agregar uno.
                                </div>

                            </td>

                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>


        {{-- PAGINACIÓN --}}
        @if ($servicios->hasPages())

            <div class="border-t border-zinc-800 px-5 py-4">
                {{ $servicios->links() }}
            </div>

        @endif

    </div>

</div>
