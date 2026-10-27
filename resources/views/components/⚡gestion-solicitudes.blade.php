
<?php

use App\Models\Solicitud;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component
{
    public string $busqueda = '';
    public string $filtroEstado = '';
    public string $filtroTipo = '';
    public string $filtroPrioridad = '';

    public ?int $solicitudId = null;
    public string $responsableId = '';
    public string $respuesta = '';
    public string $motivoRechazo = '';

    private const ESTADOS = [
        'Recibida',
        'En validación',
        'Pendiente de aprobación',
        'En atención',
        'Pendiente del solicitante',
        'Resuelta',
        'Cerrada',
        'Rechazada',
    ];

    private function rol(): string
    {
        return Auth::user()->role?->nombre ?? '';
    }

    private function verificarGestion(): void
    {
        abort_unless(
            in_array($this->rol(), [
                'Administrador',
                'Recursos Humanos',
                'Contabilidad',
            ], true),
            403,
            'No tienes permiso para gestionar solicitudes.'
        );
    }

    private function verificarAdministrador(): void
    {
        abort_unless(
            $this->rol() === 'Administrador',
            403,
            'Solo el administrador puede realizar esta operación.'
        );
    }

    private function solicitudSeleccionada(): Solicitud
    {
        return Solicitud::query()->findOrFail($this->solicitudId);
    }

    #[Computed]
    public function solicitudes()
    {
        $consulta = Solicitud::query()
            ->with(['solicitante', 'responsable', 'aprobador']);

        if (trim($this->busqueda) !== '') {
            $termino = '%' . trim($this->busqueda) . '%';

            $consulta->where(function ($q) use ($termino) {
                $q->where('codigo', 'like', $termino)
                    ->orWhere('asunto', 'like', $termino);
            });
        }

        if ($this->filtroEstado !== '') {
            $consulta->where('estado', $this->filtroEstado);
        }

        if ($this->filtroTipo !== '') {
            $consulta->where('tipo', $this->filtroTipo);
        }

        if ($this->filtroPrioridad !== '') {
            $consulta->where('prioridad', $this->filtroPrioridad);
        }

        return $consulta->latest()->get();
    }

    #[Computed]
    public function responsables()
    {
        return User::query()
            ->where('activo', true)
            ->with('role')
            ->orderBy('name')
            ->get(['id', 'name', 'role_id'])
            ->filter(fn ($usuario) => $usuario->role?->activo === true)
            ->values();
    }

    public function seleccionar(int $id): void
    {
        $this->verificarGestion();

        $solicitud = Solicitud::with('responsable')->findOrFail($id);

        $this->solicitudId = $solicitud->id;
        $this->responsableId = (string) ($solicitud->responsable_id ?? '');
        $this->respuesta = (string) ($solicitud->respuesta ?? '');
        $this->motivoRechazo = (string) ($solicitud->motivo_rechazo ?? '');

        $this->resetValidation();
    }

    public function asignarResponsable(): void
    {
        $this->verificarGestion();

        $datos = $this->validate([
            'solicitudId' => 'required|integer|exists:solicitudes,id',
            'responsableId' => 'required|integer|exists:users,id',
        ]);

        $responsable = User::query()
            ->whereKey($datos['responsableId'])
            ->where('activo', true)
            ->firstOrFail();

        abort_unless(
            $responsable->role?->activo === true,
            422,
            'El responsable debe tener un rol activo.'
        );

        $solicitud = $this->solicitudSeleccionada();

        abort_if(
            in_array($solicitud->estado, ['Cerrada', 'Rechazada'], true),
            422,
            'No se puede reasignar una solicitud cerrada o rechazada.'
        );

        $solicitud->update([
            'responsable_id' => $responsable->id,
        ]);

        session()->flash('mensaje', 'Responsable asignado correctamente.');
    }

    public function iniciarEvaluacion(): void
    {
        $this->verificarGestion();

        $solicitud = $this->solicitudSeleccionada();

        abort_unless(
            $solicitud->estado === 'Recibida',
            422,
            'Solo se pueden evaluar solicitudes recibidas.'
        );

        $solicitud->update(['estado' => 'En validación']);

        session()->flash('mensaje', 'La solicitud pasó a evaluación.');
    }

    public function enviarAprobacion(): void
    {
        $this->verificarGestion();

        $solicitud = $this->solicitudSeleccionada();

        abort_unless(
            $solicitud->estado === 'En validación',
            422,
            'La solicitud debe estar en validación.'
        );

        $solicitud->update([
            'estado' => 'Pendiente de aprobación',
        ]);

        session()->flash('mensaje', 'Solicitud enviada a aprobación.');
    }

    public function aprobar(): void
    {
        $this->verificarAdministrador();

        DB::transaction(function () {
            $solicitud = Solicitud::query()
                ->lockForUpdate()
                ->findOrFail($this->solicitudId);

            abort_unless(
                $solicitud->estado === 'Pendiente de aprobación',
                422,
                'La solicitud no está pendiente de aprobación.'
            );

            $solicitud->update([
                'estado' => 'En atención',
                'aprobador_id' => Auth::id(),
                'fecha_aprobacion' => now(),
            ]);
        });

        session()->flash('mensaje', 'Solicitud aprobada y enviada a atención.');
    }

    public function rechazar(): void
    {
        $this->verificarAdministrador();

        $this->validate([
            'solicitudId' => 'required|integer|exists:solicitudes,id',
            'motivoRechazo' => 'required|string|min:5|max:2000',
        ]);

        $solicitud = $this->solicitudSeleccionada();

        abort_unless(
            in_array(
                $solicitud->estado,
                ['En validación', 'Pendiente de aprobación'],
                true
            ),
            422,
            'La solicitud no se encuentra en una etapa que permita rechazarla.'
        );

        $solicitud->update([
            'estado' => 'Rechazada',
            'aprobador_id' => Auth::id(),
            'motivo_rechazo' => trim($this->motivoRechazo),
        ]);

        session()->flash('mensaje', 'Solicitud rechazada con motivo registrado.');
    }

    public function solicitarInformacion(): void
    {
        $this->verificarGestion();

        $this->validate([
            'solicitudId' => 'required|integer|exists:solicitudes,id',
            'respuesta' => 'required|string|min:5|max:5000',
        ]);

        $solicitud = $this->solicitudSeleccionada();

        abort_unless(
            $solicitud->estado === 'En atención',
            422,
            'La solicitud debe estar en atención.'
        );

        $solicitud->update([
            'estado' => 'Pendiente del solicitante',
            'respuesta' => trim($this->respuesta),
        ]);

        session()->flash('mensaje', 'Se registró la información pendiente del solicitante.');
    }

    public function resolver(): void
    {
        $this->verificarGestion();

        $this->validate([
            'solicitudId' => 'required|integer|exists:solicitudes,id',
            'respuesta' => 'required|string|min:5|max:5000',
        ]);

        $solicitud = $this->solicitudSeleccionada();

        abort_unless(
            in_array(
                $solicitud->estado,
                ['En atención', 'Pendiente del solicitante'],
                true
            ),
            422,
            'Solo se puede resolver una solicitud que está en atención o pendiente del solicitante.'
        );

        $solicitud->update([
            'estado' => 'Resuelta',
            'respuesta' => trim($this->respuesta),
            'fecha_resolucion' => now(),
        ]);

        session()->flash('mensaje', 'Solicitud resuelta correctamente.');
    }

    public function cerrar(): void
    {
        $this->verificarGestion();

        $solicitud = $this->solicitudSeleccionada();

        abort_unless(
            $solicitud->estado === 'Resuelta',
            422,
            'Solo se pueden cerrar solicitudes resueltas.'
        );

        $solicitud->update([
            'estado' => 'Cerrada',
            'fecha_cierre' => now(),
        ]);

        session()->flash('mensaje', 'Solicitud cerrada correctamente.');
    }

    public function render(): mixed
    {
        return view('components.⚡gestion-solicitudes');
    }
};
?>
@php
    $listaSolicitudes = $this->solicitudes;
@endphp

<div class="min-h-full space-y-6 p-4 text-slate-800 sm:p-6 dark:text-slate-100">
    <header class="flex flex-col gap-4 border-b border-slate-200 pb-5 sm:flex-row sm:items-end sm:justify-between dark:border-slate-700">
        <div>
            <p class="text-xs font-bold uppercase tracking-[0.18em] text-indigo-600 dark:text-indigo-300">Mesa de servicios</p>
            <h1 class="mt-2 text-2xl font-bold tracking-tight text-slate-950 sm:text-3xl dark:text-white">Gestión de solicitudes</h1>
            <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-600 dark:text-slate-300">Evalúa solicitudes, asigna responsables, registra decisiones y da seguimiento hasta su cierre.</p>
        </div>
        <div class="inline-flex w-fit items-center gap-2 rounded-full border border-slate-200 bg-white px-3 py-2 text-xs font-medium text-slate-600 shadow-sm dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300">
            <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
            Flujo de atención
        </div>
    </header>

    @if (session()->has('mensaje'))
        <div role="status" class="flex items-start gap-3 rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-900 dark:border-emerald-900/70 dark:bg-emerald-950/50 dark:text-emerald-200">
            <span class="mt-0.5 inline-flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-emerald-100 font-bold dark:bg-emerald-900">✓</span>
            <div>{{ session('mensaje') }}</div>
        </div>
    @endif

    @php
        $totalVisible = $listaSolicitudes->count();
        $recibidasVisibles = $listaSolicitudes->where('estado', 'Recibida')->count();
        $atencionVisible = $listaSolicitudes->filter(fn ($item) => str_starts_with($item->estado, 'En atenci'))->count();
        $aprobacionVisible = $listaSolicitudes->filter(fn ($item) => str_starts_with($item->estado, 'Pendiente de aprobaci'))->count();
    @endphp

    <section aria-label="Resumen de solicitudes" class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
        <article class="relative overflow-hidden rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-700 dark:bg-slate-900">
            <div class="absolute inset-y-0 left-0 w-1 bg-indigo-500"></div>
            <p class="text-sm font-medium text-slate-500 dark:text-slate-400">Coincidencias</p>
            <div class="mt-2 flex items-end justify-between gap-3"><p class="text-3xl font-bold tabular-nums text-slate-950 dark:text-white">{{ $totalVisible }}</p><span class="rounded-lg bg-indigo-50 px-2 py-1 text-xs font-semibold text-indigo-700 dark:bg-indigo-950 dark:text-indigo-300">Con filtros</span></div>
        </article>
        <article class="relative overflow-hidden rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-700 dark:bg-slate-900">
            <div class="absolute inset-y-0 left-0 w-1 bg-sky-500"></div>
            <p class="text-sm font-medium text-slate-500 dark:text-slate-400">Recibidas</p>
            <p class="mt-2 text-3xl font-bold tabular-nums text-slate-950 dark:text-white">{{ $recibidasVisibles }}</p>
            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Pendientes de evaluación</p>
        </article>
        <article class="relative overflow-hidden rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-700 dark:bg-slate-900">
            <div class="absolute inset-y-0 left-0 w-1 bg-amber-500"></div>
            <p class="text-sm font-medium text-slate-500 dark:text-slate-400">En atención</p>
            <p class="mt-2 text-3xl font-bold tabular-nums text-slate-950 dark:text-white">{{ $atencionVisible }}</p>
            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">En proceso de solución</p>
        </article>
        <article class="relative overflow-hidden rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-700 dark:bg-slate-900">
            <div class="absolute inset-y-0 left-0 w-1 bg-violet-500"></div>
            <p class="text-sm font-medium text-slate-500 dark:text-slate-400">Por aprobar</p>
            <p class="mt-2 text-3xl font-bold tabular-nums text-slate-950 dark:text-white">{{ $aprobacionVisible }}</p>
            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Requieren decisión administrativa</p>
        </article>
    </section>

    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-900">
        <div class="border-b border-slate-200 px-5 py-4 dark:border-slate-700">
            <h2 class="text-base font-bold text-slate-950 dark:text-white">Buscar y filtrar</h2>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Usa uno o varios filtros para localizar una solicitud específica.</p>
        </div>
        <div class="grid gap-4 p-5 sm:grid-cols-2 xl:grid-cols-4">
            <div>
                <label for="busqueda-solicitudes" class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-600 dark:text-slate-300">Código o asunto</label>
                <input id="busqueda-solicitudes" type="search" wire:model.live.debounce.300ms="busqueda" placeholder="Ej. SOL-2026 o acceso" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-900 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 dark:border-slate-600 dark:bg-slate-950 dark:text-white dark:placeholder:text-slate-500 dark:focus:border-indigo-400" />
            </div>
            <div>
                <label for="filtro-estado" class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-600 dark:text-slate-300">Estado</label>
                <select id="filtro-estado" wire:model.live="filtroEstado" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-900 shadow-sm outline-none transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 dark:border-slate-600 dark:bg-slate-950 dark:text-white dark:focus:border-indigo-400">
                    <option value="">Todos los estados</option>
                    @foreach (['Recibida', 'En validación', 'Pendiente de aprobación', 'En atención', 'Pendiente del solicitante', 'Resuelta', 'Cerrada', 'Rechazada'] as $estado)
                        <option value="{{ $estado }}">{{ $estado }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="filtro-tipo" class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-600 dark:text-slate-300">Tipo</label>
                <select id="filtro-tipo" wire:model.live="filtroTipo" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-900 shadow-sm outline-none transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 dark:border-slate-600 dark:bg-slate-950 dark:text-white dark:focus:border-indigo-400">
                    <option value="">Todos los tipos</option>
                    <option value="Acceso">Acceso</option>
                    <option value="Servicio">Servicio</option>
                    <option value="Información">Información</option>
                </select>
            </div>
            <div>
                <label for="filtro-prioridad" class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-600 dark:text-slate-300">Prioridad</label>
                <select id="filtro-prioridad" wire:model.live="filtroPrioridad" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-900 shadow-sm outline-none transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 dark:border-slate-600 dark:bg-slate-950 dark:text-white dark:focus:border-indigo-400">
                    <option value="">Todas las prioridades</option>
                    <option value="Baja">Baja</option>
                    <option value="Media">Media</option>
                    <option value="Alta">Alta</option>
                    <option value="Crítica">Crítica</option>
                </select>
            </div>
        </div>
    </section>

    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-900">
        <div class="flex flex-col gap-2 border-b border-slate-200 px-5 py-4 sm:flex-row sm:items-center sm:justify-between dark:border-slate-700">
            <div>
                <h2 class="text-base font-bold text-slate-950 dark:text-white">Listado de solicitudes</h2>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Selecciona una solicitud para ver sus datos y acciones disponibles.</p>
            </div>
            <span class="w-fit rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600 dark:bg-slate-800 dark:text-slate-300">{{ $totalVisible }} resultado(s)</span>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-[900px] w-full text-left text-sm">
                <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500 dark:bg-slate-950/70 dark:text-slate-400">
                    <tr>
                        <th scope="col" class="px-5 py-3.5 font-semibold">Solicitud</th>
                        <th scope="col" class="px-4 py-3.5 font-semibold">Solicitante</th>
                        <th scope="col" class="px-4 py-3.5 font-semibold">Prioridad</th>
                        <th scope="col" class="px-4 py-3.5 font-semibold">Responsable</th>
                        <th scope="col" class="px-4 py-3.5 font-semibold">Estado</th>
                        <th scope="col" class="px-4 py-3.5 font-semibold">Fecha de registro</th>
                        <th scope="col" class="px-5 py-3.5 text-right font-semibold">Acción</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse ($listaSolicitudes as $solicitud)
                        @php
                            $estadoFila = (string) $solicitud->estado;
                            $claseEstado = 'bg-slate-100 text-slate-700 ring-slate-600/10 dark:bg-slate-800 dark:text-slate-200 dark:ring-slate-400/20';
                            if ($estadoFila === 'Recibida') $claseEstado = 'bg-sky-50 text-sky-700 ring-sky-600/20 dark:bg-sky-950/60 dark:text-sky-300 dark:ring-sky-400/20';
                            elseif (str_starts_with($estadoFila, 'En validaci')) $claseEstado = 'bg-indigo-50 text-indigo-700 ring-indigo-600/20 dark:bg-indigo-950/60 dark:text-indigo-300 dark:ring-indigo-400/20';
                            elseif (str_starts_with($estadoFila, 'Pendiente de aprobaci')) $claseEstado = 'bg-violet-50 text-violet-700 ring-violet-600/20 dark:bg-violet-950/60 dark:text-violet-300 dark:ring-violet-400/20';
                            elseif (str_starts_with($estadoFila, 'En atenci')) $claseEstado = 'bg-amber-50 text-amber-800 ring-amber-600/20 dark:bg-amber-950/60 dark:text-amber-300 dark:ring-amber-400/20';
                            elseif ($estadoFila === 'Pendiente del solicitante') $claseEstado = 'bg-orange-50 text-orange-800 ring-orange-600/20 dark:bg-orange-950/60 dark:text-orange-300 dark:ring-orange-400/20';
                            elseif ($estadoFila === 'Resuelta') $claseEstado = 'bg-emerald-50 text-emerald-700 ring-emerald-600/20 dark:bg-emerald-950/60 dark:text-emerald-300 dark:ring-emerald-400/20';
                            elseif ($estadoFila === 'Cerrada') $claseEstado = 'bg-slate-200 text-slate-700 ring-slate-600/20 dark:bg-slate-700 dark:text-slate-200 dark:ring-slate-400/20';
                            elseif ($estadoFila === 'Rechazada') $claseEstado = 'bg-rose-50 text-rose-700 ring-rose-600/20 dark:bg-rose-950/60 dark:text-rose-300 dark:ring-rose-400/20';

                            $prioridadFila = (string) $solicitud->prioridad;
                            $clasePrioridad = 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-200';
                            if ($prioridadFila === 'Baja') $clasePrioridad = 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300';
                            elseif ($prioridadFila === 'Media') $clasePrioridad = 'bg-blue-50 text-blue-700 dark:bg-blue-950/60 dark:text-blue-300';
                            elseif ($prioridadFila === 'Alta') $clasePrioridad = 'bg-amber-50 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300';
                            elseif (str_starts_with($prioridadFila, 'Cr')) $clasePrioridad = 'bg-rose-50 text-rose-700 dark:bg-rose-950/60 dark:text-rose-300';
                        @endphp
                        <tr wire:key="solicitud-{{ $solicitud->id }}" class="transition hover:bg-slate-50/80 dark:hover:bg-slate-800/40">
                            <td class="max-w-sm px-5 py-4">
                                <div class="font-mono text-xs font-bold text-indigo-700 dark:text-indigo-300">{{ $solicitud->codigo }}</div>
                                <div class="mt-1.5 font-semibold text-slate-900 dark:text-white">{{ $solicitud->asunto }}</div>
                                <div class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ $solicitud->tipo }}</div>
                            </td>
                            <td class="px-4 py-4 text-slate-700 dark:text-slate-300">{{ $solicitud->solicitante?->name ?? 'Usuario no disponible' }}</td>
                            <td class="px-4 py-4"><span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $clasePrioridad }}">{{ $solicitud->prioridad }}</span></td>
                            <td class="px-4 py-4 text-slate-700 dark:text-slate-300">{{ $solicitud->responsable?->name ?? 'Sin asignar' }}</td>
                            <td class="px-4 py-4"><span class="inline-flex whitespace-nowrap rounded-full px-2.5 py-1 text-xs font-semibold ring-1 ring-inset {{ $claseEstado }}">{{ $solicitud->estado }}</span></td>
                            <td class="whitespace-nowrap px-4 py-4 text-xs text-slate-500 dark:text-slate-400">{{ $solicitud->created_at?->format('d/m/Y H:i') }}</td>
                            <td class="px-5 py-4 text-right">
                                <button type="button" wire:click="seleccionar({{ $solicitud->id }})" wire:loading.attr="disabled" class="inline-flex items-center justify-center rounded-lg bg-indigo-600 px-3.5 py-2 text-xs font-semibold text-white shadow-sm transition hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:cursor-wait disabled:opacity-60 dark:focus:ring-offset-slate-900">
                                    <span wire:loading.remove wire:target="seleccionar({{ $solicitud->id }})">Gestionar</span>
                                    <span wire:loading wire:target="seleccionar({{ $solicitud->id }})">Abriendo…</span>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-14 text-center">
                                <div class="mx-auto flex max-w-md flex-col items-center">
                                    <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-slate-100 text-xl text-slate-500 dark:bg-slate-800 dark:text-slate-300">⌕</span>
                                    <p class="mt-3 font-semibold text-slate-900 dark:text-white">No se encontraron solicitudes</p>
                                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Prueba con otro texto o cambia los filtros seleccionados.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-slate-200 bg-slate-50/70 px-5 py-3 text-xs text-slate-500 dark:border-slate-700 dark:bg-slate-950/40 dark:text-slate-400">En pantallas pequeñas, desplaza la tabla horizontalmente para consultar todas las columnas.</div>
    </section>

    @if ($solicitudId)
        @php
            $actual = \App\Models\Solicitud::with(['solicitante', 'responsable', 'aprobador'])->find($solicitudId);
        @endphp

        @if ($actual)
            @php
                $estadoDetalle = (string) $actual->estado;
                $claseDetalle = 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-200';
                if ($estadoDetalle === 'Recibida') $claseDetalle = 'bg-sky-50 text-sky-700 dark:bg-sky-950/60 dark:text-sky-300';
                elseif (str_starts_with($estadoDetalle, 'En validaci')) $claseDetalle = 'bg-indigo-50 text-indigo-700 dark:bg-indigo-950/60 dark:text-indigo-300';
                elseif (str_starts_with($estadoDetalle, 'Pendiente de aprobaci')) $claseDetalle = 'bg-violet-50 text-violet-700 dark:bg-violet-950/60 dark:text-violet-300';
                elseif (str_starts_with($estadoDetalle, 'En atenci')) $claseDetalle = 'bg-amber-50 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300';
                elseif ($estadoDetalle === 'Pendiente del solicitante') $claseDetalle = 'bg-orange-50 text-orange-800 dark:bg-orange-950/60 dark:text-orange-300';
                elseif ($estadoDetalle === 'Resuelta') $claseDetalle = 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300';
                elseif ($estadoDetalle === 'Rechazada') $claseDetalle = 'bg-rose-50 text-rose-700 dark:bg-rose-950/60 dark:text-rose-300';
            @endphp
            <section class="overflow-hidden rounded-2xl border border-indigo-200 bg-white shadow-md shadow-indigo-950/5 dark:border-slate-700 dark:bg-slate-900">
                <div class="flex flex-col gap-4 border-b border-slate-200 bg-slate-50/80 p-5 sm:flex-row sm:items-start sm:justify-between dark:border-slate-700 dark:bg-slate-950/50">
                    <div class="min-w-0">
                        <p class="text-xs font-bold uppercase tracking-[0.15em] text-indigo-600 dark:text-indigo-300">Detalle y acciones</p>
                        <h2 class="mt-1 break-words text-xl font-bold text-slate-950 dark:text-white">{{ $actual->codigo }}</h2>
                        <p class="mt-1 font-medium text-slate-700 dark:text-slate-300">{{ $actual->asunto }}</p>
                    </div>
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="inline-flex rounded-full px-3 py-1.5 text-xs font-semibold {{ $claseDetalle }}">{{ $actual->estado }}</span>
                        <button type="button" wire:click="$set('solicitudId', null)" class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-xs font-semibold text-slate-700 transition hover:bg-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800">Cerrar detalle</button>
                    </div>
                </div>

                <div class="space-y-6 p-5 sm:p-6">
                    <div class="grid gap-4 lg:grid-cols-3">
                        <div class="rounded-xl border border-slate-200 p-4 dark:border-slate-700">
                            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Solicitante</p>
                            <p class="mt-1 font-semibold text-slate-900 dark:text-white">{{ $actual->solicitante?->name ?? 'Usuario no disponible' }}</p>
                            <p class="mt-3 text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Tipo · prioridad</p>
                            <p class="mt-1 text-sm text-slate-700 dark:text-slate-300">{{ $actual->tipo }} · {{ $actual->prioridad }}</p>
                        </div>
                        <div class="rounded-xl border border-slate-200 p-4 dark:border-slate-700 lg:col-span-2">
                            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Descripción de la solicitud</p>
                            <p class="mt-2 whitespace-pre-line text-sm leading-6 text-slate-700 dark:text-slate-300">{{ $actual->descripcion }}</p>
                        </div>
                    </div>

                    @if ($actual->motivo_rechazo)
                        <div class="rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-800 dark:border-rose-900/70 dark:bg-rose-950/40 dark:text-rose-200">
                            <p class="font-bold">Motivo del rechazo</p><p class="mt-1 whitespace-pre-line">{{ $actual->motivo_rechazo }}</p>
                        </div>
                    @endif
                    @if ($actual->respuesta)
                        <div class="rounded-xl border border-sky-200 bg-sky-50 p-4 text-sm text-sky-900 dark:border-sky-900/70 dark:bg-sky-950/40 dark:text-sky-200">
                            <p class="font-bold">Respuesta o comentario registrado</p><p class="mt-1 whitespace-pre-line">{{ $actual->respuesta }}</p>
                        </div>
                    @endif

                    @if (in_array(auth()->user()->role?->nombre, ['Administrador', 'Recursos Humanos', 'Contabilidad'], true))
                        <div class="rounded-xl border border-slate-200 p-4 dark:border-slate-700">
                            <label for="responsable-solicitud" class="block text-sm font-bold text-slate-900 dark:text-white">Asignar responsable</label>
                            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Elige una persona activa con un rol activo.</p>
                            <div class="mt-3 flex flex-col gap-3 sm:flex-row">
                                <select id="responsable-solicitud" wire:model="responsableId" class="min-w-0 flex-1 rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-900 outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 dark:border-slate-600 dark:bg-slate-950 dark:text-white">
                                    <option value="">Selecciona un responsable</option>
                                    @foreach ($this->responsables as $usuario)
                                        <option value="{{ $usuario->id }}">{{ $usuario->name }} — {{ $usuario->role?->nombre }}</option>
                                    @endforeach
                                </select>
                                <button type="button" wire:click="asignarResponsable" wire:loading.attr="disabled" class="rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-slate-700 disabled:opacity-60 dark:bg-slate-100 dark:text-slate-900 dark:hover:bg-white">Guardar responsable</button>
                            </div>
                            @error('responsableId') <p class="mt-2 text-sm text-rose-600 dark:text-rose-400">{{ $message }}</p> @enderror
                        </div>
                    @endif

                    <div class="grid gap-4 lg:grid-cols-2">
                        <div>
                            <label for="respuesta-solicitud" class="block text-sm font-bold text-slate-900 dark:text-white">Respuesta o comentario de atención</label>
                            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Registra la evaluación, información que falta o solución aplicada, según la acción.</p>
                            <textarea id="respuesta-solicitud" wire:model="respuesta" rows="4" maxlength="5000" class="mt-2 w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 dark:border-slate-600 dark:bg-slate-950 dark:text-white dark:placeholder:text-slate-500" placeholder="Escribe una respuesta clara para el solicitante…"></textarea>
                            <p class="mt-1 text-right text-xs text-slate-400">Máximo 5000 caracteres</p>
                            @error('respuesta') <p class="mt-1 text-sm text-rose-600 dark:text-rose-400">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="motivo-rechazo" class="block text-sm font-bold text-slate-900 dark:text-white">Motivo de rechazo</label>
                            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Solo es obligatorio cuando el administrador rechaza una solicitud.</p>
                            <textarea id="motivo-rechazo" wire:model="motivoRechazo" rows="4" maxlength="2000" class="mt-2 w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 dark:border-slate-600 dark:bg-slate-950 dark:text-white dark:placeholder:text-slate-500" placeholder="Explica la razón concreta del rechazo…"></textarea>
                            <p class="mt-1 text-right text-xs text-slate-400">Máximo 2000 caracteres</p>
                            @error('motivoRechazo') <p class="mt-1 text-sm text-rose-600 dark:text-rose-400">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="rounded-xl bg-slate-50 p-4 dark:bg-slate-950/60">
                        <div class="mb-3 flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
                            <h3 class="text-sm font-bold text-slate-900 dark:text-white">Acciones del flujo</h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400">Las acciones dependen del estado y del rol.</p>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            @if ($actual->estado === 'Recibida')
                                <button type="button" wire:click="iniciarEvaluacion" wire:loading.attr="disabled" class="rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-700 disabled:opacity-60">Iniciar evaluación</button>
                            @endif

                            @if (str_starts_with($actual->estado, 'En validaci'))
                                <button type="button" wire:click="enviarAprobacion" wire:loading.attr="disabled" class="rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-700 disabled:opacity-60">Enviar a aprobación</button>
                            @endif

                            @if (str_starts_with($actual->estado, 'Pendiente de aprobaci') && auth()->user()->role?->nombre === 'Administrador')
                                <button type="button" wire:click="aprobar" wire:loading.attr="disabled" class="rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-700 disabled:opacity-60">Aprobar y enviar a atención</button>
                                <button type="button" wire:click="rechazar" wire:confirm="¿Confirmas el rechazo? Debes registrar el motivo." wire:loading.attr="disabled" class="rounded-lg bg-rose-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-rose-700 disabled:opacity-60">Rechazar solicitud</button>
                            @endif

                            @if (str_starts_with($actual->estado, 'En validaci') && auth()->user()->role?->nombre === 'Administrador')
                                <button type="button" wire:click="rechazar" wire:confirm="¿Confirmas el rechazo? Debes registrar el motivo." wire:loading.attr="disabled" class="rounded-lg bg-rose-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-rose-700 disabled:opacity-60">Rechazar solicitud</button>
                            @endif

                            @if (str_starts_with($actual->estado, 'En atenci'))
                                <button type="button" wire:click="solicitarInformacion" wire:loading.attr="disabled" class="rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-100 disabled:opacity-60 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800">Solicitar información</button>
                                <button type="button" wire:click="resolver" wire:loading.attr="disabled" class="rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-700 disabled:opacity-60">Marcar como resuelta</button>
                            @endif

                            @if ($actual->estado === 'Pendiente del solicitante')
                                <button type="button" wire:click="resolver" wire:loading.attr="disabled" class="rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-700 disabled:opacity-60">Registrar respuesta y resolver</button>
                            @endif

                            @if ($actual->estado === 'Resuelta')
                                <button type="button" wire:click="cerrar" wire:confirm="¿Confirmas que la solicitud está resuelta y puede cerrarse?" wire:loading.attr="disabled" class="rounded-lg bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-slate-700 disabled:opacity-60 dark:bg-slate-100 dark:text-slate-900 dark:hover:bg-white">Cerrar solicitud</button>
                            @endif
                        </div>
                    </div>

                    <div class="grid gap-3 border-t border-slate-200 pt-4 text-xs text-slate-500 sm:grid-cols-2 dark:border-slate-700 dark:text-slate-400">
                        <p>Responsable actual: <span class="font-semibold text-slate-700 dark:text-slate-200">{{ $actual->responsable?->name ?? 'Sin asignar' }}</span></p>
                        @if ($actual->aprobador)
                            <p>Aprobador: <span class="font-semibold text-slate-700 dark:text-slate-200">{{ $actual->aprobador->name }}</span></p>
                        @endif
                        @if ($actual->fecha_aprobacion)
                            <p>Fecha de aprobación: {{ $actual->fecha_aprobacion->format('d/m/Y H:i') }}</p>
                        @endif
                        @if ($actual->fecha_resolucion)
                            <p>Fecha de resolución: {{ $actual->fecha_resolucion->format('d/m/Y H:i') }}</p>
                        @endif
                        @if ($actual->fecha_cierre)
                            <p>Fecha de cierre: {{ $actual->fecha_cierre->format('d/m/Y H:i') }}</p>
                        @endif
                    </div>
                </div>
            </section>
        @endif
    @endif
</div>