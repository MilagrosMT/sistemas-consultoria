<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clientes', function (Blueprint $table) {
            $table->index('estado', 'clientes_estado_index');
        });

        Schema::table('compras', function (Blueprint $table) {
            $table->index('fecha_emision', 'compras_fecha_emision_index');
            $table->index('estado', 'compras_estado_index');
        });

        Schema::table('contratos', function (Blueprint $table) {
            $table->index('estado', 'contratos_estado_index');
            $table->index('fecha_inicio', 'contratos_fecha_inicio_index');
        });

        Schema::table('empleados', function (Blueprint $table) {
            $table->index('estado', 'empleados_estado_index');
        });

        Schema::table('movimiento_cajas', function (Blueprint $table) {
            $table->index('fecha', 'movimiento_cajas_fecha_index');
            $table->index('tipo_movimiento', 'movimiento_cajas_tipo_movimiento_index');
        });

        Schema::table('obligacion_tributarias', function (Blueprint $table) {
            $table->index('fecha_vencimiento', 'obligaciones_fecha_vencimiento_index');
            $table->index('estado', 'obligaciones_estado_index');
        });

        Schema::table('planillas', function (Blueprint $table) {
            $table->index('periodo', 'planillas_periodo_index');
            $table->index('estado', 'planillas_estado_index');
        });

        Schema::table('postulantes', function (Blueprint $table) {
            $table->index('estado', 'postulantes_estado_index');
            $table->index('fecha_postulacion', 'postulantes_fecha_postulacion_index');
        });

        Schema::table('servicio_procesos', function (Blueprint $table) {
            $table->index('estado', 'servicio_procesos_estado_index');
            $table->index('prioridad', 'servicio_procesos_prioridad_index');
            $table->index('fecha_vencimiento', 'servicio_procesos_fecha_vencimiento_index');
        });

        Schema::table('servicios', function (Blueprint $table) {
            $table->index('estado', 'servicios_estado_index');
        });

        Schema::table('venta_clientes', function (Blueprint $table) {
            $table->index('fecha_emision', 'venta_clientes_fecha_emision_index');
            $table->index('estado', 'venta_clientes_estado_index');
        });

        Schema::table('ventas', function (Blueprint $table) {
            $table->index('fecha_emision', 'ventas_fecha_emision_index');
            $table->index('estado', 'ventas_estado_index');
            $table->index('fecha_vencimiento', 'ventas_fecha_vencimiento_index');
        });
    }

    public function down(): void
    {
        Schema::table('clientes', function (Blueprint $table) {
            $table->dropIndex('clientes_estado_index');
        });

        Schema::table('compras', function (Blueprint $table) {
            $table->dropIndex('compras_fecha_emision_index');
            $table->dropIndex('compras_estado_index');
        });

        Schema::table('contratos', function (Blueprint $table) {
            $table->dropIndex('contratos_estado_index');
            $table->dropIndex('contratos_fecha_inicio_index');
        });

        Schema::table('empleados', function (Blueprint $table) {
            $table->dropIndex('empleados_estado_index');
        });

        Schema::table('movimiento_cajas', function (Blueprint $table) {
            $table->dropIndex('movimiento_cajas_fecha_index');
            $table->dropIndex('movimiento_cajas_tipo_movimiento_index');
        });

        Schema::table('obligacion_tributarias', function (Blueprint $table) {
            $table->dropIndex('obligaciones_fecha_vencimiento_index');
            $table->dropIndex('obligaciones_estado_index');
        });

        Schema::table('planillas', function (Blueprint $table) {
            $table->dropIndex('planillas_periodo_index');
            $table->dropIndex('planillas_estado_index');
        });

        Schema::table('postulantes', function (Blueprint $table) {
            $table->dropIndex('postulantes_estado_index');
            $table->dropIndex('postulantes_fecha_postulacion_index');
        });

        Schema::table('servicio_procesos', function (Blueprint $table) {
            $table->dropIndex('servicio_procesos_estado_index');
            $table->dropIndex('servicio_procesos_prioridad_index');
            $table->dropIndex('servicio_procesos_fecha_vencimiento_index');
        });

        Schema::table('servicios', function (Blueprint $table) {
            $table->dropIndex('servicios_estado_index');
        });

        Schema::table('venta_clientes', function (Blueprint $table) {
            $table->dropIndex('venta_clientes_fecha_emision_index');
            $table->dropIndex('venta_clientes_estado_index');
        });

        Schema::table('ventas', function (Blueprint $table) {
            $table->dropIndex('ventas_fecha_emision_index');
            $table->dropIndex('ventas_estado_index');
            $table->dropIndex('ventas_fecha_vencimiento_index');
        });
    }
};
