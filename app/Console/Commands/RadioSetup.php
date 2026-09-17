<?php

namespace App\Console\Commands;

use App\Models\Program;
use Illuminate\Console\Command;

class RadioSetup extends Command
{
    protected $signature = 'radio:setup {name=Demo} {--station=} {--context=}';

    protected $description = 'Crea o actualiza el programa por defecto (nombre, emisora, contexto para el clasificador)';

    public function handle(): int
    {
        $p = Program::default();
        $p->fill(array_filter([
            'name' => $this->argument('name'),
            'station' => $this->option('station'),
            'context' => $this->option('context'),
        ]))->save();
        $this->info("Programa #{$p->id}: {$p->name} ({$p->station})");

        return self::SUCCESS;
    }
}
