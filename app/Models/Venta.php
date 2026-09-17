<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Venta extends Model
{
    protected $fillable = [
        'cliente_id',
        'servicio_id',
        'cantidad',
        'precio_unitario',
        'descripcion',
        'tipo_comprobante',
        'numero_comprobante',
        'fecha_emision',
        'forma_pago',
        'fecha_vencimiento',
        'base_imponible',
        'igv',
        'total',
        'monto_pendiente',
        'estado',
        'observacion',
    ];

    protected function casts(): array
    {
        return [
            'fecha_emision' => 'date',
            'fecha_vencimiento' => 'date',
            'cantidad' => 'decimal:2',
            'precio_unitario' => 'decimal:2',
            'base_imponible' => 'decimal:2',
            'igv' => 'decimal:2',
            'total' => 'decimal:2',
            'monto_pendiente' => 'decimal:2',
        ];
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }

    public function servicio(): BelongsTo
    {
        return $this->belongsTo(Servicio::class);
    }
}