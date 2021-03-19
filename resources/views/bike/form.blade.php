@extends('layouts.app')
@push('scripts')
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/bs-custom-file-input/dist/bs-custom-file-input.min.js"></script>
<script src="/js/bike.form.js"></script>        
@endpush 
@php
$form_data = [];
if($bike){
	$title = __('bike.form.header_edit');
	$route = route('bike.update', ['bike_id' => $bike]);
	$method = 'PUT';
	foreach(
		['name', 'model', 'cargo_weight', 'cargo_length', 'cargo_width', 'misc_equipment', 'description', 'buffer_time_before',
		 'buffer_time_after', 'pricing_deposit'] as $field){
		$form_data[$field] = $bike[$field];
	}
	foreach(
		['hourly', 'daily', 'weekly'] as $field){
		$form_data['pricing_value_' . $field] = $bike['pricing_values'][$field];
	}
	foreach(
		['wheels', 'children', 'electric', 'box_type_id'] as $field){
		$form_data[$field] = intval($bike[$field]);
	}
  	
	if($bike['pricing_type_id'] === App\PricingType::getFreeOrDonationId()){
		$form_data['pricing_free'] = 1;
		$form_data['pricing_donation'] = 1;
	}elseif($bike['pricing_type_id'] === App\PricingType::getFreeId()){
		$form_data['pricing_free'] = 1;
	}elseif($bike['pricing_type_id'] === App\PricingType::getDonationId()){
		$form_data['pricing_donation'] = 1;
	}
}else{
	$title = __('bike.form.header_new');
	$route = route('bike.store');
	$method = 'POST';
	$form_data['box_type_id'] = App\BoxType::getNoBoxId();
	$form_data['pricing_free'] = 1;
}
@endphp
@section('content')
@component('components.form.focus',[
	'errors' => $errors,
	'mode' => 'array'
])
@endcomponent
  @if(session('update_success'))
  <div class="alert alert-success" role="alert">
    <i class="fas fa-check mr-3"></i>{{__('general.saved_success')}}
  </div>
  @endif
  @include('bike.form_tabs',['active' => 'edit'])
  <h1 class="text-primary">
    {{__($title)}}
  </h1>
	<div class="row">	
		<div class="col">
			<form id="bike-form" method="post" action="{{$route}}" enctype="multipart/form-data">
				@csrf
				@method($method)
				<h3 class="text-primary">
    			{{__('bike.form.subheader_bike')}}
  			</h3>
				<div class="form-group">
      		@component('components.form.label_input_error',[
      			'form_data' => $form_data,
        		'name' => 'name',
        		'label' => __('bike.name') . '*'
      		])
      		@endcomponent
    		</div>
				<div class="form-group">
      		@component('components.form.label_input_error',[
      			'form_data' => $form_data,
        		'name' => 'model',
        		'label' => __('bike.model') . '*'
      		])
      		@endcomponent
    		</div>
    		<div class="form-group row">
    			<div class="col-4">
        		@component('components.form.label_input_error',[
        			'form_data' => $form_data,
          		'name' => 'wheels',
          		'label' => false,
          		'type' => 'select',
          		'select_options' => $wheels_options
        		])
        		@endcomponent
      		</div>
    			<div class="col-4">
        		@component('components.form.label_input_error',[
        		  'form_data' => $form_data,
          		'name' => 'children',
          		'label' => false,
          		'type' => 'select',
          		'select_options' => $children_options
        		])
        		@endcomponent
      		</div>
    			<div class="col-4">
        		@component('components.form.label_input_error',[
        		  'form_data' => $form_data,
          		'name' => 'electric',
          		'label' => false,
          		'type' => 'select',
          		'select_options' => $electric_options
        		])
        		@endcomponent
      		</div>
    		</div>
    		<div class="form-group row">
    			<div class="col-4">
        		@component('components.form.label_input_error',[
        		  'form_data' => $form_data,
          		'name' => 'box_type_id',
          		'label' => false,
          		'type' => 'select',
          		'select_options' => $box_type_options
        		])
        		@endcomponent
      		</div>
    		</div>
    		<div class="form-group row">
    			<div class="col-4">
        		@component('components.form.label_input_error',[
        		  'form_data' => $form_data,
          		'name' => 'cargo_weight',
          		'label' => __('bike.cargo_weight') . '*'
        		])
        		@endcomponent
      		</div>
    			<div class="col-4">
        		@component('components.form.label_input_error',[
        		  'form_data' => $form_data,
          		'name' => 'cargo_length',
          		'label' => __('bike.cargo_length') . '*'
        		])
        		@endcomponent
      		</div>
    			<div class="col-4">
        		@component('components.form.label_input_error',[
        		  'form_data' => $form_data,
          		'name' => 'cargo_width',
          		'label' => __('bike.cargo_width') . '*'
        		])
        		@endcomponent
      		</div>
    		</div>
				<div class="form-group">
      		@component('components.form.label_input_error',[
      			'form_data' => $form_data,
        		'name' => 'misc_equipment',
        		'label' => __('bike.misc_equipment')
      		])
      		@endcomponent
    		</div>	
				<div class="form-group">
      		@component('components.form.label_input_error',[
      			'form_data' => $form_data,
        		'name' => 'description',
        		'label' => __('bike.description'),
        		'type' => 'textarea'
      		])
      		@endcomponent
    		</div>
    		<h3 class="text-primary">
    			{{__('bike.form.subheader_buffer_time')}}
  			</h3>
  			<p>
  				{{__('bike.form.buffer_time_text')}}
  			</p>
    		<div class="form-group mb-0">
  				<label for="buffer_time_before">{{__('bike.buffer_time_before')}}</label>
  			</div>
    		<div class="form-group row">
    			<div class="col-2">
        		@component('components.form.label_input_error',[
        		  'form_data' => $form_data,
          		'name' => 'buffer_time_before',
          		'label' => false
        		])
        		@endcomponent
      		</div>
      		<div class="align-self-center">
      			<label for="buffer_time_before ">{{trans_choice('general.minute', 2)}}</label>
      		</div>
    		</div>
    		<div class="form-group mb-0">
  				<label for="buffer_time_after">{{__('bike.buffer_time_after')}}</label>
  			</div>
    		<div class="form-group row">
    			<div class="col-2">
        		@component('components.form.label_input_error',[
        		  'form_data' => $form_data,
          		'name' => 'buffer_time_after',
          		'label' => false
        		])
        		@endcomponent
      		</div>
      		<div class="align-self-center">
      			<label for="buffer_time_after ">{{trans_choice('general.minute', 2)}}</label>
      		</div>
    		</div>
				<h3 class="text-primary">
    			{{__('bike.form.subheader_cost')}}
  			</h3>
				<div class="form-group">
      		@component('components.form.label_input_error',[
      			'form_data' => $form_data,
        		'name' => 'pricing_free',
        		'label' => __('bike.pricing.free'),
        		'type' => 'checkbox'
      		])
      		@endcomponent
    		</div>
				<div class="form-group">
      		@component('components.form.label_input_error',[
      			'form_data' => $form_data,
        		'name' => 'pricing_donation',
        		'label' => __('bike.pricing.donation'),
        		'type' => 'checkbox'
      		])
      		@endcomponent
    		</div>
    		<div class="form-group mb-0">
  				<label for="pricing_value_hourly">{{__('bike.pricing.fixed')}}</label>
  			</div>
    		<div class="form-group row">
    			<div class="col-2 pricing_values">
        		@component('components.form.label_input_error',[
        		  'form_data' => $form_data,
          		'name' => 'pricing_value_hourly',              
          		'label' => false          
        		])
        		@endcomponent
      		</div>
      		<div class="align-self-center">
      			<label for="pricing_rate_id ">{{__('bike.pricing.eur_per') . ' ' . __('bike.pricing_rate.HOURLY')}}</label>
      		</div>
    		</div>
    		<div class="form-group row">
    			<div class="col-2 pricing_values">
        		@component('components.form.label_input_error',[
        		  'form_data' => $form_data,
          		'name' => 'pricing_value_daily',              
          		'label' => false          
        		])
        		@endcomponent
      		</div>
      		<div class="align-self-center">
      			<label for="pricing_rate_id ">{{__('bike.pricing.eur_per') . ' ' . __('bike.pricing_rate.DAILY')}}</label>
      		</div>
    		</div>
    		<div class="form-group row">
    			<div class="col-2 pricing_values">
        		@component('components.form.label_input_error',[
        		  'form_data' => $form_data,
          		'name' => 'pricing_value_weekly',              
          		'label' => false          
        		])
        		@endcomponent
      		</div>
      		<div class="align-self-center">
      			<label for="pricing_rate_id ">{{__('bike.pricing.eur_per') . ' ' . __('bike.pricing_rate.WEEKLY')}}</label>
      		</div>
    		</div>
    		<div class="form-group mb-0">
  				<label for="pricing_deposit">{{__('bike.pricing.deposit')}}</label>
  			</div>
    		<div class="form-group row">
    			<div class="col-2">
        		@component('components.form.label_input_error',[
        		  'form_data' => $form_data,
          		'name' => 'pricing_deposit',
          		'label' => false         
        		])
        		@endcomponent
      		</div>
      		<div class="align-self-center">
      			<label for="pricing_deposit ">{{__('general.eur')}}</label>
      		</div>
    		</div>
    		@if(!$bike || ($bike && !$bike->no_tos_upload))
      		@if($bike && $bike['terms_of_use_file'])
            <div class="form-group">
    				<span class="alert alert-info"><i class="fas fa-info mr-3"></i>{!!__('bike.terms_of_use_file_edit', ['route' => route('bike.download_tos', ['bike_id' => $bike]), 'tos' => __('bike.terms_of_use')])!!}</span>
            </div>
          @endif
      		<div class="form-group">
            @component('components.form.label',['name' => 'terms_of_use_file', 'text' => __('bike.terms_of_use_file')])
            @endcomponent
            @component('components.form.file',[        
              'name' => 'terms_of_use_file',               
            ])    
            @endcomponent
            @component('components.form.error',['text_danger' => true, 'name' => 'terms_of_use_file'])
            @endcomponent
      		</div>
          @if($bike && $bike['terms_of_use_file'])
      		<div class="form-group">
        		@component('components.form.label_input_error',[
        			'form_data' => $form_data,
          		'name' => 'delete_terms_of_use_file',
          		'label' => __('bike.delete_terms_of_use_file'),
          		'type' => 'checkbox'
        		])
        		@endcomponent
      		</div>
      		@endif
      		<p><a href="/vorlage_verleihbedingungen.docx">{{ __('bike.form.tos_template')}}</a></p>
    		@endif
  			<div class="rental-places-container">
  				<h3 class="text-primary">
    				{{ __('bike.form.subheader_rental_places')}}
  				</h3>
  				@error('rental_place')<div class="alert alert-danger" role="alert">{{$message}}</div>@enderror
  				@php
  				  if($bike){
  				  	$form_data['rental_place_counter'] = count($bike['rentalPlaces']);
  				  }
    				$rental_place_counter = old('rental_place_counter') ?? $form_data['rental_place_counter'] ?? 1;
    			@endphp
  				<input type="hidden" name="rental_place_counter" value="{{$rental_place_counter}}" @if($bike) data-min="{{$form_data['rental_place_counter']}}" @else data-min="1" @endif>
  				@for($i = 1; $i <= $rental_place_counter; $i++)
  				@php
  				$rp_id = false;
  				if($bike && ($i <= $form_data['rental_place_counter'])){
  					$curr_rental_place = $bike['rentalPlaces'][$i-1];
  					foreach(['name', 'street_name', 'house_number', 'postal_code', 'city', 'description'] as $field){
  						$form_data['rental_place.'.$i.'.'.$field] = $curr_rental_place[$field];
  					}
  					$rp_id = $curr_rental_place['id'];
  				}
  				@endphp
    			<div class="rental-place-item" data-counter="{{$i}}">
      			<h5 class="text-primary">
        			{{ __('bike.rental_place')}} {{$i}}
      			</h5>
      			@if($rp_id)
      			<input type="hidden" name="{{'rental_place['.$i.'][id]'}}" value="{{$rp_id}}">
      			@if($form_data['rental_place_counter'] > 1)
      			<div class="form-group">
        			<a href="{{route('bike.rental_place_destroy_ask',['bike_id' => $bike, 'rental_place_id' => $curr_rental_place])}}" class="btn btn-danger">{{__('general.delete')}}</a>
    				</div>
    				@endif
      			@endif
    				<div class="form-group">
          		@component('components.form.label_input_error',[
          			'form_data' => $form_data,
            		'name' => 'rental_place['.$i.'][name]',
            		'label' => __('bike.rental_place_name')  . '*'
          		])
          		@endcomponent
        		</div>
    				<div class="form-group">
          		@component('components.form.label_input_error',[
          			'form_data' => $form_data,
            		'name' => 'rental_place['.$i.'][street_name]',
            		'label' => __('bike.rental_place_street_name')  . '*'
          		])
          		@endcomponent
        		</div>
        		<div class="form-group row">
        			<div class="col-6">
          		@component('components.form.label_input_error',[
          			'form_data' => $form_data,
            		'name' => 'rental_place['.$i.'][house_number]',
            		'label' => __('bike.rental_place_house_number')  . '*'
          		])
          		@endcomponent
          		</div>
        			<div class="col-6">
          		@component('components.form.label_input_error',[
          			'form_data' => $form_data,
            		'name' => 'rental_place['.$i.'][postal_code]',
            		'label' => __('bike.rental_place_postal_code')  . '*'
          		])
          		@endcomponent
          		</div>
        		</div>
    				<div class="form-group">
          		@component('components.form.label_input_error',[
          			'form_data' => $form_data,
            		'name' => 'rental_place['.$i.'][city]',
            		'label' => __('bike.rental_place_city')  . '*'
          		])
          		@endcomponent
        		</div>
    				<div class="form-group">
          		@component('components.form.label_input_error',[
          			'form_data' => $form_data,
            		'name' => 'rental_place['.$i.'][description]',
            		'label' => __('bike.rental_place_description'),
            		'type' => 'textarea'
          		])
          		@endcomponent
        		</div>
        		<div class='emails-container'>
        		@error('rental_place.' . $i . '.email')<div class="alert alert-danger" role="alert">{{$message}}</div>@enderror
        			@php
        				if($bike){
        					$form_data['rental_place.'.$i.'.email_counter'] = count($curr_rental_place['emails']);
        				}
    						$email_counter = old('rental_place.'.$i.'.email_counter') ?? $form_data['rental_place.'.$i.'.email_counter'] ??  1;
    					@endphp
        			<input type="hidden" name="rental_place[{{$i}}][email_counter]" value="{{$email_counter}}">
        			@for($j = 1; $j <= $email_counter; $j++)
        			@php
        				$email_id = false;
        				if($bike && ($i <= $form_data['rental_place_counter']) && ($j <= $form_data['rental_place.'.$i.'.email_counter'])){
        					$current_email = $curr_rental_place['emails'][$j-1];
        					foreach(['email', 'notify_on_reservation'] as $field){
        						$form_data['rental_place.'.$i.'.email.'.$j.'.'.$field] = $current_email[$field];
        					}
        					$email_id = $current_email['id'];
        				}else{
        				  $form_data['rental_place.'.$i.'.email.'.$j.'.notify_on_reservation'] = 1;
        				}
        			@endphp
          		<div class='email-item' data-counter="{{$j}}">
          			@if($email_id)
      					<input type="hidden" name="{{'rental_place['.$i.'][email]['.$j.'][id]'}}" value="{{$email_id}}">
      					@endif
        				<div class="form-group">
              		@component('components.form.label_input_error',[
              			'form_data' => $form_data,
                		'name' => 'rental_place['.$i.'][email]['.$j.'][email]',
                		'label' => __('bike.rental_place_email') .' ' . $j .'*'
              		])
              		@endcomponent
            		</div>
        				<div class="form-group">
              		@component('components.form.label_input_error',[
              			'form_data' => $form_data,
                		'name' => 'rental_place['.$i.'][email]['.$j.'][notify_on_reservation]',
                		'label' => __('bike.rental_place_email_notify_on_reservation'),
                		'type' => 'checkbox'
              		])
              		@endcomponent
            		</div>
          		</div>
          	@endfor
          	<div class="my-3">
    					<a class="d-flex align-items-center btn btn-link link-rental-place-email-add" href="#">
    						<span class="text-success pr-3"><i class="fas fa-plus-circle fa-1x"></i></span>
    						<span class="text-body">{{__('bike.form.add_email')}}</span>
    					</a>
    					<span @if($email_counter == 1) hidden @endif class="link-rental-place-email-remove"><a class="d-flex align-items-center btn btn-link" href="#">
    						<span class="text-danger pr-3"><i class="fas fa-minus-circle fa-1x"></i></span>
    						<span class="text-body">{{__('bike.form.remove_email')}}</span>
    					</a></span>
    					</div>
        		</div>
      		</div>
      		@endfor
    		</div>
    		<div class="form-group">
        	<button type="submit" class="btn btn-primary">
        		{{__('general.save') }}
        	</button>
        	<a href="{{route('bike.index')}}" class="btn btn-secondary">{{__('general.back')}}</a>
    		</div>
    		<div class="my-3">
    			<a class="d-flex align-items-center btn btn-link link-rental-place-add" href="#">
    			<span class="text-success pr-3"><i class="fas fa-plus-circle fa-3x"></i></span>
    			<b class="text-body">{{__('bike.form.add_place')}}</b>
    			</a>
    				<span @if((!$bike && $rental_place_counter == 1) || ($bike && ($rental_place_counter == $form_data['rental_place_counter']))) hidden @endif class="link-rental-place-remove"><a class="d-flex align-items-center btn btn-link" href="#">
    					<span class="text-danger pr-3"><i class="fas fa-minus-circle fa-3x"></i></span>
    					<b class="text-body">{{__('bike.form.remove_place')}}</b>
    				</a></span>
    		</div>
    		
    		
			</form>
		</div>
	</div>
@endsection
