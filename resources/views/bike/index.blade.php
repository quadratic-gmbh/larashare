@extends('layouts.app') 
@section('content')
  <a class="btn btn-primary mb-3" href="{{ route('embed.index') }}">{{__('bike.index.embeds_link')}}</a>
  <h1 class="text-primary">{{__('bike.index.title')}}</h1>
  @if($bikes->isNotEmpty())
    @foreach($bikes as $bike)
    <div class="card border-primary mb-3">
      <div class="card-body d-sm-flex justify-content-between">
        <div>{{$bike->name}}</div>
        <div class="w-100 d-block d-sm-none mb-3"></div>
        <div>
          <a href="{{route('bike.edit',['bike_id' => $bike])}}" class="btn btn-secondary">{{__('general.edit')}}</a>
          <a href="{{route('bike.editors',['bike_id' => $bike])}}" class="btn btn-secondary">{{__('bike.index.link_editors')}}</a>
        	<a href="{{route('bike.reservations',['bike_id' => $bike])}}" class="btn btn-secondary">{{__('bike.index.link_reservations')}}</a>          
          <a href="{{route('bike.destroy_ask',['bike_id' => $bike])}}" class="btn btn-danger">{{__('general.delete')}}</a>
        </div>
      </div>
    </div>
    @endforeach
  @else
    <p>{{__('bike.index.empty_text')}}</p>
  @endif     
	<a href="{{route('bike.create')}}" class="btn btn-primary">{{__('bike.index.new')}}</a>
  @if($editable_bikes->isNotEmpty())  
  <h1 class="text-primary mt-3">{{__('bike.index.header_editable')}}</h1>
  @foreach($editable_bikes as $bike)
    <div class="card border-primary mb-3">
      <div class="card-body d-sm-flex justify-content-between">
        <div>{{$bike->name}}</div>
        <div class="w-100 d-block d-sm-none mb-3"></div>
        <div>
          <a href="{{route('bike.edit',['bike_id' => $bike])}}" class="btn btn-secondary">{{__('general.edit')}}</a>
          <a href="{{route('bike.editors',['bike_id' => $bike])}}" class="btn btn-secondary">{{__('bike.index.link_editors')}}</a>
          <a href="{{route('bike.reservations',['bike_id' => $bike])}}" class="btn btn-secondary">{{__('bike.index.link_reservations')}}</a>                    
        </div>
      </div>
    </div>
    @endforeach
  @endif
@endsection
