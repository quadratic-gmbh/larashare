@extends('layouts.app')
@push('scripts')
<script type="text/javascript" src="/js/embed.edit_styling.js"></script>
@endpush 
@section('content')
@component('components.form.focus',[
	'errors' => $errors
])
@endcomponent
  @if(session('update_success'))
  <div class="alert alert-success" role="alert">
    <i class="fas fa-check mr-3"></i>{{__('embed.edit_styling.saved_success')}}
  </div>
  @endif
  @include('embed.form_tabs',['active' => 'edit_styling'])   
  <h1 class="text-primary">{{__('embed.edit_styling.title')}}</h1>
  @php
    $style_tabs = [
      'simple',
      'advanced'
    ];  
    $active = (session('show_advanced') ? 'advanced' : 'simple');
  @endphp
  <ul class="nav nav-tabs" id="styling-tabs" role="tablist">
   @foreach($style_tabs as $st)
    <li class="nav-item">
      <a class="nav-link @if($st == $active) active @endif" id="simple-tab" data-toggle="tab" href="#{{$st}}" role="tab" aria-controls="simple" aria-selected="true">{{__('embed.edit_styling.tab_' . $st)}}</a>
    </li>    
    @endforeach
  </ul>
  <div class="tab-content">
    @foreach($style_tabs as $st)
    <div class="tab-pane py-3 @if($st == $active) active @endif" id="{{$st}}" role="tabpanel" aria-labelledby="{{$st}}-tab">
      @include('embed.edit_styling_' . $st)
    </div>
    @endforeach    
  </div>
@endsection
