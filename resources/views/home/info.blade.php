@extends('layouts.app')
@section('content')

<h1 class="text-primary">{{ __('home.info.title') }}</h1>
<ul>
  @foreach($fields as $field => $count)
  <li><a href="#{{ $field }}">{{ __("home.info.faq_{$field}_subtitle")}}</a></li>
  @endforeach
</ul>

@foreach($fields as $field => $count)
<div class="info-item" id="{{$field}}">
  @php
    $prefix = "home.info.faq_{$field}_";
  @endphp
  <h3>{{ __($prefix . 'subtitle') }}</h3>
  @if(!$count)
   <p>{!! __($prefix . 'text') !!}</p>
  @else
    <ul>
    @for($i = 1; $i<= $count; $i++)
      <li>{!! __($prefix . $i) !!}</li>
    @endfor
    </ul>
  @endif
</div>
@endforeach

@endsection
