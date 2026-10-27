<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('solicituds') && ! Schema::hasTable('solicitudes')) {
            Schema::rename('solicituds', 'solicitudes');
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('solicitudes') && ! Schema::hasTable('solicituds')) {
            Schema::rename('solicitudes', 'solicituds');
        }
    }
};
