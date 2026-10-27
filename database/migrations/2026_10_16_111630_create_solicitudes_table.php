
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('solicituds') && Schema::hasTable('solicitudes')) {
            throw new RuntimeException(
                'Existen las tablas solicituds y solicitudes. Se requiere revisar la migración de datos antes de continuar.'
            );
        }

        if (Schema::hasTable('solicituds')) {
            Schema::rename('solicituds', 'solicitudes');

            return;
        }

        if (Schema::hasTable('solicitudes')) {
            return;
        }
        Schema::create('solicitudes', function (Blueprint $table) {
            $table->id();

            $table->string('codigo', 25)->unique();

            $table->foreignId('solicitante_id')
                ->constrained('users')
                ->restrictOnDelete();

            $table->enum('tipo', [
                'Acceso',
                'Servicio',
                'Información',
            ]);

            $table->string('asunto', 150);
            $table->text('descripcion');

            $table->enum('prioridad', [
                'Baja',
                'Media',
                'Alta',
                'Crítica',
            ])->default('Media');

            $table->enum('estado', [
                'Recibida',
                'En validación',
                'Pendiente de aprobación',
                'En atención',
                'Pendiente del solicitante',
                'Resuelta',
                'Cerrada',
                'Rechazada',
            ])->default('Recibida');

            $table->foreignId('responsable_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->foreignId('aprobador_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('fecha_aprobacion')->nullable();
            $table->timestamp('fecha_resolucion')->nullable();
            $table->timestamp('fecha_cierre')->nullable();

            $table->text('respuesta')->nullable();
            $table->text('motivo_rechazo')->nullable();

            $table->timestamps();

            $table->index(['tipo', 'estado']);
            $table->index(['solicitante_id', 'created_at']);
            $table->index(['responsable_id', 'estado']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('solicitudes');
    }
};
