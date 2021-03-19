<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Closure;

/**
 * @deprecated
 */
class EmbedCssHandler
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
      // make container full width
      View::share('container_fluid',true);
      
      $embed = $request->route('embed');
      if ($embed->has_custom_css ?? false) {
        View::share('custom_css',$embed->custom_css_path);
      }            
     
      return $next($request);
    }
}
