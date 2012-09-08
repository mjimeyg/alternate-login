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
if (!defined('IN_PHPBB'))
{
	exit;
}

define('AL_FACEBOOK_LOGIN', 0);
define('AL_OPENID_LOGIN', 2);
define('AL_OPENID_PROFILE', 3);
define('AL_GOOGLE_LOGIN', 4);
define('AL_GOOGLE_PROFILE', 5);
define('AL_TWITTER_LOGIN', 6);
define('AL_TWITTER_PROFILE', 7);
define('AL_WINDOWSLIVE_LOGIN', 8);
define('AL_WINDOWSLIVE_PROFILE', 9);

define('AL_FB_SYNC_PROFILE', 1);
define('AL_FB_SYNC_AVATAR', 11);
define('AL_FB_SYNC_STATUS', 12);

define('AL_HIDE_POST_LOGON', 10);	// Does the user want to be asked if they want to see the 'hide online' and autologin screen after verification?

define('AL_USER_OPTION_COUNT', 13);

define('WL_COOKIE', 'webauthtoken');
define('PCOOKIE', time() + (10 * 365 * 24 * 60 * 60));

define('HTTP_GET', 0);
define('HTTP_POST', 1);



/**
* Validate an Alternate Login ID for Admin privaleges..
*
* @return False if the login failed, True if success.
*/
function al_validate_admin()
{
	global $template, $db, $config, $user, $auth, $phpEx, $phpbb_root_path;

	$user->add_lang('acp/common');
        $user->add_lang('common');

	$sql = "SELECT user_email, user_allow_viewonline, al_fb_id, al_wl_id, al_tw_id" .
			" FROM " . USERS_TABLE .
			" WHERE " . USERS_TABLE . ".user_id='" . $user->data['user_id'] . "'";

	$result = $db->sql_query($sql);

	$row = $db->sql_fetchrow($result);

	$username = $user->data['username'];
	$password = '';

	if($row)
	{
            
		if($row['al_fb_id'])
		{
                    $graph_url = "https://graph.facebook.com/me?" . $user->data['session_fb_access_token'];
 
                    $fb_user = json_decode(get_fb_data($graph_url));
                    
                    if(!$fb_user)
                    {
                        add_log('critical', $user->lang['FB_ERROR_USER']);
                        trigger_error($user->lang['FB_ERROR_USER']);
                    }
                    
                    $password = $fb_user->id . $config['al_fb_key'] . $config['al_fb_secret'];
		}
		elseif($row['al_wl_id'])
		{
                    $wl_user = get_wl_rest_request($user->data['session_wl_access_token'], 'me', HTTP_GET);
                    
                    $password = $wl_user->id . $config['al_wl_client_id'] . $config['al_wl_secret'];
                    
		}
		elseif($row['al_tw_id'])
		{
                    $tw_access_token = json_decode($user->data['session_tw_access_token']);
                    
                    include($phpbb_root_path . 'alternatelogin/twitter/tmhOAuth.' . $phpEx);
                    include($phpbb_root_path . 'alternatelogin/twitter/tmhUtilities.' . $phpEx);
                    
                    $tmhOAuth = new tmhOAuth(array(
                        'consumer_key'      => $config['al_tw_key'],
                        'consumer_secret'   => $config['al_tw_secret'],
                    ));

                    $tmhOAuth->config['user_token'] = $tw_access_token->oauth_token;
                    $tmhOAuth->config['user_secret'] = $tw_access_token->oauth_token_secret;

                    $code = $tmhOAuth->request('GET', $tmhOAuth->url('1/account/verify_credentials'));

                    if($code == 200)
                    {
                        $tw_user = json_decode($tmhOAuth->response['response']);
                    }
                    else
                    {
                        trigger_error($tmhOAuth->response['response']);
                    }
                    
                    $password = $tw_user->id . $config['al_tw_key'] . $config['al_tw_secret'];
		}
		
                add_log('critical', $user->data['user_id'], 'WL Password', $password);


		$hide_online = $row['user_allow_viewonline'];

		$result = $auth->login($username, $password, false, $hide_online, true);

		if($result['status'] == LOGIN_SUCCESS)
		{
			add_log('admin', 'LOG_ADMIN_AUTH_SUCCESS');

			$redirect = "{$phpbb_root_path}adm/index.$phpEx";
			$message = $user->lang['LOGIN_ADMIN_SUCCESS'];
			$l_redirect = $user->lang['PROCEED_TO_ACP'];

			// append/replace SID (may change during the session for AOL users)
			$redirect = reapply_sid($redirect);

			// Special case... the user is effectively banned, but we allow founders to login
			if (defined('IN_CHECK_BAN') && $result['user_row']['user_type'] != USER_FOUNDER)
			{
				return;
			}

			$redirect = meta_refresh(3, $redirect);
			trigger_error($message . '<br /><br />' . sprintf($l_redirect, '<a href="' . $redirect . '">', '</a>'));

		}
		else
		{
                    
			if ($user->data['is_registered'])
			{
				add_log('admin', 'LOG_ADMIN_AUTH_FAIL');
			}
			// Something failed, determine what...
			if ($result['status'] == LOGIN_BREAK)
			{
				trigger_error($result['error_msg']);
			}

			// Special cases... determine
			switch ($result['status'])
			{
				case LOGIN_ERROR_ATTEMPTS:

					// Show confirm image
					$sql = 'DELETE FROM ' . CONFIRM_TABLE . "
						WHERE session_id = '" . $db->sql_escape($user->session_id) . "'
							AND confirm_type = " . CONFIRM_LOGIN;
					$db->sql_query($sql);

					// Generate code
					$code = gen_rand_string(mt_rand(CAPTCHA_MIN_CHARS, CAPTCHA_MAX_CHARS));
					$confirm_id = md5(unique_id($user->ip));
					$seed = hexdec(substr(unique_id(), 4, 10));

					// compute $seed % 0x7fffffff
					$seed -= 0x7fffffff * floor($seed / 0x7fffffff);

					$sql = 'INSERT INTO ' . CONFIRM_TABLE . ' ' . $db->sql_build_array('INSERT', array(
						'confirm_id'	=> (string) $confirm_id,
						'session_id'	=> (string) $user->session_id,
						'confirm_type'	=> (int) CONFIRM_LOGIN,
						'code'			=> (string) $code,
						'seed'			=> (int) $seed)
					);
					$db->sql_query($sql);

					$template->assign_vars(array(
						'S_CONFIRM_CODE'			=> true,
						'CONFIRM_ID'				=> $confirm_id,
						'CONFIRM_IMAGE'				=> '<img src="' . append_sid("{$phpbb_root_path}ucp.$phpEx", 'mode=confirm&amp;id=' . $confirm_id . '&amp;type=' . CONFIRM_LOGIN) . '" alt="" title="" />',
						'L_LOGIN_CONFIRM_EXPLAIN'	=> sprintf($user->lang['LOGIN_CONFIRM_EXPLAIN'], '<a href="mailto:' . htmlspecialchars($config['board_contact']) . '">', '</a>'),
					));

					$err = $user->lang[$result['error_msg']];

				break;

				case LOGIN_ERROR_PASSWORD_CONVERT:
					$err = sprintf(
						$user->lang[$result['error_msg']],
						($config['email_enable']) ? '<a href="' . append_sid("{$phpbb_root_path}ucp.$phpEx", 'mode=sendpassword') . '">' : '',
						($config['email_enable']) ? '</a>' : '',
						($config['board_contact']) ? '<a href="mailto:' . htmlspecialchars($config['board_contact']) . '">' : '',
						($config['board_contact']) ? '</a>' : ''
					);
				break;

				// Username, password, etc...
				default:
					$err = $user->lang[$result['error_msg']];

					// Assign admin contact to some error messages
					if ($result['error_msg'] == 'LOGIN_ERROR_USERNAME' || $result['error_msg'] == 'LOGIN_ERROR_PASSWORD')
					{
						$err = (!$config['board_contact']) ? sprintf($user->lang[$result['error_msg']], '', '') : sprintf($user->lang[$result['error_msg']], '<a href="mailto:' . htmlspecialchars($config['board_contact']) . '">', '</a>');
					}

				break;
			}
			trigger_error($err);
		}
	}
}

function get_wl_tokens($cid, $authorization_code, $refresh_token)
{
    global $config, $user;
    
    $url = "https://oauth.live.com/token?";
    $url .= "client_id={$config['al_wl_client_id']}";
    $url .= "&redirect_uri=" . urlencode($config['al_wl_callback']);
    $url .= "&client_secret={$config['al_wl_secret']}";
    if($authorization_code)
    {
        $url .= "&code={$authorization_code}";
        $url .= "&grant_type=authorization_code";
    }
    elseif($refresh_token)
    {
        $url .= "&refresh_token={$refresh_token}";
        $url .= "&grant_type=refresh_token";
    }
    else
    {
        trigger_error($user->lang['MISSING_AUTH_OR_REFRESH']);
    }
    
    $ch = curl_init();
    
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
 
    
    $data = curl_exec($ch);
    
    if(curl_errno($ch))
    {
        add_log('critical', 'WL_GET_ACCESS_TOKEN_ERROR', $user->data['user_id'], 'WL_GET_ACCESS_TOKEN_ERROR', curl_error($ch));
        trigger_error($user->lang['ERROR_RETRIEVING_TOKENS']);
    }

    curl_close($ch);
    
    $data_decoded = json_decode($data);
    
    
    if($data_decoded->error)
    {
        trigger_error($data_decoded->error);
    }
    
    return $data_decoded;
}

function get_wl_rest_request($access_token, $path, $method = HTTP_GET, $headers = NULL, $method_data = NULL)
{
    $url = "https://apis.live.net/v5.0/{$path}/?access_token={$access_token}";
    
    $ch = curl_init();
    
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    
    if($method == HTTP_GET)
    {
        curl_setopt($ch, CURLOPT_HTTPGET, true);
    }
    elseif($method == HTTP_POST)
    {
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $method_data);
    }
    else
    {
        trigger_error($user->lang['ERROR_UNDETERMINED_HTTP_METHOD']);
    }
    
    $data = curl_exec($ch);
    
    if(curl_errno($ch))
    {
        add_log('critical', 'WL_CURL_REQUEST_ERROR', $user->data['user_id'], 'WL_CURL_REQUEST_ERROR', curl_error($ch));
        return false;
    }

    curl_close($ch);
    
    return json_decode($data);
}



function get_fb_access_token($return_to_page)
{
    global $config, $db, $user, $template;
    $my_url = generate_board_url() . "/alternatelogin/al_fb_connect.php";
    if($return_to_page != null && $return_to_page != '')
    {
        //$return_to_page = urlencode($return_to_page);
        $my_url .= "?return_to_page=" . urlencode($return_to_page);
    }
    $code = request_var('code', '');

    if(empty($code)) {
        $dialog_url = "http://www.facebook.com/dialog/oauth?client_id="
            . $config['al_fb_id'] . "&redirect_uri=" . urlencode($my_url) . "&scope=user_location,user_activities,user_birthday,user_interests,user_status,user_website,user_work_history,email";

        
        page_header($user->lang['FB_REDIRECT']);
        
        $template->set_filenames(array(
            'body'      => 'al_redirect.html',
        ));
        
        $template->assign_vars(array(
            'S_DIALOG_URL'  => $dialog_url,
        ));
        
        page_footer();
         
    }
    
    $token_url = "https://graph.facebook.com/oauth/access_token?client_id="
        . $config['al_fb_id'] . "&redirect_uri=" . urlencode($my_url) . "&client_secret="
        . $config['al_fb_secret'] . "&code=" . $code;

    $ch = curl_init();
    
    curl_setopt($ch, CURLOPT_URL, $token_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    
    curl_setopt($ch, CURLOPT_HTTPGET, true);
    
    
    $access_token = curl_exec($ch);
    
    if(curl_errno($ch))
    {
        add_log('critical', 'FB_GET_ACCESS_TOKEN_ERROR', $user->data['user_id'], 'FB_GET_ACCESS_TOKEN_ERROR', curl_error($ch));
        return false;
    }

    curl_close($ch);
    
    //$access_token = file_get_contents($token_url);

    return $access_token;
}

function refresh_fb_access_token($return_to_page)
{
    global $config, $db, $user, $template;
    
	$my_url = $return_to_page;
	
    $code = request_var('code', '');

    if(empty($code)) {
        $dialog_url = "http://www.facebook.com/dialog/oauth?client_id="
            . $config['al_fb_id'] . "&redirect_uri=" . urlencode($my_url) . "&scope=user_location,user_activities,user_birthday,user_interests,user_status,user_website,user_work_history,email";

        
        page_header($user->lang['FB_REDIRECT']);
        
        $template->set_filenames(array(
            'body'      => 'al_redirect.html',
        ));
        
        $template->assign_vars(array(
            'S_DIALOG_URL'  => $dialog_url,
        ));
        
        page_footer();
         
    }
    
    $token_url = "https://graph.facebook.com/oauth/access_token?client_id="
        . $config['al_fb_id'] . "&redirect_uri=" . urlencode($my_url) . "&client_secret="
        . $config['al_fb_secret'] . "&code=" . $code;

    $ch = curl_init();
    
    curl_setopt($ch, CURLOPT_URL, $token_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    
    curl_setopt($ch, CURLOPT_HTTPGET, true);
    
    
    $access_token = curl_exec($ch);
    
    if(curl_errno($ch))
    {
        add_log('critical', 'FB_GET_ACCESS_TOKEN_ERROR', $user->data['user_id'], 'FB_GET_ACCESS_TOKEN_ERROR', curl_error($ch));
        return false;
    }

    curl_close($ch);
    
    $sql_array = array(
		'session_fb_access_token'   => $access_token,
	);

	$sql = "UPDATE " . SESSIONS_TABLE . " SET " . $db->sql_build_array('UPDATE', $sql_array) . " WHERE session_id='" . $user->data['session_id'] . "'";
	
	$db->sql_query($sql);

    redirect($my_url);
}


function get_fb_data($url)
{ 
    $ch = curl_init();
    
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    
    curl_setopt($ch, CURLOPT_HTTPGET, true);
    
    
    $data = curl_exec($ch);
    
    if(curl_errno($ch))
    {
        add_log('critical', 'FB_GET_USER_ERROR', $user->data['user_id'], 'FB_GET_USER_ERROR', curl_error($ch));
        return false;
    }
	
	$error_check = json_decode($data);
	
	if(isset($error_check->error))
	{
		if(($error_check->error->code == 2500) || ($error_check->error->error_subcode == 463))
		{
			
			refresh_fb_access_token(generate_board_url() . '/' . $user->data['session_page']);
		}
	}
    
    curl_close($ch);
    
    return $data;
}

function php_self($dropqs=true) {
$url = sprintf('%s://%s%s',
  empty($_SERVER['HTTPS']) ? (@$_SERVER['SERVER_PORT'] == '443' ? 'https' : 'http') : 'http',
  $_SERVER['SERVER_NAME'],
  $_SERVER['REQUEST_URI']
);

$parts = parse_url($url);

$port = $_SERVER['SERVER_PORT'];
$scheme = $parts['scheme'];
$host = $parts['host'];
$path = @$parts['path'];
$qs   = @$parts['query'];

$port or $port = ($scheme == 'https') ? '443' : '80';

if (($scheme == 'https' && $port != '443')
    || ($scheme == 'http' && $port != '80')) {
  $host = "$host:$port";
}
$url = "$scheme://$host$path";
if ( ! $dropqs)
  return "{$url}?{$qs}";
else
  return $url;
}
?>