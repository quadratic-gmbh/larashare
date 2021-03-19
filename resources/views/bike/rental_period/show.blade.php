@extends('layouts.app')
@push('scripts')
<script src="/js/moment.js"></script>
<script src="/js/rental_period.show.js"></script>    
@endpush 
@section('content')
@component('components.form.focus',[
	'errors' => $errors
])
@endcomponent
  @include('bike.form_tabs',['active' => 'rental_period'])  
  <h2 class="text-primary">{{__('bike.rental_periods.title')}}</h2>
  <p>{{ __('bike.rental_periods.hint') }}</p>
  <div id="form" @if($rental_periods->isNotEmpty() && !($errors->any())) hidden @endif>
    @include('bike.rental_period.show_form')
  </div>
  @if($rental_periods->isNotEmpty())
  <h3>{{__('bike.rental_periods.list_title')}}</h3>
  <div id="rental-periods" class="row">
    @foreach($rental_periods as $rp)
    @php
      $weekdays = $rp->weekdays->mapWithKeys(function($item) {
        return [$item->id => true]; 
      })->toArray();
      $weekdays_str = implode($rp->weekdays->pluck('id')->toArray(),',');
      $rentee_limitation = $rp->renteeLimitations;
      $rentee_limitation_emails = [];
      foreach($rentee_limitation as $re){
      	$rentee_limitation_emails[] = $re->email;
      }
    @endphp
    <div class="col-4">
      <div class="card mb-3 rental-period" 
        data-id="{{$rp->id}}"
        data-date-from="{{$rp->date_from->format('Y-m-d')}}" 
        data-date-to="{{$rp->date_to->format('Y-m-d')}}" 
        data-time-from="{{$rp->time_from->format('H:i')}}" 
        data-time-to="{{$rp->time_to->format('H:i')}}" 
        data-weekdays="{{$weekdays_str}}" 
        data-rental-place-id="{{$rp->rental_place_id}}" 
        data-rental-duration="{{$rp->rental_duration}}" 
        data-rental-duration-in-days="{{ (int) $rp->rental_duration_in_days}}"  
        data-no-interrupt="{{ (int) $rp->no_interrupt}}" 
        data-rental-mode-id="{{$rp->rental_mode_id}}"
        data-rentee-limitation={{implode(',', $rentee_limitation_emails)}}> 
        <div class="card-body">
          <div class="mb-2">
            <b>{{__('bike.rental_periods.dates')}}</b><br>
          {{$rp->date_from->format('d.m.Y') . ' - ' . $rp->date_to->format('d.m.Y')}}
          </div>
          <div class="mb-2">
            <b>{{trans_choice('general.weekday.weekday',0)}}</b><br>         
          @for($i = 1; $i <= 7; $i++)
            @php
              $class = (($weekdays[$i] ?? false) ? 'bg-primary text-white' : '');
            @endphp
            <span class="p-1 border border-primary {{$class}}">{{__('general.weekday_short.' . $i)}}</span>
          @endfor
          </div>        
          <div class="mb-2">
            <b>{{__('bike.rental_periods.times')}}</b><br>
            {{$rp->time_from->format('H:i') . ' - ' . $rp->time_to->format('H:i')}}
          </div>
          <div class="mb-2">
            <b>{{__('bike.rental_place')}}</b><br>
            {{$rp->rentalPlace->name}}
          </div>          
          <div class="mb-2">
            <b>{{__('bike.rental_mode.rental_mode')}}</b><br>
            {{__('bike.rental_mode.' . $rp->rentalMode->name)}}
          </div>
          <div class="mb-2">
            <b>{{__('bike.rental_duration')}}</b><br>
            @php
              $duration = $rp->rental_duration;
              if ($rp->rental_duration_in_days) {
                $duration /= 24;
                $duration .= ' ' . __('bike.rental_duration_in_days.1'); 
              } else {
                $duration .= ' ' . __('bike.rental_duration_in_days.0');
              }
            @endphp
            {{ $duration }}
          </div>     
          @php   
          /*@if( $rp->no_interrupt)                    
          <div class="mb-2">            
            <b>{{__('bike.no_interrupt_short') }}</b>
          </div>                   
          @endif*/
          @endphp
          @if($rentee_limitation_emails)
          <div class="mb-2">
            <b>{{__('bike.rentee_limitation.label_show')}}</b><br>
            {!! implode('<br>', $rentee_limitation_emails) !!}
          </div> 
          @endif
          <div class="mb-2">
            <a href="#" class="link-edit unhides-form btn btn-primary"><i class="fas fa-edit"></i> {{__('general.edit')}}</a>
            <form class="d-inline-block" method="POST" action="{{route('bike.rental_period_delete',['bike_id' => $bike, 'rp_id' => $rp])}}">
              @csrf
              @method('DELETE')
              <button type="submit" class="btn btn-danger"><i class="fas fa-trash"></i> {{__('general.delete')}}</button>
            </form>
          </div>
        </div>      
      </div>
    </div>
    @endforeach
  </div>
  <div class="my-3">
    <a class="unhides-form d-flex align-items-center btn btn-link" href="#" id="link-add">
      <span class="text-success pr-3"><i class="fas fa-plus-circle fa-3x"></i></span>
      <b class="text-body">{{__('bike.rental_periods.link_add')}}</b>
    </a>
  </div>
  <div class="my-3">
    <form method="POST" action="{{route('bike.rental_period_delete_all',['bike_id' => $bike])}}"> 
      @csrf
      @method("DELETE")
      <button type="submit" class="d-flex align-items-center btn btn-link">
        <span class="text-danger pr-3"><i class="fas fa-minus-circle fa-3x"></i></span>
        <b  class="text-body">{{__('bike.rental_periods.link_rem')}}</b>
      </button>
    </form>
  </div>
  <a href="{{route('bike.rental_period_review',['bike_id' => $bike])}}" class="btn btn-primary">{{__('bike.rental_periods.link_proceed')}}</a>
  @endif
@endsection
