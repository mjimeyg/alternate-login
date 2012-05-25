<?php
/*
	COPYRIGHT 2011 Michael J Goonawardena

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
include($phpbb_root_path . 'includes/functions_alternatelogin.' . $phpEx);	// Custom Alternate Login functions.

// Set up a new user session.
$user->session_begin();
$auth->acl($user->data);
$user->setup('ucp');
$user->add_lang('mods/info_acp_alternatelogin');	// Global Alternate Login language file.
$user->add_lang('mods/info_ucp_alternatelogin');

$template->set_filenames(array(
        'body'		=> 'facebook_registration.html')
);
//echo get_domain_url();
$al_login               = (request_var('al_login', 0) == 1) ? true : false;
$al_login_type          = request_var('al_login_type', 0);
$fb_user                = request_var('al_fb_user', '');
$agreed                 = (!empty($_POST['agreed'])) ? 1 : 0;


if(!$agreed)
{
    redirect("{$phpbb_root_path}index.$phpEx");
}

$add_lang               = request_var('int', 'en_GB');
$coppa			= (isset($_REQUEST['coppa'])) ? ((!empty($_REQUEST['coppa'])) ? 1 : 0) : false;

$template->assign_vars(array(
    'S_REDIRECT_URI'    => generate_board_url() . "/alternatelogin/al_fb_register.$phpEx?mode=register&al_login=$al_login&al_login_type=$al_login_type&al_fb_user=$fb_user&coppa=$coppa",
    'S_FB_APP_ID'       => $config['al_fb_id'],
    'S_FB_INT'          => $add_lang,
));

page_header($user->lang['TITLE_FACEBOOK_REGISTRATION'], false);

page_footer();


?>
