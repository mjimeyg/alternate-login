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
include_once($phpbb_root_path . 'includes/functions_alternatelogin.' . $phpEx);	// Custom Alternate Login functions.

// Set up a new user session.
$user->session_begin();
$auth->acl($user->data);
$user->setup('ucp');
$user->add_lang('mods/info_acp_alternatelogin');	// Global Alternate Login language file.
$user->add_lang('mods/info_ucp_alternatelogin');


// Make sure that Facebook login is enabled for this site.
if($config['al_fb_login'] == 0)
{
	// Inform the user that this feature is unavailable
	trigger_error(sprintf($user->lang['AL_LOGIN_UNAVAILABLE'], $user->lang['FACEBOOK']));
}

$return_to_page = request_var('return_to_page', base64_encode("{$phpbb_root_path}index.{$phpEx}"));
$admin = request_var('admin', 0);

$access_token = get_fb_access_token($return_to_page);

if(!$access_token)
{
    add_log('critical', $user->data['user_id'], 'FB_ERROR_ACCESS_TOKEN');
    trigger_error($user->lang['FB_ERROR_ACCESS_TOKEN']);
}
$return_to_page = base64_decode($return_to_page);
$return_to_page = str_replace("../", "", $return_to_page);
$return_to_page = str_replace("./", "", $return_to_page);

// Store the access token for use with this session.
        $sql_array = array(
            'session_fb_access_token'   => $access_token,
        );

        $sql = "UPDATE " . SESSIONS_TABLE . " SET " . $db->sql_build_array('UPDATE', $sql_array) . " WHERE session_id='" . $user->data['session_id'] . "'";
        
        $db->sql_query($sql);
        
//echo 'token:' . print_r($token_url);
$graph_url = "https://graph.facebook.com/me?" . $access_token;


$fb_user = json_decode(get_fb_data($graph_url));

//echo("Hello " . $fb_user->name);
//print_r($fb_user);
// Check to see if we have a valid Facebook user.
if(!$fb_user)
{
    add_log('critical', $user->data['user_id'], 'FB_ERROR_USER');
    // Inform the user that we couldn't get their Facebook Id.
    trigger_error(sprintf($user->lang['FB_ERROR_USER'], $user->lang['FACEBOOK']));
}
$user->lang_name = substr($fb_user->locale, 0, 2);
// Select the user_id from the Alternate Login user data table which has the same Facebook Id.



$sql = 'SELECT user_id, username, user_password, user_passchg, user_pass_convert, user_email, user_type, user_login_attempts
		FROM ' . USERS_TABLE . "
		WHERE al_fb_id = " . $fb_user->id;
        

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

                // Store the access token for use with this session.
                $sql_array = array(
                    'session_fb_access_token'   => $access_token,
                );

                $sql = "UPDATE " . SESSIONS_TABLE . " SET " . $db->sql_build_array('UPDATE', $sql_array) . " WHERE session_id='" . $user->data['session_id'] . "'";

                $db->sql_query($sql);
                $data = array();
                // Update the stored data such as profile and signatures.  Avatar is a dynamic field and doesn't require changing.

                if($user->data['al_fb_profile_sync'])
                {

                    $graph_url = "https://graph.facebook.com/me?" . $access_token;

                    $fb_user = json_decode(get_fb_data($graph_url));

                    $data['user_website']                    = isset($fb_user->website) ? $fb_user->website : '';
                    $data['user_from']                   = isset($fb_user->location->name) ? $fb_user->location->name : '';
                    $data['user_occ']                 = isset($fb_user->work[0]->employer->name) ? $fb_user->work[0]->employer->name : '';
					if(isset($fb_user->birthday))
					{
						$bday = explode('/', $fb_user->birthday);
						$data['user_birthday']              = sprintf('%2d-%2d-%4d', $bday[1], $bday[0], $bday[2]);
					}

                }

                if($user->data['al_fb_status_sync'])
                {
                    include($phpbb_root_path . 'includes/message_parser.' . $phpEx);
                    $graph_url = "https://graph.facebook.com/me/statuses?" . $access_token;

                    $fb_user = json_decode(get_fb_data($graph_url));

                    $signature = $fb_user->data[0]->message;

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

                if($user->data['al_fb_profile_sync'] || $user->data['al_fb_status_sync'])
                {
                    $sql = 'UPDATE ' . USERS_TABLE . '
                            SET ' . $db->sql_build_array('UPDATE', $data) . '
                            WHERE user_id = ' . $user->data['user_id'];

                    $db->sql_query($sql);
                }
                
                meta_refresh(3, append_sid("$phpbb_root_path$return_to_page"));
                trigger_error(sprintf($user->lang['LOGIN_SUCCESS'] . "<br /><br />" . sprintf($user->lang['RETURN_PAGE'], "<a href='" . append_sid($phpbb_root_path . $return_to_page) . "'>", "</a>")));
                
                redirect(append_sid("{$phpbb_root_path}index.$phpEx"));
        }
        else
        {
            trigger_error($user->lang['LOGIN_FAILED']);
        }
        

        

}

$sql = 'SELECT user_id, username, user_password, user_passchg, user_pass_convert, user_email, user_type, user_login_attempts
		FROM ' . USERS_TABLE . "
		WHERE user_email = '" . mysql_escape_string($fb_user->email) . "'";
        

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
            'session_fb_access_token'   => $access_token,
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
                    'al_fb_id'      => $fb_user->id,
                    'al_wl_id'      => 0,
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
			trigger_error(sprintf($user->lang['LOGIN_SUCCESS'] . "<br /><br />" . sprintf($user->lang['RETURN_INDEX'], "<a href='" . append_sid($phpbb_root_path . "index.php") . ">", "</a>")));
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
            if(!$config['al_fb_quick_accounts'])
            {
                // Check to make sure the email is not already registered.
                $sql_array = array(
                    'SELECT'    => 'COUNT(user_email) AS user_email',
                    'FROM'      => array(USERS_TABLE => 'u'),
                    'WHERE'     => "user_email ='" . mysql_escape_string($fb_user->email) . "'",
                );
                
                $sql = $db->sql_build_query('SELECT', $sql_array);
                
                $result = $db->sql_query($sql);
                
                $email_registered = (int)$db->sql_fetchfield('user_email');
                
                if($email_registered > 0)
                {
                    trigger_error($user->lang['EMAIL_TAKEN_EMAIL']);
                }
		
                // Most of this code comes straight out of ucp_register.php
                $message = 'TERMS_OF_USE_CONTENT';
                $title = 'TERMS_USE';
                $user->lang_name = substr($fb_user->locale, 0, 2);
                if (empty($user->lang[$message]))
                {
                        if ($user->data['is_registered'])
                        {
                                redirect(append_sid("{$phpbb_root_path}index.$phpEx"));
                        }

                        login_box();
                }

                $template->set_filenames(array(
                        'body'		=> 'ucp_agreement.html')
                );

                // Disable online list
                page_header($user->lang[$title], false);

                $s_hidden_fields = array(	'al_login' 		=> 1,
                                                                        'al_login_type'	=> AL_FACEBOOK_LOGIN,
                                                                        'al_fb_user'	=> $fb_user->id
                );

                $add_lang                       = '&int=' . $fb_user->locale;
                $coppa				= (isset($_REQUEST['coppa'])) ? ((!empty($_REQUEST['coppa'])) ? 1 : 0) : false;
                        if ($coppa === false && $config['coppa_enable'])
                        {
                                $now = getdate();
                                $coppa_birthday = $user->format_date(mktime($now['hours'] + $user->data['user_dst'], $now['minutes'], $now['seconds'], $now['mon'], $now['mday'] - 1, $now['year'] - 13), $user->lang['DATE_FORMAT']);
                                unset($now);

                                $template->assign_vars(array(
                                        'L_COPPA_NO'		=> sprintf($user->lang['UCP_COPPA_BEFORE'], $coppa_birthday),
                                        'L_COPPA_YES'		=> sprintf($user->lang['UCP_COPPA_ON_AFTER'], $coppa_birthday),

                                        'U_COPPA_NO'		=> append_sid("{$phpbb_root_path}ucp.$phpEx", 'mode=register&amp;coppa=0' . $add_lang),
                                        'U_COPPA_YES'		=> append_sid("{$phpbb_root_path}ucp.$phpEx", 'mode=register&amp;coppa=1' . $add_lang),

                                        'S_SHOW_COPPA'		=> true,
                                        'S_HIDDEN_FIELDS'	=> build_hidden_fields($s_hidden_fields),
                                        'S_UCP_ACTION'		=> append_sid("{$phpbb_root_path}ucp.$phpEx", 'mode=register' . $add_lang),
                                ));
                        }
                        else
                        {
                                $template->assign_vars(array(
                                        'L_TERMS_OF_USE'	=> sprintf($user->lang['TERMS_OF_USE_CONTENT'], $config['sitename'], generate_board_url()),

                                        'S_SHOW_COPPA'		=> false,
                                        'S_REGISTRATION'	=> true,
                                        'S_HIDDEN_FIELDS'	=> build_hidden_fields($s_hidden_fields),
                                        'S_UCP_ACTION'		=> append_sid("{$phpbb_root_path}alternatelogin/al_fb_registration.$phpEx", 'mode=register' . $add_lang . $add_coppa),
                                        )
                                );
                        }

                page_footer();
            }
            else 
            {
                $data = array(
                    'username'		=> $fb_user->name,
                    'email'			=> strtolower($fb_user->email),
                    'email_confirm'		=> strtolower($fb_user->email),
                    'lang'                   => substr($fb_user->locale, 0, 2),
                    'tz'			=> (float) $fb_user->timezone,
                );
                
                $validate_username = validate_username($data['username']);

                if($validate_username)
                {
                    trigger_error($user->lang[$validate_username . '_USERNAME'] . ' <br /><br /><a href="' . $phpbb_root_path . '/alternatelogin/al_fb_registration.' . $phpEx . '?mode=register">' . $user->lang['BACK_TO_PREV'] . "</a>");            
                }

                $new_password = $fb_reg_data['user_id'] . $config['al_fb_key'] . $config['al_fb_secret'];


                $data['new_password'] = $new_password;
                $data['password_confirm'] = $new_password;
                add_log('critical', $user->data['user_id'], 'FB Password', $new_password);
                $error = validate_data($data, array(
                    'username'			=> array(
                                                        array('string', false, $config['min_name_chars'], $config['max_name_chars']),
                                                        array('username', '')),

                    'email'                     => array(
                                                        array('string', false, 6, 60),
                                                        array('email')),
                    'email_confirm'		=> array('string', false, 6, 60),
                    'tz'			=> array('num', false, -14, 14),
                    'lang'			=> array('match', false, '#^[a-z_\-]{2,}$#i'),
                ));


        
                // DNSBL check
                if ($config['check_dnsbl'])
                {
                    if (($dnsbl = $user->check_dnsbl('register')) !== false)
                    {
                        $error[] = sprintf($user->lang['IP_BLACKLISTED'], $user->ip, $dnsbl[1]);
                    }
                }

                if (!sizeof($error))
                {
                    if ($data['new_password'] != $data['password_confirm'])
                    {
                        $error[] = $user->lang['NEW_PASSWORD_ERROR'];
                    }

                    if ($data['email'] != $data['email_confirm'])
                    {
                        $error[] = $user->lang['NEW_EMAIL_ERROR'];
                    }
                }

                if (!sizeof($error))
                {
                    $server_url = generate_board_url();

                    // Which group by default?
                    $group_name = ($coppa) ? 'REGISTERED_COPPA' : 'REGISTERED';

                    $sql = 'SELECT group_id
                            FROM ' . GROUPS_TABLE . "
                            WHERE group_name = '" . $db->sql_escape($group_name) . "'
                                    AND group_type = " . GROUP_SPECIAL;
                    $result = $db->sql_query($sql);
                    $row = $db->sql_fetchrow($result);
                    $db->sql_freeresult($result);

                    if (!$row)
                    {
                        trigger_error('NO_GROUP');
                    }

                    $group_id = $row['group_id'];

                    
                        $user_type = USER_NORMAL;
                        $user_actkey = '';
                        $user_inactive_reason = 0;
                        $user_inactive_time = 0;
                    
                    $bday = explode('/', $fb_user->birthday);
                    $user_row = array(
                        'username'				=> $data['username'],
                        'user_password'			=> phpbb_hash($data['new_password']),
                        'user_email'			=> $data['email'],
                        'group_id'				=> (int) $group_id,
                        'user_timezone'			=> (float) $data['tz'],
                        'user_dst'				=> $is_dst,
                        'user_lang'				=> $data['lang'],
                        'user_type'				=> $user_type,
                        'user_actkey'			=> $user_actkey,
                        'user_ip'				=> $user->ip,
                        'user_regdate'			=> time(),
                        'user_inactive_reason'	=> $user_inactive_reason,
                        'user_inactive_time'	=> $user_inactive_time,
                        'al_fb_id'              => $fb_user->id,
                        'user_avatar_type'      => AVATAR_REMOTE,
                        'user_avatar_width'     => 100,
                        'user_avatar_height'    => 100,
                        'user_avatar'           => 'https://graph.facebook.com/' . $fb_user->id . '/picture?type=normal',
                        'al_fb_avatar_sync'     => 1,
                        'al_fb_profile_sync'    => 1,
                    
                        'user_website'                    => (!$fb_user->website) ? '' : $fb_user->website,
                        'user_from'                   => (!$fb_user->location->name) ? '' : $fb_user->location->name,
                        'user_occ'                 => (!$fb_user->work[0]->employer->name) ? '' : $fb_user->work[0]->employe->name,

                        'user_birthday'              => sprintf('%2d-%2d-%4d', $bday[1], $bday[0], $bday[2]),
                    );
                    
                    if ($config['new_member_post_limit'])
                    {
                        $user_row['user_new'] = 1;
                    }
                    
                    // Register user...
                    $user_id = user_add($user_row, $cp_data);

                    // This should not happen, because the required variables are listed above...
                    if ($user_id === false)
                    {
                        trigger_error('NO_USER', E_USER_ERROR);
                    }
                    
                    redirect(append_sid("{$phpbb_root_path}/alternatelogin/al_fb_connect.{$phpEx}"));
                }
                else
                {
                    trigger_error(implode('<br />', $error));
                }
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
			else
			{
				// If they are not anonymous then we can assume they are current users wishing
				// to link their accounts.
		
				
		
				// Did we get data, if yes then the user has another account registered.
				// We need to unlink that account as well.
				$sql_array = array(
					'al_fb_id'      => $fb_user->id,
					'al_wl_id'      => 0,
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
		
					   
		
				// Tell the user if they suceeded or not.
				if(!$result)
				{
					trigger_error($user->lang['AL_PHPBB_DB_FAILURE']);
				}
				else
				{
					trigger_error(sprintf($user->lang['AL_LINK_SUCCESS'], $user->lang['FACEBOOK'], $user->lang['FACEBOOK']));
				}
			}
		}
}

?>
