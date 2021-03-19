'use strict';
$(function() {
  const form = $('#search');
  $('#search-details-toggle').on('click', function(e) {
    $(this).find('i.fas').toggleClass('fa-caret-down').toggleClass('fa-caret-up');
  });
  
  $(".slider-container.slider-single").each(function() {
    initSlider($(this));
  });
  
  initTimeSlider();
  
  function initTimeSlider() {
    let container = $('#time_slider');
    let slider = container.find('.slider')[0];
    let name = 'time';           
    let input_from = $('#time_from')[0];
    let input_to = $('#time_to')[0];    
    let min = container.data('min');
    let max = container.data('max');
    
    let input_from_picker = flatpickr(input_from,{
      enableTime: true,
      noCalendar: true,
      dateFormat: "H:i",
      minuteIncrement: 30,
      time_24hr: true
    });  
    let input_to_picker = flatpickr(input_to,{
      enableTime: true,
      noCalendar: true,
      dateFormat: "H:i",
      minuteIncrement: 30,
      time_24hr: true
    });    
        
    let start_from = (input_from.value != '' ? input_from.value : min);
    let start_to = (input_to.value != '' ? input_to.value : max);    
    noUiSlider.create(slider, {
      start: [start_from,start_to],      
      step: 30,
      connect: true,
//      tooltips: true,
      range: {
        'min': timeToMinutes(min),
        'max': timeToMinutes(max)
      },
      format: {
        to: function(value) {          
          let minutes = Math.round(value);
          let hours = Math.floor(minutes/60);
          let r_minutes = minutes - (hours*60);
          
          let time = '';
          if (hours < 10) time += '0';
          time += hours;
          time += ':';
          if (r_minutes < 10) time += '0';
          time += r_minutes;
          
          return time;
        },
        from: function(value) {
          return timeToMinutes(value);          
        }
      }
    });    
    slider.noUiSlider.on('update', function(values, handle) {
      if(handle == 0) {
        input_from_picker.setDate(values[0], false, 'H:i');//        
      } else {
        input_to_picker.setDate(values[1], false, 'H:i');
      }
    });
           
    input_from_picker.config.onChange.push(function(dates, dateStr, instance) {
      slider.noUiSlider.setHandle(0,dateStr,true);
    });
    input_to_picker.config.onChange.push(function(dates, dateStr, instance) {
      slider.noUiSlider.setHandle(1,dateStr,true);
    });       
  }  
  
  function timeToMinutes(value) {
    let parts = value.split(':');
    let hours = parseInt(parts[0]);
    let minutes = parseInt(parts[1]);
    
    return (hours * 60) + minutes;
  }
  
  function initSlider(container) {        
    let slider = container.find('.slider')[0];
    let input = container.find('input')[0];
    let min = parseInt(input.min);
    let max = parseInt(input.max);
    let start = (input.value != '' ? parseInt(input.value) : min);
    
    noUiSlider.create(slider, {
      start: start,
      connect: 'upper',
      step: 1,
      tooltips: true,
      range: {
        'min': min,
        'max': max
      }
    });
    slider.noUiSlider.on('update', function(values, handle) {      
      input.value = parseInt(values[handle]);      
    });
  }  
});
