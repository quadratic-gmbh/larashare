@extends('layouts.app') 
@section('content')
@component('components.form.focus',[
	'errors' => $errors 
])
@endcomponent
  @include('embed.form_tabs',['active' => 'edit'])
  <h1 class="text-primary">{{__('embed.form.title')}}</h1>
  @php
  	$route = null;
  	if ($edit_mode) {
  	  $route = route('embed.update',['id' => $embed->id]);
	  } else {
	  	$route = route('embed.store');
	  }
  @endphp
  <form method="POST" action="{{ $route }}">
    @csrf
    <div class="form-group">
      @component('components.form.label_input_error',[
        'name' => 'name',
        'label' => __('general.name') . '*',
        'form_data' => $form_data
      ])
      @endcomponent
    </div>
    <h3>{{__('embed.form.defaults')}}</h3>
    <h5>{{__('embed.form.search.header')}}</h5>
    <div class="form-group">
      @component('components.form.label_input_error',[
        'name' => 'search[location]',
        'label' => __('embed.form.search.location'),
        'form_data' => $form_data
      ])
      @endcomponent
    </div>
    <div class="form-group">
      <button type="submit" class="btn btn-primary">{{__('general.save')}}</button>
      <a href="{{route('embed.index')}}" class="btn btn-secondary">{{__('general.back')}}</a>
    </div>
  </form>	
@endsection
