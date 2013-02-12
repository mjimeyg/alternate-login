function is_user_logged_in(){
	$.getJSON('alternatelogin/ajax/get_user.php', function(data){
		if(data.user_id == 0){
			return false;
		}else{
			return true;
		}
	});
}

function log_user_in(access_token)
{
	$.ajax({
		url:'./alternatelogin/ajax/log_user_in.php',
		processData: false,
		data: {access_token : access_token},
		success: function(data){
			console.log('phpBB user logged in.');
			return true;
		},
		error: function(data){
			console.log('phpBB user login FAILURE.');
			return false;
		}
	});
}

function is_user_registered(al_user_id, login_type){
	var dataObj = {
			'al_id' : al_user_id,
			'al_type' : login_type
		};
		console.log(dataObj);
	var ret_val = false;
	$.ajax({
		
		url:'./alternatelogin/ajax/is_user_registered.php',
		dataType: 'json',
		async: false,
		data: dataObj,
		success: function(data){
			console.log(data);
			if(data.status == 0){
				console.log('phpBB user NOT registered.');
				ret_Val = false;
			}else{
				console.log('phpBB user registered.');
				ret_val = true;
			}
			
		},
		error: function(data){
			var jdata = $.parseJSON(data);
			//show_dialog('Error!', jdata.error_message);
			console.log(data);
			ret_val = false;
		}
	});
	
	return ret_val;
}

function is_email_valid(e_mail)
{
	$.ajax({
		url:'./alternatelogin/ajax/is_email_valid.php',
		processData: false,
		data: {email : e_mail},
		dataType: 'json',
		async : false,
		success: function(data){
			console.log(data.status);
			if(data.status == 0){
				console.log('Invalid email: ' + data.message)
				show_dialog('Email Issue', data.message);
				return false;
			}else{
				console.log('Good Email');
				return true;
			}
			
		},
		error: function(data){
			console.log('phpBB user login FAILURE.');
			return false;
		}
	});
}

function fb_register_user(){
	FB.api('/me', function(response){
		var terms = '';
		var data_json = {
			'key': 'TERMS_OF_USE_CONTENT',
			'args' : {
				'sitename' : sitename,
				'board_url' : board_url
			}
		};
		$.ajax({
			type: 'post',
			url: './alternatelogin/ajax/get_language_entry.php',
			data: data_json,
			dataType: 'json',
			async: false,
			
			success: function(data){
				console.log('success');
				console.log(data);
				terms = data.string;
			},
			error: function(data){
				console.log('error');
				console.log(data);
				terms = data;
			}
		});
		var title = $('<h2>').html(sitename + ' - ' + terms_title);
		var terms = $('<span>').html(terms);
		var registration_plugin = $('<div>')
									.append(title)
									.append(terms)
									.append($('#fb_registration'));
		
		show_dialog('Registration', registration_plugin);
		
	});
}

function validate_fb_registration(form, cb){
	console.log('Validating registration form...');
	var error_occurred = false;
	var email_message;
	var username_message;
	$.ajax({
		url:'./alternatelogin/ajax/is_username_valid.php',
		processData: false,
		data: {username : form.username},
		dataType: 'json',
		async : false,
		success: function(data){
			console.log(data.status);
			if(data.status == 0){
				$.get('./alternatelogin/ajax/get_language_entry.php?key=' + data.message, function(data){
					console.log('Invalid username: ' + data);
					show_dialog(data);
				});
				error_occurred = true;
			}
			
		},
		error: function(data){
			console.log('phpBB username validation FAILURE.');
			return false;
		}
	});
	
	$.ajax({
		url:'./alternatelogin/ajax/is_email_valid.php',
		processData: false,
		data: {email : form.email},
		dataType: 'json',
		async : false,
		success: function(data){
			console.log(data);
			if(data.status == 0){
				console.log('Invalid email: ' + data);
				$.get('./alternatelogin/ajax/get_language_entry.php?key=' + data.message, function(data){
					console.log('Invalid email: ' + data);
					show_dialog(data);
				});
				error_occurred = true;
			}
			
		},
		error: function(data){
			console.log('phpBB email validation FAILURE.');
			return false;
		}
	});
	
	if(error_occurred){
		var error_string = "";
		
		if(username_message != 'undefined' && email_message != 'undefined'){
			cb({username: username_message}, {email: email_message});
			return;
		}
		
		if(username_message != 'undefined'){
			cb({username: username_message});
			return;
		}
		if(email_message != 'undefined'){
			cb({email: email_message});
			return;
		}
	}
	cb();
}

function fb_user_login(fb_id, email){
	FB.login(function(response) {
		if (response.authResponse) {
			console.log('Welcome!  Fetching your information.... ');
			FB.api('/me', function(response) {
				console.log('Good to see you, ' + response.name + '.');
			});
		
			// Check to see if the user isn't logged in for some bizarre reason.
			if(is_user_logged_in()){
				show_dialog('Login Error!', 'This user is already shown as logged in, a potential error has occurred, please try again or contact your Administrator.');
				return false;
			}
			
			// Make sure the FB user is registered
			var user_registered = is_user_registered(fb_id, 'al_fb_id');
			console.log(user_registered);
			//return;
			if(user_registered == false){
				console.log('FB user is not registered');
				
				// Check for existing useage of email and if appropriate ask if they want to register
				if(is_email_valid(email) == false){
					// Offer account linking
					console.log(ret);
					console.log('Email is NOT valid');	
					return;
				}
				console.log('Email is valid');
				// Offer registration
				fb_register_user();
				return;
			}
			else{
				window.location = './alternatelogin/al_fb_connect.php?return_page=' + window.location.pathname;
			}
			console.log('FB user is registered.');
		} else {
				console.log('User cancelled login or did not fully authorize.');
		}
	});
}

/**
*
*	Params:
*
*	buttons : 'ok', 'ok-cancel'
*
**/
function show_dialog(title, message, buttons, callback){
	console.log(message);
	var mask = $('#mask');
	
	var dialog = $('#modal_dialog');
	
	var dialog_panel = $('#modal_dialog_panel');
	
	var dialog_title = $('#modal_dialog_title');
	var dialog_message = $('#modal_dialog_message');
	
	var dialog_buttons = $('#modal_dialog_buttons');
	
	mask.css({
			'width' : $(window).width(),
			'height' : $(document).height()
		})
		.fadeIn(1000)
		.fadeTo('slow', 0.8);
	
	dialog_title.html(title);
	dialog_message.html(message);
	
	var dialog_x = $(window).width()/2-dialog.height()/2;
    var dialog_y = $(window).width()/2-dialog.width()/2;
	
	/*dialog.css({
		'top' : dialog_y,
		'left' : dialog_x
	});*/
	
	//dialog_panel.height(dialog.height() - 10);
	//dialog_panel.width(dialog.width() - 10);
	
	// Setup the dialog buttons
	switch(buttons){
		case 'ok/cancel':
			break;
			
		case 'ok':
		default:
			
		 break;
	}
	var ok_button = $('<input type="button" id="dialog_ok">')
								.attr({
									'value' : 'Ok'
								})
								
								.appendTo(dialog_buttons);
	
	ok_button.click(function(){
		if(typeof callback == "function"){
			callback();
		}
		close_dialog();
	});
	
	dialog.fadeIn(2000);
}

function close_dialog(){
	var mask = $('#mask');
	
	var dialog = $('#modal_dialog');
	
	dialog.hide('fast');
	
	mask.fadeOut('fast');
}