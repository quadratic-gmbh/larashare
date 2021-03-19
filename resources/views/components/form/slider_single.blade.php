<div class="slider-container slider-single px-3" id="{{$name}}-slider">
  <div class="mt-5 slider"></div>
  <div class="d-flex justify-content-between mt-1">
    <span>{{$min . ($unit ?? '')}}</span>
    <span class="text-right">{{$max . ($unit ?? '')}}</span>
  </div>
  @php
  $value = old($name) ?? ($form_data[$name] ?? '');
  @endphp
  <input id="{{$name}}" min="{{$min}}" max="{{$max}}" value="{{$value}}" name="{{$name}}" type="hidden">
</div>
