
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('planillas', function (Blueprint $table) {
            $table->id();

            $table->foreignId('empleado_id')
                ->constrained('empleados')
                ->cascadeOnDelete();

            $table->date('periodo');

            $table->decimal('sueldo_base', 10, 2);

            $table->decimal('bonificaciones', 10, 2)
                ->default(0);

            $table->decimal('descuentos', 10, 2)
                ->default(0);

            $table->decimal('sueldo_neto', 10, 2);

            $table->string('estado')
                ->default('Pendiente');

            $table->timestamps();

            $table->unique([
                'empleado_id',
                'periodo',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('planillas');
    }
};
