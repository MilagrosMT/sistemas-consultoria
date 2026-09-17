<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('servicio_procesos', function (Blueprint $table) {
            $table->id();

            $table->foreignId('cliente_id')
                ->constrained('clientes')
                ->cascadeOnDelete();

            $table->foreignId('contrato_id')
                ->nullable()
                ->constrained('contratos')
                ->nullOnDelete();

            $table->foreignId('servicio_id')
                ->constrained('servicios')
                ->cascadeOnDelete();

            $table->foreignId('empleado_id')
                ->nullable()
                ->constrained('empleados')
                ->nullOnDelete();

            $table->string('periodo', 20);

            $table->date('fecha_inicio');

            $table->date('fecha_vencimiento')
                ->nullable();

            $table->enum('prioridad', [
                'Baja',
                'Media',
                'Alta'
            ])->default('Media');

            $table->enum('estado', [
                'Pendiente',
                'En proceso',
                'Completado',
                'Observado'
            ])->default('Pendiente');

            $table->text('observacion')
                ->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('servicio_procesos');
    }
};