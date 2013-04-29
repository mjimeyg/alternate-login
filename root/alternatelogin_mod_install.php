<?php
/**
*
* @author Username (mjimeyg) michaelgoonawardena@googlemail.com
* @package umil
* @copyright (c) 2008 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

/**
* @ignore
*/
define('UMIL_AUTO', true);
define('IN_PHPBB', true);
$phpbb_root_path = (defined('PHPBB_ROOT_PATH')) ? PHPBB_ROOT_PATH : './';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
include($phpbb_root_path . 'common.' . $phpEx);
$user->session_begin();
$auth->acl($user->data);
$user->setup();

if (!file_exists($phpbb_root_path . 'umil/umil_auto.' . $phpEx))
{
	trigger_error('Please download the latest UMIL (Unified MOD Install Library) from: <a href="http://www.phpbb.com/mods/umil/">phpBB.com/mods/umil</a>', E_USER_ERROR);
}

// The name of the mod to be displayed during installation.
$mod_name = 'ConSof Alternate Login System';

/*
* The name of the config variable which will hold the currently installed version
* UMIL will handle checking, setting, and updating the version itself.
*/
$version_config_name = '2013.03.22.01';

/*
* The language file which will be included when installing
* Language entries that should exist in the language file for UMIL (replace $mod_name with the mod's name you set to $mod_name above)
* $mod_name
* 'INSTALL_' . $mod_name
* 'INSTALL_' . $mod_name . '_CONFIRM'
* 'UPDATE_' . $mod_name
* 'UPDATE_' . $mod_name . '_CONFIRM'
* 'UNINSTALL_' . $mod_name
* 'UNINSTALL_' . $mod_name . '_CONFIRM'
*/
$language_file = 'mods/info_ucp_alternatelogin';

/*
* Options to display to the user (this is purely optional, if you do not need the options you do not have to set up this variable at all)
* Uses the acp_board style of outputting information, with some extras (such as the 'default' and 'select_user' options)
*
$options = array(
	'test_username'	=> array('lang' => 'TEST_USERNAME', 'type' => 'text:40:255', 'explain' => true, 'default' => $user->data['username'], 'select_user' => true),
	'test_boolean'	=> array('lang' => 'TEST_BOOLEAN', 'type' => 'radio:yes_no', 'default' => true),
);*/

/*
* Optionally we may specify our own logo image to show in the upper corner instead of the default logo.
* $phpbb_root_path will get prepended to the path specified
* Image height should be 50px to prevent cut-off or stretching.
*/
//$logo_img = 'styles/prosilver/imageset/site_logo.gif';

/*
* The array of versions and actions within each.
* You do not need to order it a specific way (it will be sorted automatically), however, you must enter every version, even if no actions are done for it.
*
* You must use correct version numbering.  Unless you know exactly what you can use, only use X.X.X (replacing X with an integer).
* The version numbering must otherwise be compatible with the version_compare function - http://php.net/manual/en/function.version-compare.php
*/
$versions = array(
	// Version 0.3.0
	'2012.08.26'	=> array(


                'table_column_add' => array(

			array(USERS_TABLE, 'al_fb_id', array('VCHAR', '0')),
                        array(USERS_TABLE, 'al_wl_id', array('VCHAR', '0')),
                        array(USERS_TABLE, 'al_oi_id', array('VCHAR', '0')),
                        array(USERS_TABLE, 'al_fb_profile_sync', array('BOOL', '0')),
                        array(USERS_TABLE, 'al_fb_status_sync', array('BOOL', '0')),
                        array(USERS_TABLE, 'al_fb_avatar_sync', array('BOOL', '0')),
                        array(USERS_TABLE, 'al_wl_profile_sync', array('BOOL', '0')),
                        array(USERS_TABLE, 'al_wl_status_sync', array('BOOL', '0')),
                        array(USERS_TABLE, 'al_wl_avatar_sync', array('BOOL', '0')),
                        array(USERS_TABLE, 'al_fb_hide_activity', array('BOOL', '0')),
                        array(USERS_TABLE, 'al_fb_hide_facepile', array('BOOL', '0')),
                        array(USERS_TABLE, 'al_fb_hide_like_box', array('BOOL', '0')),
                        
		),

            
                'config_add' => array(
                    array('al_fb_login', 0, 1),
                    array('al_wl_login', 0, 1),
                    array('al_oi_login', 0, 1),
                    array('al_wl_client_id', 0, 1),
                    array('al_wl_secret', 0, 1),
                    array('al_fb_id', 0, 1),
                    array('al_fb_secret', 0, 1),
                    array('al_site_domain', 0, 1),
					array('al_fb_login_text', 'Login with Facebook', 1),
                    array('al_fb_facepile', 0, 1),
                    array('al_fb_activity', 0, 1),
                    array('al_fb_quick_accounts', 0, 1),
					array('al_wl_quick_accounts', 0, 1),
                ),


		// Alright, now lets add some modules to the ACP
		'module_add' => array(
			// Add a main category
			array('acp', 0, 'ACP_CONSOF'),

			// First, lets add a new category named ACP_CAT_TEST_MOD to ACP_CAT_DOT_MODS
			array('acp', 'ACP_CONSOF', 'ACP_ALTERNATELOGIN'),

			// Now we will add the settings and features modes from the acp_board module to the ACP_CAT_TEST_MOD category using the "automatic" method.
			array('acp', 'ACP_ALTERNATELOGIN', array(
					'module_basename'		=> 'alternatelogin',
					'modes'					=> array('manage', 'facebook', 'windowslive'),
				),
			),
                        array('ucp', 0, 'SOCIAL_LOGIN_OPTIONS'),

                        array('ucp', 'SOCIAL_LOGIN_OPTIONS', array(
					'module_basename'		=> 'alternatelogin',
					'modes'					=> array('main'),
				),
			),
		),

	),
);

// Include the UMIF Auto file and everything else will be handled automatically.
include($phpbb_root_path . 'umil/umil_auto.' . $phpEx);

/*
* Here is our custom function that will be called for version 0.9.2.
*
* @param string $action The action (install|update|uninstall) will be sent through this.
* @param string $version The version this is being run for will be sent through this.
*/
function umil_auto_example($action, $version)
{
	global $db, $table_prefix, $umil;

	if ($action == 'uninstall')
	{
		// Run this when uninstalling
		$umil->table_row_remove('phpbb_test', array('test_text' => 'This is a test message. (Edited)'));
		$umil->table_row_remove('phpbb_test', array('test_text' => 'This is another test message.'));
	}

	/**
	* Return a string
	* 	The string will be shown as the action performed (command).  It will show any SQL errors as a failure, otherwise success
	*/
	// return 'EXAMPLE_CUSTOM_FUNCTION';

	/**
	* Return an array
	* 	With the keys command and result to specify the command and the result
	*	Returning a result (other than SUCCESS) assumes a failure
	*/
	/* return array(
		'command'	=> 'EXAMPLE_CUSTOM_FUNCTION',
		'result'	=> 'FAIL',
	);*/

	/**
	* Return an array
	* 	With the keys command and result (same as above) with an array for the command.
	*	With an array for the command it will use sprintf the first item in the array with the following items.
	*	Returning a result (other than SUCCESS) assumes a failure
	*/
	/* return array(
		'command'	=> array(
			'EXAMPLE_CUSTOM_FUNCTION',
			$username,
			$number,
			$etc,
		),
		'result'	=> 'FAIL',
	);*/
}

?>