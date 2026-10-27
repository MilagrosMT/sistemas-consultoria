
<?php

use App\Models\Solicitud;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\Attributes\Computed;

new class extends Component
{
    public string $tipo = 'Servicio';
    public string $asunto = '';
    public string $descripcion = '';
    public string $prioridad = 'Media';

    public string $busqueda = '';
    public string $filtroEstado = '';

    protected function messages(): array
    {
        return [
            'tipo.required' => 'Selecciona el tipo de solicitud.',
            'tipo.in' => 'Selecciona un tipo de solicitud válido.',
            'asunto.required' => 'Ingresa el asunto de la solicitud.',
            'asunto.string' => 'El asunto debe ser texto.',
            'asunto.max' => 'El asunto no debe superar los 150 caracteres.',
            'descripcion.required' => 'Describe qué necesitas y por qué.',
            'descripcion.string' => 'La descripción debe ser texto.',
            'descripcion.max' => 'La descripción no debe superar los 5000 caracteres.',
            'prioridad.required' => 'Selecciona una prioridad.',
            'prioridad.in' => 'Selecciona una prioridad válida.',
        ];
    }

    protected function validationAttributes(): array
    {
        return [
            'tipo' => 'tipo de solicitud',
            'asunto' => 'asunto',
            'descripcion' => 'descripción',
            'prioridad' => 'prioridad',
        ];
    }
    protected function rules(): array
    {
        return [
            'tipo' => 'required|in:Acceso,Servicio,Información',
            'asunto' => 'required|string|max:150',
            'descripcion' => 'required|string|max:5000',
            'prioridad' => 'required|in:Baja,Media,Alta,Crítica',
        ];
    }

    public function registrar(): void
    {
        $datos = $this->validate();

        DB::transaction(function () use ($datos) {
            $solicitud = Solicitud::create([
                'codigo' => 'SOL-' . now()->format('Ymd') . '-' . Str::upper(Str::random(6)),
                'solicitante_id' => Auth::id(),
                'tipo' => $datos['tipo'],
                'asunto' => $datos['asunto'],
                'descripcion' => $datos['descripcion'],
                'prioridad' => $datos['prioridad'],
                'estado' => 'Recibida',
            ]);
        });

        $this->reset(['asunto', 'descripcion']);
        $this->tipo = 'Servicio';
        $this->prioridad = 'Media';

        session()->flash('mensaje', 'La solicitud se registró correctamente.');
    }

    public function limpiarFiltros(): void
    {
        $this->busqueda = '';
        $this->filtroEstado = '';
    }
   #[Computed]
public function solicitudes()
{
    $consulta = Solicitud::query()
        ->with(['solicitante', 'responsable'])
        ->where('solicitante_id', Auth::id());

    if (trim($this->busqueda) !== '') {
        $termino = '%' . trim($this->busqueda) . '%';

        $consulta->where(function ($query) use ($termino) {
            $query->where('codigo', 'like', $termino)
                ->orWhere('asunto', 'like', $termino);
        });
    }

    if ($this->filtroEstado !== '') {
        $consulta->where('estado', $this->filtroEstado);
    }

    return $consulta->latest()->get();
}
};
?>
<div class="space-y-6 p-4 sm:p-6 lg:p-8 text-slate-900 dark:text-slate-100">
    <header class="flex flex-col gap-4 border-b border-slate-200 pb-5 sm:flex-row sm:items-end sm:justify-between dark:border-slate-800">
        <div>
            <p class="text-xs font-bold uppercase tracking-[0.18em] text-indigo-600 dark:text-indigo-300">Mesa personal de servicios</p>
            <h1 class="mt-2 text-2xl font-bold tracking-tight text-slate-950 sm:text-3xl dark:text-white">Mis solicitudes</h1>
            <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-600 dark:text-slate-300">Registra solicitudes de acceso, servicio e información, y consulta su avance desde un solo lugar.</p>
        </div>
        <div class="inline-flex w-fit items-center gap-2 rounded-full border border-emerald-200 bg-emerald-50 px-3 py-2 text-xs font-semibold text-emerald-800 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-300">
            <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
            Seguimiento disponible
        </div>
    </header>

    @if (session()->has('mensaje'))
        <div role="status" class="flex items-start gap-3 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900 shadow-sm dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-200">
            <span class="mt-0.5 inline-flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-emerald-100 font-bold text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-300">✓</span>
            <p>{{ session('mensaje') }}</p>
        </div>
    @endif

    <section class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4" aria-label="Resumen de mis solicitudes">
        <article class="relative overflow-hidden rounded-xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-700 dark:bg-slate-900">
            <span class="absolute inset-y-0 left-0 w-1 bg-indigo-500"></span>
            <p class="text-sm font-medium text-slate-500 dark:text-slate-400">Resultados</p>
            <p class="mt-2 text-3xl font-bold tracking-tight text-slate-950 dark:text-white">{{ $this->solicitudes->count() }}</p>
            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Según los filtros actuales</p>
        </article>
        <article class="relative overflow-hidden rounded-xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-700 dark:bg-slate-900">
            <span class="absolute inset-y-0 left-0 w-1 bg-sky-500"></span>
            <p class="text-sm font-medium text-slate-500 dark:text-slate-400">Recibidas</p>
            <p class="mt-2 text-3xl font-bold tracking-tight text-slate-950 dark:text-white">{{ $this->solicitudes->where('estado', 'Recibida')->count() }}</p>
            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Pendientes de evaluación</p>
        </article>
        <article class="relative overflow-hidden rounded-xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-700 dark:bg-slate-900">
            <span class="absolute inset-y-0 left-0 w-1 bg-amber-500"></span>
            <p class="text-sm font-medium text-slate-500 dark:text-slate-400">En proceso</p>
            <p class="mt-2 text-3xl font-bold tracking-tight text-slate-950 dark:text-white">{{ $this->solicitudes->whereIn('estado', ['En validación', 'Pendiente de aprobación', 'En atención', 'Pendiente del solicitante'])->count() }}</p>
            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">En evaluación o atención</p>
        </article>
        <article class="relative overflow-hidden rounded-xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-700 dark:bg-slate-900">
            <span class="absolute inset-y-0 left-0 w-1 bg-emerald-500"></span>
            <p class="text-sm font-medium text-slate-500 dark:text-slate-400">Finalizadas</p>
            <p class="mt-2 text-3xl font-bold tracking-tight text-slate-950 dark:text-white">{{ $this->solicitudes->whereIn('estado', ['Resuelta', 'Cerrada'])->count() }}</p>
            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Resueltas o cerradas</p>
        </article>
    </section>

    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-900">
        <div class="border-b border-slate-200 px-5 py-4 sm:px-6 dark:border-slate-800">
            <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-lg font-bold text-slate-950 dark:text-white">Registrar solicitud</h2>
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Completa los datos para que el equipo pueda evaluar tu requerimiento.</p>
                </div>
                <span class="mt-2 w-fit rounded-md bg-slate-100 px-2 py-1 text-xs font-medium text-slate-600 sm:mt-0 dark:bg-slate-800 dark:text-slate-300">Los campos son obligatorios</span>
            </div>
        </div>

        <form wire:submit="registrar" class="space-y-5 p-5 sm:p-6">
            <div class="grid gap-5 md:grid-cols-2">
                <div>
                    <label for="tipo" class="mb-1.5 block text-sm font-semibold text-slate-700 dark:text-slate-200">Tipo de solicitud <span class="text-rose-500">*</span></label>
                    <select id="tipo" wire:model="tipo" required class="block w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-900 shadow-sm outline-none transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 dark:border-slate-700 dark:bg-slate-800 dark:text-white">
                        <option value="Acceso">Acceso</option>
                        <option value="Servicio">Servicio</option>
                        <option value="Información">Información</option>
                    </select>
                    @error('tipo') <p class="mt-1.5 text-sm font-medium text-rose-600 dark:text-rose-400">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="prioridad" class="mb-1.5 block text-sm font-semibold text-slate-700 dark:text-slate-200">Prioridad <span class="text-rose-500">*</span></label>
                    <select id="prioridad" wire:model="prioridad" required class="block w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-900 shadow-sm outline-none transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 dark:border-slate-700 dark:bg-slate-800 dark:text-white">
                        <option value="Baja">Baja</option>
                        <option value="Media">Media</option>
                        <option value="Alta">Alta</option>
                        <option value="Crítica">Crítica</option>
                    </select>
                    @error('prioridad') <p class="mt-1.5 text-sm font-medium text-rose-600 dark:text-rose-400">{{ $message }}</p> @enderror
                </div>
            </div>

            <div>
                <label for="asunto" class="mb-1.5 block text-sm font-semibold text-slate-700 dark:text-slate-200">Asunto <span class="text-rose-500">*</span></label>
                <input id="asunto" type="text" wire:model="asunto" maxlength="150" required autocomplete="off" aria-describedby="asunto-ayuda"
                    placeholder="Ej. Solicitud de acceso al sistema contable"
                    class="block w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-900 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 dark:border-slate-700 dark:bg-slate-800 dark:text-white dark:placeholder:text-slate-500">
                <p id="asunto-ayuda" class="mt-1.5 text-xs text-slate-500 dark:text-slate-400">Resume en una frase qué necesitas (máximo 150 caracteres).</p>
                @error('asunto') <p class="mt-1.5 text-sm font-medium text-rose-600 dark:text-rose-400">{{ $message }}</p> @enderror
            </div>

            <div>
                <div class="mb-1.5 flex items-center justify-between gap-3">
                    <label for="descripcion" class="block text-sm font-semibold text-slate-700 dark:text-slate-200">Descripción y justificación <span class="text-rose-500">*</span></label>
                    <span class="text-xs text-slate-500 dark:text-slate-400">Máx. 5000 caracteres</span>
                </div>
                <textarea id="descripcion" wire:model="descripcion" rows="4" maxlength="5000" required
                    placeholder="Explica qué necesitas, qué problema ocurre y por qué se requiere la solicitud..."
                    class="block w-full resize-y rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm leading-6 text-slate-900 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 dark:border-slate-700 dark:bg-slate-800 dark:text-white dark:placeholder:text-slate-500"></textarea>
                @error('descripcion') <p class="mt-1.5 text-sm font-medium text-rose-600 dark:text-rose-400">{{ $message }}</p> @enderror
            </div>

            <div class="flex flex-col gap-3 border-t border-slate-200 pt-5 sm:flex-row sm:items-center sm:justify-between dark:border-slate-800">
                <p class="text-xs text-slate-500 dark:text-slate-400">La solicitud se registrará con estado inicial <span class="font-semibold text-sky-700 dark:text-sky-300">Recibida</span>.</p>
                <button type="submit" wire:loading.attr="disabled" wire:target="registrar"
                    class="inline-flex items-center justify-center gap-2 rounded-lg bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:cursor-wait disabled:opacity-60 dark:focus:ring-offset-slate-900">
                    <span wire:loading.remove wire:target="registrar">Registrar solicitud</span>
                    <span wire:loading wire:target="registrar">Registrando...</span>
                    <span aria-hidden="true" wire:loading.remove wire:target="registrar">→</span>
                </button>
            </div>
        </form>
    </section>

    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-900">
        <div class="border-b border-slate-200 px-5 py-4 sm:px-6 dark:border-slate-800">
            <div class="flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <h2 class="text-lg font-bold text-slate-950 dark:text-white">Historial de solicitudes</h2>
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Consulta el estado, la prioridad y la fecha de registro de tus requerimientos.</p>
                </div>
                <div class="grid gap-3 sm:grid-cols-2">
                    <div>
                        <label for="buscar-solicitud" class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Código o asunto</label>
                        <input id="buscar-solicitud" type="search" wire:model.live.debounce.300ms="busqueda"
                            placeholder="Buscar solicitud..."
                            class="block w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-900 outline-none placeholder:text-slate-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 dark:border-slate-700 dark:bg-slate-800 dark:text-white dark:placeholder:text-slate-500">
                    </div>
                    <div>
                        <label for="filtroEstado" class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Estado</label>
                        <select id="filtroEstado" wire:model.live="filtroEstado"
                            class="block w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-900 outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 dark:border-slate-700 dark:bg-slate-800 dark:text-white">
                            <option value="">Todos los estados</option>
                            <option value="Recibida">Recibida</option>
                            <option value="En validación">En validación</option>
                            <option value="Pendiente de aprobación">Pendiente de aprobación</option>
                            <option value="En atención">En atención</option>
                            <option value="Pendiente del solicitante">Pendiente del solicitante</option>
                            <option value="Resuelta">Resuelta</option>
                            <option value="Cerrada">Cerrada</option>
                            <option value="Rechazada">Rechazada</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex flex-col gap-3 border-b border-slate-200 bg-slate-50 px-5 py-3 sm:flex-row sm:items-center sm:justify-between dark:border-slate-800 dark:bg-slate-800/50">
            <p class="text-sm text-slate-600 dark:text-slate-300">Solicitudes encontradas: <span class="font-bold text-slate-950 dark:text-white">{{ $this->solicitudes->count() }}</span></p>
            <button type="button" wire:click="limpiarFiltros" class="inline-flex w-fit items-center gap-2 rounded-lg border border-slate-300 bg-white px-3 py-2 text-xs font-semibold text-slate-700 transition hover:bg-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800">
                Limpiar filtros
            </button>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-[760px] w-full text-left text-sm">
                <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-600 dark:bg-slate-800/80 dark:text-slate-300">
                    <tr>
                        <th scope="col" class="px-5 py-3.5 font-semibold">Código</th>
                        <th scope="col" class="px-5 py-3.5 font-semibold">Solicitud</th>
                        <th scope="col" class="px-5 py-3.5 font-semibold">Prioridad</th>
                        <th scope="col" class="px-5 py-3.5 font-semibold">Estado</th>
                        <th scope="col" class="px-5 py-3.5 font-semibold">Fecha de registro</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                    @forelse ($this->solicitudes as $solicitud)
                        @php
                            $clasePrioridad = match ($solicitud->prioridad) {
                                'Crítica' => 'bg-rose-100 text-rose-800 ring-rose-200 dark:bg-rose-500/10 dark:text-rose-300 dark:ring-rose-500/30',
                                'Alta' => 'bg-orange-100 text-orange-800 ring-orange-200 dark:bg-orange-500/10 dark:text-orange-300 dark:ring-orange-500/30',
                                'Media' => 'bg-amber-100 text-amber-800 ring-amber-200 dark:bg-amber-500/10 dark:text-amber-300 dark:ring-amber-500/30',
                                default => 'bg-emerald-100 text-emerald-800 ring-emerald-200 dark:bg-emerald-500/10 dark:text-emerald-300 dark:ring-emerald-500/30',
                            };
                            $claseEstado = match ($solicitud->estado) {
                                'Recibida' => 'bg-sky-100 text-sky-800 ring-sky-200 dark:bg-sky-500/10 dark:text-sky-300 dark:ring-sky-500/30',
                                'En validación' => 'bg-cyan-100 text-cyan-800 ring-cyan-200 dark:bg-cyan-500/10 dark:text-cyan-300 dark:ring-cyan-500/30',
                                'Pendiente de aprobación' => 'bg-violet-100 text-violet-800 ring-violet-200 dark:bg-violet-500/10 dark:text-violet-300 dark:ring-violet-500/30',
                                'En atención' => 'bg-indigo-100 text-indigo-800 ring-indigo-200 dark:bg-indigo-500/10 dark:text-indigo-300 dark:ring-indigo-500/30',
                                'Pendiente del solicitante' => 'bg-orange-100 text-orange-800 ring-orange-200 dark:bg-orange-500/10 dark:text-orange-300 dark:ring-orange-500/30',
                                'Resuelta' => 'bg-emerald-100 text-emerald-800 ring-emerald-200 dark:bg-emerald-500/10 dark:text-emerald-300 dark:ring-emerald-500/30',
                                'Cerrada' => 'bg-slate-100 text-slate-700 ring-slate-200 dark:bg-slate-700/60 dark:text-slate-200 dark:ring-slate-600',
                                'Rechazada' => 'bg-rose-100 text-rose-800 ring-rose-200 dark:bg-rose-500/10 dark:text-rose-300 dark:ring-rose-500/30',
                                default => 'bg-slate-100 text-slate-700 ring-slate-200 dark:bg-slate-700/60 dark:text-slate-200 dark:ring-slate-600',
                            };
                        @endphp
                        <tr wire:key="mis-solicitud-{{ $solicitud->id }}" class="transition hover:bg-slate-50 dark:hover:bg-slate-800/40">
                            <td class="whitespace-nowrap px-5 py-4 align-top">
                                <span class="font-semibold text-indigo-700 dark:text-indigo-300">{{ $solicitud->codigo }}</span>
                            </td>
                            <td class="max-w-md px-5 py-4 align-top">
                                <div class="font-semibold text-slate-900 dark:text-white">{{ $solicitud->asunto }}</div>
                                <div class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ $solicitud->tipo }}</div>
                                @if ($solicitud->respuesta)
                                    <div class="mt-2 max-w-sm whitespace-pre-line text-xs leading-5 text-slate-600 dark:text-slate-300">Respuesta registrada: {{ \Illuminate\Support\Str::limit($solicitud->respuesta, 120) }}</div>
                                @endif
                            </td>
                            <td class="whitespace-nowrap px-5 py-4 align-top">
                                <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold ring-1 ring-inset {{ $clasePrioridad }}">{{ $solicitud->prioridad }}</span>
                            </td>
                            <td class="whitespace-nowrap px-5 py-4 align-top">
                                <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold ring-1 ring-inset {{ $claseEstado }}">{{ $solicitud->estado }}</span>
                            </td>
                            <td class="whitespace-nowrap px-5 py-4 text-slate-600 align-top dark:text-slate-300">{{ $solicitud->created_at?->format('d/m/Y H:i') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-12 text-center">
                                <div class="mx-auto flex max-w-md flex-col items-center gap-2">
                                    <span class="inline-flex h-11 w-11 items-center justify-center rounded-full bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-300">⌕</span>
                                    <p class="font-semibold text-slate-800 dark:text-slate-100">No hay solicitudes para mostrar</p>
                                    <p class="text-sm text-slate-500 dark:text-slate-400">Prueba cambiando los filtros o registra una nueva solicitud.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <p class="border-t border-slate-200 px-5 py-3 text-xs text-slate-500 dark:border-slate-800 dark:text-slate-400">En pantallas pequeñas, desplaza la tabla horizontalmente para consultar todas las columnas.</p>
    </section>
</div>