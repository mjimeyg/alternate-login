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
include_once($phpbb_root_path . 'includes/functions_user.' . $phpEx);
include($phpbb_root_path . 'includes/functions_profile_fields.' . $phpEx);
include_once($phpbb_root_path . 'includes/functions_alternatelogin.' . $phpEx);// Add the custom phpBB Alternate Login functions file

if($config['al_wl_login'] == 0)
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

$mode = request_var('mode', '');



if($user->data['user_id'] == ANONYMOUS)
{
    $code = request_var('code', '');
    
    
    if($code == '')
    {
        trigger_error($user->lang['AUTHORIZE_FAILURE']);
    }
    else
    {
        
        $token = get_wl_tokens($config['al_wl_client_id'], $code, null);
        
        $sql_array = array(
            'session_wl_access_token'   => $token->access_token,
        );
        
        $sql = "UPDATE " . SESSIONS_TABLE . " SET " . $db->sql_build_array('UPDATE', $sql_array) . " WHERE session_id='" . $user->data['session_id'] . "'";

        $db->sql_query($sql);
        
    }
}

if(($wl_user = get_wl_rest_request($token->access_token, 'me', HTTP_GET)) == NULL)
{
    trigger_error($user->lang['ERROR_REST_CALL_FAILURE']);
}

// Select the user_id from the Alternate Login user data table which has the same Facebook Id.
$sql_array = array(
    'SELECT'    => 'user_id, username',
    'FROM'      => array(
        USERS_TABLE => 'u',
        ),
    'WHERE'     => "al_wl_id='" . $wl_user->id . "'",
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
                    'session_wl_access_token'   => $token->access_token,
                );

                $sql = "UPDATE " . SESSIONS_TABLE . " SET " . $db->sql_build_array('UPDATE', $sql_array) . " WHERE session_id='" . $user->data['session_id'] . "'";

                $db->sql_query($sql);

                $data = array();
                // Update the stored data such as profile and signatures.  Avatar is a dynamic field and doesn't require changing.

                if(isset($wl_user->addresses->personal->city) && isset($wl_user->addresses->personal->region))
                {
                    $user_locale = $wl_user->addresses->personal->city . ', ' . $wl_user->addresses->personal->region;

                }
                elseif(isset($wl_user->addresses->personal->city))
                {
                    $user_locale = $wl_user->addresses->personal->city;

                }
                elseif(isset($wl_user->addresses->personal->region))
                {
                    $user_locale = $wl_user->addresses->personal->region;

                }
                if($user->data['al_wl_profile_sync'])
                {

                    $data['user_from']                      = (!$user_locale) ? '' : $user_locale;
                    $data['user_occ']                       = (!$wl_user->work[0]->employer->name) ? '' : $wl_user->work[0]->employer->name;

                    $data['user_birthday']                  = sprintf('%2d-%2d-%4d', $wl_user->birth_day, $wl_user->birth_month, $wl_user->birth_year);


                }



                if($user->data['al_wl_profile_sync'])
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

$sql = 'SELECT user_id, username, user_password, user_passchg, user_pass_convert, user_email, user_type, user_login_attempts
		FROM ' . USERS_TABLE . "
		WHERE user_email = '" . mysql_escape_string($wl_user->emails->preferred) . "'";
        

// Execute the query.
$result = $db->sql_query($sql);

// Retrieve the row data.
$row = $db->sql_fetchrow($result);

// Free up the result handle from the query.
$db->sql_freeresult($result);

if($row)
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

        $result = $user->session_create($row['user_id'], $admin, $autologin, $viewonline);

        // Store the access token for use with this session.
        $sql_array = array(
            'session_wl_access_token'   => $token->access_token,
        );

        $sql = "UPDATE " . SESSIONS_TABLE . " SET " . $db->sql_build_array('UPDATE', $sql_array) . " WHERE session_id='" . $user->data['session_id'] . "'";

        $db->sql_query($sql);
        
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
                    'al_fb_id'      => 0,
                    'al_wl_id'      => $wl_user->id,
                    'al_tw_id'      => 0,
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
                else
		{
			trigger_error(sprintf($user->lang['LOGIN_SUCCESS'] . "<br /><br />" . sprintf($user->lang['RETURN_INDEX'], "<a href='{$phpbb_root_path}index.php'>", "</a>")));
		}
        }
        else
        {
            trigger_error($user->lang['LOGIN_FAILED']);
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
		
            redirect(append_sid("{$phpbb_root_path}ucp.$phpEx?mode=register"));
		
	}
	
	

}

?>