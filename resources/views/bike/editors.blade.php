@extends('layouts.app') 
@section('content')
@component('components.form.focus',[
	'errors' => $errors
])
@endcomponent
  <h1 class="text-primary">{{__('bike.editors.title')}}</h1>  
  <div class="mb-3">
    <form class="form-inline" method="POST" action="{{route('bike.editors_add',['bike_id' => $bike->id])}}">
      @csrf
      <label class="my-1 mr-2" for="email">{{__('general.email')}}</label>
      @component('components.form.input',[
        'name' => 'email',
        'classes' => 'mr-3',   
      ])
      @endcomponent
      <button type="submit" class="btn btn-primary"><i class="fas fa-user-plus mr-3"></i>{{__('general.add')}}</button>
      @error('email')
      <div class="text-danger d-block w-100">{{$message}}</div>
      @enderror
    </form>
  </div>
  @if($bike->editors->isNotEmpty())
  <div class="table-responsive">
    <table class="table">
      <thead>
        <tr>
          <th>{{__('general.name')}}</th>
          <th>{{__('general.email')}}</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td class="align-middle">{{$bike->owner->full_name}}</td>
          <td class="align-middle">{{$bike->owner->email}}</td>
          <td></td>
        </tr>      
        @foreach($bike->editors as $user)
        <tr>
          <td class="align-middle">{{$user->full_name}}</td>
          <td class="align-middle">{{$user->email}}</td>
          <td class="text-right">
          <a href="{{route('bike.editors_remove_ask',['bike_id' => $bike->id, 'user_id' => $user->id])}}" 
          class="btn btn-danger"><i class="fas fa-user-minus mr-3"></i>{{__('general.remove')}}</a>         
          </td>
        </tr>      
        @endforeach
      </tbody>
    </table>
  </div>   
  @else
    <p>{{__('bike.editors.empty_text')}}</p>
  @endif  
  <a class="btn btn-secondary mt-3" href="{{route('bike.index')}}">{{__('general.back')}}</a>

@endsection
