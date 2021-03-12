<?php

namespace IanRothmann\InertiaApp\Middleware;

use Closure;

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
        $fromBackUrlKey =  config('inertia-app.nav_history.request_key', 0);
        if ($request->has($fromBackUrlKey)) {
            if (intval($request->get($fromBackUrlKey, 0)) === 1) {
                session()->put($fromBackUrlKey, 1);
            }
            $queryString = http_build_query($request->except($fromBackUrlKey));
            return redirect()->to(url()->current() . ($queryString ? '?' . $queryString : ''));
        }
        return $next($request);
    }
}
