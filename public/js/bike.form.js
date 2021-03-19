'use strict';
$(function() {
  bsCustomFileInput.init();
  
	const form = $('#bike-form');
	
	//Preise
	const pricing_free = form.find('input[name="pricing_free"]');
	const pricing_donation = form.find('input[name="pricing_donation"]');
	const pricing_values = form.find('.pricing_values > input');
	
	pricing_free.add(pricing_donation).on('click', function(){
	  pricing_values.val('');
	});
	
	pricing_values.on('input', function(){
		pricing_free.add(pricing_donation).prop('checked', false);
	});
	
	//Nutzungsbedingungen
	const delete_terms_of_use_file = form.find('input[name="delete_terms_of_use_file"]');
	if(delete_terms_of_use_file.length){
		const terms_of_use_file = form.find('input[name="terms_of_use_file"]');
		
		terms_of_use_file.on('change', function(){
			delete_terms_of_use_file.prop('checked', false);
		});
		
		delete_terms_of_use_file.on('click', function(){
			terms_of_use_file.val('');
		});
	}
	
	//Mehrere Standorte + Emails
	const rental_places_container = form.find('.rental-places-container');
	const rental_place_counter = rental_places_container.find('input[name="rental_place_counter"]');
	const add_rental_place = form.find('.link-rental-place-add');
	const remove_rental_place = form.find('.link-rental-place-remove');
	const min_places = parseInt(rental_place_counter.attr('data-min'));
	
	//Standorte
	add_rental_place.on('click', function(ev){
		ev.preventDefault();
		let rental_place_id = parseInt(rental_place_counter.val()) + 1;
		rental_place_counter.val(rental_place_id);
		
		let tpl = `<div class="rental-place-item" data-counter="${rental_place_id}">
  			<h5 class="text-primary">
			Standort ${rental_place_id}
			</h5>
			<div class="form-group">
  		<label for="rental_place[${rental_place_id}][name]">Bezeichnung des Verleihstandorts*</label>	    
<input class="form-control  " type="text" id="rental_place[${rental_place_id}][name]" name="rental_place[${rental_place_id}][name]" value="">          		</div>
			<div class="form-group">
  		<label for="rental_place[${rental_place_id}][street_name]">Straße*</label>	    
<input class="form-control  " type="text" id="rental_place[${rental_place_id}][street_name]" name="rental_place[${rental_place_id}][street_name]" value="">          		</div>
		<div class="form-group row">
			<div class="col-6">
  		<label for="rental_place[${rental_place_id}][house_number]">Hausnummer*</label>	    
<input class="form-control  " type="text" id="rental_place[${rental_place_id}][house_number]" name="rental_place[${rental_place_id}][house_number]" value="">            		</div>
			<div class="col-6">
  		<label for="rental_place[${rental_place_id}][postal_code]">PLZ*</label>	    
<input class="form-control  " type="text" id="rental_place[${rental_place_id}][postal_code]" name="rental_place[${rental_place_id}][postal_code]" value="">            		</div>
		</div>
			<div class="form-group">
  		<label for="rental_place[${rental_place_id}][city]">Ort*</label>	    
<input class="form-control  " type="text" id="rental_place[${rental_place_id}][city]" name="rental_place[${rental_place_id}][city]" value="">          		</div>
			<div class="form-group">
  		<label for="rental_place[${rental_place_id}][description]">Zugangsbeschreibung</label>	    
<textarea class="form-control" rows="5" id="rental_place[${rental_place_id}][description]" name="rental_place[${rental_place_id}][description]"></textarea>          		</div>
		<div class="emails-container">
			        			<input type="hidden" name="rental_place[${rental_place_id}][email_counter]" value="1">
			        			          		<div class="email-item" data-counter="1">
				<div class="form-group">
      		<label for="rental_place[${rental_place_id}][email][1][email]">E-Mail-Adresse für Kommunikation und Benachrichtigungen 1*</label>	    
<input class="form-control  " type="text" id="rental_place[${rental_place_id}][email][1][email]" name="rental_place[${rental_place_id}][email][1][email]" value="">              		</div>
				<div class="form-group">
      		<div class="form-check">
<input type="hidden" name="rental_place[${rental_place_id}][email][1][notify_on_reservation]" value="0">
<input type="checkbox" class="form-check-input" name="rental_place[${rental_place_id}][email][1][notify_on_reservation]" id="rental_place[${rental_place_id}][email][1][notify_on_reservation]" value="1" checked="">
<label class="form-check-label" for="rental_place[${rental_place_id}][email][1][notify_on_reservation]">Ich möchte bei neuen Reservierungen per E-Mail informiert werden.</label>
</div>              		</div>
  		</div>
  	          	<div class="my-3">
				<a class="d-flex align-items-center btn btn-link link-rental-place-email-add" href="#">
					<span class="text-success pr-3"><i class="fas fa-plus-circle fa-1x"></i></span>
					<span class="text-body">Weitere E-Mail-Adresse zu diesem Verleihstandort hinzufügen</span>
				</a>
				<span class="link-rental-place-email-remove" hidden=""><a class="d-flex align-items-center btn btn-link" href="#">
					<span class="text-danger pr-3"><i class="fas fa-minus-circle fa-1x"></i></span>
					<span class="text-body">E-Mail-Adresse von diesem Verleihstandort entfernen</span>
				</a></span>
				</div>
		</div>
		</div>`
			
		rental_places_container.children('.rental-place-item').last().after(tpl);
		remove_rental_place.attr('hidden', false);
	});
	
	remove_rental_place.on('click', function(ev){
		ev.preventDefault();
		let rental_place_id = parseInt(rental_place_counter.val());
		if(rental_place_id === min_places){
			return;
		}
		rental_place_counter.val(rental_place_id - 1);
		rental_places_container.children('.rental-place-item[data-counter="' + rental_place_id + '"]').last().remove();
		if((rental_place_id - 1) === min_places){
			remove_rental_place.attr('hidden', true);
		}
	});
	
	//Emails
	form.on('click', '.link-rental-place-email-add', function(ev){
		ev.preventDefault();
		let emails_container = $(this).closest('.emails-container');
		let rental_place_item = emails_container.closest('.rental-place-item');
		let rental_place_id = rental_place_item.attr('data-counter');
		let counter = emails_container.find('input[name="rental_place[' + rental_place_id +'][email_counter]"]');
		let email_id = parseInt(counter.val()) + 1;
		counter.val(email_id);
		
		let tpl = `<div class="email-item" data-counter="${email_id}">
			<div class="form-group">
      		<label for="rental_place[${rental_place_id}][email][${email_id}][email]">E-Mail-Adresse für Kommunikation und Benachrichtigungen ${email_id}*</label>	    
<input class="form-control  " type="text" id="rental_place[${rental_place_id}][email][${email_id}][email]" name="rental_place[${rental_place_id}][email][${email_id}][email]" value="">              		</div>
				<div class="form-group">
      		<div class="form-check">
<input type="hidden" name="rental_place[${rental_place_id}][email][${email_id}][notify_on_reservation]" value="0">
<input type="checkbox" class="form-check-input" name="rental_place[${rental_place_id}][email][${email_id}][notify_on_reservation]" id="rental_place[${rental_place_id}][email][${email_id}][notify_on_reservation]" value="1" checked="">
<label class="form-check-label" for="rental_place[${rental_place_id}][email][${email_id}][notify_on_reservation]">Ich möchte bei neuen Reservierungen per E-Mail informiert werden.</label>
</div>              		</div>
  		</div>`
			
		emails_container.children('.email-item').last().after(tpl);
		let email_remove = emails_container.find('.link-rental-place-email-remove');
		email_remove.attr('hidden', false);
	});
	
	form.on('click', '.link-rental-place-email-remove', function(ev){
		ev.preventDefault();
		let emails_container = $(this).closest('.emails-container');
		let rental_place_item = emails_container.closest('.rental-place-item');
		let rental_place_id = rental_place_item.attr('data-counter');
		let counter = emails_container.find('input[name="rental_place[' + rental_place_id +'][email_counter]"]');
		let email_counter = parseInt(counter.val());
		if(email_counter === 1){
			return;
		}
		counter.val(email_counter - 1);
		emails_container.children('.email-item[data-counter="' + email_counter + '"]').last().remove();
		if((email_counter - 1) === 1){
			$(this).attr('hidden', true);
		}
	});
});
