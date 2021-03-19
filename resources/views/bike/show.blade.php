@extends('layouts.app')
@push('scripts')
<script src="/js/moment-with-locales.js"></script>
<script src="/fullcalendar/core/main.js"></script>
<script src="/fullcalendar/core/locales/{{App::getLocale()}}.js"></script>
<script src="/fullcalendar/bootstrap/main.js"></script>
<script src="/fullcalendar/moment/main.js"></script>
<script src="/fullcalendar/daygrid/main.js"></script>
<script src="/fullcalendar/timegrid/main.js"></script>
<script src="/fullcalendar/interaction/main.js"></script>
<script src="/lightbox2/js/lightbox.min.js"></script>
<script src="/js/bike.show.js"></script>        
@endpush 
@push('styles')
<link href="/fullcalendar/core/main.css" rel="stylesheet"/>
<link href="/fullcalendar/bootstrap/main.css" rel="stylesheet"/>
<link href="/fullcalendar/daygrid/main.css" rel="stylesheet"/>
<link href="/fullcalendar/timegrid/main.css" rel="stylesheet"/>
<link href="/lightbox2/css/lightbox.min.css" rel="stylesheet"/>
@endpush
@section('content')
@component('components.form.focus',[
	'errors' => $errors
])
@endcomponent
  <h1 class="text-primary">{{__('bike.show.title'.($reservation_id ? '_edit' : ''),['name' => $bike->name])}}</h1>
  <div class="card bg-light border-light">
    <div class="card-body">
      <h3 class="card-title">{{$bike->name}}</h3>
      @if($bike->images->isNotEmpty())      
        <div class="mb-3">
        @foreach($bike->images as $image)          
          <a href="{{$image->getUrl(App\Image::SZ_1000)}}" data-lightbox="images">
            <img src="{{$image->getUrl()}}" alt="Image" class="img-thumbnail">
          </a>        
        @endforeach
        </div>      
      @endif
      <ul class="list-unstyled">
        <li><b>{{__('bike.show.children')}}:</b> {{__('general.' . ($bike->children > 0 ? 'yes' : 'no'))}}</li>
        <li><b>{{__('bike.show.cargo_weight')}}:</b> {{$bike->cargo_weight}} kg</li>
        <li><b>{{__('bike.show.cargo_length')}}:</b> {{$bike->cargo_length}} cm</li>
        <li><b>{{__('bike.show.cargo_width')}}:</b> {{$bike->cargo_width}} cm</li>  
      </ul>      
      @if($bike->misc_equipment)           
      <p>
        <b>{{__('bike.show.misc_equipment')}}:</b> {{$bike->misc_equipment}}
      </p>
      @endif
      @if($bike->description)
      <p>
        <b>{{__('bike.show.description')}}:</b><br>
        {{$bike->description}}
      </p>       
      @endif
      @if($bike->rentalPlaces->isNotEmpty())
        @php
          $b_rp_count = $bike->rentalPlaces->count();
          $b_rp_title = trans_choice('bike.show.rental_place_description',$b_rp_count);
        @endphp         
        <b>{{ $b_rp_title }}:</b>
        <ul class="list-unstyled">
        @foreach($bike->rentalPlaces as $rp)            
          <li>
            <b>{{$rp->name}}</b>
            @if($rp->description)
            :&nbsp;
            {{$rp->description}}
            @endif
          </li>
        @endforeach
        </ul>
      </p>
      @endif
      @php
      	$p_type = $bike->pricingType->name;
        $p_string = __('bike.pricing_type.' . $p_type);
        if($p_type == App\PricingType::FIXED) {
          $p_string .= ":";
          $p_values = $bike->pricing_values; //json_decode($bike->pricing_values, true);
        }
      @endphp
      <ul class="list-unstyled">
        <li><b>{{$p_string}}</b></li>
        @if($p_type == App\PricingType::FIXED)
        	@foreach(['hourly', 'daily', 'weekly'] as $k)
          	@if($p_values[$k])
          	<li>{{ $p_values[$k] . "€ / " . __('bike.pricing_rate.' . strtoupper($k))}}</li>
          	@endif
        	@endforeach
        @endif
        @if($bike->pricing_deposit)
          <li><b>{{__('bike.pricing.deposit') . ': '. $bike->pricing_deposit . '€'}}</b></li>
        @endif
      </ul>
    @if($bike->terms_of_use_file)
    <p>
    <b><a href="{{route('bike.download_tos',['bike_id' => $bike->id])}}" target="_blank">{{__('bike.terms_of_use')}}</a></b>
    </p>
    @endif
    </div>
  </div>
  <div class="my-3">{!! __('bike.show.calendar_hint') !!}</div>
  <div>
    @php
    $params = [
    'bike_id' => $bike->id
    ];
    if ($reservation_id) {
    	$params['reservation_id'] = $reservation_id;
    } else {
    	$params['date'] = $date;
    }
    @endphp
    <form method="POST" action="{{route('bike.reserve',$params)}}" id="form" data-authed="{{(Auth::check() ? 'true' : 'false')}}" @if($errors->any()) data-errors="true" @endif>
      @csrf      
      <div class="form-group row">
        <div class="col-12 col-sm-6">
          <b>{{__('bike.show.reserve_from')}}</b><br>
          <div>
            <input name="reserve_from" class="form-control flatpickr-dt" id="reserve_from" @isset($reserve_from) value="{{ $reserve_from->format('Y-m-d H:i')}}" @endisset>
          </div>          
          @component('components.form.error',['name' => 'reserve_from','text_danger' => true])
          @endcomponent               
        </div>
        <div class="col-12 col-sm-6">
          <b>{{__('bike.show.reserve_to')}}</b><br>
          <div>
            <input name="reserve_to" class="form-control flatpickr-dt" id="reserve_to" @isset($reserve_to) value="{{ $reserve_to->format('Y-m-d H:i')}}" @endisset>
          </div>         
          @component('components.form.error',['name' => 'reserve_to','text_danger' => true])
          @endcomponent                         
        </div>        
      </div>      
      <div class="form-group">
        <textarea name="purpose" class="form-control" rows="5" placeholder="{{__('bike.show.purpose_hint')}}" id="purpose">{{$purpose}}</textarea>
        @component('components.form.error',['name' => 'placeholder','text_danger' => true])
        @endcomponent                            
      </div>
      <div id='form-error-msg-div' class="form-group alert alert-danger" hidden>
      	<i class="fas fa-exclamation-circle"></i>
      	<span class="mx-3">@error('invalid_timespan'){{$message}}@enderror</span>
      </div>
      @if($bike->terms_of_use_file)
      <div class="form-group">
      		@component('components.form.label_input_error',[
        		'name' => 'accept_bike_tos',
        		'label' => __('bike.terms_of_use_accept', ['route' => route('bike.download_tos', ['bike_id' => $bike]), 'tos' => __('bike.terms_of_use')]),
        		'type' => 'checkbox'
      		])
      		@endcomponent
      </div>
      @endif
      <div class="form-group">
      	<!-- First button prevents enter from submitting the form! -->
      	<button type="submit" aria-hidden="true" hidden disabled></button>
        <button type="submit" id="btn-reserve" class="btn btn-primary" >{{__( $reservation_id ? 'general.save' : 'general.reserve' )}}</button>
        <button type="submit" id="btn-inquiry" class="btn btn-primary" hidden>{{__('general.inquire')}}</button>
        @if($reservation_id)
	 	    <a href="{{route('user.reservations')}}" class="btn btn-secondary">{{__('general.back')}}</a>
	      @endif
      </div>
    </form>
  </div>
  <div class="mb-5">
    <ul class="list-unstyled" id="calendar-legend">
      @foreach($rental_modes as $mode)      
        <li class="d-flex align-items-center rental-mode" data-mode-id="{{$mode->id}}" data-mode-name="{{$mode->name}}">
          <span class="cal-evt-legend {{$rental_mode_colors[$mode->id]}}"></span>{{__('bike.show.legend.' . $mode->name)}}
        </li>
      @endforeach
      <li class="d-flex align-items-center">
        <span class="cal-evt-legend cal-evt-restricted"></span>{{__('bike.show.legend.restricted')}}
      </li>
      <li class="d-flex align-items-center">
        <span class="cal-evt-legend cal-evt-reserved"></span>{{__('bike.show.legend.reservation')}}
      </li>
      <li class="d-flex align-items-center">
        <span class="cal-evt-legend cal-evt-pending"></span>{{__('bike.show.legend.selection')}}
      </li>
    </ul>
    <div class="calendar-container">
      <div id="calendar" data-bike-id="{{$bike->id}}" data-date="{{$date}}" data-reservation-id="{{($reservation_id ? $reservation_id : 'false')}}"></div>     
      @if(!(\Cookie::get('calendar_usage_ok')))      
      <div class="calendar-overlay">
        <div class="inner">
          <div class="icons">
            <i class="fas fa-fingerprint mr-3"></i>          
            <i class="fas fa-mouse"></i>
          </div>
          <div class="text">{{ __('bike.show.overlay_text') }}</div>
          <div><a class="btn btn-outline-light" href="#" id="calendar-overlay-btn">{{ __('general.ok') }}</a></div>
        </div>
      </div>
      @endif
    </div>
  </div>
@endsection
