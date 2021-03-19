<form method="GET">
  <div class="form-group row mb-0">    
    <div class="col-12 col-sm-6 col-md-3 mb-3">
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
    <div class="col-12 col-sm-6 col-md-3 mb-3">
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
    <div class="col-12 col-sm-6 col-md-3 mb-3">
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
    <div class="col-12 col-sm-6 col-md-3 mb-3">
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
  <div class="form-group row mb-03">
    <div class="col-12 col-sm-6 col-md-4 mb-3 ">           
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
    <div class="col-12 col-sm-6 col-md-4 mb-3 ">           
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
    <div class="col-12 col-sm-6 col-md-4 mb-3 ">           
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
  <div class="form-group">
    <button type="submit" class="btn btn-primary">{{__('general.apply')}}</button>
  </div>
</form>