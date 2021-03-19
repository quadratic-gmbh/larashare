@if($label !== false)
	@if(!isset($type) || $type !== 'checkbox')
  	@component('components.form.label',[
    	'name' => $name,
    	'text' => $label
  	])
  	@endcomponent
	@endif
@endif
@php
  $comp_name = 'components.form.';
  $comp_cfg = [
    'name' => $name,
    'classes' => $classes ?? null,
    'form_data' => $form_data ?? [],
    'attributes' => $attributes ?? [],
    'properties' => $properties ?? [],
  ];
@endphp
@if(isset($type) && $type === "select")
  @php
    $comp_name .= 'select';
    $comp_cfg['select_options'] = $select_options ?? [];
    $comp_cfg['empty_option'] = $empty_option ?? false;
    $comp_cfg['empty_option_text'] = $empty_option_text ?? '';
    $comp_cfg['escape_option'] = $escape_option ?? false;
  @endphp     
@elseif(isset($type) && $type === "checkbox")
  @php
    $comp_name .= 'checkbox';
    $comp_cfg['label'] = $label;
  @endphp   
@elseif(isset($type) && $type === "textarea")
  @php
    $comp_name .= 'textarea';
    $comp_cfg['rows'] = $rows ?? null;
  @endphp  
@else
  @php
    $comp_name .= 'input';
    $comp_cfg['type'] = $type ?? 'text';
  @endphp  
@endif
@component($comp_name, $comp_cfg)
@endcomponent
@if($show_error ?? true)
  @component('components.form.error', ['name' => $name])
  @endcomponent
@endif
