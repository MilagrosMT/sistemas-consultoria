<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('configuracion_sistemas', function (Blueprint $table) {
            // Datos de empresa
            $table->string('razon_social')->nullable();
            $table->string('nombre_comercial')->nullable();
            $table->string('ruc', 11)->nullable();
            $table->string('direccion')->nullable();
            $table->string('telefono', 20)->nullable();
            $table->string('correo')->nullable();
            $table->string('logo')->nullable();

            // Facturación
            $table->string('serie_factura', 4)->default('F001');
            $table->unsignedBigInteger('correlativo_factura')->default(0);
            $table->string('serie_boleta', 4)->default('B001');
            $table->unsignedBigInteger('correlativo_boleta')->default(0);
            $table->unsignedTinyInteger('digitos_correlativo')->default(8);

            // Impuestos y moneda
            $table->decimal('igv', 5, 2)->default(18.00);
            $table->string('moneda', 3)->default('PEN');
            $table->string('simbolo_moneda', 5)->default('S/');

            // Parámetros
            $table->string('formato_fecha')->default('d/m/Y');
            $table->string('zona_horaria')->default('America/Lima');
        });
    }

    public function down(): void
    {
        Schema::table('configuracion_sistemas', function (Blueprint $table) {
            $table->dropColumn([
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
            ]);
        });
    }
};
