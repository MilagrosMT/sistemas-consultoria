<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('venta_clientes', function (Blueprint $table) {
            $table->id();

            // Cliente de la consultora cuya información de ventas se procesa
            $table->foreignId('cliente_id')
                ->constrained('clientes')
                ->cascadeOnDelete();

            // Datos del comprador
            $table->string('comprador');
            $table->string('ruc_comprador', 11)->nullable();

            // Comprobante
            $table->string('tipo_comprobante')->default('Factura');
            $table->string('serie', 10);
            $table->string('numero', 20);

            // Fecha
            $table->date('fecha_emision');

            // Importes
            $table->decimal('base_imponible', 10, 2);
            $table->decimal('igv', 10, 2);
            $table->decimal('total', 10, 2);

            // Forma y estado
            $table->enum('forma_pago', ['Contado', 'Credito'])
                ->default('Contado');

            $table->enum('estado', ['Registrada', 'Observada', 'Anulada'])
                ->default('Registrada');

            $table->text('observacion')->nullable();

            $table->timestamps();

            // Evita duplicar el mismo comprobante para un cliente
            $table->unique([
                'cliente_id',
                'tipo_comprobante',
                'serie',
                'numero'
            ], 'venta_cliente_comprobante_unico');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('venta_clientes');
    }
};