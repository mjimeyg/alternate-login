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


class acp_alternatelogin
{
	var $u_action;
	var $p_master;

	function acp_users(&$p_master)
	{
		$this->p_master = &$p_master;
	}

	function main($id, $mode)
	{
		global $config, $db, $user, $auth, $template, $cache;
		global $phpbb_root_path, $phpbb_admin_path, $phpEx, $table_prefix, $file_uploads;

		// Set the template details.
		$this->tpl_name = 'acp_alternatelogin';
		$this->page_title = 'ACP_ALTERNATELOGIN';
		
		// Include the Alternate Login functions file.
		if(!function_exists('get_fb_access_token'))
		{
			include($phpbb_root_path . 'includes/functions_alternatelogin.' . $phpEx);
		}
                
		// Retrieve the action and submit values.
		$action		= request_var('action', '');

		$submit		= isset($_POST['submit']) ? true : false;

		// Set the form name and add the form key.
		$form_name = 'acp_alternatelogin';
		add_form_key($form_name);
		
		
		// Process the submit action.
		if($submit)
		{
			switch($mode)
			{
				case 'manage':	// Process the main management page to determine which logins are available.
				
					// Retrieve the values that have been selected on the form.
					$facebook_login = request_var('facebook_login', '');

					$windowslive_login = request_var('windowslive_login', '');
                                        
					$openid_login = request_var('openid_login', '');

					set_config('al_fb_login', $facebook_login);

					set_config('al_wl_login', $windowslive_login);
					
					set_config('al_oi_login', $openid_login);
					
					// Let the user know its been done.
					trigger_error($user->lang['ACP_ALTERNATELOGIN_SETTINGS_UPDATED'] . adm_back_link($this->u_action));
					
				break;
				
				case 'facebook':
					
					// Retrieve the submitted values and store and save them in the config database.
					$facebook_id = request_var('facebook_id', '');
					
					set_config('al_fb_id', $facebook_id, true);
					
					$facebook_secret = request_var('facebook_secret', '');
					
					set_config('al_fb_secret', $facebook_secret, true);

                    $facebook_key = request_var('facebook_key', '');

					set_config('al_fb_key', $facebook_key, true);
                                        
                    $facebook_page_url = request_var('facebook_page_url', '');

					set_config('al_fb_page_url', $facebook_page_url, true);
					
					$facebook_page_id = request_var('facebook_page_id', '');

					set_config('al_fb_page_id', $facebook_page_id, true);
					
					$facebook_page_token = request_var('facebook_page_token', '');

					set_config('al_fb_page_token', $facebook_page_token, true);
                                        
					$site_domain = request_var('site_domain', '');

					set_config('al_site_domain', $site_domain);
					
					$facebook_login_button_text = request_var('facebook_login_button_text', '');
					
					set_config('al_fb_login_text', $facebook_login_button_text);
					
					$facebook_quick_accounts = request_var('facebook_quick_accounts', 0);

					set_config('al_fb_quick_accounts', $facebook_quick_accounts);

					$facebook_activity = request_var('facebook_activity', 0);

					set_config('al_fb_activity', $facebook_activity);

					$facebook_facepile = request_var('facebook_facepile', 0);

					set_config('al_fb_facepile', $facebook_facepile);

					$facebook_like_box = request_var('facebook_like_box', 0);

					set_config('al_fb_like_box', $facebook_like_box);
					
					$facebook_default_lang = request_var('facebook_default_lang', 'en_GB');

					set_config('al_fb_default_lang', $facebook_default_lang);
					
					$facebook_friends_list = request_var('facebook_friends_list', 0);

					set_config('al_fb_friends_list', $facebook_friends_list);
					
					$facebook_topic_post_page = request_var('facebook_topic_post_page', 'en_GB');

					set_config('al_facebook_topic_post_page', $facebook_topic_post_page);
					
					$facebook_topic_post_page_exclusions = request_var('facebook_topic_post_to_page_exclusions', array(0));
					
					set_config('al_facebook_topic_post_page_exclusions', json_encode($facebook_topic_post_page_exclusions));
					
					if($facebook_quick_accounts)
					{
						set_config('max_name_chars', 30);
					}
					

					trigger_error($user->lang['ACP_ALTERNATELOGIN_SETTINGS_UPDATED'] . adm_back_link($this->u_action));
				
				break;
				
				case 'windowslive':
				
					$app_id = request_var('windowslive_app_id', '');
					
					$app_secret = request_var('windowslive_secret', '');
                                        
					$app_callback = request_var('windowslive_callback', '');
					
					$windowslive_quick_accounts = request_var('windowslive_quick_accounts', 0);
					

					set_config('al_wl_client_id', $app_id);
					
					set_config('al_wl_secret', $app_secret);
					
					set_config('al_wl_callback', $app_callback);
					
					set_config('al_wl_quick_accounts', $windowslive_quick_accounts);
                                        
					trigger_error($user->lang['ACP_AL_SAVE_SUCCESS'] . adm_back_link($this->u_action));
					
					
				break;
                    
			}
		}
		
		$fb_valid_php_version = (version_compare(phpversion(), '5.4.0', '>=')) ? true : false;
		
		// This section deals with preparing variables and values for the
		// template.
		switch($mode)
		{
			case 'facebook':
				
				$template->assign_vars(array(
					'FACEBOOK_ALLOWED'						=> $fb_valid_php_version,
					'FACEBOOK_PHP_VERSION_LOW'				=> sprintf($user->lang['FACEBOOK_PHP_VERSION_LOW'], PHP_VERSION),
					'FACEBOOK_APP_ID'                  		=> isset($config['al_fb_id']) ? $config['al_fb_id']: '',
					'FACEBOOK_SECRET'                  		=> isset($config['al_fb_secret']) ? $config['al_fb_secret'] : '',
					'FACEBOOK_KEY'                   		=> isset($config['al_fb_key']) ? $config['al_fb_key'] : '',
					'FACEBOOK_PAGE_URL'                     => isset($config['al_fb_page_url']) ? $config['al_fb_page_url'] : '',
					'FACEBOOK_PAGE_ID'                      => isset($config['al_fb_page_id']) ? $config['al_fb_page_id'] : '',
					'FACEBOOK_PAGE_TOKEN'               	=> isset($config['al_fb_page_token']) ? $config['al_fb_page_token'] : '',
					'SITE_DOMAIN'                           => isset($config['al_site_domain']) ? $config['al_site_domain'] : '',
					'FACEBOOK_LOGIN_BUTTON_TEXT'         	=> isset($config['al_fb_login_text']) ? $config['al_fb_login_text'] : '',
					'FACEBOOK_DEFAULT_LANG'                 => (!isset($config['al_fb_default_lang'])) ? $this->fb_language_select('en_US') : $this->fb_language_select($config['al_fb_default_lang']),
					'FACEBOOK_QUICK_ACCOUNTS_YES'           => (isset($config['al_fb_quick_accounts']) && $config['al_fb_quick_accounts']) ? 'checked="checked"' : '',
					'FACEBOOK_QUICK_ACCOUNTS_NO'            => (isset($config['al_fb_quick_accounts']) && $config['al_fb_quick_accounts']) ? '' : 'checked="checked"',
					'FACEBOOK_ACTIVITY_YES'                 => (isset($config['al_fb_activity']) && $config['al_fb_activity']) ? 'checked="checked"' : '',
					'FACEBOOK_ACTIVITY_NO'                  => (isset($config['al_fb_activity']) && $config['al_fb_activity']) ? '' : 'checked="checked"',
					'FACEBOOK_FACEPILE_YES'                 => (isset($config['al_fb_facepile']) && $config['al_fb_facepile']) ? 'checked="checked"' : '',
					'FACEBOOK_FACEPILE_NO'                  => (isset($config['al_fb_facepile']) && $config['al_fb_facepile']) ? '' : 'checked="checked"',
					'FACEBOOK_LIKE_BOX_YES'                 => (isset($config['al_fb_like_box']) && $config['al_fb_like_box']) ? 'checked="checked"' : '',
					'FACEBOOK_LIKE_BOX_NO'                  => (isset($config['al_fb_like_box']) && $config['al_fb_like_box']) ? '' : 'checked="checked"',
					'FACEBOOK_FRIENDS_LIST_YES'             => (isset($config['al_fb_friends_list'])  && $config['al_fb_friends_list']) ? 'checked="checked"' : '',
					'FACEBOOK_FRIENDS_LIST_NO'              => (isset($config['al_fb_friends_list'])  && $config['al_fb_friends_list']) ? '' : 'checked="checked"',
					'FACEBOOK_TOPIC_POST_TO_PAGE_YES'       => (isset($config['al_facebook_topic_post_page']) && $config['al_facebook_topic_post_page']) ? 'checked="checked"' : '',
					'FACEBOOK_TOPIC_POST_TO_PAGE_NO'        => (isset($config['al_facebook_topic_post_page']) && $config['al_facebook_topic_post_page']) ? '' : 'checked="checked"',
					'S_FORUMS_LIST'							=> make_forum_select(json_decode((isset($config['al_facebook_topic_post_page_exclusions']) ? $config['al_facebook_topic_post_page_exclusions'] : ''), true)),
					'S_MODE_FACEBOOK'						=> true,
					'U_ACTION'                              => $this->u_action,
				));

			
			break;
			
			case 'windowslive':
			
				try
				{
					
					
					$template->assign_vars(array(
						'WINDOWSLIVE_APP_ID'						=> $config['al_wl_client_id'],
						'WINDOWSLIVE_SECRET'						=> $config['al_wl_secret'],
                        'WINDOWSLIVE_CALLBACK'          			=> $config['al_wl_callback'],
						'WINDOWSLIVE_QUICK_ACCOUNTS_YES'           	=> $config['al_wl_quick_accounts'] ? 'checked="checked"' : '',
						'WINDOWSLIVE_QUICK_ACCOUNTS_NO'            	=> $config['al_wl_quick_accounts'] ? '' : 'checked="checked"',
						'S_MODE_WINDOWSLIVE'			=> true,
						'U_ACTION'				=> $this->u_action,
					));
				}
				catch(Exception $ex)
				{
					trigger_error($ex->getMessage());
				}

			break;
                    
			case 'manage':
			default:
				
				// Set the values to be used in the template.
				// A value of 0 in the database means that Alternate Login is disabled.
				
					if($config['al_fb_login'] == 1)
					{
						$facebook_login_yes = 'checked="checked"';
						$facebook_login_no = '';
					}
					else
					{
						$facebook_login_no = 'checked="checked"';
						$facebook_login_yes = '';
					}
					
					if($config['al_wl_login'] == 1)
					{
						$windowslive_login_yes = 'checked="checked"';
						$windowslive_login_no = '';
					}
					else
					{
						$windowslive_login_no = 'checked="checked"';
						$windowslive_login_yes = '';
					}
                                        
                    if($config['al_oi_login'] == 1)
					{
						$openid_login_yes = 'checked="checked"';
						$openid_login_no = '';
					}
					else
					{
						$openid_login_no = 'checked="checked"';
						$openid_login_yes = '';
					}
					
				// Get Alternate Login Stats
				// Facebook
				$sql_array = array(
					'SELECT'	=> 'COUNT(al_fb_id) AS fb_count',
					'FROM'		=> array(USERS_TABLE => 'u'),
					'WHERE'		=> 'al_fb_id > 0',
				);
				
				$sql = $db->sql_build_query('SELECT', $sql_array);
				
				$result = $db->sql_query($sql);
				
				$fb_user_count = $db->sql_fetchfield('fb_count');
				
				// Windows Live
				$sql_array = array(
					'SELECT'	=> 'COUNT(al_wl_id) AS wl_count',
					'FROM'		=> array(USERS_TABLE => 'u'),
					'WHERE'		=> 'al_wl_id > 0',
				);
				
				$sql = $db->sql_build_query('SELECT', $sql_array);
				
				$result = $db->sql_query($sql);
				
				$wl_user_count = $db->sql_fetchfield('wl_count');
				
				// Open ID
				$sql_array = array(
					'SELECT'	=> 'COUNT(al_oi_id) AS oi_count',
					'FROM'		=> array(USERS_TABLE => 'u'),
					'WHERE'		=> 'al_oi_id > 0',
				);
				
				$sql = $db->sql_build_query('SELECT', $sql_array);
				
				$result = $db->sql_query($sql);
				
				$oi_user_count = $db->sql_fetchfield('oi_count');
                      
				$template->assign_vars(array(
					'FACEBOOK_PHP_VERSION_LOW'				=> sprintf($user->lang['FACEBOOK_PHP_VERSION_LOW'], PHP_VERSION),
					'FACEBOOK_LOGIN_YES'		=> $facebook_login_yes,
					'FACEBOOK_LOGIN_NO'         => $facebook_login_no,
					'WINDOWSLIVE_LOGIN_YES'		=> $windowslive_login_yes,
					'WINDOWSLIVE_LOGIN_NO'		=> $windowslive_login_no,
                    'OPENID_LOGIN_YES'			=> $openid_login_yes,
					'OPENID_LOGIN_NO'			=> $openid_login_no,
					'S_MODE_MAIN'				=> true,
					'U_ACTION'					=> $this->u_action,
					
					'S_FACEBOOK_USER_COUNT'		=> $fb_user_count,
					'S_WINDOWSLIVE_USER_COUNT'	=> $wl_user_count,
					'S_OPENID_USER_COUNT'		=> $oi_user_count,
				));
					
			break;
		}
	}
	
	function fb_language_select($default = '')
    {
        global $config, $phpbb_root_path ;
        $get_locale =	simplexml_load_file($phpbb_root_path . '/alternatelogin/FacebookLocales.xml');
        $arr = $get_locale->locale;

        $fb_lang_options = '';
        foreach($arr as $locale)
        {
            $selected = ($locale->codes->code->standard->representation == $default) ? ' selected="selected"' : '';
            $fb_lang_options .= '<option name="' . $locale->codes->code->standard->representation . '" id="' . $locale->codes->code->standard->representation . '" value="' . $locale->codes->code->standard->representation . '"' . $selected . '>' . $locale->englishName . '</option>';
        }

        return $fb_lang_options;
    }
}

?>