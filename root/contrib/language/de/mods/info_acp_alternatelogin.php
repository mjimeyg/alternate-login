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
	'ACP_CONSOF'               		        => 'ConSof',
	'ACP_ALTERNATELOGIN'					=> 'Alternatives Anmelden',
	'ACP_AL_MAIN_MANAGE'					=> 'Alternatives Anmelden Manager',
	'ACP_AL_FACEBOOK_MANAGE'				=> 'Facebook Manager',
	'ACP_AL_WINDOWSLIVE_MANAGE'				=> 'Windows Live Manager',
	'ACP_AL_OPENID_MANAGE'					=> 'OpenID Manager',
	'ACP_AL_MYSPACE_MANAGE'					=> 'MySpace Manager',
	'ACP_AL_TWITTER_MANAGE'                 => 'Twitter Manager',
	
	'ACP_AL_SAVE_ERROR'                     => 'Das speichern der Einstellungen ist fehlgeschlagen.',
	'ACP_AL_SAVE_SUCCESS'                   => 'Einstellungen erfolgreich gespeichert.',

	'ACP_ALTERNATELOGIN_SETTINGS_UPDATED'   => 'Einstellungen aktualisiert.',

	'TITLE_ENABLE_LOGIN'                    => 'Anmeldungs Typen verwalten',

	'ENABLE_LOGIN'                          => 'Anmelden aktivieren',

	'FACEBOOK'                              => 'Facebook',
	'TWITTER'                               => 'Twitter',
	'WINDOWSLIVE'                           => 'Windows Live',
	'OPENID'                                => 'OpenID',

	'FACEBOOK_QUICK_ACCOUNTS'               => 'Facebook Quick Accounts',
	'FACEBOOK_APP_ID'                       => 'Facebook App ID',
	'FACEBOOK_KEY'                          => 'Facebook Key',
	'FACEBOOK_SECRET'                       => 'Facebook Secret',
	'FACEBOOK_PAGE_URL'                     => 'Facebook Page Url',
	'SITE_DOMAIN'                           => 'Seiten Domain',

	'FACEBOOK_ACTIVITY'                     => 'Facebook Activity',
	'FACEBOOK_FACEPILE'                     => 'Facebook Facepile',
	'FACEBOOK_STREAM'                       => 'Facebook Stream',
	'FACEBOOK_LIKE_BOX'                     => 'Facebook Like Box',
	
	'TWITTER_KEY'                           => 'Twitter Key',
	'TWITTER_SECRET'                        => 'Twitter Secret',
	'TWITTER_CALLBACK'                      => 'Twitter Callback',

	'WINDOWSLIVE_CLIENT_ID'                 => 'Windows Live App ID',
	'WINDOWSLIVE_SECRET'                    => 'Windows Live Secret',
	'WINDOWSLIVE_CALLBACK'                  => 'Path to callback file',
	'WINDOWSLIVE_CHANNEL'                   => 'Path to channel file',
	
	'TITLE_GENERAL_SETTINGS'                => 'Einstellungen',
	'REQUIRE_PASSWORD'                      => 'Password erforderlich',
));

?>