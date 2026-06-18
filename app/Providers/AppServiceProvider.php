<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(\App\Contracts\AiProvider::class, function ($app) {
            return (new \App\Services\Ai\AiProviderFactory())->make();
        });
    }

    public function boot(): void
    {
        Paginator::defaultView('vendor.pagination.custom');
    }
}


