@if($label ?? false)
  @component('components.form.label',[
    'name' => $name,
    'text' => $label
  ])
  @endcomponent
@endif
<div class="input-group">
@isset($prepend)
  <div class="input-group-prepend">
    <span class="input-group-text">{!!$prepend!!}</span>
  </div>
@endisset
@component('components.form.input',[
  'name' => $name,
  'type' => $type ?? 'text',
  'form_data' => $form_data ?? [],  
])
@endcomponent
@isset($append)
  <div class="input-group-append">
    <span class="input-group-text">{!!$append!!}</span>
  </div>
@endisset
</div>
@if($show_error ?? true)
  @component('components.form.error',['name' => $name, 'text_danger' => true])
  @endcomponent
@endif
