$(function() {
  const form = $('#form');
  const instant_form = $('#instant-form');
  const template = $('#fieldset-template');
  const has_places_select = template.data('has-places-select');  
  const js_fieldsets = $('#js-fieldsets');
  const flatpickr_options = {
    enableTime: true,
    defaultHour: 0,
    noCalendar: true,
    dateFormat: "H:i",
    time_24hr: true
  };
  let js_fieldset_counter = js_fieldsets.data('fieldset-counter');
  $('.flatpickr-time').each(function() {
    flatpickr(this, flatpickr_options);
  })
  
  $('#fieldset-add').on('click', function(e) {
    e.preventDefault();
    let tmp = $(template.html());    
    
    let time_from = tmp.find('.js-input-from').attr('name',`time_from[${js_fieldset_counter}]`);
    let time_to = tmp.find('.js-input-to').attr('name',`time_to[${js_fieldset_counter}]`);
    flatpickr(time_from[0],flatpickr_options);  
    flatpickr(time_to[0],flatpickr_options);
    tmp.find('.js-input-rental-mode-id').attr('name',`rental_mode_id[${js_fieldset_counter}]`);
    tmp.find('.js-input-rental-place-id').attr('name',`rental_place_id[${js_fieldset_counter}]`);    
    tmp.find('.js-input-rental-duration').attr('name',`rental_duration[${js_fieldset_counter}]`);
    tmp.find('.js-input-rental-duration-in-days').attr('name',`rental_duration_in_days[${js_fieldset_counter}]`);
    tmp.find('.js-input-no-interrupt').attr('name',`no_interrupt[${js_fieldset_counter}]`);
    tmp.find('.js-input-no-interrupt[value=1]').attr('id',`no_interrupt_${js_fieldset_counter}`);
    tmp.find('.js-label-no-interrupt').attr('for',`no_interrupt_${js_fieldset_counter}`);
    tmp.find('.js-input-rentee-limitation').attr('name',`rentee_limitation[${js_fieldset_counter}]`);
    
    js_fieldset_counter++;
    js_fieldsets.append(tmp);
  });
  
  form.on('click','.fieldset-rem',function (e) {
    e.preventDefault();
    $(this).closest('.exception-row').remove();
  });  
});
