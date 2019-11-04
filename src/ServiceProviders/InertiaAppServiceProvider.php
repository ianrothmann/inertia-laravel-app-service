<?php

namespace IanRothmann\InertiaApp\ServiceProviders;

use IanRothmann\InertiaApp\Commands\CommandRouteGenerator;
use IanRothmann\InertiaApp\Commands\InertiaPageGenerator;
use Illuminate\Support\ServiceProvider;

class InertiaAppServiceProvider extends ServiceProvider
{

    public function boot()
    {
        $this->publishes([
            __DIR__.'/../Config/inertia-app.php' => config_path('inertia-app.php'),
        ],'config');

        $this->commands([
            CommandRouteGenerator::class,
            InertiaPageGenerator::class
        ]);
    }

    public function register(){
        $this->app->bind('inertia-app','IanRothmann\InertiaApp\InertiaAppService');
        $this->registerHelpers();
    }


    /**
     * Register helpers file
     */
    public function registerHelpers()
    {
        if (file_exists( __DIR__.DIRECTORY_SEPARATOR.'../helpers.php'))
        {
            require_once __DIR__.DIRECTORY_SEPARATOR.'../helpers.php';
        }
    }



}
