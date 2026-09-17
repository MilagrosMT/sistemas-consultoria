<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Compra extends Model
{
    protected $fillable = [
        'cliente_id',
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

    protected function casts(): array
    {
        return [
            'fecha_emision' => 'date',
            'fecha_vencimiento' => 'date',
            'base_imponible' => 'decimal:2',
            'igv' => 'decimal:2',
            'total' => 'decimal:2',
        ];
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }
}