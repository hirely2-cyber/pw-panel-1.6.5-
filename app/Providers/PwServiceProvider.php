<?php

namespace App\Providers;

use App\Services\PW\MapCatalog;
use App\Services\PW\ProcessManager;
use App\Services\PW\Socket;
use Illuminate\Support\ServiceProvider;

class PwServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(Socket::class, fn () => Socket::fromCurrent());
        $this->app->singleton(ProcessManager::class, fn ($app) => new ProcessManager($app->make(Socket::class)));
        $this->app->singleton(MapCatalog::class, fn ($app) => new MapCatalog($app->make(Socket::class)));
    }

    public function boot(): void
    {
        //
    }
}
