@push('scripts')
<script src="/nouislider/nouislider.min.js" type="text/javascript"></script>
<script src="/js/search.form.js" type="text/javascript"></script>
@endpush
@push('styles')
<link rel="stylesheet" href="/nouislider/nouislider.min.css" />
@endpush
@component('components.form.focus',[
	'errors' => $errors
])
@endcomponent
<form method="GET" id="search" @isset($form_action) action="{{ $form_action }}" @endisset>
  <div class="form-group row mb-0">
    <div class="col-12 col-sm-6  col-lg-4 col-xl-3 mb-3">    
      @component('components.form.label_input_group_error',[
        'name' => 'location',
        'prepend' => '<i class="fas fa-map-marker"></i>',
        'show_error' => false,
        'form_data' => $form_data,        
      ])
      @endcomponent     
      @component('components.form.error',['name' => 'location', 'text_danger' => true, 'errors' => $errors])
      @endcomponent 
      @if($invalid_location ?? false)
        <div class="alert alert-warning">
          {{__('search.index.invalid_location')}}
        </div>
      @endif        
      <div hidden id="location_placeholder" data-value="{{ __('search.current_location') }}"></div>
      @component('components.form.input',['name' => 'location_old', 'type' => 'hidden', 'form_data' => $form_data])
      @endcomponent
      @component('components.form.input',['name' => 'location_lon', 'type' => 'hidden', 'form_data' => $form_data])
      @endcomponent
      @component('components.form.input',['name' => 'location_lat', 'type' => 'hidden', 'form_data' => $form_data])
      @endcomponent
    </div>
    <div class="col-12 col-sm-6 col-lg-4 col-xl-3 mb-3">
      @component('components.form.label_input_group_error',[
        'name' => 'date',
        'prepend' => '<i class="fas fa-calendar"></i>',
        'type' => 'date',
        'show_error' => false,
        'form_data' => $form_data,        
      ])
      @endcomponent
      @component('components.form.error',['name' => 'date', 'text_danger' => true, 'errors' => $errors])
      @endcomponent    
    </div>
  </div>
  <div class="form-group row mb-0">
    <div class="col-12 col-sm-6 col-lg-4 col-xl-3 mb-3">
    	<label for="duration" class="d-none d-sm-block">&nbsp;</label>
      <div class="input-group">
        <div class="input-group-prepend">
          <span class="input-group-text"><i class="far fa-clock"></i></span>
        </div>
        @component('components.form.input',[
          'name' => 'duration',
          'type' => 'number',
          'show_error' => false,
          'form_data' => $form_data,
        ])
        @endcomponent  
        @php
          $selected = old('duration_type') ?? ($form_data['duration_type'] ?? null);
          $options = [
            'h' => 'hour',
            'd' => 'day',
          ];
        @endphp
        <select class="custom-select" id="duration_type" name="duration_type">
          @foreach($options as $v=>$t)
          <option value="{{$v}}" @if($v === $selected) selected @endif>{{trans_choice('general.' . $t,0)}}</option>
          @endforeach                    
        </select>                
      </div>      
      @component('components.form.error',['name' => 'duration','text_danger' => true, 'errors' => $errors])
      @endcomponent
      @component('components.form.error',['name' => 'duration_type','text_danger' => true, 'errors' => $errors])
      @endcomponent
    </div>
    <div class="col-12 col-sm-6 col-lg-4 col-xl-3 mb-3">
      @php
        $flex_options = [];
        for($i = 1; $i <=3; $i++) {
          $flex_options[] = ['value' => $i, 'text' => __('search.form.flex.' . $i)];
        }
      @endphp
      @component('components.form.label_input_error',[
        'name' => 'flex',
        'label' => false,
        'type' => 'select',
        'show_error' => false,
        'select_options' => $flex_options,
        'empty_option' => true,
        'empty_option_text' => __('general.no'),
        'form_data' => $form_data,
        'label' => '<b>'.__('search.form.flex.flex').'</b>',  
      ])
      @endcomponent
      @component('components.form.error',['name' => 'flex','errors' => $errors])
      @endcomponent    
    </div> 
  </div>
  <div class="collapse" id="search-details">
    <div class="form-group row">
      <div class="col-12 col-md-6 col-lg-3 mb-md-0 mb-3">
        <label><b>{{__('search.form.time')}}</b></label>
        <div>    
        @component('components.form.slider_dual',[
          'name' => 'time',          
          'min' => '00:00',
          'max' => '23:59',    
          'type' => 'time',    
          'margin' => false,
          'inputs' => false,          
          'form_data' => $form_data          
        ])                
        @endcomponent
        </div>
        <div class="row">
          <div class="col">            
            @component('components.form.input',[
              'name' => 'time_from',
              'form_data' => $form_data,
            ])
            @endcomponent
            @component('components.form.error',['name' => 'time_from','errors' => $errors])
          @endcomponent
          </div>
          <div class="col">            
            @component('components.form.input',[
              'name' => 'time_to',
              'form_data' => $form_data,
            ])
            @endcomponent
            @component('components.form.error',['name' => 'time_to','errors' => $errors,'text_danger' => true])
            @endcomponent
          </div>
        </div>     
      </div>
      <div class="col-12 col-md-6 col-lg-9">
        <div class="row">
          <div class="col-12 col-sm-6 col-md-12 col-lg-auto mb-3">
            @php
              $wheel_options = [];
              for($i = 2; $i<= 4; $i++) {
                $wheel_options[] = ['value' => $i, 'text' => __('bike.wheels.' . $i)];
              }
            @endphp
            @component('components.form.select',[
              'name' => 'wheels',
              'select_options' => $wheel_options,         
              'form_data' => $form_data,
              'empty_option' => true,
              'empty_option_text' => __('general.whatever'),
              'label' => '<b>'.__('search.form.wheels').'</b>'
            ])
            @endcomponent
            @component('components.form.error',['name' => 'wheels','errors' => $errors])
            @endcomponent
          </div>
          <div class="col-12 col-sm-6 col-md-12 col-lg-auto mb-3">
            @php
              $children_options = [];
              for($i = 0; $i<= 4; $i++) {
                $children_options[] = ['value' => $i, 'text' => trans_choice('bike.children',$i,['i' => $i])];
              }
            @endphp
            @component('components.form.select',[
              'name' => 'children',
              'select_options' => $children_options,         
              'form_data' => $form_data,
              'empty_option' => true,
              'empty_option_text' => __('general.whatever'),
              'label' => '<b>'.__('search.form.children').'</b>'
            ])
            @endcomponent
            @component('components.form.error',['name' => 'children','errors' => $errors])
            @endcomponent
          </div>
          <div class="col-12 col-sm-6 col-md-12 col-lg-auto mb-3">
            @php
              $electric_options = [
                ['value' => 2, 'text' => __('bike.electric.0')],
                ['value' => 1, 'text' => __('bike.electric.1')]
              ];              
            @endphp
            @component('components.form.select',[
              'name' => 'electric',
              'select_options' => $electric_options,        
              'empty_option' => true,
              'empty_option_text' => __('general.whatever'), 
              'form_data' => $form_data,
              'label' => '<b>'.__('search.form.electric').'</b>'
            ])
            @endcomponent
            @component('components.form.error',['name' => 'electric','errors' => $errors])
            @endcomponent
          </div>
          <div class="col-12 col-sm-6 col-md-12 col-lg-auto mb-3">
            @php
              $box_type_options = App\BoxType::getSelectOptions();                         
            @endphp
            @component('components.form.select',[
              'name' => 'box_type_id',
              'select_options' => $box_type_options,
              'empty_option' => true,
              'empty_option_text' => __('general.whatever'),
              'form_data' => $form_data,
              'label' => '<b>'.__('search.form.box_type_id').'</b>'
            ])
            @endcomponent
            @component('components.form.error',['name' => 'box_type_id','errors' => $errors])
            @endcomponent
          </div>
        </div>
      </div>
    </div>
    <div class="form-group row">
      <div class="col-12 col-sm-4">           
        <label><b>{{__('search.form.cargo_weight')}}</b></label>
        @component('components.form.slider_single',[
          'name' => 'cargo_weight',
          'min' => 0,
          'max' => 300,
          'unit' => ' kg',
          'form_data' => $form_data          
        ])        
        @endcomponent       
        @component('components.form.error',['name' => 'cargo_weight','errors' => $errors,'text_danger' => true])
        @endcomponent
      </div>
      <div class="col-12 col-sm-4">           
        <label><b>{{__('search.form.cargo_length')}}</b></label>
        @component('components.form.slider_single',[
          'name' => 'cargo_length',
          'min' => 0,
          'max' => 200,
          'unit' => ' cm',
          'form_data' => $form_data          
        ])        
        @endcomponent       
        @component('components.form.error',['name' => 'cargo_length','errors' => $errors,'text_danger' => true])
        @endcomponent
      </div>
      <div class="col-12 col-sm-4">           
        <label><b>{{__('search.form.cargo_width')}}</b></label>
        @component('components.form.slider_single',[
          'name' => 'cargo_width',
          'min' => 0,
          'max' => 80,
          'unit' => ' cm',
          'form_data' => $form_data          
        ])        
        @endcomponent       
        @component('components.form.error',['name' => 'cargo_width','errors' => $errors,'text_danger' => true])
        @endcomponent
      </div>
    </div>
  </div>
  <div class="form-group">
    <button type="submit" class="btn btn-primary px-5">{{__('general.search')}}</button>
    <a href="{{route('home')}}" >{{__('search.form.reset')}}</a>
  </div>
  <h6>
  <a class="" id="search-details-toggle" data-toggle="collapse" href="#search-details" role="button" aria-expanded="false" aria-controls="search-details">
    {{__('search.form.details')}}&nbsp<i class="fas fa-caret-down"></i>
  </a>
  </h6>
</form>
