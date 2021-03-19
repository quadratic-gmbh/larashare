<form method="POST" action="{{route('embed.update_styling_simple',['id' => $embed])}}" id="form_simple">
  @csrf
  <h4>{{__('embed.edit_styling.colors')}}</h4>
  <div class="form-group row">
    <div class="col-12 col-sm-6">
      @component('components.form.label_input_error',[
        'name' => 'color_body',
        'label' => __('embed.edit_styling.color_body'),
        'type' => 'color',
        'form_data' => $form_data
      ])
      @endcomponent
    </div>
    <div class="col-12 col-sm-6">
      @component('components.form.label_input_error',[
        'name' => 'color_primary',
        'label' => __('embed.edit_styling.color_primary'),
        'type' => 'color',
        'form_data' => $form_data
      ])
      @endcomponent
    </div>
  </div>
  <h4>{{__('embed.edit_styling.font')}}</h4>
  <div class="form-group row">
    <div class="col-12 col-sm-6">
      @component('components.form.label_input_group_error',[
        'name' => 'font_size',
        'label' => __('embed.edit_styling.font_size'),
        'append' => 'px',
        'form_data' => $form_data,
        'type' => 'number'
      ])
      @endcomponent
    </div>
    <div class="col-12 col-sm-6">
      @component('components.form.label_input_error',[
        'name' => 'font_family',
        'label' => __('embed.edit_styling.font_family'),
        'form_data' => $form_data,
        'type' => 'select',
        'select_options' => $allowed_fonts,
      ])
      @endcomponent
    </div>
  </div>
  <div class="form-group">
    <button type="submit" class="btn btn-primary">{{__('general.save')}}</button>
  </div>
</form>
