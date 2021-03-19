<?php

namespace App\Http\Middleware;

use App\Services\UserWarningManager;
use Closure;

class CheckForUserWarnings
{
  /**   
   * @var UserWarningManager
   */
  protected $user_warning_manager;
  
  public function __construct(UserWarningManager $user_warning_manager)
  {
    $this->user_warning_manager = $user_warning_manager;
    
  }
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
      $this->user_warning_manager->checkForWarnings($request);      
      
      return $next($request);
    }
}
