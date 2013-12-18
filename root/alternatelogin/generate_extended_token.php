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

if(!isset($_REQUEST['access_token']))
{
	add_log('critical', 'No access token passed for extension.');
	return false;
}

$short_token = request_var('access_token', '');

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

echo json_encode($data);

?>
