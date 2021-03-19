@extends('layouts.app')
@push('scripts')
<script type="text/javascript" src="/js/embed.show.js"></script>
@endpush
@section('content')
  @include('embed.form_tabs',['active' => 'show'])
  <h1 class="text-primary">{{__('embed.show.title')}}</h1>
  <p>{{ __('embed.show.hint') }}</p>    
	<div class="mb-3">
    <h5>{{ __('embed.show.css_title') }}</h5>
    <p>{{ __('embed.show.css_hint') }}</p>
    <?php
     $src_css = secure_url('client/client.css');
     $css_code = "<link rel=\"stylesheet\" href=\"{$src_css}\" >";
    ?>
    <div class="input-group copy-container">
      <input readonly class="form-control copy-text" value="{{ $css_code }}">
      <div class="input-group-append">
        <button class="btn btn-primary copy-btn"><i class="fas fa-clipboard mr-2"></i> {{__('general.copy_clipboard')}}</button>        
      </div>
    </div>
  </div>
  <div class="mb-3">
    <h5>{{ __('embed.show.js_title') }}</h5>
    <p>{{ __('embed.show.js_hint') }}</p>
    <div class="form-inline mb-3">    
      <label class="my-1 mr-2" for="embed-options">{{ __('embed.show.widget')}}</label>
      <select id="embed-options" class="custom-select">
      @php
        $options = [
          'search',
          'browse'
        ];
      @endphp
      @foreach($options as $opt)
        <option value="{{ $opt }}">{{__('embed.show.options.' . $opt)}}</option>
      @endforeach    
      </select>      
    </div> 
    <div>
      @php
        $base_src_js = secure_url('client/client.js') . "?id={$embed->id}";
        $src_css = secure_url('cient/client.css');
      @endphp
      @foreach($options as $opt) 
      <div id="embed-code-{{ $opt }}" class="embed-code mb-3 @if($loop->first) active @endif" >
        <?php
          $src_js = $base_src_js . "&widget={$opt}";
          $js_code = "<script src=\"{$src_js}\" defer></script>";
        ?>
        <div class="input-group copy-container">
          <input readonly class="form-control copy-text" value="{{ $js_code }}">
          <div class="input-group-append">
            <button class="btn btn-primary copy-btn"><i class="fas fa-clipboard mr-2"></i> {{__('general.copy_clipboard')}}</button>        
          </div>
        </div>      
      </div>
      @endforeach    
    </div> 
  </div> 
  <div class="mb-3">
    <?php 
      $div_code = "<div id=\"kel-widget\"></div>";
    ?>
    <h5>{{ __('embed.show.div_title') }}</h5>
    <p>{{ __('embed.show.div_hint') }}</p>
     <div class="input-group copy-container">
      <input readonly class="form-control copy-text" value="{{ $div_code }}">
      <div class="input-group-append">
        <button class="btn btn-primary copy-btn"><i class="fas fa-clipboard mr-2"></i> {{__('general.copy_clipboard')}}</button>        
      </div>
    </div>      
  </div>
  <a href="{{ route('embed.index') }}" class="btn btn-secondary">{{ __('embed.show.btn_index') }}</a>
@endsection

