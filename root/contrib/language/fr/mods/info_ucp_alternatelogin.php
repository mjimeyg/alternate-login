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
    'ALTERNATE_LOGIN'						=> 'Ce site vous permet de vous connecter à l\'aide de différents médias sociaux.',

    'AL_LOGIN'								=> 'Mode de connexion alternatif',
	'AL_LOGIN_EXPLAIN'						=> 'Vous pouvez sélectionner un mode de connexion alternatif. Un nom d\'utilisateur et un mot de passe est quand même nécessaire.',
	'AL_REGISTER_QUERY'						=> 'Voulez-vous vous inscrire à l\'aide de votre compte %s ?',
	'AL_REGISTRATION'						=> 'Si vous avez un compte sur les médias sociaux suivant et voulez l\'utiliser pour vous connecter, cliquez sur une des icones suivante', 
	'AL_LOGIN_UNAVAILABLE'					=> '%s n\'est pas disponible.',
	'AL_PHPBB_DB_FAILURE'					=> 'Impossible de se connecter à la base de donnée phpBB.',
    
    'AL_FB_HIDE_ACTIVITY'                   => 'Cacher le plugin Facebook Activity',
    'AL_FB_HIDE_STREAM'                     => 'Cacher le plugin Facebook Stream',
    'AL_FB_HIDE_FACEPILE'                   => 'Cacher le plugin Facepile',
    'AL_FB_HIDE_LIKE_BOX'                   => 'Cacher le bouton "J\'aime"',

    'FB_SYNC'                               => 'Synchroniser votre profil à votre profil Facebook',
    'FB_STATUS'                             => 'Synchroniser votre signature avec vos status Facebook',
    'FB_AVATAR'                             => 'Photo de profil Facebook',
    'FB_AVATAR_EXPLAIN'                     => 'Synchroniser votre avatar avec votre photo de profil Facebook.',

    'WL_SYNC'                               => 'Synchroniser votre profil avec votre profil Windows Live',
    'WL_STATUS'                             => 'Synchroniser votre signature avec vos status Windows Live',
    'WL_AVATAR'                             => 'Photo de profil Windows Live',
    'WL_AVATAR_EXPLAIN'                     => 'Synchroniser votre avatar avec votre photo de profil Windows Live.',

    'TW_SYNC'                               => 'Synchroniser votre profil avec votre compte Twitter',
    'TW_STATUS'                             => 'Synchroniser votre signature avec vos Tweet',
    'TW_AVATAR'                             => 'Photo de profil Twitter',
    'TW_AVATAR_EXPLAIN'                     => 'Synchroniser votre avatar avec votre photo de profil Twitter.',

    'TITLE_ENABLE_LOGIN_PROFILE'			=> 'Activer la connexion alternative et la synchronisation des profils.',
	'FACEBOOK'								=> 'Facebook',
	'WINDOWSLIVE'							=> 'Windows Live',
    'OPENID'                                => 'OpenID',
	'ENABLE_LOGIN'							=> 'Activer la connexion',
	'ENABLE_PROFILE_SYNC'					=> 'Activer la synchronisation des profils',
	'FACEBOOK_ID'							=> 'ID d\'application Facebook',
	'FACEBOOK_SECRET'						=> 'Clé secrète Facebook',
    'FACEBOOK_KEY'                          => 'Clé d\'application Facebook',
    'FACEBOOK_APP_ID'                       => 'ID de l\'application Facebook',
	'WINDOWSLIVE_APP_ID'					=> 'ID de l\'application Windows Live',
	'WINDOWSLIVE_SECRET'					=> 'Clé secrète Windows Live',
	'NOT_REGISTERED'						=> 'Vous n\'êtes pas inscrit sur ce site.<br /><br />%sCliquez ici pour vous inscrire%s',
	
	'LOGIN_FAILURE'							=> 'La connexion à échoué.',
	'LOGIN_SUCCESS'							=> 'Vous vous êtes connecté avec succès.',

	'WINDOWSLIVE_ERROR'						=> 'Une erreur Windows Live.',
	'SIGN_IN'								=> 'Connexion',
	'LOGGED_IN'								=> 'Connecté',

	'UCP_ALTERNATELOGIN'					=> 'Gestionnaire des connexions alternatives',
	'UCP_ALTERNATELOGIN_SETTINGS'			=> 'Paramètres des connexions alternatives',
	'UCP_AL_MAIN'							=> 'Général',
	'UCP_AL_FACEBOOK'						=> 'Gestionnaire Facebook',
	'UCP_AL_WINDOWSLIVE'					=> 'Gestionnaire Windows Live',
    'UCP_AL_TWITTER'						=> 'Gestionnaire Twitter',
	'UCP_AL_FACEBOOK_SETTINGS'				=> 'Paramètres Facebook',
	'UCP_AL_FACEBOOK_DISABLED'				=> 'Facebook est désactivé.',
	'UCP_AL_ASK_DISABLE_FACEBOOK'			=> 'Désactiver la connexion depuis Facebook',
	'UCP_AL_ASK_ENABLE_FACEBOOK'			=> 'Activer la connexion depuis Facebook',
	'UCP_AL_ENABLE_WINDOWSLIVE_TEXT'		=> 'Activer la connexion depuis Windows Live',
	'UCP_AL_ENABLE_FACEBOOK_TEXT'			=> 'Activer la connexion depuis Facebook',
    'UCP_AL_ENABLE_TWITTER_TEXT'			=> 'Activer la connexion depuis Twitter',
	'UCP_AL_DISABLE_WINDOWSLIVE_TEXT'		=> 'Désactiver la connexion depuis  Windows Live',
	'UCP_AL_DISABLE_FACEBOOK_TEXT'			=> 'Désactiver la connexion depuis  Facebook',
    'UCP_AL_DISABLE_TWITTER_TEXT'			=> 'Désactiver la connexion depuis  Twitter',
	'UCP_AL_UNLINK'							=> 'Êtes-vous certain de vouloir dissocier votre compte %s ?',
	'UCP_AL_LINK'							=> 'Êtes-vous certain de vouloir lier votre compte %s ?',
	'UCP_ENABLE_FACEBOOK_LOGIN'				=> 'Activer la connexion depuis Facebook ?',
	'UCP_AL_ACCOUNT_UNLINKED'				=> 'Vous avez dissocié votre comptre %s avec succès.<br /><br />Un nouveau mot de passe a été envoyé à votre email d\'inscription.',
	'UCP_AL_CONFIRM_DISABLE'				=> 'Êtes-vous certain de vouloir désactiver la connexion depuis votre compte Windows Live login? Un mot de passe vous sera envoyé par email pour que vous puissiez vous connecter.',
    'UCP_CONSOF'               			    => 'ConSof',
	'UCP_DISABLE_AL_TEXT'					=> 'Dissocier le compte %s.',
	'UCP_DISABLE_AL_DESCRIPTION'			=> 'Cliquez sur le bouton suivant pour dissocier la connexion depuis %s.<br />Un nouveau mot de passe sera envoyé à votre email d\'inscription. Si vous voulez lier votre compte à une autre méthode de connexion disponible, cliquez simplement sur le bouton approprié. Votre compte %s sera dissocié automatiquement.',
	'UCP_ENABLE_AL_DESCRIPTION'				=> 'Si vous souhaitez lier et vous connecter depuis votre compte %s, cliquez sur le bouton suivant.',
	'DISABLE_WINDOWSLIVE'					=> 'Désactiver la connexion depuis Windows Live',
	'DISABLE_FACEBOOK'						=> 'Désactiver la connexion depuis Facebook',
    'DISABLE_TWITTER'						=> 'Désactiver la connexion depuis Twitter',
	'ENABLE_FACEBOOK'						=> 'Activer la connexion depuis Facebook',
	'AL_LINK_SUCCESS'						=> 'Votre compte %s a été lié avec succès.<br /><br />Vous pouvez désormais vous connecter au site en utilisant le bouton %s.',
    
    'SOCIAL_LOGIN_OPTIONS'                  => 'Paramètre de connexion depuis les médias sociaux',

    'FB_ERROR_ACCESS_TOKEN'                	=> 'Une erreur a été rencontré lors de la récupération du jeton d\'accès.',
    'FB_ERROR_USER'                         => 'Une erreur a été rencontré lors de la récupération des informations de l\'utilisateur',

    'FB_NAME'               => 'Nom',
    'FB_USERNAME'           => 'Pseudo',
    'FB_DESCRIPTION'        => 'Sélectionner un pseudonyme unique pour ce site',
    'FB_BIRTHDAY'           => 'Anniveraire',
    'FB_GENDER'             => 'Sexe',
    'FB_LOCATION'           => 'Localisation',
    'FB_EMAIL'              => 'Email',
));

?>