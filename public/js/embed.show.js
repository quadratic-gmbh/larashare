'use strict';
$(function() {
  $('#embed-options').on('change', function() {    
    $('.embed-code.active').removeClass('active');
    $('#embed-code-' +  this.value).addClass('active');
  });
  
  $('.copy-btn').on('click', function() {
    $(this).closest('.copy-container').find('.copy-text').select();
    document.execCommand('copy');
  });
});
