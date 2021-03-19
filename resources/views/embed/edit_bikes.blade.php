@extends('layouts.app')
@push('scripts')
<script src="/js/embed.edit_bikes.js"></script>        
@endpush 
@section('content')
  @if(session('update_success'))
  <div class="alert alert-success" role="alert">
    <i class="fas fa-check mr-3"></i>{{__('general.saved_success')}}
  </div>
  @endif
  @include('embed.form_tabs',['active' => 'edit_bikes'])
  <h1 class="text-primary">{{__('embed.edit_bikes.title')}}</h1>
  <p>{{__('embed.edit_bikes.hint_all')}}</p>
  @if($selected_bikes->isNotEmpty())  
  <form method="POST" action="{{ route('embed.update_bikes_allow_all',['id' => $embed->id]) }}" class="mb-3">
    @csrf           
    <button name="allow_all" type="submit" value="1" class="btn btn-primary">{{__('embed.edit_bikes.btn_all')}}</button>
  </form>  
  @endif
  <div class="row">
    <div class="col">
      <div class="form-group row">
        <div class="col-6 col-sm-4">
         <input class="form-control" type="text" id="table-search" name="table-search" value="" placeholder="Suche...">
        </div>
      </div>
    </div>
  </div>
  
  <form method="POST" action="{{ route('embed.update_bikes',['id' => $embed->id]) }}">
    @csrf           
    @error('bike')
    <div class="text-danger">
      <strong>{{$message}}</strong>
    </div>
    @enderror
    <table class="table table-bordered" id="bike-table">
      <thead>
        <th>{{__('general.name')}}</th>
        <th>{{__('bike.form.subheader_rental_places')}}</th>
        <th>{{__('embed.edit_bikes.col_cb')}}</th>
      </thead>
      <tbody>
      @foreach($sorted_bikes as $bikes)
        @foreach($bikes as $bike)
          @php
            $checked = $selected_bikes[$bike->id] ?? false;
            $name = 'bike[' . $bike->id . ']';
          @endphp        
          <tr>
            <td><label class="mb-0 d-block" for="{{ $name }}">{{$bike->name}}</label></td>
            @php
             $rp_names = [];
             foreach($bike['rentalPlaces'] as $rp) {
               $rp_names[] = $rp->name;
             }
            @endphp
            <td>{{join(', ', $rp_names)}}</td>
            <td>
              @component('components.form.checkbox',[
                'name' => $name,
                'custom' => true,
                'hidden_input' => false,
                'error_inline' => true,
                'form_data' => $selected_bikes
              ])            
              @endcomponent           
            </td>
          </tr>
        @endforeach
      @endforeach
      </tbody>
    </table>          
    <div class="form-group">
      <button type="submit" class="btn btn-primary">{{__('general.save')}}</button>      
    </div>
  </form>  
@endsection
