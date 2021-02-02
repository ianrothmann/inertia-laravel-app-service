<?php

namespace IanRothmann\InertiaApp\ServiceProviders;

use IanRothmann\InertiaApp\Commands\CommandRouteGenerator;
use IanRothmann\InertiaApp\Commands\InertiaPageGenerator;
use IanRothmann\InertiaApp\Middleware\SetFromBackUrlInSession;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Routing\Router;
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

        $this->registerMiddleware();
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

    /**
     * Register all middleware
     */
    public function registerMiddleware() {
        $router = $this->app->make(Router::class);
        $router->pushMiddlewareToGroup('web', SetFromBackUrlInSession::class);
    }

}
