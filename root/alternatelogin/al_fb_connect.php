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
include_once($phpbb_root_path . 'alternatelogin/facebook/facebook.' . $phpEx);	// Custom Alternate Login functions.
// Set up a new user session.
$user->session_begin();
$auth->acl($user->data);
$user->setup('ucp');
$user->add_lang('mods/info_acp_alternatelogin');	// Global Alternate Login language file.
$user->add_lang('mods/info_ucp_alternatelogin');

global $facebook;

// Make sure that Facebook login is enabled for this site.
if($config['al_fb_login'] == 0)
{
	// Inform the user that this feature is unavailable
	trigger_error(sprintf($user->lang['AL_LOGIN_UNAVAILABLE'], $user->lang['FACEBOOK']));
}

$return_to_page = request_var('return_to_page', "{$phpbb_root_path}index.{$phpEx}");
$admin = request_var('admin', 0);

if($signed_request != '')
{
	$return_to_page = base64_decode($return_to_page);
}
$return_to_page = str_replace("../", "", $return_to_page);
$return_to_page = str_replace("./", "", $return_to_page);

// Check for a signed request, a signed request means attempted registration...
$signed_request         = request_var('signed_request', '');

if($signed_request != '')
{
    
    list($encoded_sig, $payload) = explode('.', $signed_request, 2);

    $sig = base64_decode(strtr($encoded_sig, '-_', '+/'));

    $fb_reg_data = json_decode(base64_decode(strtr($payload, '-_', '+/')), true);

    if(strtoupper($fb_reg_data['algorithm']) !== 'HMAC-SHA256')
    {
        trigger_error('Unknown algorithm. Expected HMAC-SHA256');

        return;
    }

    $expected_sig = $expected_sig = hash_hmac('sha256', $payload, $config['al_fb_secret'], $raw = true);
    if ($sig !== $expected_sig)
    {
        trigger_error('Bad Signed JSON signature!');
        return;
    }
	
	$is_dst = $config['board_dst'];
	$timezone = $config['board_timezone'];

	$data = array(
		'username'			=> utf8_normalize_nfc($fb_reg_data['registration']['username']),
		'new_password'		=> $fb_reg_data['registration']['password'],
		'password_confirm'	=> $fb_reg_data['registration']['password'],
		'email'				=> $fb_reg_data['registration']['email'],
		'email_confirm'		=> $fb_reg_data['registration']['email'],
		'lang'				=> basename($fb_reg_data['registration']['lang']),
		'tz'				=> (float) $timezone,
		'al_fb_id'			=> $fb_reg_data['user_id'],
	);
	
	$error = validate_data($data, array(
				'username'			=> array(
					array('string', false, $config['min_name_chars'], $config['max_name_chars']),
					array('username', '')),
				'new_password'		=> array(
					array('string', false, $config['min_pass_chars'], $config['max_pass_chars']),
					array('password')),
				'password_confirm'	=> array('string', false, $config['min_pass_chars'], $config['max_pass_chars']),
				'email'				=> array(
					array('string', false, 6, 60),
					array('email')),
				'email_confirm'		=> array('string', false, 6, 60),
				'tz'				=> array('num', false, -14, 14),
				'lang'				=> array('language_iso_name'),
			));
	
	$error = preg_replace('#^([A-Z_]+)$#e', "(!empty(\$user->lang['\\1'])) ? \$user->lang['\\1'] : '\\1'", $error);
	
	// DNSBL check
	if ($config['check_dnsbl'])
	{
		if (($dnsbl = $user->check_dnsbl('register')) !== false)
		{
			$error[] = sprintf($user->lang['IP_BLACKLISTED'], $user->ip, $dnsbl[1]);
		}
	}
	
	if(sizeof($error))
	{
		trigger_error(implode('<br />', $error));
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

		if (($coppa ||
			$config['require_activation'] == USER_ACTIVATION_SELF ||
			$config['require_activation'] == USER_ACTIVATION_ADMIN) && $config['email_enable'])
		{
			$user_actkey = gen_rand_string(mt_rand(6, 10));
			$user_type = USER_INACTIVE;
			$user_inactive_reason = INACTIVE_REGISTER;
			$user_inactive_time = time();
		}
		else
		{
			$user_type = USER_NORMAL;
			$user_actkey = '';
			$user_inactive_reason = 0;
			$user_inactive_time = 0;
		}

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
			'al_fb_id'				=> $data['al_fb_id'],
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

		// Okay, captcha, your job is done.
		if ($config['enable_confirm'] && isset($captcha))
		{
			$captcha->reset();
		}

		if ($coppa && $config['email_enable'])
		{
			$message = $user->lang['ACCOUNT_COPPA'];

			$email_template = 'coppa_welcome_inactive';
			
		}
		else if ($config['require_activation'] == USER_ACTIVATION_SELF && $config['email_enable'])
		{
			$message = $user->lang['ACCOUNT_INACTIVE'];
			$email_template = 'user_welcome_inactive';
			
		}
		else if ($config['require_activation'] == USER_ACTIVATION_ADMIN && $config['email_enable'])
		{
			$message = $user->lang['ACCOUNT_INACTIVE_ADMIN'];
			$email_template = 'admin_welcome_inactive';
			
		}
		else
		{
			$message = $user->lang['ACCOUNT_ADDED'];
			$email_template = 'user_welcome';
			
		}

		if ($config['email_enable'])
		{
			include_once($phpbb_root_path . 'includes/functions_messenger.' . $phpEx);

			$messenger = new messenger(false);

			$messenger->template($email_template, $data['lang']);

			$messenger->to($data['email'], $data['username']);

			$messenger->anti_abuse_headers($config, $user);

			$messenger->assign_vars(array(
				'WELCOME_MSG'	=> htmlspecialchars_decode(sprintf($user->lang['WELCOME_SUBJECT'], $config['sitename'])),
				'USERNAME'		=> htmlspecialchars_decode($data['username']),
				'PASSWORD'		=> htmlspecialchars_decode($data['new_password']),
				'U_ACTIVATE'	=> "$server_url/ucp.$phpEx?mode=activate&u=$user_id&k=$user_actkey")
			);

			if ($coppa)
			{
				$messenger->assign_vars(array(
					'FAX_INFO'		=> $config['coppa_fax'],
					'MAIL_INFO'		=> $config['coppa_mail'],
					'EMAIL_ADDRESS'	=> $data['email'])
				);
			}

			$messenger->send(NOTIFY_EMAIL);

			if ($config['require_activation'] == USER_ACTIVATION_ADMIN)
			{
				// Grab an array of user_id's with a_user permissions ... these users can activate a user
				$admin_ary = $auth->acl_get_list(false, 'a_user', false);
				$admin_ary = (!empty($admin_ary[0]['a_user'])) ? $admin_ary[0]['a_user'] : array();

				// Also include founders
				$where_sql = ' WHERE user_type = ' . USER_FOUNDER;

				if (sizeof($admin_ary))
				{
					$where_sql .= ' OR ' . $db->sql_in_set('user_id', $admin_ary);
				}

				$sql = 'SELECT user_id, username, user_email, user_lang, user_jabber, user_notify_type
					FROM ' . USERS_TABLE . ' ' .
					$where_sql;
				$result = $db->sql_query($sql);

				while ($row = $db->sql_fetchrow($result))
				{
					$messenger->template('admin_activate', $row['user_lang']);
					$messenger->to($row['user_email'], $row['username']);
					$messenger->im($row['user_jabber'], $row['username']);

					$messenger->assign_vars(array(
						'USERNAME'			=> htmlspecialchars_decode($data['username']),
						'U_USER_DETAILS'	=> "$server_url/memberlist.$phpEx?mode=viewprofile&u=$user_id",
						'U_ACTIVATE'		=> "$server_url/ucp.$phpEx?mode=activate&u=$user_id&k=$user_actkey")
					);

					$messenger->send($row['user_notify_type']);
				}
				$db->sql_freeresult($result);
			}
		}
		
		$message = $message . '<br /><br />' . sprintf($user->lang['RETURN_PAGE'], '<a href="' . append_sid("{$phpbb_root_path}{$return_to_page}") . '">', '</a>');
		trigger_error($message);
	}
}

try
{
	
	$access_token = json_decode($user->data['session_fb_access_token']);
	
	if(($access_token->expires < time()) || !isset($user->data['session_fb_access_token']))
	{
		
		$access_token = get_fb_access_token(generate_board_url() . '/alternatelogin/al_fb_connect.' . $phpEx . '?return_to_page=' . $return_to_page);
	}
	
	if(!$access_token)
	{
		add_log('critical', $user->data['user_id'], 'FB_ERROR_ACCESS_TOKEN');
		trigger_error($user->lang['FB_ERROR_ACCESS_TOKEN']);
	}
	
	//echo 'token:' . print_r($token_url);
	$graph_url = "https://graph.facebook.com/me?access_token=" . $access_token->access_token;
	
	$fb_user = get_fb_data($graph_url);
	
	
	
	
	
	print_r($fb_user);
	
	$user->lang_name = substr($fb_user['locale'], 0, 2);
	// Select the user_id from the Alternate Login user data table which has the same Facebook Id.
	//$fb_user = $facebook->api('/me', 'GET');
	
	
	$sql = 'SELECT user_id, username, user_password, user_passchg, user_pass_convert, user_email, user_type, user_login_attempts
			FROM ' . USERS_TABLE . "
			WHERE al_fb_id = " . $fb_user_id;
			
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
						'session_fb_access_token'   => $facebook->getAccessToken(),
					);
	
					$sql = "UPDATE " . SESSIONS_TABLE . " SET " . $db->sql_build_array('UPDATE', $sql_array) . " WHERE session_id='" . $user->data['session_id'] . "'";
	
					$db->sql_query($sql);
					$data = array();
					// Update the stored data such as profile and signatures.  Avatar is a dynamic field and doesn't require changing.
	
					if($user->data['al_fb_profile_sync'])
					{
	
						//$graph_url = "https://graph.facebook.com/me?" . $access_token;
	
						//$fb_user = json_decode(get_fb_data($graph_url));
	
						$data['user_website']                    = isset($fb_user['website']) ? $fb_user['website'] : '';
						$data['user_from']                   = isset($fb_user['location']['name']) ? $fb_user['location']['name'] : '';
						$data['user_occ']                 = isset($fb_user['work'][0]['employer']['name']) ? $fb_user['work'][0]['employer']['name'] : '';
						if(isset($fb_user['birthday']))
						{
							$bday = explode('/', $fb_user['birthday']);
							$data['user_birthday']              = sprintf('%2d-%2d-%4d', $bday[1], $bday[0], $bday[2]);
						}
	
					}
	// NEEDS WORK!!!
					if($user->data['al_fb_status_sync'])
					{
						include($phpbb_root_path . 'includes/message_parser.' . $phpEx);
						
						$fb_user = $facebook->api('/me/statuses', 'GET');
	
						$signature = $fb_user['data'][0]['message'];
	
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
					echo append_sid("{$phpbb_root_path}index.$phpEx");
					//redirect(append_sid("{$phpbb_root_path}index.$phpEx"));
			}
			else
			{
				trigger_error($user->lang['LOGIN_FAILED']);
			}
	}
	else
	{
		$action = request_var('action', '');
		$password = request_var('password_field', '');
		
		$sql = 'SELECT user_id, username, user_password, user_passchg, user_pass_convert, user_email, user_type, user_login_attempts
			FROM ' . USERS_TABLE . "
			WHERE user_email = '" . mysql_escape_string($fb_user['email']) . "'";
			
		// Execute the query.
		$result = $db->sql_query($sql);
		
		// Retrieve the row data.
		$row = $db->sql_fetchrow($result);
		
		// Free up the result handle from the query.
		$db->sql_freeresult($result);
		
		if($action == 'link')
		{
			if(phpbb_check_hash($password, $row['user_password']))
			{
				$data = array(
					'al_fb_id'		=> $fb_user_id,
				);
				$sql = 'UPDATE ' . USERS_TABLE . '
								SET ' . $db->sql_build_array('UPDATE', $data) . '
								WHERE user_id = ' . $row['user_id'];
				
				$db->sql_query($sql);
				
				
				
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
				
				if($result === true)
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
						'session_fb_access_token'   => $facebook->getAccessToken(),
					);
	
					$sql = "UPDATE " . SESSIONS_TABLE . " SET " . $db->sql_build_array('UPDATE', $sql_array) . " WHERE session_id='" . $user->data['session_id'] . "'";
	
					$db->sql_query($sql);
					
					meta_refresh(3, append_sid("$phpbb_root_path$return_to_page"));
					trigger_error(sprintf($user->lang['LOGIN_SUCCESS'] . "<br /><br />" . sprintf($user->lang['RETURN_PAGE'], "<a href='" . append_sid($phpbb_root_path . $return_to_page) . "'>", "</a>")));
				}
			}
			else
			{
				trigger_error('The passwords DON\'T match');
			}
		}
	}
}
catch(FacebookApiException $ex)
{
	$error = $ex->getResult();
	if($error['error']['type'] == 'OAuthException')
	{
		refresh_fb_access_token(generate_board_url() . '/alternatelogin/al_fb_connect.' . $phpEx . '?return_to_page=' . $return_to_page);
	}
	else
	{
		trigger_error("Facebook: " . $error['error']['message']);
	}
		
}
catch(Exception $ex)
{
	trigger_error("Code: " . $ex->getCode() . "<br/>" . $ex->getMessage());
}

?>
