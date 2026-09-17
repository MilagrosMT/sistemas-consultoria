<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Process\Process;

class DatabaseBackup extends Command
{
    protected $signature = 'db:backup';

    protected $description = 'Genera un backup de la base de datos MySQL';

    public function handle(): int
    {
        $mysqldump = 'C:\\Program Files\\MySQL\\MySQL Server 8.0\\bin\\mysqldump.exe';

        $host = config('database.connections.mysql.host');
        $port = config('database.connections.mysql.port');
        $database = config('database.connections.mysql.database');
        $username = config('database.connections.mysql.username');
        $password = config('database.connections.mysql.password');

        $backupDirectory = storage_path('app/backups');

        if (!is_dir($backupDirectory)) {
            mkdir($backupDirectory, 0755, true);
        }

        $filename = $database . '_' . now()->format('Y-m-d_H-i-s') . '.sql';
        $backupPath = $backupDirectory . DIRECTORY_SEPARATOR . $filename;

        $process = new Process([
            $mysqldump,
            '--protocol=TCP',
            '--host=' . $host,
            '--port=' . $port,
            '--user=' . $username,
            '--password=' . $password,
            '--result-file=' . $backupPath,
            $database,
        ]);

        $process->setTimeout(300);
        $process->run();

        if (!$process->isSuccessful()) {
            $this->error('No se pudo generar el backup.');
            $this->error($process->getErrorOutput());

            return self::FAILURE;
        }

        if (!file_exists($backupPath) || filesize($backupPath) === 0) {
            $this->error('El archivo de backup no fue generado correctamente.');

            return self::FAILURE;
        }

        $this->info('Backup generado correctamente.');
        $this->line('Archivo: ' . $backupPath);
        $this->line('Tamaño: ' . number_format(filesize($backupPath)) . ' bytes');

        return self::SUCCESS;
    }
}
