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
	'ALTERNATE_LOGIN'						=> 'Auf dieser Seite kannst du dich anmelden, indem du einen Sozial Network Account benutzt.',

	'AL_LOGIN'								=> 'Auswählen und alternativ anmelden',
	'AL_LOGIN_EXPLAIN'						=> 'Du kannst eine alternative Anmeldungs Methode auswählen. Wir werden dennoch einen Benutzernamen und ein Passwort verwenden.',
	'AL_REGISTER_QUERY'						=> 'Willst du dich mit deinem %s Account anmelden?',
	'AL_REGISTRATION'						=> 'Wenn du einen alternativen Sozial Networt Account hast, den du auf dieser Seite verwenden willst, klicke auf einen der Icons unten <b>FIRST</b>.',
	'AL_LOGIN_UNAVAILABLE'					=> '%s ist nich verfügbar auf dieser Seite.',
	'AL_PHPBB_DB_FAILURE'					=> 'Beim Versuch eine Verbindung zu der phpBB Datenbank herzustellen, ist ein Fehler aufgetretten.',
	
	'AL_FB_HIDE_ACTIVITY'                   => 'Facebook Activity Plugin ausblenden',
	'AL_FB_HIDE_STREAM'                     => 'Facebook Stream Plugin ausblenden',
	'AL_FB_HIDE_FACEPILE'                   => 'Facebook Facepile Plugin ausblenden',
	'AL_FB_HIDE_LIKE_BOX'                   => 'Facebook Like Box Plugin ausblenden',

	'FB_SYNC'                               => 'Profil mit Facebook synchronisieren',
	'FB_STATUS'                             => 'Signatur mit dem Facebook Status synchronisieren',
	'FB_AVATAR'                             => 'Facebook Profil Bild',
	'FB_AVATAR_EXPLAIN'                     => 'Avatar mit Facebook Profil Bild synchronisieren.',
	
	'WL_SYNC'                               => 'Profil mit Windows Live synchronisieren',
	'WL_STATUS'                             => 'Signatur mit Windows Live Status synchronisieren',
	'WL_AVATAR'                             => 'Windows Live Profil Bild',
	'WL_AVATAR_EXPLAIN'                     => 'Avatar mit Windows Live Profil Bild synchronisieren.',

	'TW_SYNC'                               => 'Profil mit Twitter synchronisieren',
	'TW_STATUS'                             => 'Signatur mit Twitter Status synchronisieren',
	'TW_AVATAR'                             => 'Twitter Profil Bild',
	'TW_AVATAR_EXPLAIN'                     => 'Avatar mit Twitter Profil Bild synchronisieren.',

	'TITLE_ENABLE_LOGIN_PROFILE'			=> 'Alternative Anmeldungen und Profil Synchronisationen aktivieren.',
	'FACEBOOK'								=> 'Facebook',
	'WINDOWSLIVE'							=> 'Windows Live',
	'OPENID'                                => 'OpenID',
	'ENABLE_LOGIN'							=> 'Anmeldung aktivieren',
	'ENABLE_PROFILE_SYNC'					=> 'Profil Sync aktivieren',
	'FACEBOOK_ID'							=> 'Facebook Application Id',
	'FACEBOOK_SECRET'						=> 'Facebook Application Secret',
	'FACEBOOK_KEY'                          => 'Facebook Application Key',
	'FACEBOOK_APP_ID'                       => 'Facebook Application ID',
	'WINDOWSLIVE_APP_ID'					=> 'Windows Live Application Id',
	'WINDOWSLIVE_SECRET'					=> 'Windows Live Secret Key',
	'NOT_REGISTERED'						=> 'Du bist auf dieser Seite nicht registriert.<br /><br />%sKlicke hier um dich zu registrieren%s',
	
	'LOGIN_FAILURE'							=> 'Die Anmeldung ist gescheitert.',
	'LOGIN_SUCCESS'							=> 'Du hast dich erfolgreich eingeloggt!',

	'WINDOWSLIVE_ERROR'						=> 'Bei dem Versuch eine Verbunding mit Windows Live aufzubauen, ist ein Fehler aufgetretten.',
	'SIGN_IN'								=> 'Registrieren',
	'LOGGED_IN'								=> 'Einloggen',

	'UCP_ALTERNATELOGIN'					=> 'Alternate Anmeldungs Manager',
	'UCP_ALTERNATELOGIN_SETTINGS'			=> 'Alternate Anmeldungs Einstellungen',
	'UCP_AL_FACEBOOK'						=> 'Facebook Manager',
	'UCP_AL_WINDOWSLIVE'					=> 'Windows Live Manager',
	'UCP_AL_TWITTER'						=> 'Twitter Manager',
	'UCP_AL_FACEBOOK_SETTINGS'				=> 'Facebook Einstellungen',
	'UCP_AL_FACEBOOK_DISABLED'				=> 'Facebook ist auf dieser Seite deaktiviert.',
	'UCP_AL_ASK_DISABLE_FACEBOOK'			=> 'Facebook Anmeldung deaktivieren',
	'UCP_AL_ASK_ENABLE_FACEBOOK'			=> 'Facebook Anmeldung aktivieren',
	'UCP_AL_ENABLE_WINDOWSLIVE_TEXT'		=> 'Windows Live Anmeldung aktivieren.',
	'UCP_AL_ENABLE_FACEBOOK_TEXT'			=> 'Facebook Anmeldung aktivieren.',
	'UCP_AL_ENABLE_TWITTER_TEXT'			=> 'Twitter Anmeldung aktivieren.',
	'UCP_AL_DISABLE_WINDOWSLIVE_TEXT'		=> 'Windows Live Anmeldung deaktivieren.',
	'UCP_AL_DISABLE_FACEBOOK_TEXT'			=> 'Facebook Anmeldung deaktivieren.',
	'UCP_AL_DISABLE_TWITTER_TEXT'			=> 'Twitter Anmeldung deaktivieren.',
	'UCP_AL_UNLINK'							=> 'Bist du sicher, dass du die Verknüpfung mit deinem %s Account aufheben möchtest?',
	'UCP_AL_LINK'							=> 'Bist du sicher, dass du deinen %s Account verknüpfen willst?',
	'UCP_ENABLE_FACEBOOK_LOGIN'				=> 'Willst du die Facebook Anmeldung aktivieren?',
	'UCP_AL_ACCOUNT_UNLINKED'				=> 'Du hast deinen %s Account erfolgreich verknüpft.<br /><br />Wir schicken dir eine E-Mail mit deinem Passwort.',
	'UCP_AL_CONFIRM_DISABLE'				=> 'Bist du sicher, dass du die Windows Live Anmeldung deaktivieren willst? Wir senden dir eine E-Mail mit deinem Passwort.',
	'UCP_CONSOF'                            => 'ConSof',
	'UCP_DISABLE_AL_TEXT'					=> 'Verknüpfung mit dem %s Account aufheben.',
	'UCP_DISABLE_AL_DESCRIPTION'			=> 'Bitte klicke auf den gegenüberliegende Button, wenn du deine %s Anmeldung deaktivieren willst.<br />Wir senden dir eine E-Mail mit deinem Passwort.  Wenn du deinen Forum Account mit einem anderen alternativen Sozial Netzwerk verbinden willst, klicke auf einen der alternativen Anmeldungen. Die Verknüpfung mit dem alte %s Account wird automatisch aufgelöst.',
	'UCP_ENABLE_AL_DESCRIPTION'				=> 'Wenn du deinen %s Account nutzen willst, um dich anzumelden, klick auf einen der unteren Buttons und wir verknüpfen deinen Account.',
	'DISABLE_WINDOWSLIVE'					=> 'Windows Live Anmeldung deaktivieren',
	'DISABLE_FACEBOOK'						=> 'Facebook Anmeldung deaktivieren',
	'DISABLE_TWITTER'						=> 'Twitter Anmeldung deaktivieren',
	'ENABLE_FACEBOOK'						=> 'Facebook Anmeldung aktivieren',
	'AL_LINK_SUCCESS'						=> 'Du hast deinen %s Account erfolgreich mit deinem Account verbunden. <br /><br />Wenn du dich in Zukunft anmelden willst, benutze bitte den %s Button.',
	
	'SOCIAL_LOGIN_OPTIONS'                  => 'Social Login Einstellungen',
	
	'FB_ERROR_ACCESS_TOKEN'                 => 'Bei dem Versuch, eine Berechtigung zu holen, ist ein Fehler aufgetretten.',
	'FB_ERROR_USER'                         => 'Bei dem Versuch deine Daten in deinen Foren Account zu übertragen, ist ein Fehler aufgetretten',
	'FB_NAME'                               => 'Name',
	'FB_USERNAME'                           => 'Benutzername',
	'FB_DESCRIPTION'                        => 'Bitte gib nochmal deinen Vor- und Nachnamen ein.',
	'FB_BIRTHDAY'                           => 'Geburtstag',
	'FB_GENDER'                             => 'Geschlecht',
	'FB_LOCATION'                           => 'Wohnort',
	'FB_EMAIL'                              => 'Email',
	'LOGIN_DESCRIPTION'						=> 'Anmelden oder einen neuen Account erstellen',
	'PROVIDER_SELECT'						=> 'Bitte wähle ein Sozial Network aus, mit dem du dich anmelden willst:',
));

?>