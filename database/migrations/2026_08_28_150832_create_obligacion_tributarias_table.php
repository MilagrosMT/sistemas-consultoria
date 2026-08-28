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
Schema::create('obligacion_tributarias', function (Blueprint $table) {
    $table->id();

    $table->string('cliente');
    $table->string('ruc', 11);
    $table->string('tipo_obligacion');
    $table->string('periodo');
    $table->date('fecha_vencimiento');
    $table->decimal('monto', 10, 2)->default(0);
    $table->string('estado')->default('Pendiente');

    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('obligacion_tributarias');
    }
};
