<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Solicitud extends Model
{
    protected $table = 'solicitudes';

    protected $fillable = [
        'codigo',
        'solicitante_id',
        'tipo',
        'asunto',
        'descripcion',
        'prioridad',
        'estado',
        'responsable_id',
        'aprobador_id',
        'fecha_aprobacion',
        'fecha_resolucion',
        'fecha_cierre',
        'respuesta',
        'motivo_rechazo',
    ];

    protected function casts(): array
    {
        return [
            'fecha_aprobacion' => 'datetime',
            'fecha_resolucion' => 'datetime',
            'fecha_cierre' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function solicitante(): BelongsTo
    {
        return $this->belongsTo(User::class, 'solicitante_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function responsable(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responsable_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function aprobador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'aprobador_id');
    }
}
