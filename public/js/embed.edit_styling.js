'use strict';
$(function() {
  const form_simple = $('#form_simple');  
  
  initSimple();
  
  function initSimple() {
    let color_body = $('#color_body');
    let color_primary = $('#color_primary');
    
    if (color_body.attr('value') == '') {      
      let color = $('body').css('color');
      color_body.attr('value',colorToHex(color));
    }
    
    if (color_primary.attr('value') == '') {      
      let color = $('.btn-primary').css('background-color');
      color_primary.attr('value',colorToHex(color));
    }   
    
  }
  
  function componentToHex(c) {
    var hex = c.toString(16);
    return hex.length == 1 ? "0" + hex : hex;
  }  
  
  function colorToHex(color) {
    if (_.startsWith(color,'rgb')) {
      color = _.chain(color)
      .replace('rgb(','')
      .replace(')','')
      .split(',')
      .forEach(function(value, key, arr) {
        arr[key] = componentToHex(_.parseInt(value));          
      })
      .join('')
      .padStart(7,'#')
      .value();      
    }
    return color;
  }
  
});
