<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('compras', function (Blueprint $table) {
            $table->id();

            // Cliente de la consultora cuya información contable se registra
            $table->foreignId('cliente_id')
                ->constrained('clientes')
                ->cascadeOnDelete();

            // Datos del proveedor
            $table->string('proveedor');
            $table->string('ruc_proveedor', 11);

            // Comprobante
            $table->string('tipo_comprobante')->default('Factura');
            $table->string('serie', 10);
            $table->string('numero', 20);

            // Fechas
            $table->date('fecha_emision');
            $table->date('fecha_vencimiento')->nullable();

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

            // Evita registrar dos veces el mismo comprobante del mismo proveedor
            $table->unique([
                'cliente_id',
                'ruc_proveedor',
                'tipo_comprobante',
                'serie',
                'numero'
            ], 'compras_comprobante_unico');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('compras');
    }
};