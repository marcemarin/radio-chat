<?php

namespace App\Providers;

use App\Classify\AnthropicClassifier;
use App\Classify\Classifier;
use App\Classify\FakeClassifier;
use App\Classify\JevClassifier;
use App\Stt\SttManager;
use App\Wa\EvolutionProvider;
use App\Wa\WaProvider;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(WaProvider::class, fn () => match (config('services.wa_provider', 'evolution')) {
            default => EvolutionProvider::fromConfig(),
        });

        $this->app->singleton(SttManager::class);

        $this->app->singleton(Classifier::class, function () {
            $c = config('services.classify');
            $llm = $c['anthropic_key'] ? new AnthropicClassifier((string) $c['anthropic_key'], (string) $c['model']) : null;

            return match (true) {
                // Decisiones con Jev; extracción de tema/lugar/nombre con el LLM si hay key, si no con el heurístico.
                $c['driver'] === 'jev' && $c['typesafe_key'] => new JevClassifier(
                    (string) $c['typesafe_key'], (string) $c['typesafe_model'], $llm ?? new FakeClassifier, $llm !== null,
                ),
                $c['driver'] === 'anthropic' && $llm => $llm,
                default => new FakeClassifier,
            };
        });
    }

    public function boot(): void
    {
        //
    }
}
