@extends('layouts.app') 
@section('content')
  <h1 class="text-primary">{{__('embed.index.title')}}</h1>
  @if($embeds->isNotEmpty())
    @foreach($embeds as $embed)
    <div class="card border-primary mb-3">
      <div class="card-body d-sm-flex justify-content-between">
        <div>{{$embed->name}}</div>
        <div class="w-100 d-block d-sm-none mb-3"></div>
        <div>
          <a href="{{route('embed.edit',['id' => $embed])}}" class="btn btn-secondary">{{__('general.edit')}}</a>                   
          <a href="{{route('embed.show',['id' => $embed])}}" class="btn btn-secondary">{{__('embed.index.btn_show')}}</a>
          <a href="{{route('embed.destroy',['id' => $embed])}}" class="btn btn-danger">{{__('general.delete')}}</a>
        </div>
      </div>
    </div>
    @endforeach
  @else
    <p>{{__('embed.index.empty_text')}}</p>
  @endif     
	<a href="{{route('embed.create')}}" class="btn btn-primary">{{__('embed.index.btn_new')}}</a>
@endsection
