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

/**
* DO NOT CHANGE
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

if (empty($lang) || !is_array($lang))
{
	$lang = array();
}

// DEVELOPERS PLEASE NOTE
//
// All language files should use UTF-8 as their encoding and the files must not contain a BOM.
//
// Placeholders can now contain order information, e.g. instead of
// 'Page %s of %s' you can (and should) write 'Page %1$s of %2$s', this allows
// translators to re-order the output of data while ensuring it remains correct
//
// You do not need this where single placeholders are used, e.g. 'Message %d' is fine
// equally where a string contains only two placeholders which are used to wrap text
// in a url you again do not need to specify an order e.g., 'Click %sHERE%s' is fine

$lang = array_merge($lang, array(
        'ALTERNATE_LOGIN'						=> 'This site allows you to login using a social networking account.',
		'AUTHORIZE_FAILURE'						=> 'Failed to get authorisation code.',
        'AL_LOGIN'								=> 'Select and alternate login',
		'AL_LOGIN_EXPLAIN'						=> 'You can select an alternate login method.  We will still need a user name and password.',
		'AL_REGISTER_QUERY'						=> 'Do you wish to register with your %s account?',
		'AL_REGISTRATION'						=> 'If you have an alternate social networking account you would like to use with this site please click one of the icons below <b>FIRST</b>.',
		'AL_LOGIN_UNAVAILABLE'					=> '%s is unavailable at this site.',
		'AL_PHPBB_DB_FAILURE'					=> 'An error occured whilst trying to connect to the phpBB database.',
    
        'AL_FB_HIDE_ACTIVITY'                   => 'Hide Facebook Activity Plugin',
        'AL_FB_HIDE_FACEPILE'                   => 'Hide Facebook Facepile Plugin',
        'AL_FB_HIDE_LIKE_BOX'                   => 'Hide Facebook Like Box Plugin',

        'FB_SYNC'                               => 'Sync profile with Facebook profile',
        'FB_STATUS'                             => 'Sync signature with Facebook status',
        'FB_AVATAR'                             => 'Facebook Profile Picture',
        'FB_AVATAR_EXPLAIN'                     => 'Sync your Facebook Profile Picture with your Avatar.',
		'FB_REDIRECT'							=> 'Facebook is redirecting...',
		'LOGIN_BUTTON_TEXT'						=> 'Login button text',
    
        'WL_SYNC'                               => 'Sync profile with Windows Live profile',
        'WL_STATUS'                             => 'Sync signature with Windows Live status',
        'WL_AVATAR'                             => 'Windows Live Profile Picture',
        'WL_AVATAR_EXPLAIN'                     => 'Sync your Windows Live Profile Picture with your Avatar.',

        'TITLE_ENABLE_LOGIN_PROFILE'			=> 'Enable alternate logins and profile syncs.',
		
		'NO_ACCESS_TOKEN'						=> 'No access token was found.',
		'FACEBOOK'								=> 'Facebook',
		'WINDOWSLIVE'							=> 'Windows Live',
        'OPENID'                                => 'OpenID',
		'ENABLE_LOGIN'							=> 'Enable Login',
		'ENABLE_PROFILE_SYNC'					=> 'Enable Profile Sync',
		'FACEBOOK_ID'							=> 'Facebook Application Id',
		'FACEBOOK_SECRET'						=> 'Facebook Application Secret',
        'FACEBOOK_KEY'                          => 'Facebook Application Key',
        'FACEBOOK_APP_ID'                       => 'Facebook Application ID',
		'WINDOWSLIVE_APP_ID'					=> 'Windows Live Application Id',
		'WINDOWSLIVE_SECRET'					=> 'Windows Live Secret Key',
		'NOT_REGISTERED'						=> 'You are not registered at this site.<br /><br />%sClick here to register%s',
	
		'LOGIN_FAILURE'							=> 'Login has failed.',
		'LOGIN_SUCCESS'							=> 'You have been successfully logged in!',
	
		'WINDOWSLIVE_ERROR'						=> 'An error occurred whilst attemtping to connect to Windows Live.',
		'SIGN_IN'								=> 'Sign In',
		'LOGGED_IN'								=> 'Logged In',
	
		'UCP_ALTERNATELOGIN'					=> 'Alternate Login Manager',
		'UCP_ALTERNATELOGIN_SETTINGS'			=> 'Alternate Login Settings',
		'UCP_AL_MAIN'							=> 'Main',
		'UCP_AL_FACEBOOK'						=> 'Facebook Manager',
		'UCP_AL_WINDOWSLIVE'					=> 'Windows Live Manager',
        'UCP_AL_FACEBOOK_SETTINGS'				=> 'Facebook Settings',
		'UCP_AL_FACEBOOK_DISABLED'				=> 'Facebook is disabled at this site.',
		'UCP_AL_ASK_DISABLE_FACEBOOK'			=> 'Disable Facebook Login',
		'UCP_AL_ASK_ENABLE_FACEBOOK'			=> 'Enable Facebook Login',
		'UCP_AL_ENABLE_WINDOWSLIVE_TEXT'		=> 'Enable Windows Live login.',
		'UCP_AL_ENABLE_FACEBOOK_TEXT'			=> 'Enable Facebook login.',
        'UCP_AL_DISABLE_WINDOWSLIVE_TEXT'		=> 'Disable Windows Live login.',
		'UCP_AL_DISABLE_FACEBOOK_TEXT'			=> 'Disable Facebook login.',
        'UCP_AL_UNLINK'							=> 'Are you sure you want to unlink your %s account?',
		'UCP_AL_LINK'							=> 'Are you sure you want to link your %s account?',
		'UCP_ENABLE_FACEBOOK_LOGIN'				=> 'Do you wish to enable Facebook login?',
		'UCP_AL_ACCOUNT_UNLINKED'				=> 'You have successfully unlined your %s account.<br /><br />You will recieve your new password to your registered email.',
		'UCP_AL_CONFIRM_DISABLE'				=> 'Are you sure you wish to disable Windows Live login?  You will be sent a new password by email.',
        'UCP_CONSOF'                             => 'ConSof',
		'UCP_DISABLE_AL_TEXT'					=> 'Unlink %s Account.',
		'UCP_DISABLE_AL_DESCRIPTION'			=> 'Please click the button opposite if you wish to disable your %s logon.<br />A new password will be sent to your registered email.  If you wish to link your forum account using one of the other available Alternate Logins then simply click the button to link the alternate account and this %s account will be unlinked automatically.',
		'UCP_ENABLE_AL_DESCRIPTION'				=> 'If you would like to link and login using your %s account then please login with the button opposite and we will link your account.',
		'DISABLE_WINDOWSLIVE'					=> 'Disable Windows Live Login',
		'DISABLE_FACEBOOK'						=> 'Disable Facebook Login',
        'ENABLE_FACEBOOK'						=> 'Enable Facebook Login',
		'AL_LINK_SUCCESS'						=> 'You have successfully linked your %s account <br /><br />In the future when logging in please use the %s button.',
    
        'SOCIAL_LOGIN_OPTIONS'                  => 'Social Login Options',
    
        'FB_ERROR_ACCESS_TOKEN'                 => 'An error occured attempting to retrieve an access token.',
        'FB_ERROR_USER'                         => 'An error coccured whilst trying to retrieve user data',
    
        'FB_NAME'                               => 'Name',
        'FB_USERNAME'                           => 'Username',
        'FB_DESCRIPTION'                        => 'Please enter a unique username for this site.',
        'FB_BIRTHDAY'                           => 'Birthday',
        'FB_GENDER'                             => 'Gender',
        'FB_LOCATION'                           => 'Location',
        'FB_EMAIL'                              => 'Email',
));

?>