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
	var dataObj = {
		'email' : e_mail
	};
	var ret_val = true;
	$.ajax({
		url:'./alternatelogin/ajax/is_email_valid.php',
		data: dataObj,
		dataType: 'json',
		async : false,
		success: function(data){
			console.log(data);
			
			ret_val = data;
			
			
		},
		error: function(data){
			console.log('phpBB user login FAILURE.');
			ret_val = false;
		}
	});
	return ret_val;
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
				
				var key = data.message;
				if(data.message == 'INVALID_CHARS'){
					key += '_USERNAME';
				}
				console.log(key);
				$.get('./alternatelogin/ajax/get_language_entry.php?key=' + key, function(data){
					console.log('Invalid username: ' + data);
					username = $.parseJSON(data).string;
				});
				error_occurred = true;
			}
			else
			{
				
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
					email_message = $.parseJSON(data).string;
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
				var valid_email = is_email_valid(email);
				console.log('Valid Email: ' + valid_email.status);
				if(valid_email.status == 0 && valid_email.message == 'EMAIL_TAKEN'){
					// Offer account linking
					if(duplicate_emails){
						show_dialog('Link accounts?', 'This account already exists.  Would you like to link it or create a new account?', [{'name':'Link Accounts'},{'name':'Create New Account'}]);
					}
					else{
						show_dialog('Link accounts?', 'This account already exists.  Would you like to link it?', [{'name':'Link Accounts'},{'name':'Cancel'}], link_accounts);
					}
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

function link_accounts(e){
	console.log('link_accounts() called.');
	
	
	
	if($(this).attr('id') == 'Link Accounts'){
		console.log('Link Accounts clicked.');
		verify_password('link');
	}
	else if($(this).attr('id') == 'Create New account'){
		console.log('Create new Account clicked.');
	}
	else{
		console.log('Cancel clicked.');
		close_dialog();
	}
}

function verify_password(action){
	console.log('verify_password()');
	var password_form = $('<form id="verify_password_form" method="post" action="' + './alternatelogin/al_fb_connect.php?action=' + action + '">');
	var fieldset = $('<fieldset>').appendTo(password_form);
	var dl = $('<dl>').appendTo(fieldset);
	var dd = $('<dd>').appendTo(dl);
	var label = $('<label>')
					.attr('for', 'password_field')
					.html('Please enter your board password:')
					.appendTo(dd);
	var dt = $('<dt>').appendTo(dl);
	var input = $('<input type="password">')
					.attr({
						
						'id' : 'password_field',
						'name' : 'password_field'
					})
					.appendTo(dt);
	
	show_dialog('Please enter your board password.', password_form, [{'name':'Submit'},{'name': 'Cancel'}], submit_password);
}

function submit_password(e){
	console.log('password_form() called.');
	
	if($(this).attr('id') == 'Submit'){
		console.log('Submit clicked.');
		$('#verify_password_form').submit();
	}
	else{
		console.log('Cancel clicked.');
		close_dialog();
	}
}

/**
*
*	Params:
*
*	buttons : json array [{'name':'Ok'},{'name':'Cancel'}]
*
**/
function show_dialog(title, message, buttons, callback){
	console.log(message);
	$('.modal_dialog_user_buttons').unbind('click');
	var mask = $('#mask');
	
	var dialog = $('#modal_dialog');
	
	var dialog_panel = $('#modal_dialog_panel');
	
	var dialog_title = $('#modal_dialog_title');
	var dialog_message = $('#modal_dialog_message');
	
	var dialog_buttons = $('#modal_dialog_buttons');
	
	var dialog_close_button = $("#modal_dialog_close");
	
	dialog_title.html('');
	dialog_message.html('');
	dialog_buttons.html('');
	
	mask.css({
			'width' : $(window).width(),
			'height' : $(document).height()
		})
		.fadeIn(300)
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
	if(typeof buttons !== "undefined"){
	$.each(buttons, function(key, value){
		$('<button type="button" id="' + value.name + '">')
									.html(value.name)
									.addClass('modal_dialog_user_buttons')
									.appendTo(dialog_buttons);
	});
	}
	console.log(typeof callback);
	if(typeof callback == "function"){
		console.log('callback called');
		$('.modal_dialog_user_buttons').bind('click', callback);
	}else{
		$('.modal_dialog_user_buttons').bind('click', close_dialog);
		console.log('default called');
	}

	
	dialog_close_button.click(function(e) {
        e.preventDefault();
		
		close_dialog();
    });
	
	dialog.fadeIn(2000);
}

function close_dialog(){
	$('.modal_dialog_user_buttons').unbind('click');
	
	var mask = $('#mask');
	
	var dialog = $('#modal_dialog');
	
	var dialog_title = $('#modal_dialog_title');
	var dialog_message = $('#modal_dialog_message');
	
	var dialog_buttons = $('#modal_dialog_buttons');
	
	
	
	dialog_title.html('');
	dialog_message.html('');
	dialog_buttons.html('');
	
	dialog.hide('fast');
	
	mask.fadeOut('fast');
}