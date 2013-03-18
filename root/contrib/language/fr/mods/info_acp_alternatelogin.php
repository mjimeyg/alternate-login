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
     'ACP_CONSOF'                           => 'ConSof',
	'ACP_ALTERNATELOGIN'					=> 'Connexion alternatives',
	'ACP_AL_MAIN_MANAGE'					=> 'Gestionnaire des connexion alternatives',
	'ACP_AL_FACEBOOK_MANAGE'				=> 'Gestionnaire Facebook',
	'ACP_AL_WINDOWSLIVE_MANAGE'				=> 'Gestionnaire Windows Live',
	'ACP_AL_OPENID_MANAGE'					=> 'Gestionnaire OpenID',
	//'ACP_AL_MYSPACE_MANAGE'					=> 'Gestionnaire MySpace',
    'ACP_AL_TWITTER_MANAGE'                 => 'Gestionnaire Twitter',

    'ACP_AL_SAVE_ERROR'                     => 'Erreur lors de l\'enregistrement des paramètres.',
    'ACP_AL_SAVE_SUCCESS'                   => 'Paramètres sauvegardés avec succès.',

    'ACP_ALTERNATELOGIN_SETTINGS_UPDATED'   => 'Paramètres mis à jours.',

    'TITLE_ENABLE_LOGIN'                    => 'Gestion des types de connexion',

    'ENABLE_LOGIN'                          => 'Activer la connexion',

    'FACEBOOK'                              => 'Facebook',
    'TWITTER'                               => 'Twitter',
    'WINDOWSLIVE'                           => 'Windows Live',
    'OPENID'                                => 'OpenID',

    'FACEBOOK_QUICK_ACCOUNTS'               => 'Comptes rapides Facebook',
    'FACEBOOK_APP_ID'                       => 'ID de l\'application Facebook',
    'FACEBOOK_KEY'                          => 'Clé d\'application Facebook',
	'FACEBOOK_SECRET'						=> 'Clé secrète Facebook',
    'FACEBOOK_PAGE_URL'                     => 'URL de la page Facebook',
	'FACEBOOK_DEFAULT_LANG'					=> 'Langue par défault des plugins Facebook',
    'SITE_DOMAIN'                           => 'Domaine du site',

    'FACEBOOK_ACTIVITY'                     => 'Facebook Activity',
    'FACEBOOK_FACEPILE'                     => 'Facebook Facepile',
    'FACEBOOK_STREAM'                       => 'Facebook Stream',
    'FACEBOOK_LIKE_BOX'                     => 'Facebook Like Box',

    'TWITTER_KEY'                           => 'Clé Twitter',
    'TWITTER_SECRET'                        => 'Clé secrète Twitter',
    'TWITTER_CALLBACK'                      => 'Callback Twitter',

    'WINDOWSLIVE_CLIENT_ID'                 => 'ID de l\'application Windows Live',
    'WINDOWSLIVE_SECRET'                    => 'Clé secrète Windows Live',
    'WINDOWSLIVE_CALLBACK'                  => 'Chemin vers le fichier "callback"',
    'WINDOWSLIVE_CHANNEL'                   => 'Chemin vers le fichier "channel"',
	'WINDOWSLIVE_QUICK_ACCOUNTS'            => 'Comptes rapides Windows Live',

    'TITLE_GENERAL_SETTINGS'                => 'Paramètres généraux',
    'REQUIRE_PASSWORD'                      => 'Mot de passe requis',
        
));

?>