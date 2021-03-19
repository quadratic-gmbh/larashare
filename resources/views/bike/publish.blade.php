@extends('layouts.app')    
@section('content')
@component('components.form.focus',[
	'errors' => $errors
])
@endcomponent
  @include('bike.form_tabs',['active' => 'publish'])
  <h1 class="text-primary">{{__('bike.publish.title',['bike' => $bike->name])}}</h1>  
  <form method="POST">
    @csrf    
    <div class="form-group">
      @component('components.form.label_input_error',[
        'type' => 'checkbox',
        'name' => 'accepts_tos',
        'form_data' => $form_data,
        'label' => __('bike.publish.accepts_tos',['url' => '/allgemeine_nutzungsbedingungen.pdf']),        
      ])    
      @endcomponent
    </div>
    <div class="form-group">
      @component('components.form.label_input_error',[
        'type' => 'checkbox',
        'name' => 'has_permission',
        'form_data' => $form_data,
        'label' => __('bike.publish.has_permission'),        
      ])    
      @endcomponent
    </div>
    <div class="form-group">
      @component('components.form.label_input_error',[
        'type' => 'checkbox',
        'name' => 'public',
        'form_data' => $form_data,
        'label' => __('bike.publish.public'),        
      ])    
      @endcomponent
    </div>    
    <button type="submit" class="btn btn-primary">{{__('general.save')}}</button>
  </form>  
@endsection
