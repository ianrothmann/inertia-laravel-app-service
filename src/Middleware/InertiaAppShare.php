<?php

namespace IanRothmann\InertiaApp\Middleware;

use Closure;

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
