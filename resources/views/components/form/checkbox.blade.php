@php
$dot_name = $name;
if(@strpos($name, '[') !== false){
	$dot_name = str_replace(['[',']'], ['.',''], $name);
}
$value = old($dot_name) ?? ($form_data[$dot_name] ?? ''); 
$checked = $value ? 'checked' : '';

$d_class = 'form-check';
$i_class = 'form-check-input';
$l_class = 'form-check-label';
if ($custom ?? false) {
  $d_class = 'custom-control custom-checkbox';
  $i_class = 'custom-control-input';
  $l_class = 'custom-control-label';
}

if ($errors->has($dot_name)) {
  $i_class .= ' is-invalid';
}

@endphp
<div class="{{ $d_class }}">
  @if($hidden_input ?? true)
  <input type="hidden" name="{{$name}}" value="0">
  @endif
  <input type="checkbox" class="{{ $i_class }}" name="{{$name}}" id="{{$name}}" value="1" {{$checked}}>
  <label class="{{ $l_class }}" for="{{$name}}">{!!$label ?? null!!}</label>
  @component('components.form.error', ['name' => $name, 'inline' => $error_inline ?? false])
  @endcomponent
</div>
