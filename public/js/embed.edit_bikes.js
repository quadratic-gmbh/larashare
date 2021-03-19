'use strict';
$(function() {
  const table_search = $('#table-search');
  const bike_table = $('#bike-table tbody tr');
  
  table_search.on('input', function (){
    if(!table_search.val().length){
      bike_table.show();
      return;
    }
    bike_table.each(function(index, element){
      if ($(this).text().toLowerCase().includes(table_search.val().toLowerCase())){
        $(this).show();
      }else{
        $(this).hide();
      }
    });
  });

});
