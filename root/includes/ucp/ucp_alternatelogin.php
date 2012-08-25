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

if (!defined('IN_PHPBB'))
{
	exit;
}

class ucp_alternatelogin
{
	var $p_master;
	var $u_action;

	function ucp_alternatelogin(&$p_master)
	{
		$this->p_master = &$p_master;
	}

	function main($id, $mode)
	{
		global $config, $db, $user, $auth, $template, $phpbb_root_path, $phpEx;
		
		// Include the Alternate Login functions.
		include($phpbb_root_path . 'includes/functions_alternatelogin.' . $phpEx);
		
		// Include the AL language files.
		$user->add_lang('mods/info_ucp_alternatelogin');
		$user->add_lang('mods/info_acp_alternatelogin');
		$user->add_lang('ucp');

		$submit = isset($_POST['submit']) ? true : false;
		
		
		
		$template->assign_vars(array(
			'AL_WL_APP_ID'					=> $config['al_wl_client_id'],
			'S_MODE_WINDOWSLIVE'                            => ($mode == 'windowslive') ? true : false,
			'S_WINDOWSLIVE_LOGIN_ENABLED'                   => $user->data['al_wl_id'] ? true : false,
			'S_UCP_WINDOWSLIVE_DESCRIPTION'                 => $user->data['al_wl_id'] ? sprintf($user->lang['UCP_DISABLE_AL_DESCRIPTION'], $user->lang['WINDOWSLIVE'], $user->lang['WINDOWSLIVE']) : sprintf($user->lang['UCP_ENABLE_AL_DESCRIPTION'], $user->lang['WINDOWSLIVE']),
			
			'S_MODE_FACEBOOK'				=> ($mode == 'facebook') ? true : false,
			'S_FACEBOOK_LOGIN_ENABLED'                      => $user->data['al_fb_id'] ? true : false,
			'S_UCP_FACEBOOK_DESCRIPTION'                    => $user->data['al_fb_id'] ? sprintf($user->lang['UCP_DISABLE_AL_DESCRIPTION'], $user->lang['FACEBOOK'], $user->lang['FACEBOOK']) : sprintf($user->lang['UCP_ENABLE_AL_DESCRIPTION'], $user->lang['FACEBOOK']),
			
                        'S_MODE_TWITTER'				=> ($mode == 'twitter') ? true : false,
			'S_TWITTER_LOGIN_ENABLED'                       => $user->data['al_tw_id'] ? true : false,
			'S_UCP_TWITTER_DESCRIPTION'                     => $user->data['al_tw_id'] ? sprintf($user->lang['UCP_DISABLE_AL_DESCRIPTION'], $user->lang['TWITTER'], $user->lang['TWITTER']) : sprintf($user->lang['UCP_ENABLE_AL_DESCRIPTION'], $user->lang['TWITTER']),
			
			'S_HIDDEN_FIELDS'				=> (isset($s_hidden_fields)) ? $s_hidden_fields : '',
			'S_UCP_ACTION'					=> $this->u_action,
		));

		// Set desired template
		$this->tpl_name = 'ucp_alternatelogin';
		$this->page_title = 'UCP_ALTERNATELOGIN';
	}
}

?>