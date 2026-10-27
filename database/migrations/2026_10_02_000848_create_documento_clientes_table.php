<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documento_clientes', function (Blueprint $table) {
            $table->id();

            // Relaciones
            $table->foreignId('cliente_id')
                ->constrained('clientes')
                ->cascadeOnDelete();

            $table->foreignId('contrato_id')
                ->nullable()
                ->constrained('contratos')
                ->nullOnDelete();

            $table->foreignId('usuario_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            // Información del documento
            $table->string('nombre');
            $table->string('tipo_documento');
            $table->string('nombre_archivo');
            $table->string('ruta_archivo');
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('tamano_bytes')->default(0);
            $table->text('descripcion')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documento_clientes');
    }
};
