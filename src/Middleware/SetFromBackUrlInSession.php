<?php

namespace IanRothmann\InertiaApp\Middleware;

use Closure;
use IanRothmann\InertiaApp\Facades\InertiaApp;

class SetFromBackUrlInSession
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        InertiaApp::removeUrlFromNavigationHistory($request->fullUrl());

        $fromBackUrlKey = config('inertia-app.nav_history.request_key');
        if ($request->has($fromBackUrlKey)) {
            if (intval($request->get($fromBackUrlKey, 0)) === 1) {
                session()->put($fromBackUrlKey, 1);
            }
            $queryString = http_build_query($request->except($fromBackUrlKey));
            return redirect()->to(url()->current() . ($queryString ? '?' . $queryString : ''));
        }
        $response = $next($request);
        //Remove key from session after request has been processed. This prevents middlewares without back buttons from passing the incorrect url to the next middleware with a back button
        session()->remove($fromBackUrlKey);
        return $response;
    }
}
