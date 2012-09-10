<?php
/*
	COPYRIGHT 2009 Michael J Goonawardena
		
	This file is part of ConSof Alternate Login.

    ConSof Alternate Login is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    ConSof Alternate Login is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with ConSof Alternate Login.  If not, see <http://www.gnu.org/licenses/>.*/

// Basic setup of phpBB variables.
define('IN_PHPBB', true);
$phpbb_root_path = (defined('PHPBB_ROOT_PATH')) ? PHPBB_ROOT_PATH : '../';
$phpEx = substr(strrchr(__FILE__, '.'), 1);

// Load include files.
include($phpbb_root_path . 'common.' . $phpEx);
include($phpbb_root_path . 'includes/functions_user.' . $phpEx);
include($phpbb_root_path . 'includes/functions_profile_fields.' . $phpEx);
include($phpbb_root_path . 'includes/functions_alternatelogin.' . $phpEx);// Add the custom phpBB Alternate Login functions file
include($phpbb_root_path . 'alternatelogin/twitter/tmhOAuth.' . $phpEx);

if($config['al_tw_login'] == 0)
{
	// Inform the user that this feature is unavailable
	trigger_error(sprintf($user->lang['AL_LOGIN_UNAVAILABLE'], $user->lang['WINDOWS_LIVE']));
}


// Set up a new user session.
$user->session_begin();
$auth->acl($user->data);
$user->setup('ucp'); 
$user->add_lang('mods/info_acp_alternatelogin');
$user->add_lang('mods/info_ucp_alternatelogin');// Global Alternate Login language file.

$tmhOAuth = new tmhOAuth(array(
  'consumer_key'    => $config['al_tw_key'],
  'consumer_secret' => $config['al_tw_secret'],
));

$here = php_self();

$authentication = request_var('authentication', 0);
$oauth_verifier = request_var('oauth_verifier', '');

if($authentication)
{
    $params = array(
        'oauth_callback'     => $callback,
        'x_auth_access_type'    => 'read',
    );
    
    $code = $tmhOAuth->request('POST', $tmhOAuth->url('oauth/request_token', ''), $params);

    if ($code == 200) {
        $access_token = $tmhOAuth->extract_params($tmhOAuth->response['response']);
        $access_token['authenticated'] = 1;
        $sql_array = array(
            'session_tw_access_token'   => json_encode($access_token),
        );
        
        $sql = "UPDATE " . SESSIONS_TABLE . " SET " . $db->sql_build_array('UPDATE', $sql_array) . " WHERE session_id='" . $user->data['session_id'] . "'";

        $db->sql_query($sql);
        
        $authurl = $tmhOAuth->url("oauth/authenticate", '') .  "?oauth_token={$access_token['oauth_token']}";
        
        header("Location: " . $authurl);
    } 
    else 
    {
        trigger_error($tmhOAuth->response['response']);
    }
}
elseif($oauth_verifier !== '')
{
    $access_token = json_decode($user->data['session_tw_access_token']);
    
    $tmhOAuth->config['user_token']  = $access_token->oauth_token;
    $tmhOAuth->config['user_secret'] = $access_token->oauth_token_secret;

    $code = $tmhOAuth->request('POST', $tmhOAuth->url('oauth/access_token', ''), array(
    'oauth_verifier' => $oauth_verifier,
    'oauth_token'    => $access_token,
    ));

    if ($code == 200) {
        $access_token = $tmhOAuth->extract_params($tmhOAuth->response['response']);
        $access_token['verified'] = 1;
        $sql_array = array(
            'session_tw_access_token'   => json_encode($access_token),
        );

        $sql = "UPDATE " . SESSIONS_TABLE . " SET " . $db->sql_build_array('UPDATE', $sql_array) . " WHERE session_id='" . $user->data['session_id'] . "'";

        $db->sql_query($sql);
        
        header("Location: {$here}");
    } 
    else 
    {
        trigger_error($tmhOAuth->response['response']);
    }
}
$access_token = json_decode($user->data['session_tw_access_token']);
$tmhOAuth = new tmhOAuth(array(
  'consumer_key'    => $config['al_tw_key'],
  'consumer_secret' => $config['al_tw_secret'],
));
$tmhOAuth->config['user_token']  = $access_token->oauth_token;
$tmhOAuth->config['user_secret'] = $access_token->oauth_token_secret;

$code = $tmhOAuth->request('GET', $tmhOAuth->url('1/account/verify_credentials'));

if ($code == 200) {
    $tw_user = json_decode($tmhOAuth->response['response']);

} 
else 
{
    trigger_error($tmhOAuth->response['response']);
}

// Select the user_id from the Alternate Login user data table which has the same Facebook Id.
$sql_array = array(
    'SELECT'    => 'user_id, username',
    'FROM'      => array(
        USERS_TABLE => 'u',
        ),
    'WHERE'     => "al_tw_id='" . $tw_user->id . "'",
);

$sql = $db->sql_build_query('SELECT', $sql_array);


// Execute the query.
$result = $db->sql_query($sql);

// Retrieve the row data.
$row = $db->sql_fetchrow($result);

// Free up the result handle from the query.
$db->sql_freeresult($result);

// Check to see if we found a user_id with the associated Facebook Id.
if ($row)   // User is registered already, let's log him in!
{
	$old_session_id = $user->session_id;

        if ($admin)
        {
                global $SID, $_SID;

                $cookie_expire = time() - 31536000;
                $user->set_cookie('u', '', $cookie_expire);
                $user->set_cookie('sid', '', $cookie_expire);
                unset($cookie_expire);

                $SID = '?sid=';
                $user->session_id = $_SID = '';
        }

        
        $admin = false;
		$autologin = true;
		$viewonline = true;
        $result = $user->session_create($row['user_id'], $admin, $autologin, $viewonline);
		
        
        // Successful session creation
        if ($result === true)
        {
                // If admin re-authentication we remove the old session entry because a new one has been created...
                if ($admin)
                {
                        // the login array is used because the user ids do not differ for re-authentication
                        $sql = 'DELETE FROM ' . SESSIONS_TABLE . "
                                WHERE session_id = '" . $db->sql_escape($old_session_id) . "'
                                AND session_user_id = {$row['user_id']}";
                        $db->sql_query($sql);
                }
                $sql_array = array(
                    'session_tw_access_token'   => json_encode($access_token),
                );

                $sql = "UPDATE " . SESSIONS_TABLE . " SET " . $db->sql_build_array('UPDATE', $sql_array) . " WHERE session_id='" . $user->data['session_id'] . "'";

                $db->sql_query($sql);

                $data = array();
                // Update the stored data such as profile and signatures.  Avatar is a dynamic field and doesn't require changing.


                if($user->data['al_tw_profile_sync'])
                {

                    $data['user_from']                      = (!$tw_user->location) ? '' : $tw_user->location;
                    //$data['user_occ']                       = $wl_user->work[0]->employer->name;
                    $data['user_website']                      = (!$tw_user->url) ? '' : $tw_user->url;
                    //$data['user_birthday']                  = sprintf('%2d-%2d-%4d', $wl_user->birth_day, $wl_user->birth_month, $wl_user->birth_year);


                }

                if($user->data['al_tw_status_sync'])
                {
                    include($phpbb_root_path . 'includes/message_parser.' . $phpEx);

                    $signature = $tw_user->status->text;

                    $enable_bbcode                      = ($config['allow_sig_bbcode']) ? (bool) $user->optionget('sig_bbcode') : false;
                    $enable_smilies                     = ($config['allow_sig_smilies']) ? (bool) $user->optionget('sig_smilies') : false;
                    $enable_urls                        = ($config['allow_sig_links']) ? (bool) $user->optionget('sig_links') : false;

                    $message_parser = new parse_message($signature);

                    // Allowing Quote BBCode
                    $message_parser->parse($enable_bbcode, $enable_urls, $enable_smilies, $config['allow_sig_img'], $config['allow_sig_flash'], true, $config['allow_sig_links'], true, 'sig');

                    $data['user_sig']                   = (string) $message_parser->message;
                    $data['user_options']               = $user->data['user_options'];
                    $data['user_sig_bbcode_uid']	= (string) $message_parser->bbcode_uid;
                    $data['user_sig_bbcode_bitfield']	= $message_parser->bbcode_bitfield;
                }

                if($user->data['al_tw_avatar_sync'])
                {
                    $data['user_avatar'] = $tw_user->profile_image_url;
                }

                if($user->data['al_tw_profile_sync'] | $user->data['al_tw_status_sync'] | $user->data['al_tw_avatar_sync'])
                {
                    $sql = 'UPDATE ' . USERS_TABLE . '
                            SET ' . $db->sql_build_array('UPDATE', $data) . '
                            WHERE user_id = ' . $user->data['user_id'];

                    $db->sql_query($sql);
                }
                meta_refresh(5, "{$phpbb_root_path}index.{$phpEx}");
                trigger_error(sprintf($user->lang['LOGIN_SUCCESS'] . "<br /><br />" . sprintf($user->lang['RETURN_INDEX'], "<a href='{$phpbb_root_path}index.php'>", "</a>")));

        }
	else
	{
		trigger_error($user->lang['LOGIN_FAILURE']);
	}
        
        

}
else
{
    
	// No user was registered with the associate Facebook Id.
	// We need to see if they are anonymous.
	// If they are then that means they might want to register.
	// We will check to see if they wish to register.
	if($user->data['user_id'] == ANONYMOUS)
	{

		if(confirm_box(true))
		{
			redirect(append_sid("{$phpbb_root_path}ucp.$phpEx?mode=register"));
		}
		else
		{
                    
			confirm_box(false, sprintf($user->lang['AL_REGISTER_QUERY'], $user->lang['TWITTER']));
			// They said no so send them to the home page.
                        
			redirect(append_sid("{$phpbb_root_path}index.$phpEx"));
		}
	}
	else
	{
		// If they are not anonymous then we can assume they are current users wishing
		// to link their accounts.

		

		// Did we get data, if yes then the user has another account registered.
		// We need to unlink that account as well.
                $sql_array = array(
                    'al_fb_id'      => 0,
                    'al_wl_id'      => 0,
                    'al_tw_id'      => $tw_user->id,
                    'al_oi_id'      => 0,
                );

                // Prepare the query to update the users Alternate Login record.
                $sql = 'UPDATE ' . USERS_TABLE
                . " SET " . $db->sql_build_array('UPDATE', $sql_array)
                . " WHERE user_id='{$user->data['user_id']}'";


                // Execute the query.
		$result = $db->sql_query($sql);

                if(!$result)
		{
			trigger_error($user->lang['AL_PHPBB_DB_FAILURE']);
		}

                $sql_array = array(
                    'user_password' => phpbb_hash($tw_user->id . $config['al_tw_key'] . $config['al_tw_secret']),
                );

                $sql = "UPDATE " . USERS_TABLE .
                        " SET " . $db->sql_build_array('UPDATE', $sql_array) .
                        " WHERE user_id=" . (int)$user->data['user_id'];



		// Execute the query.
		$result = $db->sql_query($sql);

		// Tell the user if they suceeded or not.
		if(!$result)
		{
			trigger_error($user->lang['AL_PHPBB_DB_FAILURE']);
		}
		else
		{
			trigger_error(sprintf($user->lang['AL_LINK_SUCCESS'], $user->lang['TWITTER'], $user->lang['TWITTER']));
		}
	}

}

?>