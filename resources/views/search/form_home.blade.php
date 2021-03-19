<form method="GET" action="{{route('search.index')}}">
  <div class="form-group row mb-0">
    <div class="col-12 col-sm-6 col-md-3 mb-3">    
      @component('components.form.label_input_group_error',[
        'name' => 'location',
        'prepend' => '<i class="fas fa-map-marker"></i>',
        'show_error' => false,
        'form_data' => $form_data,        
      ])
      @endcomponent     
      <input type="hidden" name="location_old">
      <input type="hidden" name="location_lon">
      <input type="hidden" name="location_lat">
    </div>
    <div class="col-12 col-sm-6 col-md-3 mb-3">
      @component('components.form.label_input_group_error',[
        'name' => 'date',
        'prepend' => '<i class="fas fa-calendar"></i>',
        'type' => 'date',
        'show_error' => false,
        'form_data' => $form_data,        
      ])
      @endcomponent    
    </div>
    <div class="col-12 col-sm-6 col-md-3 mb-3">
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
        'empty_option_text' => __('search.form.flex.flex'),            
        'form_data' => $form_data,        
      ])
      @endcomponent  
    </div>  
    <div class="col-12 col-sm-6 col-md-3 mb-3">
      <div class="input-group">
        <div class="input-group-prepend">
          <span class="input-group-text"><i class="fas fa-stopwatch"></i></span>
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
    </div>     
  </div>
  <div class="form-group">    
    <button type="submit" class="btn btn-primary px-5">{{__('general.search')}}</button>    
  </div>  
</form>