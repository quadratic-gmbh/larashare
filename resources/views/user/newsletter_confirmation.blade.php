@extends('layouts.app')
@section('content')
	<h1 class="text-primary">
    {{__('user.newsletter_confirmation.title')}}
  </h1>
  <p>{{__('user.newsletter_confirmation.text')}}</p>
 <div class="form-group">
 <a href="{{route('home')}}" class="btn btn-secondary">{{__('general.back')}}</a>
 </div>
@endsection
