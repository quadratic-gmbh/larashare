@extends('layouts.app') 
@section('content')
@component('components.form.focus',[
	'errors' => $errors
])
@endcomponent
  <h1 class="text-primary">
    {{ __('user.edit.title')}}
  </h1>
  @if(session('update_success'))
  <div class="alert alert-success" role="alert">
  <i class="fas fa-check mr-3"></i>{{__('general.saved_success')}}
  </div>
  @endif
  <form method="POST">
    @csrf
    <div class="form-group row">
      <div class="col-6">
        @component('components.form.label_input_error',[
          'name' => 'firstname',
          'label' => __('user.firstname') . '*',
        'form_data' => $form_data     
        ])
        @endcomponent
      </div>
      <div class="col-6">
        @component('components.form.label_input_error',[
          'name' => 'lastname',
          'label' => __('user.lastname')  . '*',
        'form_data' => $form_data    
        ])
        @endcomponent
      </div>
    </div>
    <div class="form-group row">
      <div class="col-6">
        @component('components.form.label_input_error',[
          'name' => 'date_of_birth',
          'label' => __('user.date_of_birth') . '*',
          'type' => 'date',
        'form_data' => $form_data
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
          'empty_option' => true,
          'form_data' => $form_data
        ])
        @endcomponent
      </div>
    </div>
    <div class="form-group">
      @component('components.form.label_input_error',[
        'name' => 'street_name',
        'label' => __('general.street_name') . '*',
        'form_data' => $form_data
      ])
      @endcomponent
    </div>
    <div class="form-group row">
      <div class="col-6">
        @component('components.form.label_input_error',[
          'name' => 'house_number',
          'label' => __('general.house_number') . '*',
        'form_data' => $form_data
        ])
        @endcomponent
      </div>
      <div class="col-6">
        @component('components.form.label_input_error',[
          'name' => 'postal_code',
          'label' => __('general.postal_code') . '*',
        'form_data' => $form_data
        ])
        @endcomponent
      </div>
    </div>    
    <div class="form-group">
      @component('components.form.label_input_error',[
        'name' => 'city',
        'label' => __('general.city') . '*',
        'form_data' => $form_data
      ])
      @endcomponent
    </div>        
    <div class="form-group">
      @component('components.form.label_input_error',[
        'name' => 'telephone',
        'label' => __('general.telephone') . '*',
        'form_data' => $form_data
      ])
      @endcomponent
    </div>   
    <div class="form-group">
      <label>{{__('user.edit.email_old')}}</label>
      <input class="form-control" type="text" readonly value="{{$form_data['email_old']}}">
    </div>
    <div class="form-group">
      @component('components.form.label_input_error',[
        'name' => 'email',
        'label' => __('general.email'),
        'type' => 'email',
        'form_data' => $form_data
      ])
      @endcomponent
    </div>    
    <div class="form-group">
      @component('components.form.label_input_error',[
        'name' => 'email_confirmation',
        'label' => __('general.email_confirmation'),
        'type' => 'email',
        'form_data' => $form_data
      ])
      @endcomponent
    </div>
    <div class="form-group">      
      @component('components.form.label_input_error',[
        'name' => 'newsletter',
        'type' => 'checkbox',
        'label' => __('auth.register.newsletter'),
        'form_data' => $form_data
      ])
      @endcomponent
    </div>
    <div class="form-group">
      @component('components.form.label_input_error',[
        'name' => 'password_new',
        'label' => __('user.edit.password_new'),
        'type' => 'password',
      ])
      @endcomponent
    </div>    
    <div class="form-group">
      @component('components.form.label_input_error',[
        'name' => 'password_new_confirmation',
        'label' => __('user.edit.password_new_confirmation'),
        'type' => 'password',
      ])
      @endcomponent
    </div>
    <div class="form-group">
      @component('components.form.label_input_error',[
        'name' => 'password',
        'label' => __('general.password'),
        'type' => 'password',
      ])
      @endcomponent
    </div>   
    <div class="form-group">
      <button type="submit" class="btn btn-primary">{{__('general.save') }}</button>
      <a class="btn btn-danger" href="{{route('user.delete')}}">{{__('general.delete')}}</a>      
    </div>
  </form>
@endsection
