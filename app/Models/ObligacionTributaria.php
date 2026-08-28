<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ObligacionTributaria extends Model
{
    protected $fillable = [
        'cliente',
        'ruc',
        'tipo_obligacion',
        'periodo',
        'fecha_vencimiento',
        'monto',
        'estado',
    ];

    protected function casts(): array
    {
        return [
            'fecha_vencimiento' => 'date',
            'monto' => 'decimal:2',
        ];
    }
}
