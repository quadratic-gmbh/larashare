<div>
  <p>{{__('embed.edit_styling.advanced_hint')}}</p>
  <h5>{{__('embed.edit_styling.advanced_links')}}</h5>
  <a href="https://getbootstrap.com/docs/4.3/getting-started/introduction/" class="btn btn-primary" target="_blank">
    <i class="fas fa-external-link-alt"></i>
    {{__('embed.edit_styling.advanced_link_bootstrap')}}
  </a>
  <a href="https://sass-lang.com/documentation" class="btn btn-primary" target="_blank">
    <i class="fas fa-external-link-alt"></i>
    {{__('embed.edit_styling.advanced_link_sass')}}
  </a>  
</div>
<div class="mt-5">
  <h5>{{__('embed.edit_styling.advanced_styling')}}</h5>
  <form method="POST" action="{{route('embed.update_styling_advanced',['id' => $embed])}}" id="form_advanced">  
    @csrf
    <div class="form-group">
      @php
        $use_example = ($advanced_variables === null);
      @endphp
      @if($use_example) 
      <p>{{ __('embed.edit_styling.advanced_example_used') }}</p>
      @endif
      @component('components.form.label_input_error',[
        'name' => 'variables',
        'type' => 'textarea',
        'form_data' => ['variables' => ($advanced_variables ?? $advanced_example)],
        'label' => __('embed.edit_styling.advanced_variables'),
        'rows' => '10',
        'classes' => 'code-input',
        'attributes' => ['placeholder' => __('embed.edit_styling.advanced_empty')]
      ])
      @endcomponent  
    </div>
    <div class="form-group">  
      @component('components.form.label_input_error',[
        'name' => 'text',
        'type' => 'textarea',
        'form_data' => ['text' => $advanced_text ?? null],
        'label' => __('embed.edit_styling.advanced_text'),
        'rows' => '10',
        'classes' => 'code-input',        
        'attributes' => ['placeholder' => __('embed.edit_styling.advanced_empty')]
      ])
      @endcomponent
    </div>
    <div class="form-group">
      <button type="submit" class="btn btn-primary">{{__('general.save')}}</button>
    </div>
  </form>
  
</div>

