<div class="slider-container slider-dual px-3" id="{{$name}}_slider" data-min="{{$min}}" data-max="{{$max}}">
  <div class="@if($margin ?? true) mt-5 @endif slider"></div>
  <div class="d-flex justify-content-between mt-1">
    <span>{{$min}}</span>
    <span class="text-right">{{$max}}</span>
  </div>
  @if($inputs ?? true)
    @php
    $value_from = old($name .'_from') ?? ($form_data[$name. '_from'] ?? '');
    $value_to = old($name . '_to') ?? ($form_data[$name. '_to'] ?? '');
    @endphp
    <input id="{{$name}}_from" min="{{$min}}" max="{{$max}}" value="{{$value_from}}" name="{{$name}}_from" type="hidden">
    <input id="{{$name}}_to" min="{{$min}}" max="{{$max}}" value="{{$value_to}}" name="{{$name}}_to" type="hidden">
  @endif
</div>
