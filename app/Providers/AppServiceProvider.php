<?php

namespace App\Providers;

use App\Contracts\File;
use App\Http\Services\FileService;
// use GreatTree\Base\Services\FileService;
use Illuminate\Support\ServiceProvider;
// use GreatTree\Base\Contracts\Services\File as FileContracts;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(File::class, FileService::class);
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
