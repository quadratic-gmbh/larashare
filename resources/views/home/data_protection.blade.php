@extends('layouts.app')
@section('content')
  <h1 class="text-primary">{{ __('home.data_protection.title') }}</h1>
  <p>{{ __('home.data_protection.intro') }}</p>
  @foreach($fields as $field)
  <div class="info-item" id="{{$field}}">
    @php
      $prefix = "home.data_protection.{$field}_";
    @endphp
    <h3>{{ __($prefix . 'subtitle') }}</h3>   
    <p>{!! __($prefix . 'text') !!}</p>   
  </div>
  @endforeach
@endsection
