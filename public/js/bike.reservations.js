'use strict';
$(function() {
  const locale = $('html').attr('lang');  
  const calendar_div = $('#calendar');
  const bike_id = calendar_div.data('bike-id');  
  const default_date = calendar_div.data('date');
  const date_format = 'Y-MM-DD';  
  const rental_modes = {};   
  const legend = $('#calendar-legend');  
  legend.find('li.rental-mode').each(function() {
    let item = $(this);    
    rental_modes[item.data('mode-id')] = item.data('mode-name');
  });
  let inquiry = false;   
  const calendar_cfg = {
    plugins: ['timeGrid','bootstrap','moment','interaction'],
    themeSystem: 'bootstrap',
    timeZone: 'UTC',
    locale: locale,
    allDaySlot: false,
    contentHeight: "auto",
    displayEventTime: false,  
    eventSources: [
      {
        events: getRentalPeriods,
      },
      {
        events: getReservations,
      }
    ],     
    eventRender: function(info) {
      let el = info.el;
      let event = info.event;
      if (event._def.rendering == 'background') {
        let title = event._def.title;
        title = title.replace(/(?:\r\n|\r|\n)/g, '<br>');
        $(el).append($('<div></div>').addClass('fc-title').html(title));        
      }
    },
  };  
  if (default_date) {
    calendar_cfg.defaultDate = default_date;
  }
  const calendar = new FullCalendar.Calendar(calendar_div[0],calendar_cfg);
  calendar.render();

  function getReservations(fetchInfo, successCallback, failureCallback) {     
    let start = moment.utc(fetchInfo.startStr);   
    let end = moment.utc(fetchInfo.endStr);    
    
    $.ajax({
      url: '/api/reservations_backend/' + bike_id,
      cache: false,
      data: {
        start: start.format(date_format),
        end: end.format(date_format)
      },
      dataType: 'json',
      success: function(data) {
        let events = [];
        for (let i = 0; i < data.length; i++) {
          let event = data[i];
          let class_name = 'cal-evt-clickable cal-evt-';
          if (event.confirmed) {
            class_name += 'reserved text-white';
          } else {
            class_name += 'pending';
          }
          event.className = class_name;
          events.push(event);
        }
        successCallback(events);
      },      
      error: function(xhr) {        
        failureCallback(xhr.responseJSON.message); 
      }   
    });
  } 
  function getRentalPeriods(fetchInfo, successCallback, failureCallback) {
    let start = moment.utc(fetchInfo.startStr);   
    let end = moment.utc(fetchInfo.endStr);    
    
    $.ajax({
      url: '/api/rental_period/' + bike_id,
      cache: false,
      data: {
        start: start.format(date_format),
        end: end.format(date_format),
      },
      dataType: 'json',
      success: function(data) {   
        let events = [];
        
        for (let i = 0; i < data.length; i++) {
          let event = data[i];
          event.rendering = 'background';
          let class_name = 'cal-evt-';
          if (rental_modes[event.rental_mode] == 'INQUIRY') {
            class_name += 'inquiry text-white';
          } else {
            class_name += 'instant text-white';
          }
          event.className = class_name;
          
          events.push(event);
        }
        successCallback(events);
      },
      error: function(xhr) {
        failureCallback(xhr.responseJSON.message); 
      }             
    });
  }
});