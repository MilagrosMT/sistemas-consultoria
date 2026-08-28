<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Planilla extends Model
{
    protected $fillable = [
        'empleado_id',
        'periodo',
        'sueldo_base',
        'bonificaciones',
        'descuentos',
        'sueldo_neto',
        'estado',
    ];

    protected function casts(): array
    {
        return [
            'periodo' => 'date',
            'sueldo_base' => 'decimal:2',
            'bonificaciones' => 'decimal:2',
            'descuentos' => 'decimal:2',
            'sueldo_neto' => 'decimal:2',
        ];
    }

    public function empleado(): BelongsTo
    {
        return $this->belongsTo(Empleado::class);
    }
}
