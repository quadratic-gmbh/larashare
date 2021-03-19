<div class="card">
  <div class="card-body">
    <h4>{{$header}}</h4>
    <b>{{$date_time}}</b><br>
    @isset($rental_place)
    	<b>{{$rental_place->name}}</b><br>
      {{$rental_place->full_address}}
      @isset($rental_place->description)
      <br><b>{{__('bike.rental_place_description')}}</b>
      <p>{!!$rental_place->description!!}</p>
      @endisset
    @else
      <div class="alert alert-danger mt-3 mb-0" role="alert">
        <i class="fas fa-lg fa-exclamation-circle"></i> {{__('user.reservation.place_not_found')}}
      </div>
    @endisset
  </div>
</div>
