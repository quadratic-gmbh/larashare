@extends('layouts.app')
@push('scripts')
<script src="/js/rental_period.exception.js"></script>  
@endpush
@section('content')
@component('components.form.focus',[
	'errors' => $errors,
	'mode' => 'line'
 ])
@endcomponent
  @include('bike.form_tabs',['active' => 'review'])
  <h1 class="text-primary">{{__('bike.rental_periods_exception.title')}}</h1>
  <div id="fieldset-template" hidden data-has-places-select="{{($rental_places->count() > 1 ? '1' : '0')}}">
    <div class="exception-row border p-3 mb-3">
      <div class="form-group row">    
        <div class="col-3">        
          <label>{{ __('general.from') }}</label>
          <input type="time" class="js-input-from form-control">
        </div>
        <div class="col-3">        
          <label>{{ __('general.to') }}</label>
          <input type="time" class="js-input-to form-control">
        </div>
        <div class="col-3">
          <label>{{ __('bike.rental_mode.rental_mode') }}</label>        
          <select class="form-control js-input-rental-mode-id">
          @foreach($rental_modes as $item)
            <option value="{{$item['value']}}">{{$item['text']}}</option>
          @endforeach
          </select>
        </div>
        @if($rental_places->count() > 1)
        <div class="col-3">        
          <label>{{ __('bike.rental_place') }}</label>
          <select class="form-control js-input-rental-place-id">
          @foreach($rental_places as $item)
            <option value="{{$item['value']}}">{{$item['text']}}</option>
          @endforeach
          </select>
        </div>
        @else 
        <input type="hidden" class="js-input-rental-place-id" value="{{$rental_places->first()['value']}}">
        @endif
      </div>
    	<div class="form-group row">
  			<div class="col-4">
    			<label>{{ __('bike.rentee_limitation.label_form') }}</label>
    			<textarea class="js-input-rentee-limitation form-control" rows="5" ></textarea>
   			 </div>
  		</div>
      <div class="form-group row">
        <div class="col-4">
          <label>{{ __('bike.rental_duration') }}</label>
          <div class="input-group">
            <input type="number" class="js-input-rental-duration form-control">
            <select class="form-control js-input-rental-duration-in-days">          
            @for($i = 0; $i < 2; $i++)
              <option value="{{ $i }}">{{ __('bike.rental_duration_in_days.' . $i) }}</option>
            @endfor            
            </select>
          </div>   
        </div>
        @php
        /*<div class="col-5">
          <label>&nbsp;</label>
          <div class="mt-2">
            <div class="custom-control custom-checkbox ">        
              <input type="hidden" value="0" class="js-input-no-interrupt">
              <input type="checkbox" class="custom-control-input js-input-no-interrupt" value="1">
              <label class="custom-control-label js-label-no-interrupt">{{ __('bike.no_interrupt_short') }}</label>
            </div>
          </div>
        </div>*/
        @endphp
        <div class="col-3">   
          <label>&nbsp;</label>        
          <a href="#" class="btn btn-danger btn-block fieldset-rem">{{__('general.delete')}}</a>
        </div>
      </div>
    </div>
  </div>
  <form id="form" method="POST">
    <input type="hidden" name="date" value="{{$date}}">
    @csrf
    @foreach($rows as $row)            
      @component('bike.rental_period.exception_fieldset',[
        'data' => $row,
        'index' => $loop->index,
        'rental_places' => $rental_places,
        'rental_modes' => $rental_modes
      ])
      @endcomponent      
    @endforeach
    <div id="js-fieldsets" data-fieldset-counter="{{$rows->count()}}">    
    </div>
    <div class="form-group">
      <a class="btn btn-secondary" id="fieldset-add" href="#"><i class="fas fa-plus"></i></a>
      <button class="btn btn-secondary" type="submit">{{__('general.save')}}</button>
      <button class="btn btn-secondary" type="submit" name="reset" value="1">{{__('general.reset')}}</button>
      <a class="btn btn-secondary" href="{{route('bike.rental_period_review',['bike_id' => $bike, 'date' => $date])}}">{{__('general.back')}}</a>
    </div>
  </form>  
@endsection
