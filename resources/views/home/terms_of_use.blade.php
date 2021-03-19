@extends('layouts.app')
@section('content')
  <h1 class="text-primary">{{ __('home.terms_of_use.title') }}</h1>
  <p>{{ __('home.terms_of_use.intro') }}</p>
  @foreach($fields as $field)
  <div class="info-item" id="{{$field}}">
    @php
      $prefix = "home.terms_of_use.{$field}_";
    @endphp
    <h3>{{ __($prefix . 'subtitle') }}</h3>   
    <p>{!! __($prefix . 'text') !!}</p>   
  </div>
  @endforeach
@endsection
