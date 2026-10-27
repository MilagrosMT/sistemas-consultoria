<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Empleado extends Model
{
    protected $fillable = [
        'nombres',
        'apellidos',
        'dni',
        'cargo',
        'area',
        'fecha_ingreso',
        'salario',
        'estado',
    ];

    protected function casts(): array
    {
        return [
            'fecha_ingreso' => 'date',
            'salario' => 'decimal:2',
        ];
    }

    /**
     * @return HasMany<Planilla, $this>
     */
    public function planillas(): HasMany
    {
        return $this->hasMany(Planilla::class);
    }
}
