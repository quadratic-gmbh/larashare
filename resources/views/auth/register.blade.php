@extends('layouts.app') 
@section('content')
@component('components.form.focus',[
	'errors' => $errors
])
@endcomponent
  <h1 class="text-primary">
    {{ __('auth.register.title')}}
  </h1>
  <p>{{__('auth.register.hint')}}</p>
  <form method="POST" action="{{ route('register') }}">
    @csrf

    <div class="form-group row">
      <div class="col-6">
        @component('components.form.label_input_error',[
          'name' => 'firstname',
          'label' => __('user.firstname') . '*'     
        ])
        @endcomponent
      </div>
      <div class="col-6">
        @component('components.form.label_input_error',[
          'name' => 'lastname',
          'label' => __('user.lastname')  . '*'    
        ])
        @endcomponent
      </div>
    </div>
    <div class="form-group row">
      <div class="col-6">
        @component('components.form.label_input_error',[
          'name' => 'date_of_birth',
          'label' => __('user.date_of_birth') . '*',
          'type' => 'date'     
        ])
        @endcomponent
      </div>
      <div class="col-6">
        @component('components.form.label_input_error',[
          'name' => 'gender_id',
          'label' => __('user.gender.gender'),
          'type' => 'select',
          'select_options' => $gender_options,
          'empty_option_text' => __('user.gender.'),
          'empty_option' => true     
        ])
        @endcomponent
      </div>
    </div>
    <div class="form-group">
      @component('components.form.label_input_error',[
        'name' => 'street_name',
        'label' => __('general.street_name') . '*'
      ])
      @endcomponent
    </div>
    <div class="form-group row">
      <div class="col-6">
        @component('components.form.label_input_error',[
          'name' => 'house_number',
          'label' => __('general.house_number') . '*'
        ])
        @endcomponent
      </div>
      <div class="col-6">
        @component('components.form.label_input_error',[
          'name' => 'postal_code',
          'label' => __('general.postal_code') . '*'
        ])
        @endcomponent
      </div>
    </div>    
    <div class="form-group">
      @component('components.form.label_input_error',[
        'name' => 'city',
        'label' => __('general.city') . '*'
      ])
      @endcomponent
    </div>    
    <div class="form-group">
      @component('components.form.label_input_error',[
        'name' => 'telephone',
        'label' => __('general.telephone') . '*'
      ])
      @endcomponent
    </div>   
    <div class="form-group">
      @component('components.form.label_input_error',[
        'name' => 'email',
        'label' => __('general.email') . '*',
        'type' => 'email',
      ])
      @endcomponent
    </div>    
    <div class="form-group">
      @component('components.form.label_input_error',[
        'name' => 'email_confirmation',
        'label' => __('general.email_confirmation') . '*',
        'type' => 'email',
      ])
      @endcomponent
    </div>
    <div class="form-group">      
      @component('components.form.label_input_error',[
        'name' => 'newsletter',
        'type' => 'checkbox',
        'label' => __('auth.register.newsletter')
      ])
      @endcomponent
    </div>
    <div class="form-group">
      @component('components.form.label_input_error',[
        'name' => 'password',
        'label' => __('general.password') . '*',
        'type' => 'password',
      ])
      @endcomponent
    </div>    
    <div class="form-group">
      @component('components.form.label_input_error',[
        'name' => 'password_confirmation',
        'label' => __('general.password_confirmation') . '*',
        'type' => 'password',
      ])
      @endcomponent
    </div>
    <div class="form-group">
      <label for="referrer">{{__('auth.register.referrer_text')}}</label>      
      @component('components.form.select',[
        'name' => 'user_referrer_id',
        'select_options' => $referrer_options,
        'empty_option' => true,
        'empty_option_text' => __('general.please_select')
      ])
      @endcomponent
    </div>
    <div class="form-group">      
      @component('components.form.label_input_error',[
        'name' => 'accept_tos',
        'type' => 'checkbox',
        'label' => __('auth.register.accept_tos_text',['url' => '/allgemeine_nutzungsbedingungen.pdf'])
      ])
      @endcomponent
    </div>
    <div class="form-group">
        <button type="submit" class="btn btn-primary">
        {{__('auth.register.register') }}</button>      
    </div>
  </form>
  
@endsection
