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
include($phpbb_root_path . 'alternatelogin/openid/openid.' . $phpEx);

if($config['al_oi_login'] == 0)
{
	// Inform the user that this feature is unavailable
	trigger_error(sprintf($user->lang['AL_LOGIN_UNAVAILABLE'], $user->lang['OPENID']));
}


// Set up a new user session.
$user->session_begin();
$auth->acl($user->data);
$user->setup('ucp'); 
$user->add_lang('mods/info_acp_alternatelogin');
$user->add_lang('mods/info_ucp_alternatelogin');// Global Alternate Login language file.

$openid_identifier = request_var('openid_identifier', '');
$openid_email = request_var('openid_email', '');

$admin = request_var('admin', 0);
// Select the user_id from the Alternate Login user data table which has the same Facebook Id.
$sql_array = array(
    'SELECT'    => 'user_id, username',
    'FROM'      => array(
        USERS_TABLE => 'u',
        ),
    'WHERE'     => "al_oi_id='" . $openid_identifier . "'",
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
                meta_refresh(5, "{$phpbb_root_path}index.{$phpEx}");
                trigger_error(sprintf($user->lang['LOGIN_SUCCESS'] . "<br /><br />" . sprintf($user->lang['RETURN_INDEX'], "<a href='" . append_sid("{$phpbb_root_path}index.php") . "'>", "</a>")));
                
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
            
            $url = "{$phpbb_root_path}ucp.$phpEx?mode=register&openid_identifier=" . urlencode($openid_identifier);
            if($openid_email != '')
            {
                $url .= "&openid_email=" . urlencode($openid_email);
            }

            redirect(append_sid($url));
            return;
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
                    'al_oi_id'      => $openid_identifier,
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
                    'user_password' => phpbb_hash($openid_identifier . $config['board_startdate']),
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
			trigger_error(sprintf($user->lang['AL_LINK_SUCCESS'], $user->lang['OPENID'], $user->lang['OPENID']));
		}
	}

}

?>