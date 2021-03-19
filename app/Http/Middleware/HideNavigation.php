<?php

namespace App\Http\Middleware;

use Illuminate\Support\Facades\View;
use Closure;

class HideNavigation
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
      View::share('hide_navigation',true);
      return $next($request);
    }
}
