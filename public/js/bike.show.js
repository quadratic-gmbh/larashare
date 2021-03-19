'use strict';
$(function() {
  const locale = $('html').attr('lang');  
  const calendar_div = $('#calendar');
  const bike_id = calendar_div.data('bike-id');
  const default_date = calendar_div.data('date');
  const reservation_id = (calendar_div.data('reservation-id'))
  const date_format = 'Y-MM-DD';
  let available = {};
  const reservations = [];
  const rental_modes = {};   
  const legend = $('#calendar-legend');
  const error_div = $('#form-error-msg-div');
  error_div.hide();
  error_div.removeAttr('hidden');
  const error_div_text = error_div.find('span');
  legend.find('li.rental-mode').each(function() {
    let item = $(this);    
    rental_modes[item.data('mode-id')] = item.data('mode-name');
  });  
  let first_call = true;
  let inquiry = false;
  let reservation_possible = true;
  let current_start_date = null;
  let time_constraints = {};
//  let min_time = null;
//  let max_time = null;
  const calendar_cfg = {
    plugins: ['timeGrid','bootstrap','moment','interaction'],
    themeSystem: 'bootstrap',
    timeZone: 'UTC',
    locale: locale,
    allDaySlot: false,
    contentHeight: "auto",
    eventAllow: eventAllow,
    viewSkeletonRender : function(info) {           
      let start_date = moment.utc(info.view.calendar.state.dateProfile.currentRange.start).format('YMMDD');
      if (current_start_date == null || current_start_date != start_date) {                      
        if(time_constraints[start_date]) {
          current_start_date = start_date;
          let constraint = time_constraints[start_date];
          
          calendar.setOption('minTime',constraint.min.format('HH:mm'));
          calendar.setOption('maxTime',constraint.max.format('HH:mm'));
        }
      }
    },
    eventOverlap: eventOverlap,
    dateClick: dateClickHandler,
    displayEventTime: false,
    eventResize: handleEventResizeDrop,
    eventDrop: handleEventResizeDrop,
    eventSources: [
      {
        events: getRentalPeriods,
      },
      {
        events: getReservations,
        classNames: ['cal-evt-reserved'],
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
    eventLongPressDelay: 250
  };  
  if (default_date) {
    calendar_cfg.defaultDate = default_date;
  }
  const calendar = new FullCalendar.Calendar(calendar_div[0],calendar_cfg);
  calendar.render();
  
  const form = $('#form');
  const btn_reserve = $('#btn-reserve');
  const btn_inquiry = $('#btn-inquiry');
  
  // flatpickr date/time
  const flatpickr_options = {
    enableTime: true,      
    minuteIncrement: 30,
    time_24hr: true,
    onChange: handleFlatpickrChange
  };
  
  form.on('submit', function(e){
	  if(form.data('authed')){
		  return;
	  }
	  if(history.pushState){
		  let reserve_from = $('#reserve_from').val();
		  let reserve_to = $('#reserve_to').val();
		  let purpose = $('#purpose').val();
		  history.pushState({}, "", window.location.href.split('?')[0] + "?reserve_from=" + encodeURIComponent(reserve_from) + "&reserve_to=" + encodeURIComponent(reserve_to) + "&purpose=" + encodeURIComponent(purpose));
	  }
  });
  
  const reserve_from = flatpickr('#reserve_from',flatpickr_options);
  const reserve_to = flatpickr('#reserve_to',flatpickr_options);
  
  if (form.data('errors')) {
    form[0].scrollIntoView();
  }
  
  $('#calendar-overlay-btn').on('click', function(e) {
    e.preventDefault();    
    let overlay = $(this).closest('.calendar-overlay').remove();    
    
    $.ajax('/calendar_usage_ok',{
      method: 'POST',
      data: {
        _token: $('meta[name=csrf-token]').attr('content')
      }
    });
  });

  if ($('#reserve_from').val() !== '' && $('#reserve_to').val() !== '') {   
    let start = moment.utc($('#reserve_from').val());
    let end = moment.utc($('#reserve_to').val());
    createSelectionEvent(start, end);
  } 
  
  function handleFlatpickrChange(dates, dateStr, instance) {
    let from_dt = reserve_from.selectedDates[0];
    let to_dt = reserve_to.selectedDates[0];    
    
    if(!from_dt || !to_dt) {
      return;
    }
    
    let start_tmp = moment(from_dt);
    let start = moment.utc(start_tmp.format('YYYY-MM-DD HH:mm'))
    let end_tmp = moment(to_dt);
    let end = moment.utc(end_tmp.format('YYYY-MM-DD HH:mm'))        
    createSelectionEvent(start,end);    
  }
  
  function dateClickHandler(info) {       
    let start = moment.utc(info.dateStr);
    let end = moment.utc(start).add(2,'h');
    
    // check if reservation is even possible - let it happen but show error
    if(checkIfReserved(start, end)) {
      return;
    }
    
    let now = moment.utc();
    if(start.isBefore(now)){
    	return;
    }
    
    // reset selection
    setInquiry(false);    
    let old_selection = calendar.getEventById('selection');
    if(old_selection) {
      old_selection.remove();
    }
    
    if(!checkIfAvailable(start, end)) {
      // warning?
    }
      
    createSelectionEvent(start, end);
    setFormFields(start, end);
  }
  
  function createSelectionEvent(start, end) {
    let old_selection = calendar.getEventById('selection');
    if(old_selection) {
      old_selection.remove();
    }
    
    let event = {
        id: 'selection',
        start: start.format(),
        end: end.format(),
        editable: true,
        durationEditable: true,
        eventStartEditable: true,
        classNames: ['cal-evt-pending']
    }
    
    if(!first_call) {
      let request_data = {
          reserve_from: start.format('YYYY-MM-DD HH:mm'),
          reserve_to: end.format('YYYY-MM-DD HH:mm')
      };
      if (reservation_id) {
        request_data.reservation_id = reservation_id;
      }
      
      $.ajax({
        url: '/api/check_selection/' + bike_id,
        cache: false,
        data: request_data,
        dataType: 'json',
        success: function(data) {
          if(data.possible === true){
            reservation_possible = true;
            error_div_text.text('');
            error_div.slideUp();
          }else{
            reservation_possible = false;
            error_div_text.text(data.possible);
            error_div.slideDown();
          }
          
          let old_selection = calendar.getEventById('selection');
          if(old_selection) {
            old_selection.remove();
          }
          if(!reservation_possible){
            event.color = 'red';
          }
          calendar.addEvent(event);
        },      
        error: function(xhr) {
        }   
      });
    }
    
    if(first_call){
      first_call = false;
      if(error_div_text.text().length > 0){
        event.color = 'red';
        error_div.show();
      }
    }
    
    calendar.addEvent(event);
  }
  
  function checkIfReserved(start, end) {
    for (let i = 0; i < reservations.length; i++) {
      let reservation = reservations[i];
      if(start.isBetween(reservation.start, reservation.end, null,'()') || 
           end.isBetween(reservation.start, reservation.end, null,'()')) 
      {
        return true;
      }
    }
    return false;
  }
  
  function checkIfAvailable(start, end) {        
    let start_date = start.format(date_format);
    let end_date = end.format(date_format);
    if (!available[start_date] || !available[end_date]) {
      return false;
    }           
    let found_start = false;    
    let found_end = false;           
    let last_day = moment.utc(end_date);
    for (let current_day = moment.utc(start_date); current_day.isSameOrBefore(last_day); current_day.add(1,'d')) {      
      let day_events = available[current_day.format(date_format)];
      for (let i = 0; i < day_events.length;i++) {
        let day_event = day_events[i];
        if(!found_start && start.isBetween(day_event.start, day_event.end, null, '[]')) {
          found_start = true;
        }
        
        if(!found_end && end.isBetween(day_event.start, day_event.end, null, '[]')) {
          found_end = true;
        }        
        
        if(!inquiry && rental_modes[day_event.rental_mode] == 'INQUIRY') {
          if (!(day_event.end.isBefore(start) || day_event.start.isAfter(end))) {
            setInquiry(true);
          }                    
        }        
        
        if (inquiry && found_start && found_end) {
          break;
        }
      }
    }
    return found_start && found_end;
  }
  
  function handleEventResizeDrop(info) {    
    let start = moment.utc(info.event.start);
    let end = moment.utc(info.event.end);
    
    setFormFields(start, end);
    createSelectionEvent(start, end);
  }
  
  function setInquiry(is_inquiry) {
    inquiry = is_inquiry;
    btn_inquiry.prop('hidden',!is_inquiry);
    btn_reserve.prop('hidden',is_inquiry);
  }
  
  function setFormFields(start, end) {
    reserve_from.setDate(start.format('YYYY-MM-DD HH:mm'),false,'Y-m-d H:i');
    reserve_to.setDate(end.format('YYYY-MM-DD HH:mm'),false,'Y-m-d H:i');
  }
  
  function checkInquiry(event) {
    if(inquiry) {
      return;
    }
    let event_mode = event._def.extendedProps.rental_mode;
    if (rental_modes[event_mode] == 'INQUIRY') {
      setInquiry(true);      
    }    
  }
  
  function eventOverlap(event) {      
    return event.rendering === 'background';
  }
  
  function eventAllow(selectInfo) {    
    setInquiry(false);
    let start = moment.utc(selectInfo.startStr);
    let end = moment.utc(selectInfo.endStr); 
    
    return checkIfAvailable(start, end);
  }
  function getReservations(fetchInfo, successCallback, failureCallback) {     
    let start = moment.utc(fetchInfo.startStr);   
    let end = moment.utc(fetchInfo.endStr);    
    let request_data = {
      start: start.format(date_format),
      end: end.format(date_format)
    };
    
    if (reservation_id) {
      request_data.reservation_id = reservation_id;
    }
    
    $.ajax({
      url: '/api/reservations/' + bike_id,
      cache: false,
      data: request_data,
      dataType: 'json',
      success: function(data) { 
        for(let i = 0; i < data.length; i++) {
          let event = {
            start: moment.utc(data[i].start),
            end: moment.utc(data[i].end)
          };
          reservations.push(event);
        }
        successCallback(data);
      },      
      error: function(xhr) {
        failureCallback(xhr.responseJSON.message); 
      }   
    });
  } 
  function getRentalPeriods(fetchInfo, successCallback, failureCallback) {
    let start = moment.utc(fetchInfo.startStr);   
    let end = moment.utc(fetchInfo.endStr);    
    let start_date = start.format('YMMDD');    
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
        let min_time = null, max_time = null;
        for (let i = 0; i < data.length; i++) {
          let event = data[i];
          event.rendering = 'background';
          let class_name = 'cal-evt-';
          if (event.restricted === true) {
            class_name += 'restricted text-white';
          } else if (rental_modes[event.rental_mode] == 'INQUIRY') {
            class_name += 'inquiry text-white';
          } else {
            class_name += 'instant text-white';
          }
          /*if (event.no_interrupt === true) {
            class_name += ' cal-evt-no-interrupt';
          }*/
          
          event.className = class_name;
          let dt_from = moment.utc(event.start);
          let dt_to = moment.utc(event.end);
          let dt_from_date = dt_from.format(date_format);          
          if(!available[dt_from_date]) {
            available[dt_from_date] = [];
          }
          
          available[dt_from_date].push({
            start: dt_from,
            end: dt_to,
            rental_mode: event.rental_mode
          });
          
          events.push(event);
          
          // compute min/max time
          let start_time = dt_from.minutes() + dt_from.hours()*60;
          let end_time = dt_to.minutes() + dt_to.hours()*60;
          if(min_time == null || min_time > start_time) {
            min_time = start_time;
          }
          if(max_time == null || max_time < end_time) {
            max_time = end_time;
          }
        }       
        successCallback(events);
                
        if(min_time == null || max_time == null) {
          return;
        }
        
        let min_h = Math.floor(min_time / 60);
        let min_m = min_time - (60*min_h);                               
        if(min_h > 1) {
          min_h--;
        }
        
        let moment_min = moment.utc(`${min_h} ${min_m}`,'H m');
        
        let max_h = Math.floor(max_time / 60);
        let max_m = max_time - (60*max_h);
        if(max_h < 23) {
          max_h++;
        }
        
        let moment_max = moment.utc(`${max_h} ${max_m}`,'H m');
        
        moment_min.startOf('hour');
        if(max_m > 0) {
          moment_max.endOf('hour');
        }                
        
        time_constraints[start_date] = {
          min: moment_min,
          max: moment_max
        }      
        calendar.render();
//        calendar.setOption('minTime',moment_min.format('HH:mm:ss'));
//        calendar.setOption('maxTime',moment_max.format('HH:mm:ss'));
      },
      error: function(xhr) {
        failureCallback(xhr.responseJSON.message); 
      }             
    });
  }
});