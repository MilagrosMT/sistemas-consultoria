<?php

use Livewire\Component;

new class extends Component
{
    public string $buscar = '';

    public bool $mostrarFormulario = false;

    public string $empresa = '';
    public string $contacto = '';
    public string $correo = '';
    public string $telefono = '';
    public string $servicio = '';
    public string $estado = 'Prospecto';

    public array $prospectos = [
        [
            'empresa' => 'Empresa Demo S.A.C.',
            'contacto' => 'Juan Pérez',
            'correo' => 'juan@empresa-demo.com',
            'telefono' => '987654321',
            'servicio' => 'Contabilidad',
            'estado' => 'Interesado',
        ],
        [
            'empresa' => 'Comercial Arequipa S.R.L.',
            'contacto' => 'María Torres',
            'correo' => 'maria@comercialarequipa.com',
            'telefono' => '986123456',
            'servicio' => 'Administración Tributaria',
            'estado' => 'Prospecto',
        ],
        [
            'empresa' => 'Servicios del Sur E.I.R.L.',
            'contacto' => 'Carlos Flores',
            'correo' => 'carlos@serviciosdelsur.com',
            'telefono' => '985456789',
            'servicio' => 'Elaboración de Planillas',
            'estado' => 'Contactado',
        ],
    ];

    public function guardarProspecto(): void
    {
        $this->validate([
            'empresa' => 'required|string|max:150',
            'contacto' => 'required|string|max:100',
            'correo' => 'required|email|max:150',
            'telefono' => 'nullable|string|max:20',
            'servicio' => 'required|string|max:100',
            'estado' => 'required|string',
        ]);

        $this->prospectos[] = [
            'empresa' => $this->empresa,
            'contacto' => $this->contacto,
            'correo' => $this->correo,
            'telefono' => $this->telefono,
            'servicio' => $this->servicio,
            'estado' => $this->estado,
        ];

        $this->reset([
            'empresa',
            'contacto',
            'correo',
            'telefono',
            'servicio',
        ]);

        $this->estado = 'Prospecto';
        $this->mostrarFormulario = false;
    }

    public function getProspectosFiltradosProperty(): array
    {
        if ($this->buscar === '') {
            return $this->prospectos;
        }

        return array_values(array_filter(
            $this->prospectos,
            fn ($prospecto) =>
                str_contains(
                    strtolower($prospecto['empresa']),
                    strtolower($this->buscar)
                ) ||
                str_contains(
                    strtolower($prospecto['contacto']),
                    strtolower($this->buscar)
                ) ||
                str_contains(
                    strtolower($prospecto['servicio']),
                    strtolower($this->buscar)
                )
        ));
    }
};
?>

<div class="min-h-screen bg-zinc-50 text-zinc-900 transition-colors dark:bg-zinc-950 dark:text-white">

    <div class="mx-auto max-w-7xl px-6 py-8">

        <!-- Encabezado -->
        <div class="flex flex-col gap-5 md:flex-row md:items-center md:justify-between">

            <div>
                <h1 class="text-2xl font-semibold">
                    Marketing
                </h1>

                <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">
                    Gestión de prospectos y seguimiento comercial de servicios.
                </p>
            </div>

            <button
                wire:click="$set('mostrarFormulario', true)"
                class="rounded-lg bg-teal-500 px-5 py-2.5 text-sm font-medium text-zinc-900 dark:text-white transition hover:bg-teal-600"
            >
                + Nuevo prospecto
            </button>

        </div>


        <!-- Indicadores -->
        <div class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">

            <div class="rounded-xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                <p class="text-sm text-zinc-500">
                    Prospectos
                </p>

                <p class="mt-2 text-2xl font-semibold">
                    {{ count($prospectos) }}
                </p>
            </div>

            <div class="rounded-xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                <p class="text-sm text-zinc-500">
                    Interesados
                </p>

                <p class="mt-2 text-2xl font-semibold text-teal-700 dark:text-teal-400">
                    {{ count(array_filter($prospectos, fn ($p) => $p['estado'] === 'Interesado')) }}
                </p>
            </div>

            <div class="rounded-xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                <p class="text-sm text-zinc-500">
                    Contactados
                </p>

                <p class="mt-2 text-2xl font-semibold">
                    {{ count(array_filter($prospectos, fn ($p) => $p['estado'] === 'Contactado')) }}
                </p>
            </div>

            <div class="rounded-xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                <p class="text-sm text-zinc-500">
                    Servicios consultados
                </p>

                <p class="mt-2 text-2xl font-semibold">
                    {{ count(array_unique(array_column($prospectos, 'servicio'))) }}
                </p>
            </div>

        </div>


        <!-- Búsqueda -->
        <div class="mt-8">

            <input
                type="text"
                wire:model.live="buscar"
                placeholder="Buscar por empresa, contacto o servicio..."
                class="w-full rounded-lg border border-zinc-300 bg-white px-4 py-3 text-sm text-zinc-900 placeholder-zinc-400 outline-none transition focus:border-teal-500 focus:ring-2 focus:ring-teal-500 dark:border-zinc-800 dark:bg-zinc-950 dark:text-white dark:placeholder-zinc-500"
            >

        </div>


        <!-- Tabla -->
        <div class="mt-5 overflow-hidden rounded-xl border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">

            <div class="overflow-x-auto">

                <table class="w-full text-left text-sm">

                    <thead class="border-b border-zinc-200 bg-zinc-50 dark:border-zinc-800 dark:bg-zinc-950">
                        <tr>
                            <th class="px-5 py-4 font-medium text-zinc-600 dark:text-zinc-400">
                                Empresa
                            </th>

                            <th class="px-5 py-4 font-medium text-zinc-600 dark:text-zinc-400">
                                Contacto
                            </th>

                            <th class="px-5 py-4 font-medium text-zinc-600 dark:text-zinc-400">
                                Servicio
                            </th>

                            <th class="px-5 py-4 font-medium text-zinc-600 dark:text-zinc-400">
                                Estado
                            </th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">

                        @forelse ($this->prospectosFiltrados as $prospecto)

                            <tr class="transition hover:bg-zinc-50 dark:hover:bg-zinc-800/50">

                                <td class="px-5 py-4">
                                    <p class="font-medium text-zinc-900 dark:text-white">
                                        {{ $prospecto['empresa'] }}
                                    </p>

                                    <p class="mt-1 text-xs text-zinc-500">
                                        {{ $prospecto['correo'] }}
                                    </p>
                                </td>

                                <td class="px-5 py-4 text-zinc-700 dark:text-zinc-300">
                                    {{ $prospecto['contacto'] }}
                                </td>

                                <td class="px-5 py-4 text-zinc-700 dark:text-zinc-300">
                                    {{ $prospecto['servicio'] }}
                                </td>

                                <td class="px-5 py-4">

                                    @php
                                        $estadoClase = match ($prospecto['estado']) {
                                            'Interesado' => 'bg-teal-500/10 text-teal-700 dark:text-teal-400',
                                            'Contactado' => 'bg-blue-50 text-blue-700 dark:bg-blue-500/10 dark:text-blue-400',
                                            default => 'bg-zinc-800 text-zinc-600 dark:text-zinc-400',
                                        };
                                    @endphp

                                    <span class="inline-flex rounded-full px-3 py-1 text-xs font-medium {{ $estadoClase }}">
                                        {{ $prospecto['estado'] }}
                                    </span>

                                </td>

                            </tr>

                        @empty

                            <tr>
                                <td
                                    colspan="4"
                                    class="px-5 py-10 text-center text-sm text-zinc-500 dark:text-zinc-500"
                                >
                                    No se encontraron prospectos.
                                </td>
                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>

        </div>

    </div>


    <!-- Modal -->
    @if ($mostrarFormulario)

        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 px-4">

            <div class="w-full max-w-lg rounded-2xl border border-zinc-200 bg-white p-6 text-zinc-900 shadow-2xl dark:border-zinc-800 dark:bg-zinc-900 dark:text-white">

                <div class="flex items-center justify-between">

                    <div>
                        <h2 class="text-lg font-semibold">
                            Nuevo prospecto
                        </h2>

                        <p class="mt-1 text-sm text-zinc-500">
                            Registra una empresa interesada en los servicios.
                        </p>
                    </div>

                    <button
                        wire:click="$set('mostrarFormulario', false)"
                        class="text-zinc-500 transition hover:text-zinc-900 dark:hover:text-white"
                    >
                        ✕
                    </button>

                </div>


                <div class="mt-6 grid gap-4">

                    <div>
                        <label class="mb-2 block text-sm text-zinc-600 dark:text-zinc-400">
                            Empresa
                        </label>

                        <input
                            wire:model="empresa"
                            type="text"
                            class="w-full rounded-lg border border-zinc-300 bg-white px-4 py-2.5 text-sm text-zinc-900 outline-none transition focus:border-teal-500 focus:ring-2 focus:ring-teal-500 dark:border-zinc-800 dark:bg-zinc-950 dark:text-white"
                        >

                        @error('empresa')
                            <p class="mt-1 text-xs text-red-400">{{ $message }}</p>
                        @enderror
                    </div>


                    <div>
                        <label class="mb-2 block text-sm text-zinc-600 dark:text-zinc-400">
                            Persona de contacto
                        </label>

                        <input
                            wire:model="contacto"
                            type="text"
                            class="w-full rounded-lg border border-zinc-300 bg-white px-4 py-2.5 text-sm text-zinc-900 outline-none transition focus:border-teal-500 focus:ring-2 focus:ring-teal-500 dark:border-zinc-800 dark:bg-zinc-950 dark:text-white"
                        >

                        @error('contacto')
                            <p class="mt-1 text-xs text-red-400">{{ $message }}</p>
                        @enderror
                    </div>


                    <div class="grid gap-4 md:grid-cols-2">

                        <div>
                            <label class="mb-2 block text-sm text-zinc-600 dark:text-zinc-400">
                                Correo
                            </label>

                            <input
                                wire:model="correo"
                                type="email"
                                class="w-full rounded-lg border border-zinc-300 bg-white px-4 py-2.5 text-sm text-zinc-900 outline-none transition focus:border-teal-500 focus:ring-2 focus:ring-teal-500 dark:border-zinc-800 dark:bg-zinc-950 dark:text-white"
                            >
                        </div>

                        <div>
                            <label class="mb-2 block text-sm text-zinc-600 dark:text-zinc-400">
                                Teléfono
                            </label>

                            <input
                                wire:model="telefono"
                                type="text"
                                class="w-full rounded-lg border border-zinc-300 bg-white px-4 py-2.5 text-sm text-zinc-900 outline-none transition focus:border-teal-500 focus:ring-2 focus:ring-teal-500 dark:border-zinc-800 dark:bg-zinc-950 dark:text-white"
                            >
                        </div>

                    </div>


                    <div>
                        <label class="mb-2 block text-sm text-zinc-600 dark:text-zinc-400">
                            Servicio de interés
                        </label>

                        <select
                            wire:model="servicio"
                            class="w-full rounded-lg border border-zinc-300 bg-white px-4 py-2.5 text-sm text-zinc-900 outline-none transition focus:border-teal-500 focus:ring-2 focus:ring-teal-500 dark:border-zinc-800 dark:bg-zinc-950 dark:text-white"
                        >
                            <option value="">Seleccionar servicio</option>
                            <option value="Contabilidad">Contabilidad</option>
                            <option value="Administración Tributaria">
                                Administración Tributaria
                            </option>
                            <option value="Elaboración de Planillas">
                                Elaboración de Planillas
                            </option>
                            <option value="Reclutamiento de Personal">
                                Reclutamiento de Personal
                            </option>
                            <option value="Servicio Integral">
                                Servicio Integral
                            </option>
                        </select>

                        @error('servicio')
                            <p class="mt-1 text-xs text-red-400">{{ $message }}</p>
                        @enderror
                    </div>


                    <div>
                        <label class="mb-2 block text-sm text-zinc-600 dark:text-zinc-400">
                            Estado
                        </label>

                        <select
                            wire:model="estado"
                            class="w-full rounded-lg border border-zinc-300 bg-white px-4 py-2.5 text-sm text-zinc-900 outline-none transition focus:border-teal-500 focus:ring-2 focus:ring-teal-500 dark:border-zinc-800 dark:bg-zinc-950 dark:text-white"
                        >
                            <option value="Prospecto">Prospecto</option>
                            <option value="Contactado">Contactado</option>
                            <option value="Interesado">Interesado</option>
                        </select>
                    </div>

                </div>


                <div class="mt-6 flex justify-end gap-3">

                    <button
                        wire:click="$set('mostrarFormulario', false)"
                        class="rounded-lg border border-zinc-200 bg-white px-5 py-2.5 text-sm text-zinc-700 transition hover:bg-zinc-50 dark:border-zinc-800 dark:bg-zinc-900 dark:text-zinc-300 dark:hover:bg-zinc-800"
                    >
                        Cancelar
                    </button>

                    <button
                        wire:click="guardarProspecto"
                        class="rounded-lg bg-teal-500 px-5 py-2.5 text-sm font-medium text-zinc-900 dark:text-white hover:bg-teal-600"
                    >
                        Guardar prospecto
                    </button>

                </div>

            </div>

        </div>

    @endif

</div>
