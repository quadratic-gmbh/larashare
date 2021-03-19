<form method="POST">
  @csrf
  @component('components.form.input',[
    'name' => 'rp_id',
    'type' => 'hidden',    
    'form_data' => $form_data
  ])
  @endcomponent
  <div class="form-group row">
    @if($rental_place_options->count() > 1)
    <div class="col">
      @component('components.form.label_input_error',[
        'name' => 'rental_place_id',
        'label' => __('bike.rental_periods.rental_place_select'),
        'type' => 'select',
        'select_options' => $rental_place_options,
        'form_data' => $form_data
      ])          
      @endcomponent
    </div>        
    @else
    <input type="hidden" name="rental_place_id" id="rental_place_id" value="{{$rental_place_options->first()['value']}}">
    @endif
    <div class="col">
      <div class="row">
        <div class="col">
        @component('components.form.label_input_error',[
          'name' => 'time_from',
          'type' => 'time',
          'label' => __('general.from'),
          'form_data' => $form_data            
        ])
        @endcomponent
        </div>
        <div class="col">
        @component('components.form.label_input_error',[
          'name' => 'time_to',
          'type' => 'time',
          'label' => __('general.to'),
          'form_data' => $form_data            
        ])
        @endcomponent
        </div>
      </div>
    </div>     
    <div class="col">
      @component('components.form.label_input_error',[
        'name' => 'rental_mode_id',
        'label' => __('bike.rental_mode.rental_mode'),
        'type' => 'select',
        'select_options' => $rental_mode_options,
        'form_data' => $form_data
      ])          
      @endcomponent
    </div>
  </div>
  <div class="form-group">
    <div>{{__('bike.rental_duration')}}</div>
    <div class="row">    
  		<div class="col-3">
    		@component('components.form.label_input_error',[
    		  'form_data' => $form_data,
      		'name' => 'rental_duration',
      		'label' => false
    		])
    		@endcomponent
  		</div>
  		<div class="col-3">
    		@component('components.form.label_input_error',[
    		  'form_data' => $form_data,
      		'name' => 'rental_duration_in_days',
      		'label' => false,
      		'type' => 'select',
      		'select_options' => $rental_duration_options
    		])
    		@endcomponent
  		</div>    
    </div>
	</div>
	@php
  /*<div class="form-group">
     @component('components.form.label_input_error',[
        'form_data' => $form_data,
        'name' => 'no_interrupt',
        'label' => __('bike.no_interrupt'),
        'type' => 'checkbox'
      ])
      @endcomponent
  </div>*/
  @endphp
  <div class="form-group ">       
    <h6>{{__('bike.rental_periods.weekdays')}}</h6>
    @for($i = 1; $i <= 7; $i++)
    @php 
      $value = old('weekday.' . $i) ?? ($form_data['weekday'][$i] ?? 0);
      $checked = ($value == 1 ? 'checked' : '');  
    @endphp
      <div class="d-inline-block text-center px-1">
        <label for="weekday_{{$i}}" class="mb-0">{{__('general.weekday_short.' . $i)}}</label>
        <div class="form-check">
          <input name="weekday[{{$i}}]" value="0" type="hidden">
          <input id="weekday_{{$i}}" {{$checked}} name="weekday[{{$i}}]" class="form-check-input position-static" type="checkbox" value="1" >
        </div>
      </div>
    @endfor
    @component('components.form.error',[
      'name' => 'weekday',
      'text_danger' => true
    ])
    @endcomponent
  </div>
  <h6>{{__('bike.rental_periods.dates')}}</h6> 
  <div class="form-group row">    
    <div class="col">
      @component('components.form.label_input_error',[
        'name' => 'date_from',
        'type' => 'date',
        'label' => __('general.from'),
        'form_data' => $form_data         
      ])
      @endcomponent
    </div>
    <div class="col">
      @component('components.form.label_input_error',[
        'name' => 'date_to',
        'type' => 'date',
        'label' => __('general.to'),
        'form_data' => $form_data            
      ])
      @endcomponent
    </div>
  </div>
  @error('rental_period_overlap')
  <div class="form-group text-danger">
  {{$message}}
  </div>
  @enderror
  <div class="form-group">
   @component('components.form.label_input_error',[
      'form_data' => $form_data,
      'name' => 'rentee_limitation',
      'label' => __('bike.rentee_limitation.label_form'),
      'type' => 'textarea'
    ])
    @endcomponent
  </div>
  <div class="form-group">
    <button type="submit" class="btn btn-primary">{{__('general.save')}}</button>
  </div>
</form>