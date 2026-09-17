<?php

namespace App\Console\Commands;

use App\Wa\WaProvider;
use Illuminate\Console\Command;

class WaConnect extends Command
{
    protected $signature = 'wa:connect {--open : abrir el QR con el visor del sistema}';

    protected $description = 'Crea/conecta la instancia de WhatsApp y muestra el QR para escanear';

    public function handle(WaProvider $wa): int
    {
        $res = $wa->connect();
        $this->info('Estado: '.$res['state']);

        if (! $res['qr']) {
            $this->info($res['state'] === 'open' ? 'Ya está conectado.' : 'Sin QR disponible todavía; volvé a correr el comando en unos segundos.');

            return self::SUCCESS;
        }

        $png = base64_decode(preg_replace('#^data:image/\w+;base64,#', '', $res['qr']));
        $path = storage_path('app/wa-qr.png');
        file_put_contents($path, $png);
        $this->info("QR guardado en {$path}");
        $this->line('WhatsApp → Dispositivos vinculados → Vincular un dispositivo → escanear.');

        if ($this->option('open') && PHP_OS_FAMILY === 'Darwin') {
            exec('open '.escapeshellarg($path));
        }

        return self::SUCCESS;
    }
}
