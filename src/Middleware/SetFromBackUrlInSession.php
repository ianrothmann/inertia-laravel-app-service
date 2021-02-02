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
        $fromBackUrlKey = config('inertia-app.back_request_key');
        if ($request->has($fromBackUrlKey)) {
            if (intval($request->get($fromBackUrlKey, 0)) === 1) {
                session()->put($fromBackUrlKey, 1);
            }
            return redirect()->to(url()->current() . '?' . http_build_query($request->except($fromBackUrlKey)));
        }
        return $next($request);
    }
}
