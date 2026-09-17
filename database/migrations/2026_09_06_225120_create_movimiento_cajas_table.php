<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('movimiento_cajas', function (Blueprint $table) {
            $table->id();

            // Cliente de la consultora
            $table->foreignId('cliente_id')
                ->constrained('clientes')
                ->cascadeOnDelete();

            // Tipo de movimiento
            $table->enum('tipo_movimiento', ['Ingreso', 'Egreso']);

            // Información del movimiento
            $table->string('concepto');
            $table->string('categoria')->nullable();
            $table->date('fecha');

            // Medio utilizado
            $table->enum('forma_pago', [
                'Efectivo',
                'Transferencia',
                'Tarjeta',
                'Yape/Plin',
                'Otro'
            ])->default('Efectivo');

            // Importe
            $table->decimal('monto', 10, 2);

            // Referencia opcional
            $table->string('numero_comprobante', 50)->nullable();
            $table->text('observacion')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('movimiento_cajas');
    }
};