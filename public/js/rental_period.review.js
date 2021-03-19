$(function() {
  const locale = $('html').attr('lang');  
  const calendar_div = $('#calendar');  
  const fa_plus = $('<span></span>').addClass('fas fa-plus');
  const fa_edit = $('<span></span>').addClass('fas fa-edit');
  const fa_trash = $('<span></span>').addClass('fas fa-trash')
  const template_event_btns = $('<div></div>').addClass('calendar-context-btns text-right')
    .append($('<a></a>').attr('href','#').addClass('calendar-context-btn-add calendar-context-btn-instant').append(fa_plus))
    .append($('<a></a>').addClass('calendar-context-btn-edit').append(fa_edit))
    .append($('<a></a>').attr('href','#').addClass('calendar-context-btn-del calendar-context-btn-instant').append(fa_trash));
  
  const bike_id = calendar_div.data('bike-id');
  const form = $('#exception-form');  
  const exception_url = calendar_div.data('exception-url');
  const default_date = calendar_div.data('date');   
  const rental_modes = {};   
  const legend = $('#calendar-legend');  
  legend.find('li.rental-mode').each(function() {
    let item = $(this);    
    rental_modes[item.data('mode-id')] = item.data('mode-name');
  });
  const date_format = 'Y-MM-DD';
  const calendar_cfg = {
    plugins: ['timeGrid','bootstrap','moment'],
    themeSystem: 'bootstrap',
    timeZone: 'UTC',
    locale: locale,
    allDaySlot: false,
    contentHeight: "auto",
    displayEventTime: false,
    eventSources: [
      {
        events: getRentalPeriods
      } 
    ],
    eventRender: onEventRender,
  };
  if (default_date) {
    calendar_cfg.defaultDate = default_date;
  }
  const calendar = new FullCalendar.Calendar(calendar_div[0],calendar_cfg);  
  
  calendar.render();
  
  calendar_div.on('click','.calendar-context-btn-instant',function (e) {
    e.preventDefault();
    let item = $(this);
    let data = item.data('data');
        
    for (let field in data) {
      form.find('[name=' + field).attr('value',data[field]);
    }
    form.submit();
  });
  
  function getRentalPeriods(fetchInfo, successCallback, failureCallback) {
    let start = moment.utc(fetchInfo.startStr);    
    let end = moment.utc(fetchInfo.endStr);    
    let today = moment.utc();
    if (end.isBefore(today)) {
      successCallback([]);
    }
    
    if (start.isBefore(today)){
      start = today;
    }
    
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
        let temp = [];
        let new_events = [];
        // prepare event  map
        for(let i = start.clone(); i.isBefore(end); i.add(1, 'd')) {      
          temp[i.format(date_format)] = {            
            events: []
          };
        }     
        // push the regular events
        for (let i = 0; i < data.length; i++) {          
          let event = data[i];                            
          let class_name = 'cal-evt-';
          if (rental_modes[event.rental_mode] == 'INQUIRY') {
            class_name += 'inquiry';
          } else {
            class_name += 'instant';
          }
          event.className = class_name;
          let e_start = moment.utc(event.start);          
          let e_end = moment.utc(event.end);
          if (!temp[e_start.format(date_format)]) {
            continue;
          }        
          
          event.event_type = 'set';
          events.push(event);
          evt2 = {
            start: e_start,
            end: e_end
          }
          temp[evt2.start.format(date_format)].events.push(evt2);          
        }   
        
        // add full day not set events and sort other events
        for (let date = start.clone(); date.isBefore(end); date.add(1, 'd')) {    
          let i = date.format(date_format);
          if (!temp[i]) {
            continue;
          }
          
          if (!temp[i].events.length) {            
            new_events.push({
              start: i + 'T00:00:00',
              end: i + 'T23:59:00',                      
            });
          }
          
          temp[i].events.sort(function(a,b) {
            return (a.start.isSameOrAfter(b.start) ? 1 : -1);               
          });
        }
        for (let date = start.clone(); date.isBefore(end); date.add(1, 'd')) {
          let i = date.format(date_format)
          if (!temp[i] || !temp[i].events.length) {
            continue;
          }
          
          let day_events = temp[i].events;
          if (day_events[0].start.format('HH:mm') != '00:00') {           
            new_events.push({
              start: date.format(date_format) + 'T00:00:00',
              end: day_events[0].start.format('Y-MM-DD\THH:mm'),      
            });
          }
          for (let j = 0; j < day_events.length - 1; j++) {
            let curr = temp[i].events[j];
            let next = temp[i].events[j+1];
            
            if (curr.end.isSame(next.start)) {
              continue;
            }           
            
            new_events.push({
              start: date.format(date_format) + 'T' + curr.end.format('HH:mm'),
              end: date.format(date_format) + 'T' + next.start.format('HH:mm'), 
            });
          }
                    
          let last_index = day_events.length - 1;
          if (day_events[last_index].end.format('HH:mm') != '23:59') {            
            new_events.push({
              start: day_events[last_index].end.format('Y-MM-DD\THH:mm'),
              end: date.format(date_format) + 'T23:59',        
            });
          }                   
        }
            
        for (let i = 0; i < new_events.length; i++) {
          let event = new_events[i];
          event.event_type = 'not_set';
          event.className = 'cal-evt-blocked';
          events.push(event);
        }
        successCallback(events);
      },
      error: function(xhr) {
        failureCallback(xhr.responseJSON.message); 
      }             
    });
  }
  
  function onEventRender(info) {
    let element = $(info.el);
    let event = info.event;
    let start = moment.utc(event.start);
    let end = moment.utc(event.end);
    
    let content = element.children('.fc-content');        
    let event_btns = template_event_btns.clone();    
    let edit_base = exception_url + '?date=' + start.format(date_format);
    let instant_data = {
      date: start.format(date_format),
      time_from: start.format('HH:mm'),
      time_to: end.format('HH:mm')      
    }    
    
    let btn_edit = event_btns.children('.calendar-context-btn-edit');                             
    let btn_del = event_btns.children('.calendar-context-btn-del');
    let btn_add = event_btns.children('.calendar-context-btn-add');
      
    btn_edit.attr('href',edit_base);
    if (event._def.extendedProps.event_type == 'not_set') {          
      instant_data["delete"] = 0;      
      btn_add.data('data',instant_data);       
      btn_del.remove();     
    } else {            
      instant_data["delete"] = 1;    
      btn_del.data('data',instant_data);
      btn_add.remove();
    }
    
    content.prepend(event_btns);            
  }
});
