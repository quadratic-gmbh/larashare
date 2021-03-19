@php
  $postfix = '_' . $index;
  $postfix_name = '[' . $index . ']';
  $original_index = $data['index'] ?? false;
@endphp
<div class="exception-row border p-3 mb-3">
  <div class="form-group row">
    <div class="col-3"> 
      <label>{{__('general.from')}}</label>
      <input
        id="{{'time_from' . $postfix}}"
      	type="time"
        name="{{'time_from' .$postfix_name}}"      
        value="{{$data['time_from']}}" 
        class="form-control flatpickr-time @error('time_from.' . $original_index) is-invalid @enderror"
      >
      @error('time_from.' . $original_index)
      <span class="invalid-feedback">
        <strong>{{$message}}</strong>
      </span>
      @enderror
    </div>
    <div class="col-3">
    <label>{{__('general.to')}}</label>    
      <input
        id="{{'time_to' . $postfix}}"
      	type="time"
        name="{{'time_to' .$postfix_name}}"      
        value="{{$data['time_to']}}" 
        class="form-control flatpickr-time @error('time_to.' . $original_index) is-invalid @enderror"
      >
      @error('time_to.' . $original_index)
      <span class="invalid-feedback">
        <strong>{{$message}}</strong>
      </span>
      @enderror
    </div>
    <div class="col-3">    
    <label>{{__('bike.rental_mode.rental_mode')}}</label>
      <select
        id="{{'rental_mode_id' . $postfix}}"      
        name="{{'rental_mode_id' . $postfix_name}}"
        class="form-control @error('rental_mode_id.' . $original_index) is-invalid @enderror"
      >
      @php
        $selected = $data['rental_mode_id'];        
      @endphp
      @foreach($rental_modes as $item)
        <option value="{{$item['value']}}" @if($selected == $item['value']) selected @endif>{{$item['text']}}</option>
      @endforeach
      </select>
      @error('rental_mode_id.' . $original_index)
      <span class="invalid-feedback">
        <strong>{{$message}}</strong>
      </span>
      @enderror
    </div>
    @if($rental_places->count() > 1)
      <div class="col-3">      
        <label>{{__('bike.rental_place')}}</label>
        <select
          id="{{'rental_place_id' . $postfix}}"      
          name="{{'rental_place_id' . $postfix_name}}"
          class="form-control @error('rental_place_id.' . $original_index) is-invalid @enderror"
        >
        @php
          $selected = $data['rental_place_id'];        
        @endphp
        @foreach($rental_places as $item)
          <option value="{{$item['value']}}" @if($selected == $item['value']) selected @endif>{{$item['text']}}</option>
        @endforeach
        </select>
        @error('rental_place_id.' . $original_index)
        <span class="invalid-feedback">
          <strong>{{$message}}</strong>
        </span>
        @enderror
      </div>
    @else  
      <input type="hidden" name="{{'rental_place_id'.$postfix_name}}" value="{{$data['rental_place_id']}}">
    @endif
  </div>
  <div class="form-group row">
  	<div class="col-4">
    <label>{{ __('bike.rentee_limitation.label_form') }}</label>
    <textarea class="js-input-rentee-limitation form-control @error('rentee_limitation.' . $original_index) is-invalid @enderror" rows="5" name="{{'rentee_limitation' .$postfix_name}}">{{$data['rentee_limitation']}}</textarea>
    @error('rentee_limitation.' . $original_index)
    <span class="text-danger">
    	<strong>{{$message}}</strong>
    </span>
    @enderror
    </div>
  </div>
  <div class="form-group row">
    <div class="col-4">
      <label>{{__('bike.rental_duration')}}</label>
      <div class="input-group">
        <input
        id="{{'rental_duration' . $postfix}}"
        type="number"
        name="{{'rental_duration' .$postfix_name}}"      
        value="{{$data['rental_duration']}}" 
        class="form-control"
        >
        <select
          id="{{'rental_duration_in_days' . $postfix}}"      
          name="{{'rental_duration_in_days' .$postfix_name}}"      
          class="form-control"
        >
        @php
          $selected = $data['rental_duration_in_days'];        
        @endphp
        @for($i = 0; $i < 2; $i++)
          <option value="{{ $i }}" @if($selected == $i) selected @endif>{{ __('bike.rental_duration_in_days.' . $i) }}</option>
        @endfor        
        </select>
      </div>    
      @error('rental_duration.' . $original_index)
      <div class="text-danger">
        <strong>{{$message}}</strong>
      </div>
      @enderror    
      @error('rental_duration_in_days.' . $original_index)
      <div class="text-danger">
        <strong>{{$message}}</strong>
      </div>
      @enderror
    </div>
    @php
    /*<div class="col-5">
      <label>&nbsp;</label>
      <div class="mt-2">
        <div class="custom-control custom-checkbox ">        
          <input type="hidden" value="0" name="{{'no_interrupt' .$postfix_name}}">
          <input type="checkbox" 
          @if($data['no_interrupt'] == 1) checked @endif 
          class="custom-control-input" 
          id="{{'no_interrupt' . $postfix}}" 
          name="{{'no_interrupt' .$postfix_name}}"       
          value="1">
          <label class="custom-control-label" for="{{'no_interrupt' . $postfix}}">{{ __('bike.no_interrupt_short') }}</label>
        </div>
      </div>
      @error('no_interrupt.' . $original_index)
      <div class="text-danger">
        <strong>{{$message}}</strong>
      </div>
      @enderror
    </div>*/
    @endphp
    <div class="col-3">   
      <label>&nbsp;</label> 
      <a href="#" class="btn btn-danger btn-block fieldset-rem">{{__('general.delete')}}</a>   
    </div>  
  </div>
</div>