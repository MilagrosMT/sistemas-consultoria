<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cliente extends Model
{
    protected $fillable = [
        'ruc',
        'razon_social',
        'nombre_comercial',
        'direccion',
        'telefono',
        'correo',
        'servicio_contratado',
        'servicio_id',
        'estado',
    ];

    /**
     * @return BelongsTo<Servicio, $this>
     */
    public function servicio(): BelongsTo
    {
        return $this->belongsTo(Servicio::class);
    }

    /**
     * @return HasMany<Contrato, $this>
     */
    public function contratos(): HasMany
    {
        return $this->hasMany(Contrato::class);
    }

    /**
     * @return HasMany<DocumentoCliente, $this>
     */
    public function documentos(): HasMany
    {
        return $this->hasMany(DocumentoCliente::class);
    }
}
