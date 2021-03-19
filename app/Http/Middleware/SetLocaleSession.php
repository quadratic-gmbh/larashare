<?php
namespace App\Http\Middleware;

use Illuminate\Support\Facades\App;
use Closure;

class SetLocaleSession
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
    $locale = $request->session()->get('locale',function() use ($request) {
      return $this->getPreferredLocale($request);
    });

    App::setLocale($locale);
    return $next($request);
  }

  private function getPreferredLocale($request)
  {
    $locales = explode(',',config('app.public_locales'));
    $preferred = $request->getPreferredLanguage($locales);
    $request->session()->put('locale',$preferred);
    $request->session()->save();
    return $preferred;
  }
}
