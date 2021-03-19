@php
$dot_name = $name;
if(@strpos($name, '[') !== false){
	$dot_name = str_replace(['[',']'], ['.',''], $name);
}
$value = old($dot_name) ?? ($form_data[$dot_name] ?? ''); 
$class = 'form-control ' . ($classes ?? '');
if ($errors->has($dot_name)) {
  $class .= ' is-invalid';
}
$_rows = $rows ?? 5;
@endphp
<textarea 
class="{!!$class!!}" 
rows="{{$_rows}}" 
id="{{$name}}" 
name="{{$name}}" 
@isset($attributes)
  @foreach($attributes as $attr=>$val)
    {{$attr}}={{$val}}
  @endforeach 
@endisset 
@isset($properties)
  @foreach($properties as $prop)
    {{$prop}}
  @endforeach 
@endisset 
>{{$value}}</textarea>