'use strict';
$(function() {
	const container = $('#chat-main-container');
	const form = container.find('#chat-form');
	const max_length = 500;
	const chat_message_length = form.find('#chat-message-length');
	const chat_message = form.find('#chat_message');
	const save_button = form.find('#save-button');
	
	chat_message.on('input', function(){
		let current_length = max_length - $(this).val().length;
		chat_message_length.text(current_length);
		if(current_length < 0){
			save_button.prop('disabled', true);
			chat_message_length.css('color', 'red');
		}else if(current_length == max_length){
			save_button.prop('disabled', true);
			chat_message_length.css('color', '');
		}else{
			save_button.prop('disabled', false);
			chat_message_length.css('color', '');
		}
	});
	
	let message = container.find('.chat-message-new').filter(":first");
	if(!message.length){
		message = container.find('.chat-message-old').filter(":last");
	}
	if(message.length){
		window.location.hash = message[0].closest('.row').id;
	}
	
	let echo = new window.Echo({
	    broadcaster: 'socket.io',
	    host: window.location.hostname + ':443'
	});
	
	let do_refresh = false;
	
	echo.private(`chat.`+form.attr('data-chat-id'))
    .listen('ChatNewMessageEvent', (e) => {
    	console.log(e.message);
    	if(do_refresh === false){
    		do_refresh = true;
    		window.setTimeout(doRefresh, 10000);
    	}
    });
	
	function doRefresh(){
		console.log('actual refresh');
		do_refresh = false;
		location.reload();
	};
	
	form.on('submit', function(event){
		event.preventDefault();
		$.ajax({
			  type: "POST",
			  url: form.attr('action'),
			  data: form.serialize(),
			  success: function(){
			  },
		});
		form.trigger("reset");
		window.setTimeout(doRefresh, 3000);
	});
});
