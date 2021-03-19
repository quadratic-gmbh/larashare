<?php

namespace App\Http\Controllers\Auth;

use App\Gender;
use App\NewsletterConfirmation;
use App\User;
use App\UserReferrer;
use App\Http\Controllers\Controller;
use App\Services\Notifier;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Auth\RegistersUsers;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    /**
     * Where to redirect users after registration.
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
        $this->middleware('guest');
    }

    /**
     * Show the application registration form.
     *
     * @return \Illuminate\Http\Response
     */
    public function showRegistrationForm()
    {
      $gender_options = Gender::getSelectOptions();
      $referrer_options = UserReferrer::getSelectOptions();
      return view('auth.register',[
        'gender_options' => $gender_options,
        'referrer_options' => $referrer_options
      ]);
    }
    
    
    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
          'firstname' => ['required', 'string', 'max:255'],
          'lastname' => ['required', 'string', 'max:255'],
          'date_of_birth' => ['required', 'date_format:"Y-m-d"', 'before:today'],
          'gender_id' => ['nullable','integer','exists:genders,id'],
          'street_name' => ['required', 'string', 'max:255'],
          'house_number' => ['required', 'string', 'max:10'],
          'postal_code' => ['required', 'string', 'max:10'],
          'city' => ['required', 'string', 'max:255'],
          'telephone' => ['required', 'phone:AUTO,AT', 'max:100'],
          'email' => ['required', 'string', 'email', 'max:255', 'unique:users', 'confirmed'],
          'newsletter' => ['required', 'bool'],
          'password' => ['required', 'string', 'min:8', 'confirmed'],
          'user_referrer_id' => ['nullable','integer','exists:user_referrers,id'],
          'accept_tos' => ['required',function($attribute, $value, $fail) {
            if(!boolval($value)) {
              $fail(__('auth.register.accept_tos_error'));
            }
          }],
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return \App\User
     */
    protected function create(array $data)
    {            
        $filtered = [
          'date_of_birth' => $data['date_of_birth'],
          'gender_id' => $data['gender_id'],          
          'email' => $data['email'],
          'password' => Hash::make($data['password']),
        ];
        $strings = [
          'firstname',
          'lastname',
          'street_name',
          'city',
          'house_number',
          'postal_code',
          'telephone'
        ];
        foreach ($strings as $str) {
          $filtered[$str] = strip_tags($data[$str]);
        }  
        
        if (isset($data['user_referrer_id'])) {
          $filtered['user_referrer_id'] = intval($data['user_referrer_id']);
        }
        
        if($data['newsletter']){
          $newsletter_confirmation = NewsletterConfirmation::create(['email' => $filtered['email'], 'token' => Str::random(100)]);
          $notifier = new Notifier();
          $notifier->notifyEmailNewsletterConfirmation($newsletter_confirmation);
        }
        
        return User::create($filtered);
    }
}
