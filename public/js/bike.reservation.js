'use strict';
$(function() {
  const reservation_buttons =  $('#reservation-buttons');
  const show_reservation_cancel = $('#show-reservation-cancel');
  const reservation_cancel = $('#reservation-cancel');
  const reservation_cancel_no = $('#reservation-cancel-no');
  
  reservation_cancel.hide().removeAttr('hidden');
  
  show_reservation_cancel.click(function() {
    reservation_buttons.hide();
    reservation_cancel.show();
  });
  
  reservation_cancel_no.click(function() {
    reservation_cancel.hide();
    reservation_buttons.show();
  });
});
