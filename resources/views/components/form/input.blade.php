@php 
$dot_name = $name;
if(@strpos($name, '[') !== false){
	$dot_name = str_replace(['[',']'], ['.',''], $name);
}
$value = old($dot_name) ?? ($form_data[$dot_name] ?? ''); 
@endphp
<input class="form-control {{($classes ?? '')}} @error($dot_name) is-invalid @enderror" 
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
type="{{ $type ?? 'text'}}" 
id="{{$name}}" 
name="{{$name}}" 
value="{{$value}}"
>