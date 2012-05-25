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

try
{
    $openid = new LightOpenID($config['server_name']);
  
    if(!$openid->mode)
    {
        if($openid_identifier != '')
        {
             
            $openid->identity = $openid_identifier;
            
            $openid->required = array('contact/email');

            header('Location: ' . $openid->authUrl());
        }
    }
    elseif($openid->mode == 'cancel')
    {
        trigger_error('You have canceled your login.');
    }
    else
    {
        if($openid->validate())
        {
            $openid_identifier = request_var('openid_claimed_id', '');

            $attributes = $openid->getAttributes();
            $url = "{$phpbb_root_path}alternatelogin/al_oi_connect.$phpEx?openid_identifier=" . urlencode($openid_identifier);
            if(isset($attributes['contact/email']))
            {
                $url .= "&openid_email=" . urlencode($attributes['contact/email']);
            }

            redirect(append_sid($url));

        }
        else
        {
            
            trigger_error($user->lang['FAILED_TO_AUTHENTICATE']);
        }
    }
}
catch(ErrorException $ex)
{
    trigger_error($ex->getMessage());
}




?>