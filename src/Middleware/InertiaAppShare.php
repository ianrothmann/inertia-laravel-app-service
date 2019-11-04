<?php

namespace IanRothmann\InertiaApp\Middleware;

use Closure;
use IanRothmann\InertiaApp\Facades\InertiaApp;
use Inertia\Inertia;

class InertiaAppShare
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {

        $response=$next($request);

        return $response;
    }
}
