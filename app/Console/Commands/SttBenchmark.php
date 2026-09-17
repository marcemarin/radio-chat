<?php

namespace App\Console\Commands;

use App\Stt\SttManager;
use Illuminate\Console\Command;
use Throwable;

class SttBenchmark extends Command
{
    protected $signature = 'stt:benchmark {dir : carpeta con audios (ogg/opus, mp3, m4a)} {--drivers= : lista separada por coma; por defecto todos los configurados} {--limit=50}';

    protected $description = 'Transcribe los mismos audios con cada proveedor STT y muestra texto, tiempo y costo estimado';

    private const USD_PER_MIN = ['assemblyai' => 0.0025, 'deepgram' => 0.0043, 'openai' => 0.003, 'fake' => 0];

    public function handle(SttManager $stt): int
    {
        $drivers = $this->option('drivers') ? explode(',', $this->option('drivers')) : $stt->available();
        if (! $drivers) {
            $this->error('No hay drivers configurados: cargá ASSEMBLYAI_API_KEY / DEEPGRAM_API_KEY / OPENAI_API_KEY en .env');

            return self::FAILURE;
        }

        $files = collect(glob(rtrim($this->argument('dir'), '/').'/*.{ogg,opus,oga,mp3,m4a,wav}', GLOB_BRACE))
            ->take((int) $this->option('limit'));
        if ($files->isEmpty()) {
            $this->error('No encontré audios en '.$this->argument('dir'));

            return self::FAILURE;
        }
        $this->info(sprintf('%d audios × %s', $files->count(), implode(', ', $drivers)));

        $totals = [];
        foreach ($files as $file) {
            $this->newLine();
            $this->line('<fg=cyan>'.basename($file).'</>');
            $mime = match (pathinfo($file, PATHINFO_EXTENSION)) {
                'mp3' => 'audio/mpeg', 'm4a' => 'audio/mp4', 'wav' => 'audio/wav', default => 'audio/ogg',
            };
            foreach ($drivers as $d) {
                try {
                    $t = $stt->driver($d)->transcribe($file, $mime);
                    $dur = (float) ($t->raw['audio_duration'] ?? $t->raw['duration'] ?? 0);
                    $totals[$d]['ms'] = ($totals[$d]['ms'] ?? 0) + $t->ms;
                    $totals[$d]['min'] = ($totals[$d]['min'] ?? 0) + $dur / 60;
                    $totals[$d]['n'] = ($totals[$d]['n'] ?? 0) + 1;
                    $this->line(sprintf('  <fg=yellow>%-10s</> %5dms  %s', $d, $t->ms, $t->text));
                } catch (Throwable $e) {
                    $this->line(sprintf('  <fg=red>%-10s</> ERROR %s', $d, $e->getMessage()));
                }
            }
        }

        $this->newLine();
        $this->table(['driver', 'audios', 'ms promedio', 'min de audio', 'USD estimado'], collect($totals)->map(fn ($t, $d) => [
            $d, $t['n'], (int) ($t['ms'] / max(1, $t['n'])), round($t['min'], 1), round($t['min'] * (self::USD_PER_MIN[$d] ?? 0), 4),
        ])->values()->all());
        $this->line('Marcá a mano cuál transcribió mejor: el costo es casi igual, la precisión en español regional es lo que decide.');

        return self::SUCCESS;
    }
}
