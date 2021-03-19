@extends('layouts.app')
@section('content')
  <h1 class="text-primary">
    {{$header}}
  </h1>
	<div class="row">	
		<div class="col">
			<form method="post" action="{{$route}}" enctype="multipart/form-data">
				@csrf
				@method('DELETE')
    		<div class="form-group">
        	<button type="submit" class="btn btn-danger">
        		{{$submit_text ?? __('general.delete')}}
        	</button>
        	<a href="{{$route_back}}" class="btn btn-secondary">{{__('general.back')}}</a>
    		</div>
			</form>
		</div>
	</div>
@endsection
