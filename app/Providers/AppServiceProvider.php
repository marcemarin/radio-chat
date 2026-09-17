<?php

namespace App\Providers;

use App\Classify\AnthropicClassifier;
use App\Classify\Classifier;
use App\Classify\FakeClassifier;
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

            return $c['driver'] === 'anthropic' && $c['anthropic_key']
                ? new AnthropicClassifier((string) $c['anthropic_key'], (string) $c['model'])
                : new FakeClassifier;
        });
    }

    public function boot(): void
    {
        //
    }
}
