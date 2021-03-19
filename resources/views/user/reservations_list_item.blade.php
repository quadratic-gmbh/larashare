<div class="card border-primary mb-3">
  <div class="card-body">
    <div class="d-flex justify-content-between align-items-center">
      <div>{{$reservation->bike_name}}</div>
      <div>
      @php
      $date_range_str = null;
      $r_from = $reservation->reserved_from;
      $r_to = $reservation->reserved_to;
      $date_range_str = null;
      if ($r_from->format('Y-m-d') !== $r_to->format('Y-m-d')) {
       $date_range_str = $r_from->format('d.m.Y, H:i') . ' - ' . $r_to->format('d.m.Y, H:i');
      } else {
       $date_range_str = $r_from->format('d.m.Y, H:i') . ' - ' . $r_to->format('H:i'); 
      }
      @endphp
      {{$date_range_str}}
      </div>
      <div>        
        <div class="btn-group">    
          <a class="btn btn-secondary" href="{{route('chat.reservation',['id' => $reservation->id])}}">{{__('user.reservations.btn_contact')}}</a>                
          <a class="btn btn-secondary" href="{{route('user.reservation',['id' => $reservation->id])}}">{{__('user.reservations.btn_details')}}</a>
          @if($r_to > now())
          <a class="btn btn-secondary" href="{{route('bike.show',['bike_id' => $reservation->bike_id, 'reservation_id' => $reservation->id])}}">{{__('user.reservations.btn_edit')}}</a>
          @endif
          @if($new)
          <a class="btn btn-danger" href="{{route('user.reservation_cancel',['id' => $reservation->id,'ref' => 'list'])}}">{{__('user.reservations.btn_cancel')}}</a>
          @endif            
        </div>
      </div>      
    </div>  
    @if($reservation->confirmed_on === null)
    <div class="mt-3 d-flex justify-content-center">
      <span class="alert alert-warning mb-0" role="alert">
        <i class="fas fa-lg fa-exclamation-circle"></i> {{__('user.reservations.not_yet_confirmed')}}
      </span>
    </div>
    @endif  
  </div>
</div>
