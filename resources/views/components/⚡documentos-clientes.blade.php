
<?php

use App\Models\Cliente;
use App\Models\Contrato;
use App\Models\DocumentoCliente;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;

new #[Layout('layouts.app')] class extends Component
{
    use WithFileUploads, WithPagination;

    public string $buscar = '';
    public string $filtroCliente = '';
    public string $filtroTipo = '';

    public bool $mostrarFormulario = false;

    public $cliente_id = '';
    public $contrato_id = '';
    public string $nombre = '';
    public string $tipo_documento = '';
    public string $descripcion = '';
    public $archivo;

    protected function rules(): array
    {
        return [
            'cliente_id' => ['required', 'exists:clientes,id'],
            'contrato_id' => ['nullable', 'exists:contratos,id'],
            'nombre' => ['required', 'string', 'max:150'],
            'tipo_documento' => [
                'required',
                'in:Contrato,Comprobante,Declaración tributaria,Planilla,Identificación,Otro'
            ],
            'descripcion' => ['nullable', 'string', 'max:1000'],
            'archivo' => [
                'required',
                'file',
                'mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png',
                'max:10240'
            ],
        ];
    }

    public function updatedClienteId(): void
    {
        $this->contrato_id = '';
    }

    public function guardar(): void
    {
        $datos = $this->validate(null, [
    'cliente_id.required' => 'Debe seleccionar un cliente.',
    'contrato_id.exists' => 'El contrato seleccionado no es válido.',
    'nombre.required' => 'El nombre del documento es obligatorio.',
    'nombre.max' => 'El nombre no debe superar los 255 caracteres.',
    'tipo_documento.required' => 'Debe seleccionar un tipo de documento.',
    'archivo.required' => 'Debe adjuntar un archivo.',
    'archivo.file' => 'El archivo seleccionado no es válido.',
    'archivo.max' => 'El archivo no debe superar los 10 MB.',
    'descripcion.max' => 'La descripción no debe superar los 1000 caracteres.',
]);

        $ruta = $this->archivo->store(
            'documentos-clientes',
            'local'
        );

        DocumentoCliente::create([
            'cliente_id' => $datos['cliente_id'],
            'contrato_id' => $datos['contrato_id'] ?: null,
            'usuario_id' => auth()->id(),
            'nombre' => $datos['nombre'],
            'tipo_documento' => $datos['tipo_documento'],
            'nombre_archivo' => $this->archivo->getClientOriginalName(),
            'ruta_archivo' => $ruta,
            'mime_type' => $this->archivo->getMimeType(),
            'tamano_bytes' => $this->archivo->getSize(),
            'descripcion' => $datos['descripcion'] ?? null,
        ]);

        $this->reset([
            'cliente_id',
            'contrato_id',
            'nombre',
            'tipo_documento',
            'descripcion',
            'archivo',
        ]);

        $this->mostrarFormulario = false;

        session()->flash('mensaje', 'Documento cargado correctamente.');
    }

    public function descargar(int $id)
    {
        $documento = DocumentoCliente::findOrFail($id);

        abort_unless(
            Storage::disk('local')->exists($documento->ruta_archivo),
            404
        );

        return Storage::disk('local')->download(
            $documento->ruta_archivo,
            $documento->nombre_archivo
        );
    }

    public function eliminar(int $id): void
    {
        $documento = DocumentoCliente::findOrFail($id);

        Storage::disk('local')->delete($documento->ruta_archivo);

        $documento->delete();

        session()->flash('mensaje', 'Documento eliminado correctamente.');
    }

    public function render()
    {
        $documentos = DocumentoCliente::with([
            'cliente',
            'contrato',
            'usuario',
        ])
        ->when($this->buscar, function ($query) {
            $query->where(function ($q) {
                $q->where('nombre', 'like', '%' . $this->buscar . '%')
                    ->orWhere('nombre_archivo', 'like', '%' . $this->buscar . '%')
                    ->orWhereHas('cliente', function ($cliente) {
                        $cliente->where(
                            'razon_social',
                            'like',
                            '%' . $this->buscar . '%'
                        );
                    });
            });
        })
        ->when($this->filtroCliente, fn ($query) =>
            $query->where('cliente_id', $this->filtroCliente)
        )
        ->when($this->filtroTipo, fn ($query) =>
            $query->where('tipo_documento', $this->filtroTipo)
        )
        ->latest()
        ->paginate(10);

        return view('components.⚡documentos-clientes', [
            'documentos' => $documentos,
            'clientes' => Cliente::orderBy('razon_social')->get(),
            'contratos' => $this->cliente_id
                ? Contrato::where('cliente_id', $this->cliente_id)->get()
                : collect(),
        ]);
    }
};
?>

<div class="min-h-screen space-y-6 bg-zinc-50 p-1 text-zinc-900 transition-colors dark:bg-zinc-950 dark:text-white">

    {{-- ENCABEZADO --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">

        <div class="flex items-center gap-4">
            <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-zinc-100 text-zinc-700 dark:bg-zinc-800 dark:text-zinc-200">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h7l5 5v11a2 2 0 01-2 2z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" d="M14 3v5h5"/>
                </svg>
            </div>

            <div>
                <h1 class="text-2xl font-bold tracking-tight text-zinc-900 dark:text-white">
                    Gestión Documentaria
                </h1>
                <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">
                    Administración de documentos y expedientes de clientes.
                </p>
            </div>
        </div>

        <button wire:click="$set('mostrarFormulario', true)" type="button"
            class="inline-flex items-center justify-center gap-2 rounded-xl bg-teal-500 px-5 py-2.5 text-sm font-semibold text-zinc-900 shadow-lg shadow-teal-500/20 transition hover:bg-teal-600 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:ring-offset-2 focus:ring-offset-zinc-50 dark:text-white dark:focus:ring-offset-zinc-950">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
            </svg>
            Nuevo documento
        </button>
    </div>

    {{-- MENSAJE --}}
    @if (session()->has('mensaje'))
        <div class="rounded-xl border border-teal-200 bg-teal-50 p-4 text-sm text-teal-800 dark:border-teal-500/20 dark:bg-teal-500/10 dark:text-teal-300">
            {{ session('mensaje') }}
        </div>
    @endif

    {{-- INDICADORES --}}
    <div class="grid grid-cols-1 gap-4 md:grid-cols-3">

        <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-zinc-600 dark:text-zinc-400">Total de documentos</p>
                    <p class="mt-2 text-3xl font-bold text-zinc-900 dark:text-white">
                        {{ \App\Models\DocumentoCliente::count() }}
                    </p>
                    <p class="mt-1 text-xs text-zinc-500">Archivos registrados</p>
                </div>
                <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-zinc-100 text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h7l5 5v11a2 2 0 01-2 2z"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-zinc-600 dark:text-zinc-400">Clientes con documentos</p>
                    <p class="mt-2 text-3xl font-bold text-teal-700 dark:text-teal-400">
                        {{ \App\Models\DocumentoCliente::distinct('cliente_id')->count('cliente_id') }}
                    </p>
                    <p class="mt-1 text-xs text-green-700 dark:text-green-400">Clientes registrados</p>
                </div>
                <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-teal-50 text-teal-700 dark:bg-teal-500/10 dark:text-teal-400">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5V4H2v16h5m10 0v-5a3 3 0 00-3-3h-2a3 3 0 00-3 3v5m8 0H9"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-zinc-600 dark:text-zinc-400">Documentos recientes</p>
                    <p class="mt-2 text-3xl font-bold text-zinc-900 dark:text-white">
                        {{ \App\Models\DocumentoCliente::whereDate('created_at', today())->count() }}
                    </p>
                    <p class="mt-1 text-xs text-zinc-500">Registrados hoy</p>
                </div>
                <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-zinc-100 text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </div>

    </div>

    {{-- BUSCADOR Y FILTROS --}}
    <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
        <div class="flex flex-col gap-4">

            <div>
                <h2 class="font-semibold text-zinc-900 dark:text-white">
                    Archivo documental
                </h2>
                <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-500">
                    Busque y consulte los documentos registrados por cliente.
                </p>
            </div>

            <div class="grid grid-cols-1 gap-3 md:grid-cols-3">

                <input wire:model.live.debounce.300ms="buscar" type="search"
                    placeholder="Buscar documento o cliente..."
                    class="w-full rounded-xl border border-zinc-300 bg-white px-4 py-3 text-sm text-zinc-900 outline-none transition placeholder:text-zinc-400 focus:border-teal-500 focus:ring-2 focus:ring-teal-500 dark:border-zinc-700 dark:bg-zinc-950 dark:text-white dark:placeholder:text-zinc-500">

                <select wire:model.live="filtroCliente"
                    class="w-full rounded-xl border border-zinc-300 bg-white px-4 py-3 text-sm text-zinc-900 outline-none transition focus:border-teal-500 focus:ring-2 focus:ring-teal-500 dark:border-zinc-700 dark:bg-zinc-950 dark:text-white">
                    <option value="">Todos los clientes</option>
                    @foreach ($clientes as $cliente)
                        <option value="{{ $cliente->id }}">{{ $cliente->razon_social }}</option>
                    @endforeach
                </select>

                <select wire:model.live="filtroTipo"
                    class="w-full rounded-xl border border-zinc-300 bg-white px-4 py-3 text-sm text-zinc-900 outline-none transition focus:border-teal-500 focus:ring-2 focus:ring-teal-500 dark:border-zinc-700 dark:bg-zinc-950 dark:text-white">
                    <option value="">Todos los tipos</option>
                    <option value="Contrato">Contrato</option>
                    <option value="Comprobante">Comprobante</option>
                    <option value="Declaración tributaria">Declaración tributaria</option>
                    <option value="Planilla">Planilla</option>
                    <option value="Identificación">Identificación</option>
                    <option value="Otro">Otro</option>
                </select>

            </div>
        </div>
    </div>

    {{-- FORMULARIO --}}
    @if ($mostrarFormulario)
        <div class="rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">

            <div class="mb-6 flex items-start gap-3 border-b border-zinc-200 pb-5 dark:border-zinc-800">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-teal-500/10 text-teal-600 dark:text-teal-400">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                    </svg>
                </div>
                <div>
                    <h2 class="text-lg font-bold text-zinc-900 dark:text-white">Registrar nuevo documento</h2>
                    <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">
                        Complete los datos y seleccione el archivo que desea guardar.
                    </p>
                </div>
            </div>

            <form wire:submit="guardar" class="space-y-5">

                <div class="grid grid-cols-1 gap-5 md:grid-cols-2">

                    <div>
                        <label class="block text-sm font-semibold text-zinc-700 dark:text-zinc-200">
                            Cliente <span class="text-red-500">*</span>
                        </label>
                        <select wire:model.live="cliente_id"
                            class="mt-1.5 w-full rounded-xl border border-zinc-300 bg-white px-3 py-2.5 text-sm text-zinc-900 outline-none transition focus:border-teal-500 focus:ring-2 focus:ring-teal-500 dark:border-zinc-700 dark:bg-zinc-950 dark:text-white">
                            <option value="">Seleccione un cliente</option>
                            @foreach ($clientes as $cliente)
                                <option value="{{ $cliente->id }}">
                                    {{ $cliente->razon_social }} - {{ $cliente->ruc }}
                                </option>
                            @endforeach
                        </select>
                        @error('cliente_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-zinc-700 dark:text-zinc-200">
                            Contrato (opcional)
                        </label>
                        <select wire:model="contrato_id" @disabled(!$cliente_id)
                            class="mt-1.5 w-full rounded-xl border border-zinc-300 bg-white px-3 py-2.5 text-sm text-zinc-900 outline-none transition focus:border-teal-500 focus:ring-2 focus:ring-teal-500 disabled:cursor-not-allowed disabled:opacity-50 dark:border-zinc-700 dark:bg-zinc-950 dark:text-white">
                            <option value="">Sin contrato asociado</option>
                            @foreach ($contratos as $contrato)
                                <option value="{{ $contrato->id }}">
                                    Contrato #{{ $contrato->id }} - {{ $contrato->fecha_inicio?->format('d/m/Y') }}
                                </option>
                            @endforeach
                        </select>
                        @error('contrato_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-zinc-700 dark:text-zinc-200">
                            Nombre del documento <span class="text-red-500">*</span>
                        </label>
                        <input wire:model="nombre" type="text"
                            placeholder="Ej. Contrato de asesoría contable"
                            class="mt-1.5 w-full rounded-xl border border-zinc-300 bg-white px-3 py-2.5 text-sm text-zinc-900 placeholder:text-zinc-400 outline-none transition focus:border-teal-500 focus:ring-2 focus:ring-teal-500 dark:border-zinc-700 dark:bg-zinc-950 dark:text-white dark:placeholder:text-zinc-500">
                        @error('nombre')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-zinc-700 dark:text-zinc-200">
                            Tipo de documento <span class="text-red-500">*</span>
                        </label>
                        <select wire:model="tipo_documento"
                            class="mt-1.5 w-full rounded-xl border border-zinc-300 bg-white px-3 py-2.5 text-sm text-zinc-900 outline-none transition focus:border-teal-500 focus:ring-2 focus:ring-teal-500 dark:border-zinc-700 dark:bg-zinc-950 dark:text-white">
                            <option value="">Seleccione un tipo</option>
                            <option value="Contrato">Contrato</option>
                            <option value="Comprobante">Comprobante</option>
                            <option value="Declaración tributaria">Declaración tributaria</option>
                            <option value="Planilla">Planilla</option>
                            <option value="Identificación">Identificación</option>
                            <option value="Otro">Otro</option>
                        </select>
                        @error('tipo_documento')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-semibold text-zinc-700 dark:text-zinc-200">
                            Archivo <span class="text-red-500">*</span>
                        </label>
                        <input wire:model="archivo" type="file"
                            accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png"
                            class="mt-1.5 w-full rounded-xl border border-zinc-300 bg-white px-3 py-2.5 text-sm text-zinc-900 file:mr-4 file:rounded-lg file:border-0 file:bg-teal-500 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:bg-teal-600 dark:border-zinc-700 dark:bg-zinc-950 dark:text-white">
                        <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-500">
                            Formatos permitidos: PDF, Word, Excel e imágenes. Tamaño máximo: 10 MB.
                        </p>
                        @error('archivo')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <div wire:loading wire:target="archivo" class="mt-2 text-sm text-teal-600 dark:text-teal-400">
                            Cargando archivo...
                        </div>
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-semibold text-zinc-700 dark:text-zinc-200">
                            Descripción (opcional)
                        </label>
                        <textarea wire:model="descripcion" rows="3"
                            class="mt-1.5 w-full rounded-xl border border-zinc-300 bg-white px-3 py-2.5 text-sm text-zinc-900 outline-none transition focus:border-teal-500 focus:ring-2 focus:ring-teal-500 dark:border-zinc-700 dark:bg-zinc-950 dark:text-white"></textarea>
                        @error('descripcion')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                </div>

                <div class="flex flex-wrap gap-3 border-t border-zinc-200 pt-5 dark:border-zinc-800">
                    <button type="submit" wire:loading.attr="disabled"
                        class="inline-flex items-center justify-center rounded-xl bg-teal-500 px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-teal-500/20 transition hover:bg-teal-600 disabled:cursor-not-allowed disabled:opacity-50">
                        Guardar documento
                    </button>

                    <button type="button" wire:click="$set('mostrarFormulario', false)"
                        class="rounded-xl border border-zinc-300 px-5 py-2.5 text-sm font-semibold text-zinc-700 transition hover:bg-zinc-100 dark:border-zinc-700 dark:text-zinc-200 dark:hover:bg-zinc-800">
                        Cancelar
                    </button>
                </div>

            </form>
        </div>
    @endif

    {{-- TABLA --}}
    <div class="overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900">

        <div class="flex items-center gap-3 border-b border-zinc-200 p-5 dark:border-zinc-800">
            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-teal-50 text-teal-700 dark:bg-teal-500/10 dark:text-teal-400">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h7l5 5v11a2 2 0 01-2 2z"/>
                </svg>
            </div>
            <div>
                <h2 class="font-semibold text-zinc-900 dark:text-white">Documentos registrados</h2>
                <p class="text-xs text-zinc-500 dark:text-zinc-500">Listado de archivos asociados a clientes.</p>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-zinc-50 text-xs uppercase tracking-wide text-zinc-600 dark:bg-zinc-950 dark:text-zinc-400">
                    <tr>
                        <th class="px-5 py-4 font-semibold">Documento</th>
                        <th class="px-5 py-4 font-semibold">Cliente</th>
                        <th class="px-5 py-4 font-semibold">Tipo</th>
                        <th class="px-5 py-4 font-semibold">Contrato</th>
                        <th class="px-5 py-4 font-semibold">Fecha</th>
                        <th class="px-5 py-4 font-semibold">Tamaño</th>
                        <th class="px-5 py-4 text-center font-semibold">Acciones</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800">
                    @forelse ($documentos as $documento)
                        <tr class="transition hover:bg-zinc-50 dark:hover:bg-zinc-800/50">
                            <td class="px-5 py-4">
                                <div class="font-semibold text-zinc-900 dark:text-white">
                                    {{ $documento->nombre }}
                                </div>
                                <div class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">
                                    {{ $documento->nombre_archivo }}
                                </div>
                            </td>

                            <td class="px-5 py-4 text-zinc-700 dark:text-zinc-300">
                                {{ $documento->cliente?->razon_social ?? 'Sin cliente' }}
                            </td>

                            <td class="px-5 py-4 text-zinc-700 dark:text-zinc-300">
                                {{ $documento->tipo_documento }}
                            </td>

                            <td class="px-5 py-4 text-zinc-700 dark:text-zinc-300">
                                {{ $documento->contrato ? 'Contrato #' . $documento->contrato->id : '—' }}
                            </td>

                            <td class="px-5 py-4 text-zinc-600 dark:text-zinc-400">
                                {{ $documento->created_at->format('d/m/Y') }}
                            </td>

                            <td class="px-5 py-4 text-zinc-600 dark:text-zinc-400">
                                {{ number_format($documento->tamano_bytes / 1024, 1) }} KB
                            </td>

                            <td class="px-5 py-4">
                                <div class="flex items-center justify-center gap-3">
                                    <button wire:click="descargar({{ $documento->id }})"
                                        class="text-sm font-medium text-teal-700 transition hover:text-teal-600 dark:text-teal-400 dark:hover:text-teal-300">
                                        Descargar
                                    </button>

                                    <button wire:click="eliminar({{ $documento->id }})"
                                        wire:confirm="¿Está seguro de eliminar este documento?"
                                        class="text-sm font-medium text-red-600 transition hover:text-red-500 dark:text-red-400">
                                        Eliminar
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-12 text-center">
                                <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-xl bg-zinc-100 text-zinc-500 dark:bg-zinc-800 dark:text-zinc-400">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h7l5 5v11a2 2 0 01-2 2z"/>
                                    </svg>
                                </div>
                                <p class="mt-3 text-sm font-medium text-zinc-700 dark:text-zinc-300">
                                    No se encontraron documentos registrados.
                                </p>
                                <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-500">
                                    Cuando cargue un documento, aparecerá en este listado.
                                </p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="border-t border-zinc-200 p-4 dark:border-zinc-800">
            {{ $documentos->links() }}
        </div>

    </div>

</div>
