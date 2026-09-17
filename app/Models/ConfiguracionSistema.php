<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConfiguracionSistema extends Model
{
    protected $table = 'configuracion_sistemas';

    protected $fillable = [
        'razon_social',
        'nombre_comercial',
        'ruc',
        'direccion',
        'telefono',
        'correo',
        'logo',
        'serie_factura',
        'correlativo_factura',
        'serie_boleta',
        'correlativo_boleta',
        'digitos_correlativo',
        'igv',
        'moneda',
        'simbolo_moneda',
        'formato_fecha',
        'zona_horaria',
    ];

    protected function casts(): array
    {
        return [
            'correlativo_factura' => 'integer',
            'correlativo_boleta' => 'integer',
            'digitos_correlativo' => 'integer',
            'igv' => 'decimal:2',
        ];
    }
}