<?php

namespace App\Providers;

use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;

class AppServiceProvider extends ServiceProvider
{
    public function boot()
    {
        Paginator::useBootstrapFour();
        if ($this->app->environment('production')) {
            URL::forceRootUrl('https://pms.tashicell.com');
            URL::forceScheme('https');
        }

        view()->composer('master', 'App\Http\ViewComposers\MasterComposer');
    }

    public function register()
    {
        if ($this->app->environment() !== 'production' && class_exists(\Barryvdh\LaravelIdeHelper\IdeHelperServiceProvider::class)) {
            $this->app->register(\Barryvdh\LaravelIdeHelper\IdeHelperServiceProvider::class);
        }
    }
}
