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
        Schema::create('roles', function (Blueprint $table) {

            $table->id();

            // Nombre del rol:
            // Administrador, Recursos Humanos, Contabilidad, Consulta, etc.
            $table->string('nombre');

            // Descripción opcional del rol
            $table->string('descripcion')->nullable();

            // Permite activar o desactivar un rol
            // sin eliminarlo de la base de datos.
            $table->boolean('activo')->default(true);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('roles');
    }
};
