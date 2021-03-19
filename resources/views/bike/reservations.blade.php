@extends('layouts.app')
@push('scripts')
<script src="/js/moment.js"></script>
<script src="/fullcalendar/core/main.js"></script>
<script src="/fullcalendar/core/locales/{{App::getLocale()}}.js"></script>
<script src="/fullcalendar/bootstrap/main.js"></script>
<script src="/fullcalendar/moment/main.js"></script>
<script src="/fullcalendar/daygrid/main.js"></script>
<script src="/fullcalendar/timegrid/main.js"></script>
<script src="/fullcalendar/interaction/main.js"></script>
<script src="/js/bike.reservations.js"></script>        
@endpush 
@push('styles')
<link href="/fullcalendar/core/main.css" rel="stylesheet"/>
<link href="/fullcalendar/bootstrap/main.css" rel="stylesheet"/>
<link href="/fullcalendar/daygrid/main.css" rel="stylesheet"/>
<link href="/fullcalendar/timegrid/main.css" rel="stylesheet"/>
@endpush
@section('content')
  <h1 class="text-primary">{{__('bike.reservations.title',['name' => $bike->name])}}</h1> 
  <div class="my-3">{{__('bike.reservations.calendar_hint')}}</div>
  <div class="mb-5">
    <ul class="list-unstyled" id="calendar-legend">
      @foreach($rental_modes as $mode)      
        <li class="d-flex align-items-center rental-mode" data-mode-id="{{$mode->id}}" data-mode-name="{{$mode->name}}">
          <span class="cal-evt-legend {{$rental_mode_colors[$mode->id]}}"></span>
          {{__('bike.reservations.legend.' . $mode->name)}}
        </li>
      @endforeach
      <li class="d-flex align-items-center">
        <span class="cal-evt-legend cal-evt-reserved"></span>{{__('bike.reservations.legend.reserved')}}
      </li>
      <li class="d-flex align-items-center">
        <span class="cal-evt-legend cal-evt-pending"></span>{{__('bike.reservations.legend.pending')}}
      </li>       
    </ul>
    <div id="calendar" data-bike-id="{{$bike->id}}" data-date="{{$date}}"></div>
  </div> 
@endsection
