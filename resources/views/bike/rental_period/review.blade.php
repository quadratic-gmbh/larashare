@extends('layouts.app')
@push('scripts')
<script src="/js/moment.js"></script>
<script src="/fullcalendar/core/main.js"></script>
<script src="/fullcalendar/core/locales/{{App::getLocale()}}.js"></script>
<script src="/fullcalendar/bootstrap/main.js"></script>
<script src="/fullcalendar/moment/main.js"></script>
<script src="/fullcalendar/daygrid/main.js"></script>
<script src="/fullcalendar/timegrid/main.js"></script>
<script src="/js/rental_period.review.js"></script>        
@endpush 
@push('styles')
<link href="/fullcalendar/core/main.css" rel="stylesheet"/>
<link href="/fullcalendar/bootstrap/main.css" rel="stylesheet"/>
<link href="/fullcalendar/daygrid/main.css" rel="stylesheet"/>
<link href="/fullcalendar/timegrid/main.css" rel="stylesheet"/>
@endpush
@section('content')
  @include('bike.form_tabs',['active' => 'review'])
  <h1 class="text-primary">{{__('bike.rental_periods_review.title')}}</h1>
  <p>{{__('bike.rental_periods_review.hint')}}</p>
  <ul class="list-unstyled" id="calendar-legend">    
  @foreach($rental_modes as $mode)
    <li class="d-flex align-items-center rental-mode" data-mode-id="{{$mode->id}}" data-mode-name="{{$mode->name}}">
      <i class="cal-evt-legend {{$rental_mode_colors[$mode->id]}}"></i>&nbsp;{{__('bike.show.legend.' . $mode->name)}}
    </li>
  @endforeach      
    <li class="d-flex align-items-center">
      <i class="cal-evt-legend cal-evt-blocked"></i>&nbsp;{{__('bike.rental_mode.none')}}
    </li>
  </ul>
  <div id="calendar" 
  data-bike-id="{{$bike->id}}" 
  @if($date) data-date="{{$date->format('Y-m-d')}}" @endif 
  data-exception-url="{{route('bike.rental_period_exception',['bike_id' => $bike])}}"></div>
  <form hidden id="exception-form" action="{{route('bike.rental_period_exception_instant',['bike_id' => $bike])}}" method="POST">
    @csrf
    <input type="hidden" name="date">
    <input type="hidden" name="time_from">
    <input type="hidden" name="time_to">
    <input type="hidden" name="delete">
  </form>  
@endsection
