<?php

namespace App\Imports;

use App\Models\Cliente;
use App\Models\Compra;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ComprasImport implements ToCollection, WithHeadingRow
{
    public int $importadas = 0;

    public array $errores = [];

    public function collection(Collection $rows): void
    {
        foreach ($rows as $indice => $row) {

            $fila = $indice + 2;

            $datos = [
                'cliente_ruc' => trim((string) ($row['cliente_ruc'] ?? '')),
                'proveedor' => trim((string) ($row['proveedor'] ?? '')),
                'ruc_proveedor' => trim((string) ($row['ruc_proveedor'] ?? '')),
                'tipo_comprobante' => trim((string) ($row['tipo_comprobante'] ?? '')),
                'serie' => trim((string) ($row['serie'] ?? '')),
                'numero' => trim((string) ($row['numero'] ?? '')),
                'fecha_emision' => $row['fecha_emision'] ?? null,
                'fecha_vencimiento' => $row['fecha_vencimiento'] ?? null,
                'base_imponible' => $row['base_imponible'] ?? null,
                'igv' => $row['igv'] ?? null,
                'total' => $row['total'] ?? null,
                'forma_pago' => trim((string) ($row['forma_pago'] ?? '')),
                'estado' => trim((string) ($row['estado'] ?? 'Registrada')),
                'observacion' => trim((string) ($row['observacion'] ?? '')),
            ];

            if (
                collect($datos)
                    ->except(['fecha_vencimiento', 'observacion', 'estado'])
                    ->every(fn ($valor) => $valor === null || $valor === '')
            ) {
                continue;
            }

            $validator = Validator::make($datos, [
                'cliente_ruc' => ['required', 'digits:11'],
                'proveedor' => ['required', 'string', 'max:255'],
                'ruc_proveedor' => ['required', 'digits:11'],
                'tipo_comprobante' => [
                    'required',
                    'in:Factura,Boleta,Nota de crédito,Nota de débito',
                ],
                'serie' => ['required', 'string', 'max:10'],
                'numero' => ['required', 'string', 'max:20'],
                'base_imponible' => ['required', 'numeric', 'min:0'],
                'igv' => ['required', 'numeric', 'min:0'],
                'total' => ['required', 'numeric', 'min:0'],
                'forma_pago' => ['required', 'in:Contado,Credito'],
                'estado' => ['required', 'in:Registrada,Observada,Anulada'],
            ]);

            if ($validator->fails()) {
                $this->errores[] = [
                    'fila' => $fila,
                    'mensaje' => implode(' ', $validator->errors()->all()),
                ];

                continue;
            }

            $cliente = Cliente::where('ruc', $datos['cliente_ruc'])->first();

            if (!$cliente) {
                $this->errores[] = [
                    'fila' => $fila,
                    'mensaje' => "No existe un cliente registrado con RUC {$datos['cliente_ruc']}.",
                ];

                continue;
            }

            try {
                $fechaEmision = $this->convertirFecha($datos['fecha_emision']);
                $fechaVencimiento = $this->convertirFecha($datos['fecha_vencimiento']);

                if (
                    $datos['forma_pago'] === 'Credito'
                    && $fechaVencimiento === null
                ) {
                    $this->errores[] = [
                        'fila' => $fila,
                        'mensaje' => 'Una compra a crédito debe tener fecha de vencimiento.',
                    ];

                    continue;
                }

                $existe = Compra::where('cliente_id', $cliente->id)
                    ->where('ruc_proveedor', $datos['ruc_proveedor'])
                    ->where('tipo_comprobante', $datos['tipo_comprobante'])
                    ->where('serie', $datos['serie'])
                    ->where('numero', $datos['numero'])
                    ->exists();

                if ($existe) {
                    $this->errores[] = [
                        'fila' => $fila,
                        'mensaje' => "El comprobante {$datos['serie']}-{$datos['numero']} ya está registrado para este cliente.",
                    ];

                    continue;
                }

                Compra::create([
                    'cliente_id' => $cliente->id,
                    'proveedor' => $datos['proveedor'],
                    'ruc_proveedor' => $datos['ruc_proveedor'],
                    'tipo_comprobante' => $datos['tipo_comprobante'],
                    'serie' => $datos['serie'],
                    'numero' => $datos['numero'],
                    'fecha_emision' => $fechaEmision,
                    'fecha_vencimiento' => $fechaVencimiento,
                    'base_imponible' => $datos['base_imponible'],
                    'igv' => $datos['igv'],
                    'total' => $datos['total'],
                    'forma_pago' => $datos['forma_pago'],
                    'estado' => $datos['estado'],
                    'observacion' => $datos['observacion'] ?: null,
                ]);

                $this->importadas++;

            } catch (\Throwable $e) {
                $this->errores[] = [
                    'fila' => $fila,
                    'mensaje' => 'No se pudo procesar la fila.',
                ];
            }
        }
    }

    private function convertirFecha(mixed $valor): ?string
    {
        if ($valor === null || $valor === '') {
            return null;
        }

        if (is_numeric($valor)) {
            return Carbon::create(1899, 12, 30)
                ->addDays((int) $valor)
                ->format('Y-m-d');
        }

        try {
            return Carbon::parse($valor)->format('Y-m-d');
        } catch (\Throwable) {
            return null;
        }
    }
}