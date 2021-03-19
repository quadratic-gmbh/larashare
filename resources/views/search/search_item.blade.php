@php
$id = "bike-" . $bike->id;
if (!isset($rental_places)) {
  $id.= "-". $place->id;
}
$show_url = route('bike.show',[
  'bike_id' => $bike->id,
  'date' => $date
]);
@endphp
<div class="search-result-item card bg-light mb-3" id="{{$id}}" data-id="{{$bike->id}}" data-name="{{$bike->name}}">
  @isset($image)
  <div class="search-result-item-image-container">
  	<a href="{{$show_url}}">
    	<img src="{{App\Image::getUrlById($image)}}" alt="image" class="card-img-top">
  	</a>
  	<a href="{{App\Image::getUrlById($image, App\Image::SZ_1000)}}" data-lightbox="image-{{$index}}" class="search-result-item-image-icon">
      <i class="fa fa-search fa-2x"></i>
  	</a>
  </div>
  @else
  <a href="{{$show_url}}">
    <img src="/img/bike_square.svg" alt="image" class="card-img-top">
  </a>
  @endisset
  <div class="card-body ">
    <h3 class="card-title">
      <b>{{$bike->name}}</b>
      @isset($bike_instance) 
      <br><small>{{ __('bike.rental_place') . ' ' . $bike_instance }}</small>
      @endisset
    </h3>
    @isset($rental_places)
      <ul class="list-unstyled">
        @foreach($rental_places as $place)
        <li class="rental-place"
          data-lon="{{$place->lon}}" 
          data-lat="{{$place->lat}}" 
          data-name="{{$place->name}}"  
          data-id="{{$place->id}}">    
          {{$place->name . ', ' . $place->postal_code . ' ' . $place->city}}
        </li>
        @endforeach 
      </ul>  
    @else    
      <p class="rental-place"
       data-lon="{{$place->lon}}" 
      data-lat="{{$place->lat}}" 
      data-name="{{$place->name}}"  
      data-id="{{$place->id}}">
      @php
        $place_text = $place->postal_code . ' ' . $place->city;
        /*
        if(isset($place->distance)) {              
          $d = $place->distance;
          if ($d < 1) {
            $d *= 1000;
            $d .= ' m';
          } else {
            $d .= ' km';
          }
          $place_text .= ' - ' . $d; 
        }
        */
      @endphp
      {{$place->name}}<br>
      {{$place_text}}
      </p>
    @endisset
    @php
      $p_type = $bike->pricing_type;
      $p_string = __('bike.pricing_type.' . $p_type);
      if($p_type == App\PricingType::FIXED) {
        $p_string .= ":";
        $p_values = json_decode($bike->pricing_values, true);
      }
    @endphp
    <ul class="list-unstyled">
    <li>{{$p_string}}</li>
    @if($p_type == App\PricingType::FIXED)
    	@foreach(['hourly', 'daily', 'weekly'] as $k)
      	@if($p_values[$k])
      	<li>{{ $p_values[$k] . "€ / " . __('bike.pricing_rate.' . strtoupper($k))}}</li>
      	@endif
    	@endforeach
    @endif
     @if($bike->pricing_deposit)
     <li>{{__('bike.pricing.deposit') . ': '. $bike->pricing_deposit . '€'}}</li>
     @endif
    </ul>
    @if($bike->terms_of_use_file)
    <p>
    <a href="{{route('bike.download_tos',['bike_id' => $bike->id])}}" target="_blank">{{__('bike.terms_of_use')}}</a>
    </p>
    @endif
    <p>
    {{__('search.result.children')}}
    @if($bike->children > 0)
    <i class="fas fa-check text-success"></i>
    @else
    <i class="fas fa-times text-danger"></i>
    @endif
    </p>
    <p>
    {{__('search.form.electric')}}
    @if($bike->electric > 0)
    <i class="fas fa-check text-success"></i>
    @else
    <i class="fas fa-times text-danger"></i>
    @endif
    </p>
    <ul class="list-unstyled">
    <li>{{__('search.result.cargo_weight',['n' => $bike->cargo_weight])}}</li>
    <li>{{__('search.result.cargo_length',['n' => $bike->cargo_length])}}</li>
    <li>{{__('search.result.cargo_width',['n' => $bike->cargo_width])}}</li>
    </ul>          
  </div>
  <div class="card-footer bg-light border-top-0">
    <a href="{{$show_url}}" class="btn btn-primary">{{__('search.result.reserve_link')}}</a>
  </div>
</div>    