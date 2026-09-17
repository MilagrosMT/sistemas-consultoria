<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('ventas', function (Blueprint $table) {
            $table->id();

            $table->foreignId('cliente_id')
                ->constrained('clientes')
                ->cascadeOnDelete();

            $table->foreignId('servicio_id')
                ->constrained('servicios')
                ->cascadeOnDelete();

            $table->string('tipo_comprobante')->default('Factura');
            $table->string('numero_comprobante')->unique();

            $table->date('fecha_emision');

            $table->decimal('base_imponible', 10, 2);
            $table->decimal('igv', 10, 2);
            $table->decimal('total', 10, 2);

            $table->enum('estado', [
                'Pendiente',
                'Pagada',
                'Anulada'
            ])->default('Pendiente');

            $table->text('observacion')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ventas');
    }
};