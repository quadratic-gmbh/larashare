@extends('layouts.app')
@push('scripts')
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/bs-custom-file-input/dist/bs-custom-file-input.min.js"></script>
<script type="text/javascript">
'use strict';
$(function() {
  bsCustomFileInput.init();
});
</script>
@endpush  
@section('content')
@component('components.form.focus',[
	'errors' => $errors
])
@endcomponent
  @include('bike.form_tabs',['active' => 'images'])
  <h1 class="text-primary">{{__('bike.images.title',['bike' => $bike->name])}}</h1>
  <p>{{ __('bike.images.hint') }}</p>  
  <form method="POST" action="{{route('bike.image_upload',['bike_id' => $bike->id])}}" enctype="multipart/form-data">
    @csrf    
    <div class="form-group">      
      @component('components.form.file',[        
        'name' => 'file',        
        'text' => __('bike.images.file_text')        
      ])    
      @endcomponent
      @component('components.form.error',['text_danger' => true, 'name' => 'file'])
      @endcomponent
    </div>
    <button type="submit" class="btn btn-primary">{{__('general.upload')}}</button>
  </form>  
    <div class="row mt-3">    
    @foreach($bike->images as $img)
      <div class="col-3">
        <div class="card bg-light">
          <img src="{{$img->getUrl(App\Image::SZ_300)}}" class="card-img-top" alt="bike image">
          <div class="card-body text-center">
            <form method="POST" action="{{route('bike.image_delete',['bike_id' => $bike->id, 'id' => $img->id])}}">
              @csrf
              @method("DELETE")
              <button type="submit" class="btn btn-danger">{{__('general.delete')}}</button>                
            </form>            
          </div>
        </div>
      </div>
    @endforeach
  </div>
@endsection
