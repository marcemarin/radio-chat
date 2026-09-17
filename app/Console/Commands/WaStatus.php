<?php

namespace App\Console\Commands;

use App\Wa\WaProvider;
use Illuminate\Console\Command;

class WaStatus extends Command
{
    protected $signature = 'wa:status';

    protected $description = 'Estado de la conexión de WhatsApp';

    public function handle(WaProvider $wa): int
    {
        $this->info('Estado: '.$wa->state());

        return self::SUCCESS;
    }
}
