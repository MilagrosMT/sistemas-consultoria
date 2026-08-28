<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Postulante extends Model
{
    protected $fillable = [
        'nombres',
        'apellidos',
        'dni',
        'telefono',
        'email',
        'puesto',
        'fecha_postulacion',
        'estado',
    ];

    protected function casts(): array
    {
        return [
            'fecha_postulacion' => 'date',
        ];
    }
}