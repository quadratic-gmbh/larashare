@extends('layouts.app')
@section('content')
  <h1 class="text-primary">
    {{__('user.delete.header')}}
  </h1>
  @if($has_bikes || $has_reservations)
  <div class="delete-info">  
    <h4 class="delete-info-head">{{__('user.delete.hint_not_possible')}}</h4>
    <ul class="delete-info-list">
      @if($has_bikes)
      <li><a target="_blank" href="{{route('bike.index')}}">{{__('user.delete.hint_bikes')}}<i class="fas fa-link ml-2"></i></a></li>
      @endif
      @if($has_reservations)
      <li><a target="_blank"  href="{{route('user.reservations')}}">{{__('user.delete.hint_reservations')}}<i class="fas fa-link ml-2"></i></a></li>
      @endif
    </ul>
  </div> 
  
  
  <a href="{{route('user.edit')}}" class="btn btn-secondary">{{__('general.back')}}</a>
  @else
  <p>{{__('user.delete.hint')}}</p>
	<form method="post">
		@csrf
		@method('DELETE')
    <div class="form-group">
      @component('components.form.label_input_error',[
        'name' => 'password',
        'label' => __('general.password'),
        'type' => 'password',
      ])
      @endcomponent
    </div>
		<div class="form-group">      
    	<button type="submit" class="btn btn-danger">
    		{{__('general.delete')}}
    	</button>
    	<a href="{{route('user.edit')}}" class="btn btn-secondary">{{__('general.back')}}</a>
		</div>
	</form>
  @endif
@endsection
