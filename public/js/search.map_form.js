'use strict';
$(function() {    
  $(".slider-container.slider-single").each(function() {
    initSlider($(this));
  });
        
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
