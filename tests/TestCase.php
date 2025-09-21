<?php

namespace Mbsoft\OpenAlex\Tests;

use Illuminate\Support\Facades\Cache;
use Mbsoft\OpenAlex\OpenAlexServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;
use Spatie\LaravelData\LaravelDataServiceProvider;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function getPackageProviders($app): array
    {
        return [
            OpenAlexServiceProvider::class,
            // Register the service provider for spatie/laravel-data
            LaravelDataServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app): void
    {
        // Use the sqlite in-memory database for testing
        config()->set('database.default', 'testing');

        // Use the array cache driver for all tests
        config()->set('cache.default', 'array');
    }

    protected function tearDown(): void
    {
        // Clear cache after each test to ensure isolation
        Cache::flush();
        parent::tearDown();
    }
}
