<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

class VentasClientesTemplateExport implements FromArray, WithHeadings, ShouldAutoSize
{
    public function headings(): array
    {
        return [
            'cliente_ruc',
            'comprador',
            'ruc_comprador',
            'tipo_comprobante',
            'serie',
            'numero',
            'fecha_emision',
            'base_imponible',
            'igv',
            'total',
            'forma_pago',
            'estado',
            'observacion',
        ];
    }

    public function array(): array
    {
        return [
            [
                '20123456789',
                'Cliente comprador Demo S.A.C.',
                '20678912345',
                'Factura',
                'F001',
                '00000001',
                '01/09/2026',
                1000.00,
                180.00,
                1180.00,
                'Contado',
                'Registrada',
                'Ejemplo de registro. Reemplazar estos datos.',
            ],
        ];
    }
}