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
        Schema::create('postulantes', function (Blueprint $table) {
    $table->id();

    $table->string('nombres');
    $table->string('apellidos');
    $table->string('dni', 8)->unique();
    $table->string('telefono', 9)->nullable();
    $table->string('email')->nullable();
    $table->string('puesto');
    $table->date('fecha_postulacion');
    $table->string('estado')->default('Postulando');

    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('postulantes');
    }
};
