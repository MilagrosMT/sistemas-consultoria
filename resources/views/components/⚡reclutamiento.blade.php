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

    public function nuevoPostulante(): void
    {
        $this->resetFormulario();
        $this->resetValidation();
        $this->mostrarFormulario = true;
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

        /*
        |--------------------------------------------------------------------------
        | Flujo del proceso de selección
        |--------------------------------------------------------------------------
        */

        if ($this->postulanteEditando) {
            $postulanteActual = Postulante::find($this->postulanteEditando);

            if ($postulanteActual) {
                $estadoAnterior = $postulanteActual->estado;
                $estadoNuevo = $this->estado;

                $transicionesPermitidas = [
                    'Postulando' => [
                        'Postulando',
                        'Entrevista',
                        'Rechazado',
                    ],
                    'Entrevista' => [
                        'Entrevista',
                        'Seleccionado',
                        'Rechazado',
                    ],
                    'Seleccionado' => [
                        'Seleccionado',
                    ],
                    'Rechazado' => [
                        'Rechazado',
                        'Postulando',
                    ],
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

        /*
        |--------------------------------------------------------------------------
        | Guardar o actualizar
        |--------------------------------------------------------------------------
        */

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

    public function cancelar(): void
    {
        $this->mostrarFormulario = false;
        $this->resetFormulario();
        $this->resetValidation();
    }

    private function resetFormulario(): void
    {
        $this->reset([
            'postulanteEditando',
            'nombres',
            'apellidos',
            'dni',
            'telefono',
            'email',
            'puesto',
            'fecha_postulacion',
        ]);

        $this->estado = 'Postulando';
    }

    public function render()
    {
        $totalPostulantes = Postulante::count();

        $totalPostulando = Postulante::where(
            'estado',
            'Postulando'
        )->count();

        $totalEntrevista = Postulante::where(
            'estado',
            'Entrevista'
        )->count();

        $totalSeleccionados = Postulante::where(
            'estado',
            'Seleccionado'
        )->count();

        $totalRechazados = Postulante::where(
            'estado',
            'Rechazado'
        )->count();

        $postulantes = Postulante::query()
            ->when($this->buscar, function ($query) {
                $query->where(function ($query) {
                    $query
                        ->where(
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

<div class="min-h-full bg-zinc-50 px-5 py-6 text-zinc-900 transition-colors dark:bg-black dark:text-white sm:px-7 lg:px-8">

    {{-- =========================================================
         ENCABEZADO
         ========================================================= --}}

    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">

        <div>
            <div class="mb-2 flex items-center gap-2 text-sm text-zinc-600 dark:text-gray-400">
                <svg xmlns="http://www.w3.org/2000/svg"
                     class="h-4 w-4"
                     fill="none"
                     viewBox="0 0 24 24"
                     stroke="currentColor"
                     stroke-width="1.7">
                    <path stroke-linecap="round"
                          stroke-linejoin="round"
                          d="M15 19a6 6 0 00-12 0m6-6a4 4 0 100-8 4 4 0 000 8zm9 6a5 5 0 00-4-4.9M17 3.1a4 4 0 010 7.8"/>
                </svg>

                <span>Gestión del personal</span>

                <span>/</span>

                <span>Reclutamiento</span>
            </div>

            <h1 class="text-3xl font-bold tracking-tight text-zinc-900 dark:text-white">
                Reclutamiento
            </h1>

            <p class="mt-1 text-sm text-zinc-600 dark:text-gray-400">
                Gestiona los postulantes y el proceso de selección de personal.
            </p>
        </div>

        <button
            type="button"
            wire:click="nuevoPostulante"
            class="inline-flex items-center justify-center gap-2 rounded-xl bg-teal-500 px-5 py-3 text-sm font-semibold text-zinc-900 dark:text-white shadow-lg shadow-teal-500/10 transition hover:bg-teal-400"
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

            Nuevo postulante
        </button>

    </div>


    {{-- =========================================================
         MENSAJE
         ========================================================= --}}

    @if (session('mensaje'))
        <div class="mb-6 flex items-center gap-3 rounded-xl border border-emerald-500/20 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-400">

            <svg xmlns="http://www.w3.org/2000/svg"
                 class="h-5 w-5 shrink-0"
                 fill="none"
                 viewBox="0 0 24 24"
                 stroke="currentColor"
                 stroke-width="2">
                <path stroke-linecap="round"
                      stroke-linejoin="round"
                      d="M5 13l4 4L19 7"/>
            </svg>

            <span>{{ session('mensaje') }}</span>

        </div>
    @endif


    {{-- =========================================================
         INDICADORES
         ========================================================= --}}

    <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-5">

        {{-- Total --}}
        <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-[#151515]">

            <div class="flex items-start justify-between">

                <div>
                    <p class="text-sm text-zinc-600 dark:text-gray-400">
                        Total postulantes
                    </p>

                    <p class="mt-2 text-3xl font-bold text-zinc-900 dark:text-white">
                        {{ $totalPostulantes }}
                    </p>

                    <p class="mt-1 text-xs text-gray-500">
                        Personas registradas
                    </p>
                </div>

                <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-white/10 text-zinc-700 dark:text-gray-300">
                    <svg xmlns="http://www.w3.org/2000/svg"
                         class="h-5 w-5"
                         fill="none"
                         viewBox="0 0 24 24"
                         stroke="currentColor"
                         stroke-width="1.7">
                        <path stroke-linecap="round"
                              stroke-linejoin="round"
                              d="M15 19a6 6 0 00-12 0m6-6a4 4 0 100-8 4 4 0 000 8zm9 6a5 5 0 00-4-4.9M17 3.1a4 4 0 010 7.8"/>
                    </svg>
                </div>

            </div>

        </div>


        {{-- Postulando --}}
        <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-[#151515]">

            <div class="flex items-start justify-between">

                <div>
                    <p class="text-sm text-zinc-600 dark:text-gray-400">
                        Postulando
                    </p>

                    <p class="mt-2 text-3xl font-bold text-amber-400">
                        {{ $totalPostulando }}
                    </p>

                    <p class="mt-1 text-xs text-gray-500">
                        En proceso inicial
                    </p>
                </div>

                <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-amber-500/10 text-amber-400">
                    <svg xmlns="http://www.w3.org/2000/svg"
                         class="h-5 w-5"
                         fill="none"
                         viewBox="0 0 24 24"
                         stroke="currentColor"
                         stroke-width="1.7">
                        <path stroke-linecap="round"
                              stroke-linejoin="round"
                              d="M12 6v6l4 2"/>
                        <circle cx="12" cy="12" r="9"/>
                    </svg>
                </div>

            </div>

        </div>


        {{-- Entrevista --}}
        <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-[#151515]">

            <div class="flex items-start justify-between">

                <div>
                    <p class="text-sm text-zinc-600 dark:text-gray-400">
                        En entrevista
                    </p>

                    <p class="mt-2 text-3xl font-bold text-sky-400">
                        {{ $totalEntrevista }}
                    </p>

                    <p class="mt-1 text-xs text-gray-500">
                        Candidatos evaluados
                    </p>
                </div>

                <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-sky-500/10 text-sky-400">
                    <svg xmlns="http://www.w3.org/2000/svg"
                         class="h-5 w-5"
                         fill="none"
                         viewBox="0 0 24 24"
                         stroke="currentColor"
                         stroke-width="1.7">
                        <path stroke-linecap="round"
                              stroke-linejoin="round"
                              d="M8 10h8M8 14h5"/>
                        <path stroke-linecap="round"
                              stroke-linejoin="round"
                              d="M19 10a7 7 0 11-14 0c0-3.866 3.134-7 7-7s7 3.134 7 7z"/>
                    </svg>
                </div>

            </div>

        </div>


        {{-- Seleccionados --}}
        <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-[#151515]">

            <div class="flex items-start justify-between">

                <div>
                    <p class="text-sm text-zinc-600 dark:text-gray-400">
                        Seleccionados
                    </p>

                    <p class="mt-2 text-3xl font-bold text-emerald-400">
                        {{ $totalSeleccionados }}
                    </p>

                    <p class="mt-1 text-xs text-gray-500">
                        Candidatos elegidos
                    </p>
                </div>

                <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-emerald-500/10 text-emerald-400">
                    <svg xmlns="http://www.w3.org/2000/svg"
                         class="h-5 w-5"
                         fill="none"
                         viewBox="0 0 24 24"
                         stroke="currentColor"
                         stroke-width="1.7">
                        <path stroke-linecap="round"
                              stroke-linejoin="round"
                              d="M5 13l4 4L19 7"/>
                    </svg>
                </div>

            </div>

        </div>


        {{-- Rechazados --}}
        <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-[#151515]">

            <div class="flex items-start justify-between">

                <div>
                    <p class="text-sm text-zinc-600 dark:text-gray-400">
                        Rechazados
                    </p>

                    <p class="mt-2 text-3xl font-bold text-red-400">
                        {{ $totalRechazados }}
                    </p>

                    <p class="mt-1 text-xs text-gray-500">
                        Fuera del proceso
                    </p>
                </div>

                <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-red-500/10 text-red-400">
                    <svg xmlns="http://www.w3.org/2000/svg"
                         class="h-5 w-5"
                         fill="none"
                         viewBox="0 0 24 24"
                         stroke="currentColor"
                         stroke-width="1.7">
                        <path stroke-linecap="round"
                              stroke-linejoin="round"
                              d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </div>

            </div>

        </div>

    </div>


    {{-- =========================================================
         BÚSQUEDA Y FILTRO
         ========================================================= --}}

    <div class="mb-6 rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-[#151515]">

        <div class="grid grid-cols-1 gap-4 lg:grid-cols-[1fr_280px]">

            <div>
                <label class="mb-2 block text-sm font-semibold text-zinc-800 dark:text-gray-200">
                    Buscar postulante
                </label>

                <div class="relative">

                    <svg xmlns="http://www.w3.org/2000/svg"
                         class="absolute left-4 top-1/2 h-5 w-5 -translate-y-1/2 text-gray-500"
                         fill="none"
                         viewBox="0 0 24 24"
                         stroke="currentColor"
                         stroke-width="1.7">
                        <circle cx="11" cy="11" r="7"/>
                        <path stroke-linecap="round"
                              d="M20 20l-4-4"/>
                    </svg>

                    <input
                        wire:model.live.debounce.300ms="buscar"
                        type="text"
                        placeholder="Buscar por nombre, DNI o puesto..."
                        class="h-12 w-full rounded-xl border border-zinc-300 bg-white pl-12 pr-4 text-sm text-zinc-900 placeholder-zinc-400 outline-none transition focus:border-teal-500 focus:ring-1 focus:ring-teal-500 dark:border-zinc-800 dark:bg-[#202020] dark:text-white dark:placeholder-gray-500"
                    >

                </div>
            </div>


            <div>
                <label class="mb-2 block text-sm font-semibold text-zinc-800 dark:text-gray-200">
                    Filtrar por estado
                </label>

                <select
                    wire:model.live="filtroEstado"
                    class="h-12 w-full rounded-xl border border-zinc-300 bg-white px-4 text-sm text-zinc-900 outline-none transition focus:border-teal-500 focus:ring-1 focus:ring-teal-500 dark:border-zinc-800 dark:bg-[#202020] dark:text-white"
                >
                    <option value="">Todos los estados</option>
                    <option value="Postulando">Postulando</option>
                    <option value="Entrevista">Entrevista</option>
                    <option value="Seleccionado">Seleccionado</option>
                    <option value="Rechazado">Rechazado</option>
                </select>
            </div>

        </div>

    </div>


    {{-- =========================================================
         TABLA
         ========================================================= --}}

    <div class="overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-[#151515]">

        <div class="flex flex-col gap-2 border-b border-zinc-200 dark:border-zinc-800 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">

            <div>
                <h2 class="text-lg font-semibold text-zinc-900 dark:text-white">
                    Postulantes registrados
                </h2>

                <p class="text-sm text-gray-500">
                    Historial y seguimiento del proceso de selección.
                </p>
            </div>

            <div class="text-sm text-gray-500">
                {{ $postulantes->total() }} registros
            </div>

        </div>


        <div class="overflow-x-auto">

            <table class="w-full min-w-[900px] text-left">

                <thead class="border-b border-zinc-200 dark:border-zinc-800 bg-black/40">

                    <tr>
                        <th class="px-5 py-4 text-xs font-semibold uppercase tracking-wider text-zinc-600 dark:text-gray-400">
                            Postulante
                        </th>

                        <th class="px-5 py-4 text-xs font-semibold uppercase tracking-wider text-zinc-600 dark:text-gray-400">
                            DNI
                        </th>

                        <th class="px-5 py-4 text-xs font-semibold uppercase tracking-wider text-zinc-600 dark:text-gray-400">
                            Puesto
                        </th>

                        <th class="px-5 py-4 text-xs font-semibold uppercase tracking-wider text-zinc-600 dark:text-gray-400">
                            Fecha
                        </th>

                        <th class="px-5 py-4 text-xs font-semibold uppercase tracking-wider text-zinc-600 dark:text-gray-400">
                            Estado
                        </th>

                        <th class="px-5 py-4 text-right text-xs font-semibold uppercase tracking-wider text-zinc-600 dark:text-gray-400">
                            Acciones
                        </th>
                    </tr>

                </thead>


                <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">

                    @forelse ($postulantes as $postulante)

                        <tr class="transition hover:bg-zinc-50 dark:hover:bg-white/[0.025]">

                            {{-- POSTULANTE --}}
                            <td class="px-5 py-4">

                                <div class="flex items-center gap-3">

                                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-white/10 text-sm font-semibold text-zinc-700 dark:text-gray-300">
                                        {{ strtoupper(substr($postulante->nombres, 0, 1) . substr($postulante->apellidos, 0, 1)) }}
                                    </div>

                                    <div>
                                        <p class="font-semibold text-zinc-900 dark:text-white">
                                            {{ $postulante->apellidos }}, {{ $postulante->nombres }}
                                        </p>

                                        @if ($postulante->email)
                                            <p class="text-xs text-gray-500">
                                                {{ $postulante->email }}
                                            </p>
                                        @else
                                            <p class="text-xs text-gray-600">
                                                Sin correo registrado
                                            </p>
                                        @endif
                                    </div>

                                </div>

                            </td>


                            {{-- DNI --}}
                            <td class="px-5 py-4 text-sm text-zinc-700 dark:text-gray-300">
                                {{ $postulante->dni }}
                            </td>


                            {{-- PUESTO --}}
                            <td class="px-5 py-4">

                                <span class="text-sm font-medium text-zinc-800 dark:text-gray-200">
                                    {{ $postulante->puesto }}
                                </span>

                            </td>


                            {{-- FECHA --}}
                            <td class="px-5 py-4 text-sm text-zinc-600 dark:text-gray-400">
                                {{ $postulante->fecha_postulacion?->format('d/m/Y') }}
                            </td>


                            {{-- ESTADO --}}
                            <td class="px-5 py-4">

                                @if ($postulante->estado === 'Seleccionado')

                                    <span class="inline-flex items-center gap-2 rounded-full bg-emerald-500/10 px-3 py-1.5 text-xs font-semibold text-emerald-400">
                                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-400"></span>
                                        Seleccionado
                                    </span>

                                @elseif ($postulante->estado === 'Rechazado')

                                    <span class="inline-flex items-center gap-2 rounded-full bg-red-500/10 px-3 py-1.5 text-xs font-semibold text-red-400">
                                        <span class="h-1.5 w-1.5 rounded-full bg-red-400"></span>
                                        Rechazado
                                    </span>

                                @elseif ($postulante->estado === 'Entrevista')

                                    <span class="inline-flex items-center gap-2 rounded-full bg-sky-500/10 px-3 py-1.5 text-xs font-semibold text-sky-400">
                                        <span class="h-1.5 w-1.5 rounded-full bg-sky-400"></span>
                                        Entrevista
                                    </span>

                                @else

                                    <span class="inline-flex items-center gap-2 rounded-full bg-amber-500/10 px-3 py-1.5 text-xs font-semibold text-amber-400">
                                        <span class="h-1.5 w-1.5 rounded-full bg-amber-400"></span>
                                        Postulando
                                    </span>

                                @endif

                            </td>


                            {{-- ACCIONES --}}
                            <td class="px-5 py-4">

                                <div class="flex justify-end gap-2">

                                    <button
                                        type="button"
                                        wire:click="editar({{ $postulante->id }})"
                                        class="flex h-9 w-9 items-center justify-center rounded-lg text-zinc-500 transition hover:bg-zinc-100 hover:text-zinc-900 dark:text-gray-400 dark:hover:bg-white/10 dark:hover:text-white"
                                        title="Editar"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg"
                                             class="h-5 w-5"
                                             fill="none"
                                             viewBox="0 0 24 24"
                                             stroke="currentColor"
                                             stroke-width="1.7">
                                            <path stroke-linecap="round"
                                                  stroke-linejoin="round"
                                                  d="M16.862 4.487l1.65-1.65a2.121 2.121 0 013 3l-9.193 9.193-4.5 1.5 1.5-4.5 7.543-7.543z"/>
                                            <path stroke-linecap="round"
                                                  stroke-linejoin="round"
                                                  d="M19 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h4"/>
                                        </svg>
                                    </button>


                                    <button
                                        type="button"
                                        wire:click="eliminar({{ $postulante->id }})"
                                        wire:confirm="¿Estás seguro de eliminar este postulante?"
                                        class="flex h-9 w-9 items-center justify-center rounded-lg text-gray-500 transition hover:bg-red-500/10 hover:text-red-400"
                                        title="Eliminar"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg"
                                             class="h-5 w-5"
                                             fill="none"
                                             viewBox="0 0 24 24"
                                             stroke="currentColor"
                                             stroke-width="1.7">
                                            <path stroke-linecap="round"
                                                  stroke-linejoin="round"
                                                  d="M6 7h12M9 7V5a1 1 0 011-1h4a1 1 0 011 1v2m2 0v12a1 1 0 01-1 1H8a1 1 0 01-1-1V7m3 4v6m4-6v6"/>
                                        </svg>
                                    </button>

                                </div>

                            </td>

                        </tr>

                    @empty

                        <tr>
                            <td colspan="6" class="px-5 py-12 text-center">

                                <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-xl bg-zinc-100 text-zinc-500 dark:bg-white/5 dark:text-gray-500">

                                    <svg xmlns="http://www.w3.org/2000/svg"
                                         class="h-6 w-6"
                                         fill="none"
                                         viewBox="0 0 24 24"
                                         stroke="currentColor"
                                         stroke-width="1.7">
                                        <path stroke-linecap="round"
                                              stroke-linejoin="round"
                                              d="M15 19a6 6 0 00-12 0m6-6a4 4 0 100-8 4 4 0 000 8z"/>
                                    </svg>

                                </div>

                                <p class="mt-3 text-sm font-medium text-zinc-700 dark:text-gray-300">
                                    No hay postulantes registrados.
                                </p>

                                <p class="mt-1 text-xs text-gray-600">
                                    Registra un nuevo postulante para comenzar.
                                </p>

                            </td>
                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>


        {{-- PAGINACIÓN --}}
        @if ($postulantes->hasPages())

            <div class="border-t border-zinc-800 px-5 py-4">
                {{ $postulantes->links() }}
            </div>

        @endif

    </div>


    {{-- =========================================================
         MODAL DE REGISTRO / EDICIÓN
         ========================================================= --}}

    @if ($mostrarFormulario)

        <div
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4 backdrop-blur-sm"
            wire:keydown.escape="cancelar"
        >

            <div class="w-full max-w-3xl overflow-hidden rounded-2xl border border-zinc-200 bg-white text-zinc-900 shadow-2xl dark:border-zinc-800 dark:bg-[#181818] dark:text-white">

                {{-- CABECERA --}}
                <div class="flex items-center justify-between border-b border-zinc-200 dark:border-zinc-800 px-6 py-5">

                    <div>

                        <h2 class="text-xl font-semibold text-zinc-900 dark:text-white">
                            {{ $postulanteEditando ? 'Editar postulante' : 'Registrar postulante' }}
                        </h2>

                        <p class="mt-1 text-sm text-gray-500">
                            Completa la información del candidato.
                        </p>

                    </div>

                    <button
                        type="button"
                        wire:click="cancelar"
                        class="flex h-9 w-9 items-center justify-center rounded-lg text-zinc-500 transition hover:bg-zinc-100 hover:text-zinc-900 dark:text-gray-400 dark:hover:bg-white/10 dark:hover:text-white"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg"
                             class="h-5 w-5"
                             fill="none"
                             viewBox="0 0 24 24"
                             stroke="currentColor"
                             stroke-width="1.7">
                            <path stroke-linecap="round"
                                  d="M6 6l12 12M18 6L6 18"/>
                        </svg>
                    </button>

                </div>


                {{-- FORMULARIO --}}
                <form wire:submit="guardar">

                    <div class="max-h-[70vh] overflow-y-auto px-6 py-6">

                        <div class="grid grid-cols-1 gap-5 md:grid-cols-2">

                            {{-- NOMBRES --}}
                            <div>
                                <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-gray-300">
                                    Nombres
                                </label>

                                <input
                                    wire:model="nombres"
                                    type="text"
                                    placeholder="Nombres del postulante"
                                    class="h-11 w-full rounded-xl border border-zinc-300 bg-white px-4 text-sm text-zinc-900 placeholder-zinc-400 outline-none transition focus:border-teal-500 focus:ring-1 focus:ring-teal-500 dark:border-zinc-800 dark:bg-[#222] dark:text-white dark:placeholder-gray-600"
                                >

                                @error('nombres')
                                    <span class="mt-1 block text-xs text-red-400">
                                        {{ $message }}
                                    </span>
                                @enderror
                            </div>


                            {{-- APELLIDOS --}}
                            <div>
                                <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-gray-300">
                                    Apellidos
                                </label>

                                <input
                                    wire:model="apellidos"
                                    type="text"
                                    placeholder="Apellidos del postulante"
                                    class="h-11 w-full rounded-xl border border-zinc-300 bg-white px-4 text-sm text-zinc-900 placeholder-zinc-400 outline-none transition focus:border-teal-500 focus:ring-1 focus:ring-teal-500 dark:border-zinc-800 dark:bg-[#222] dark:text-white dark:placeholder-gray-600"
                                >

                                @error('apellidos')
                                    <span class="mt-1 block text-xs text-red-400">
                                        {{ $message }}
                                    </span>
                                @enderror
                            </div>


                            {{-- DNI --}}
                            <div>
                                <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-gray-300">
                                    DNI
                                </label>

                                <input
                                    wire:model="dni"
                                    type="text"
                                    maxlength="8"
                                    inputmode="numeric"
                                    placeholder="00000000"
                                    class="h-11 w-full rounded-xl border border-zinc-300 bg-white px-4 text-sm text-zinc-900 placeholder-zinc-400 outline-none transition focus:border-teal-500 focus:ring-1 focus:ring-teal-500 dark:border-zinc-800 dark:bg-[#222] dark:text-white dark:placeholder-gray-600"
                                >

                                @error('dni')
                                    <span class="mt-1 block text-xs text-red-400">
                                        {{ $message }}
                                    </span>
                                @enderror
                            </div>


                            {{-- TELÉFONO --}}
                            <div>
                                <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-gray-300">
                                    Teléfono
                                </label>

                                <input
                                    wire:model="telefono"
                                    type="text"
                                    maxlength="9"
                                    inputmode="numeric"
                                    placeholder="000000000"
                                    class="h-11 w-full rounded-xl border border-zinc-300 bg-white px-4 text-sm text-zinc-900 placeholder-zinc-400 outline-none transition focus:border-teal-500 focus:ring-1 focus:ring-teal-500 dark:border-zinc-800 dark:bg-[#222] dark:text-white dark:placeholder-gray-600"
                                >

                                @error('telefono')
                                    <span class="mt-1 block text-xs text-red-400">
                                        {{ $message }}
                                    </span>
                                @enderror
                            </div>


                            {{-- EMAIL --}}
                            <div>
                                <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-gray-300">
                                    Correo electrónico
                                </label>

                                <input
                                    wire:model="email"
                                    type="email"
                                    placeholder="correo@ejemplo.com"
                                    class="h-11 w-full rounded-xl border border-zinc-300 bg-white px-4 text-sm text-zinc-900 placeholder-zinc-400 outline-none transition focus:border-teal-500 focus:ring-1 focus:ring-teal-500 dark:border-zinc-800 dark:bg-[#222] dark:text-white dark:placeholder-gray-600"
                                >

                                @error('email')
                                    <span class="mt-1 block text-xs text-red-400">
                                        {{ $message }}
                                    </span>
                                @enderror
                            </div>


                            {{-- PUESTO --}}
                            <div>
                                <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-gray-300">
                                    Puesto al que postula
                                </label>

                                <input
                                    wire:model="puesto"
                                    type="text"
                                    placeholder="Ej. Asistente contable"
                                    class="h-11 w-full rounded-xl border border-zinc-300 bg-white px-4 text-sm text-zinc-900 placeholder-zinc-400 outline-none transition focus:border-teal-500 focus:ring-1 focus:ring-teal-500 dark:border-zinc-800 dark:bg-[#222] dark:text-white dark:placeholder-gray-600"
                                >

                                @error('puesto')
                                    <span class="mt-1 block text-xs text-red-400">
                                        {{ $message }}
                                    </span>
                                @enderror
                            </div>


                            {{-- FECHA --}}
                            <div>
                                <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-gray-300">
                                    Fecha de postulación
                                </label>

                                <input
                                    wire:model="fecha_postulacion"
                                    type="date"
                                    class="h-11 w-full rounded-xl border border-zinc-300 bg-white px-4 text-sm text-zinc-900 outline-none transition focus:border-teal-500 focus:ring-1 focus:ring-teal-500 dark:border-zinc-800 dark:bg-[#222] dark:text-white"
                                >

                                @error('fecha_postulacion')
                                    <span class="mt-1 block text-xs text-red-400">
                                        {{ $message }}
                                    </span>
                                @enderror
                            </div>


                            {{-- ESTADO --}}
                            <div>
                                <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-gray-300">
                                    Estado del proceso
                                </label>

                                <select
                                    wire:model="estado"
                                    class="h-11 w-full rounded-xl border border-zinc-300 bg-white px-4 text-sm text-zinc-900 outline-none transition focus:border-teal-500 focus:ring-1 focus:ring-teal-500 dark:border-zinc-800 dark:bg-[#222] dark:text-white"
                                >
                                    <option value="Postulando">Postulando</option>
                                    <option value="Entrevista">Entrevista</option>
                                    <option value="Seleccionado">Seleccionado</option>
                                    <option value="Rechazado">Rechazado</option>
                                </select>

                                @error('estado')
                                    <span class="mt-1 block text-xs text-red-400">
                                        {{ $message }}
                                    </span>
                                @enderror
                            </div>

                        </div>

                    </div>


                    {{-- BOTONES --}}
                    <div class="flex flex-col-reverse gap-3 border-t border-zinc-200 px-6 py-4 dark:border-zinc-800 sm:flex-row sm:justify-end">

                        <button
                            type="button"
                            wire:click="cancelar"
                            class="rounded-xl border border-zinc-800 px-5 py-2.5 text-sm font-semibold text-zinc-700 dark:text-gray-300 transition hover:bg-white/5 hover:text-white"
                        >
                            Cancelar
                        </button>

                        <button
                            type="submit"
                            class="rounded-xl bg-teal-500 px-5 py-2.5 text-sm font-semibold text-zinc-900 dark:text-white transition hover:bg-teal-400"
                        >
                            {{ $postulanteEditando ? 'Actualizar postulante' : 'Registrar postulante' }}
                        </button>

                    </div>

                </form>

            </div>

        </div>

    @endif

</div>
