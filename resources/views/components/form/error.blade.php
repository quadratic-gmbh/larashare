@php
if(@strpos($name, '[') !== false){
	$name = str_replace(['[',']'], ['.',''], $name);
}
@endphp
@if($errors->has($name))
  <div class="{{($text_danger ?? false) ? 'text-danger' : 'invalid-feedback'}} {{($inline ?? false) ? 'd-inline' : ''}}" role="alert">
  <strong>{{ $errors->first($name) }}</strong>
  </div>
@endif
