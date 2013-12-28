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

if(!defined('AL_FACEBOOK_LOGIN'))
{
	define('AL_FACEBOOK_LOGIN', 0);
}
if(!defined('AL_OPENID_LOGIN'))
{
	define('AL_OPENID_LOGIN', 2);
}
if(!defined('AL_OPENID_PROFILE'))
{
	define('AL_OPENID_PROFILE', 3);
}
if(!defined('AL_GOOGLE_LOGIN'))
{
	define('AL_GOOGLE_LOGIN', 4);
}
if(!defined('AL_GOOGLE_PROFILE'))
{
	define('AL_GOOGLE_PROFILE', 5);
}
if(!defined('AL_WINDOWSLIVE_LOGIN'))
{
	define('AL_WINDOWSLIVE_LOGIN', 8);
}
if(!defined('AL_WINDOWSLIVE_PROFILE'))
{
	define('AL_WINDOWSLIVE_PROFILE', 9);
}

if(!defined('AL_FB_SYNC_PROFILE'))
{
	define('AL_FB_SYNC_PROFILE', 1);
}
if(!defined('AL_FB_SYNC_AVATAR'))
{
	define('AL_FB_SYNC_AVATAR', 11);
}
if(!defined('AL_FB_SYNC_STATUS'))
{
	define('AL_FB_SYNC_STATUS', 12);
}

if(!defined('AL_HIDE_POST_LOGON'))
{
	define('AL_HIDE_POST_LOGON', 10);	// Does the user want to be asked if they want to see the 'hide online' and autologin screen after verification?
}

if(!defined('AL_USER_OPTION_COUNT'))
{
	define('AL_USER_OPTION_COUNT', 13);
}

if(!defined('WL_COOKIE'))
{
	define('WL_COOKIE', 'webauthtoken');
}
if(!defined('PCOOKIE'))
{
	define('PCOOKIE', time() + (10 * 365 * 24 * 60 * 60));
}

if(!defined('HTTP_GET'))
{
	define('HTTP_GET', 0);
}

if(!defined('HTTP_POST'))
{
	define('HTTP_POST', 1);
}



if(!function_exists('get_wl_tokens'))
{
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
      
      
      if(isset($data_decoded->error))
      {
         trigger_error($data_decoded->error);
      }
      
      return $data_decoded;
   }
}

if(!function_exists('get_wl_rest_request'))
{
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
}

if(!function_exists('get_fb_access_token'))
{
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
            . $config['al_fb_id'] . "&redirect_uri=" . urlencode($my_url) . "&scope=user_location,user_activities,user_birthday,user_interests,user_status,user_website,user_work_history,email,publish_actions,manage_pages,publish_stream";
   
         
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
      
     // $access_token = array();
	  
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
}

if(!function_exists('get_fb_extended_tokens'))
{
	function get_fb_extended_tokens($short_token)
	{
		global $config;
		$user_token = array();
		
		$url = "https://graph.facebook.com/oauth/access_token?grant_type=fb_exchange_token&client_id={$config['al_fb_id']}&client_secret={$config['al_fb_secret']}&fb_exchange_token={$short_token}";
		
		$ch = curl_init();
			  
			  
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		
		parse_str(curl_exec($ch), $user_token);
		
		if(curl_errno($ch))
		{
		   add_log('critical', curl_error($ch));
		}
		
		$url = "https://graph.facebook.com/me/accounts?access_token=" . $user_token['access_token'];
		
		curl_setopt($ch, CURLOPT_URL, $url);
		
		$page_tokens = json_decode(curl_exec($ch), true);
		
		if(curl_errno($ch))
		{
		   add_log('critical', curl_error($ch));
		}
		
		curl_close($ch);
		
		$data = array(
			'user_token'	=> $user_token,
			'page_tokens'	=> $page_tokens,
		);
		
		return $data;
	}
}

if(!function_exists('get_fb_page_token'))
{
	function get_fb_page_token($access_token)
	{
		try
		{
			global $user, $config;
			
			parse_str($access_token, $access_token);
			$url = "https://graph.facebook.com/" . $user->data['al_fb_id'] . "/accounts?access_token=" . $access_token['access_token'];
			
			
			$ch = curl_init();
			
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
			curl_setopt($ch, CURLOPT_HTTPGET, true);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			  
			 // $access_token = array();
			  
			$accounts = json_decode(curl_exec($ch), true);
			
			if(isset($accounts['error']))
			{
				add_log('critical', $accounts['error']['message']);
				return false;
			}
			
			foreach($accounts['data'] as $a)
			{
				if($a['id'] == $config['al_fb_page_id'])
				{
					set_config('al_fb_page_token', $a['access_token']);
				}
			}
			
			
			  
			curl_close($ch);
		}
		catch(Exception $ex)
		{
			add_log('critical', $ex->getMessage());
			return false;
		}
		
	}
}

if(!function_exists('get_fb_app_token'))
{
	function get_fb_app_token()
	{
		global $user, $config;
		
		$url = "https://graph.facebook.com/oauth/access_token?client_id=" . $config['al_fb_id'] . "&client_secret=" . $config['al_fb_secret'] . "&grant_type=client_credentials";
		
		
		$ch = curl_init();
		
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
		curl_setopt($ch, CURLOPT_HTTPGET, true);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		  
		$access_token = array();
		
		parse_str(curl_exec($ch), $access_token);
		
		set_config('al_fb_app_token', $access_token['access_token']);
			
		
		  
		curl_close($ch);
		
	}
}

if(!function_exists('refresh_fb_access_token'))
{
   function refresh_fb_access_token($return_to_page)
   {
      global $config, $db, $user, $template;
      
      $my_url = $return_to_page;
      
      $code = request_var('code', '');
   
      if(empty($code)) {
         $dialog_url = "http://www.facebook.com/dialog/oauth?client_id="
            . $config['al_fb_id'] . "&redirect_uri=" . urlencode($my_url) . "&scope=user_location,user_activities,user_birthday,user_interests,user_status,user_website,user_work_history,email,publish_actions";
   
         
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
}

if(!function_exists('get_fb_data'))
{
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
      
      
      curl_close($ch);
      
      return $data;
   }
}

if(!function_exists('post_to_fb_user_wall'))
{
   function post_to_fb_user_wall($data, $users = null)
   { 
		global $user;
		
		$access_token = array();
		
		parse_str($user->data['session_fb_access_token'], $access_token);
		
		if(!isset($data['access_token']))
		{
			$data['access_token']		= $access_token['access_token'];
		}
		$data_string = "";
		foreach($data as $key=>$value) 
		{ 
			$data_string .= $key.'='.$value.'&'; 
		}
		rtrim($fields_string, '&');
		
		if($users == null)
		{
			$url = "https://graph.facebook.com/" . $user->data['al_fb_id'] . "/feed";
			
			$ch = curl_init();
			
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			
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
		}
		else
		{
			foreach($users as $u)
			{
				$url = "https://graph.facebook.com/" . $u . "/feed";
			
				$ch = curl_init();
				
				curl_setopt($ch, CURLOPT_URL, $url);
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
				curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
				curl_setopt($ch, CURLOPT_POST, true);
				curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				
				$data = curl_exec($ch);
				
				if(curl_errno($ch))
				{
				   add_log('critical', 'FB_GET_USER_ERROR', $u, 'FB_GET_USER_ERROR', curl_error($ch));
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
			}
		}
		curl_close($ch);
		
		return $data;
   }
}

if(!function_exists('update_fb_user_status'))
{
   function update_fb_user_status($data)
   { 
		global $user;
		
		$access_token = array();
		
		parse_str($user->data['session_fb_access_token'], $access_token);
		
		$data['access_token']		= $access_token['access_token'];
		$data['name']				= "phpbb_test";
		$data_string = "";
		foreach($data as $key=>$value) 
		{ 
			$data_string .= $key.'='.$value.'&'; 
		}
		rtrim($fields_string, '&');
		
		$url = "https://graph.facebook.com/" . $user->data['al_fb_id'] . "/feed";
		
		$ch = curl_init();
		
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		
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
		print_r($data);
		return $data;
   }
}

if(!function_exists('post_to_fb_page'))
{
   function post_to_fb_page($data)
   { 
		global $config, $user;
		
		
		$data['access_token']		= $config['al_fb_page_token'];
		
		$data_string = "";
		foreach($data as $key=>$value) 
		{ 
			$data_string .= $key.'='.$value.'&'; 
		}
		rtrim($fields_string, '&');
		
		$url = "https://graph.facebook.com/" . $config['al_fb_page_id'] . "/feed";
		
		$ch = curl_init();
		
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		
		$data = curl_exec($ch);
		echo $data;
		if(curl_errno($ch))
		{
		   add_log('critical', 'FB_GET_USER_ERROR', $user->data['user_id'], 'FB_GET_USER_ERROR', curl_error($ch));
		   return false;
		}
		
		$error_check = json_decode($data);
		
		if(isset($error_check->error))
		{
		   return false;
		}
		
		curl_close($ch);
		
		return $data;
   }
}

if(!function_exists('publish_post_to_fb_page'))
{
	function publish_post_to_fb_page($data)
	{
		global $user;
		print_r($data);
		$post_data = array(
			'message'		=> sprintf($user->lang['FB_TEMPLATE_POST_PUBLISHED'], $user->data['username'], $data['topic_title']),
			'link'			=> generate_board_url() . '/viewtopic.php?f=' . $data['forum_id'] . '&t=' . $data['topic_id'] . '#p' . $data['post_id'],
		);
		
		return post_to_fb_page($post_data);
	}
}

if(!function_exists('publish_topic_to_fb_page'))
{
	function publish_topic_to_fb_page($data)
	{
		global $user;
		$post_data = array(
			'message'		=> vsprintf($user->lang['FB_TOPIC_PAGE_TITLE'], array($user->data['username'], $data['topic_title'])),
			'link'			=> generate_board_url() . '/viewtopic.php?f=' . $data['forum_id'] . '&t=' . $data['topic_id'] . '#p' . $data['post_id'],
		);
		
		return post_to_fb_page($post_data);
	}
}

if(!function_exists('publish_post_to_fb_user'))
{
	function publish_post_to_fb_user($data)
	{
		global $user;
		
		$fb_user = get_fb_data('https://graph.facebook.com/me?access_token=' . $user->data['session_fb_access_token']);
		$post_data = array(
			'message'		=> vsprintf($user->lang['FB_USER_POST_TO_FEED_TITLE'], array($fb_user->name, $data['topic_title'])),
			'link'			=> generate_board_url() . '/viewtopic.php?f=' . $data['forum_id'] . '&t=' . $data['topic_id'] . '#p' . $data['post_id'],
		);
		
		return update_fb_user_status($post_data);
	}
}

/*if(!function_exists('post_curl'))
{
	function post_url($url, $params) {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params, null, '&'));
		$ret = curl_exec($ch);
		curl_close($ch);
		return $ret;
  	}
}*/

if(!function_exists('php_self'))
{
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
}
?>