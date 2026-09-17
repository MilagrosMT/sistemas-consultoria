<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ComprasTemplateExport implements FromArray, WithHeadings, ShouldAutoSize
{
    public function headings(): array
    {
        return [
            'cliente_ruc',
            'proveedor',
            'ruc_proveedor',
            'tipo_comprobante',
            'serie',
            'numero',
            'fecha_emision',
            'fecha_vencimiento',
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
                'Proveedor Demo S.A.C.',
                '20456789123',
                'Factura',
                'F001',
                '00000001',
                '01/09/2026',
                '',
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
