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
        if ($request->has('_from_back_url')) {
            if (intval($request->get('_from_back_url', 0)) === 1) {
                session()->put('_from_back_url', 1);
            }
            return redirect()->to(url()->current() . '?' . http_build_query($request->except('_from_back_url')));
        }
        return $next($request);
    }
}
