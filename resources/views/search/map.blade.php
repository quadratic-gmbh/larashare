@extends('layouts.app')
@push('scripts')
<script type="text/javascript" src="/openlayers/ol.js"></script>
<script type="text/javascript" src="/lightbox2/js/lightbox.min.js"></script>
<script type="text/javascript" src="/nouislider/nouislider.min.js"></script>
<script type="text/javascript" src="/js/search.map_form.js"></script>
<script type="text/javascript" src="/js/search.map.js"></script>
@endpush
@push('styles')
<link rel="stylesheet" href="/openlayers/ol.css"/>
<link rel="stylesheet" href="/lightbox2/css/lightbox.min.css">
<link rel="stylesheet" href="/nouislider/nouislider.min.css" />
@endpush
@section('content')
  @include('search.form_map')
  @isset($bikes)    
    @if($bikes->isEmpty())
      <p>{{__('search.no_result')}}</p>
    @endif
      <div id="map-container" class="mb-3 mx-4 mx-sm-3 border border-primary">
        <div class="embed-responsive embed-responsive-map">
          <div id="map" class="embed-responsive-item"></div>
        </div>  
      </div>
      <p class="text-muted">{{ __('search.randomness_hint') }}</p>
      <div id="map-popup" class="map-popup bg-light">        
        <div class="map-popup-title"><b>{{__('search.index.popup_title')}}</b></div>
        <div class="map-popup-content"></div>        
      </div>
      <div hidden id="map-icon-color" class="bg-primary"></div>
      <div hidden id="map-highlight-color" class="bg-success"></div>
      @php
      $date = now()->format('Y-m-d');
      @endphp
      <div id="search-result" class="card-deck card-deck-search" data-date="{{$date}}" data-search-mode="false">      
      @foreach($bikes as $b)
        @component('search.search_item',[
          'index' => $loop->index,
          'bike' => $b,          
          'rental_places' => $rental_places[$b->id],
          'image' => $bike_images[$b->id] ?? null,
          'date' => $date,
          'embed' => $embed
        ])
        @endcomponent      
      @endforeach
      </div>
  @endisset 
@endsection
