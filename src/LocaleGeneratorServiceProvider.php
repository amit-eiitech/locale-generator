<?php

namespace Eii\LocaleGenerator;

use Eii\LocaleGenerator\Console\Commands\TranslateBladeToJson;
use Illuminate\Support\ServiceProvider;

class LocaleGeneratorServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/locale-generator.php', 'locale-generator');
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                TranslateBladeToJson::class
            ]);
        }

        $this->publishes([
            __DIR__ . '/../config/locale-generator.php' => config_path('locale-generator.php'),
        ], 'config');
    }
}
