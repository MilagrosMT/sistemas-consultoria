<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class DatabaseHealth extends Command
{
    protected $signature = 'db:health';

    protected $description = 'Verifica el estado de la conexión y la base de datos MySQL';

    public function handle(): int
    {
        try {
            $database = DB::connection()->getDatabaseName();
            $driver = DB::connection()->getDriverName();

            $version = DB::selectOne('SELECT VERSION() AS version')->version;

            $tables = DB::select('SHOW TABLES');

            $this->info('Estado de la base de datos: OK');
            $this->line('Motor: ' . strtoupper($driver));
            $this->line('Base de datos: ' . $database);
            $this->line('Versión MySQL: ' . $version);
            $this->line('Tablas encontradas: ' . count($tables));

            return self::SUCCESS;

        } catch (\Throwable $e) {
            $this->error('La conexión con la base de datos no está disponible.');
            $this->error($e->getMessage());

            return self::FAILURE;
        }
    }
}
