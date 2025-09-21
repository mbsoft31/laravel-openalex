<?php

namespace Mbsoft\OpenAlex;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Mbsoft\OpenAlex\Commands\OpenAlexCommand;

class OpenAlexServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('laravel-openalex')
            ->hasConfigFile()
            ->hasViews()
            ->hasCommand(OpenAlexCommand::class);
    }

    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/openalex.php', 'openalex');

        $this->app->bind('openalex', function () {
            return new OpenAlex();
        });
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/openalex.php' => config_path('openalex.php'),
            ], 'config');
        }
    }
}
