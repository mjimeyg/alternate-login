<?php
/*COPYRIGHT 2009 Michael J Goonawardena

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
$user->add_lang('mods/info_ucp_alternatelogin');


$app_id = $config['al_fb_id'];
$app_secret = $config['al_fb_secret'];
$my_url = generate_board_url() . "/alternatelogin/al_fb_connect.php";

$code = request_var('code', '');

if(empty($code)) {
    $dialog_url = "http://www.facebook.com/dialog/oauth?client_id="
        . $app_id . "&redirect_uri=" . urlencode($my_url);

    $template->set_filenames(array(
        'body'		=> 'al_redirect.html')
    );
    
    $template->assign_vars(array(
        'S_DIALOG_URL'  => $dialog_url,
    ));
    
    page_header($user->lang['TITLE_FACEBOOK_REGISTRATION'], false);

    page_footer();
}





?>
