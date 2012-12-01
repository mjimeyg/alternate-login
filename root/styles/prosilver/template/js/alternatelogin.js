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