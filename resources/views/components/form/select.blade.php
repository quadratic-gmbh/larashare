@php
$dot_name = $name;
if(@strpos($name, '[') !== false){
	$dot_name = str_replace(['[',']'], ['.',''], $name);
}
$selected = old($dot_name) ?? ($form_data[$dot_name] ?? null); 

$class = (($custom ?? false) ? 'custom-select' : 'form-control');
$class .= ' ' . ($classes ?? '');
if ($errors->has($dot_name)) {
  $class .= ' is-invalid';
}
@endphp
@if($label ?? false)
  @component('components.form.label',[
    'name' => $name,
    'text' => $label
  ])
  @endcomponent
@endif
<select class="{!!$class!!}" id="{{$name}}" name="{{$name}}">
@php
$already_selected = false;
@endphp
@if($empty_option ?? false)
  <option value="" @if($selected === null)@php $already_selected = true; @endphp selected @endif>{{$empty_option_text ?? ''}}</option>
@endif
@isset($select_options)
<?php foreach ($select_options as $option) {?>
  <option value="{{$option['value']}}" 
  @if(($selected == $option['value']) && !$already_selected) selected @endif
  @isset($option['style']) style="{!! $option['style'] !!}" @endisset
  >
  {{$option['text']}}
  </option>
<?php }?>
@endisset
</select>   
