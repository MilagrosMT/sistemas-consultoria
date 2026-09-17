<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VentaCliente extends Model
{
    protected $fillable = [
        'cliente_id',
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

    protected function casts(): array
    {
        return [
            'fecha_emision' => 'date',
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