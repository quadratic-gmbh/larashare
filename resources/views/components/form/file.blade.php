@php 
$dot_name = $name;
if(@strpos($name, '[') !== false){
	$dot_name = str_replace(['[',']'], ['.',''], $name);
}
@endphp
<div class="custom-file">
  <label class="custom-file-label" for="{{$name}}">{{$text ?? __('general.browse_files')}}</label>
  <input class="custom-file-input" 
  @isset($attributes)
    @foreach($attributes as $attr=>$val)
      {{$attr}}={{$val}}
    @endforeach 
  @endisset
  type="file" 
  id="{{$name}}" 
  name="{{$name}}" 
  >
</div>
