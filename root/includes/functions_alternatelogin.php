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
}


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