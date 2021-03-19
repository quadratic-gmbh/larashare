'use strict';
$(function() {
  const form = $('#form');    
  const rental_periods = $('#rental-periods');
  const rental_mode_id = $('#rental_mode_id')[0].options[0].value;
  const rental_place = $('#rental_place_id');  
  const rental_place_id = (rental_place.is('input') ? rental_place[0].value : rental_place[0].options[0].value);
  const time_from = $('#time_from');  
  const time_to = $('#time_to');
  const flatpickr_options = {
    enableTime: true,
    defaultHour: 0,
    noCalendar: true,
    dateFormat: "H:i",
    time_24hr: true
  };
  const time_from_picker = flatpickr(time_from[0],flatpickr_options);  
  const time_to_picker = flatpickr(time_to[0],flatpickr_options);  
  
  rental_periods.on('click','.link-edit', function(e) {
    e.preventDefault();
    let rp = $(this).closest('.rental-period');    
    
    let data = {
      'rp_id': rp.data('id'),
      'date_from': rp.data('date-from'),
      'date_to': rp.data('date-to'),
      'time_from': rp.data('time-from'),
      'time_to': rp.data('time-to'),          
      'weekdays': rp.data('weekdays').toString().split(','),
      'rental_place_id': rp.data('rental-place-id'),
      'rental_mode_id': rp.data('rental-mode-id'),
      'rental_duration': rp.data('rental-duration'),
      'rental_duration_in_days': rp.data('rental-duration-in-days'),
      'no_interrupt': rp.data('no-interrupt'),
      'rentee_limitation': rp.data('rentee-limitation').replace(/,/g,'\n')
    };    
    if (data.rental_duration_in_days) {
      data.rental_duration = (data.rental_duration / 24);
    }
    setFormData(data);            
  });
  
  $('#link-add').on('click',function(e) {
    e.preventDefault();
    
    let date = moment();        
    let data = {
      'rp_id': '',
      'date_from': date.format('Y-MM-DD'),
      'date_to': date.add(10,'years').format('Y-MM-DD'),
      'time_from': '00:00',
      'time_to': '00:00',    
      'rental_place_id': rental_place_id,
      'rental_mode_id': rental_mode_id,
      'rental_duration': 0,
      'rental_duration_in_days': 0,
      'no_interrupt': 0,
      'weekdays': ["1","2","3","4","5","6","7"],
      'rentee_limitation': ''
    };    
    setFormData(data);
  })
  
  $('.unhides-form').on('click', function(e){
	  e.preventDefault();
	  form.prop('hidden', false);
  });
  
  function setFormData(data) {
    $('#rp_id').val(data['rp_id']);
    $('#date_from').val(data['date_from']);
    $('#date_to').val(data['date_to']);
    $('#rental_place_id').val(data['rental_place_id']);
    $('#rental_mode_id').val(data['rental_mode_id']);
    $('#rental_duration').val(data['rental_duration']);
    $('#rental_duration_in_days').val(data['rental_duration_in_days']);    
    $('#no_interrupt').prop('checked', data['no_interrupt']);
    $('#rentee_limitation').val(data['rentee_limitation']);
    
    time_from_picker.setDate(data['time_from'], false, 'H:i');
    time_to_picker.setDate(data['time_to'], false, 'H:i');
    
    for (let i = 1; i <= 7; i++) {    
      let checkbox = $('#weekday_' + i);   
      checkbox.prop('checked',data['weekdays'].includes(i.toString()));
    }
    
    form[0].scrollIntoView();
  }
});