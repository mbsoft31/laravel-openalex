<?php

namespace Mbsoft\OpenAlex;

use Mbsoft\OpenAlex\Commands\OpenAlexCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

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
            ->hasMigration('create_laravel_openalex_table')
            ->hasCommand(OpenAlexCommand::class);
    }
}
