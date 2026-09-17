<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ventas', function (Blueprint $table) {
            $table->string('forma_pago', 10)->default('Contado')->after('fecha_emision');
            $table->date('fecha_vencimiento')->nullable()->after('forma_pago');
            $table->decimal('monto_pendiente', 10, 2)->default(0)->after('total');

            $table->decimal('cantidad', 10, 2)->default(1)->after('servicio_id');
            $table->decimal('precio_unitario', 10, 2)->default(0)->after('cantidad');
            $table->text('descripcion')->nullable()->after('precio_unitario');
        });
    }

    public function down(): void
    {
        Schema::table('ventas', function (Blueprint $table) {
            $table->dropColumn([
                'forma_pago',
                'fecha_vencimiento',
                'monto_pendiente',
                'cantidad',
                'precio_unitario',
                'descripcion',
            ]);
        });
    }
};