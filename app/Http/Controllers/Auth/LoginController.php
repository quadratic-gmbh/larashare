<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/home';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }
    
    /**
     * Attempt to log the user into the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    protected function attemptLogin(Request $request)
    {
      // fake remember me checked
      $request->merge(['remember' => 1]);
      
      return $this->guard()->attempt(
        $this->credentials($request),
        true //$request->filled('remember') /always remember!
        );
    }
    
    
    /**
     * Send the response after the user was authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    protected function sendLoginResponse(Request $request)
    {
      if($request->has('remember')) {
        $remember_me_minutes = config('auth.remember_me_minutes');
        $remember_me_cookie_key = Auth::getRecallerName();
        Cookie::queue($remember_me_cookie_key, Cookie::get($remember_me_cookie_key), $remember_me_minutes);
      }
      
      $request->session()->regenerate();
      
      $this->clearLoginAttempts($request);
      
      return $this->authenticated($request, $this->guard()->user())
      ?: redirect()->intended($this->redirectPath());
    }
    
}
