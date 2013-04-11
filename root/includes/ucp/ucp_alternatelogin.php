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
		
		//Define default vars
		$fb_website = $fb_location = $fb_occupation = $fb_birthday = $fb_avatar = $fb_status = "";
		$wl_location = $wl_occupation = "";
		
		if($user->data['al_fb_id'])
		{
			
			$graph_url = "https://graph.facebook.com/me?fields,address,website,work,birthday&" . $user->data['session_fb_access_token'];
						
            $fb_user = json_decode(get_fb_data($graph_url));
			$fb_website = isset($fb_user->website) ? $fb_user->website : false;
            $fb_location = isset($fb_user->location->name) ? $fb_user->location->name : false;
            $fb_occupation = isset($fb_user->work[0]->employer->name) ? $fb_user->work[0]->employer->name : false;
			
			if(!$fb_user->birthday)
			{
				$fb_birthday = false;
			}
			else
			{
				$birth_date = explode('/', $fb_user->birthday);
				$fb_birthday = $birth_date[1] . '-' . $birth_date[0] . '-' . $birth_date[2];
			}
			$graph_url = "https://graph.facebook.com/me?fields=picture&" . $user->data['session_fb_access_token'];

            $fb_user = json_decode(get_fb_data($graph_url));
			
			$fb_avatar = (!$fb_user->picture) ? false : $fb_user->picture->data->url;
			
			$graph_url = "https://graph.facebook.com/me?fields=statuses&" . $user->data['session_fb_access_token'];
 
            $fb_user = json_decode(get_fb_data($graph_url));
			
			$fb_status = (!$fb_user->statuses->data[0]->message) ? false : $fb_user->statuses->data[0]->message;
			
			
			
		}
		
		if($user->data['al_wl_id'])
		{
			$wl_user = get_wl_rest_request($user->data['session_wl_access_token'], 'me', HTTP_GET);
			
			if(isset($wl_user->addresses->personal->city) && isset($wl_user->addresses->personal->region))
			{
				$user_locale = $wl_user->addresses->personal->city . ', ' . $wl_user->addresses->personal->region;
				
			}
			elseif(isset($wl_user->addresses->personal->city))
			{
				$user_locale = $wl_user->addresses->personal->city;
				
			}
			elseif(isset($wl_user->addresses->personal->region))
			{
				$user_locale = $wl_user->addresses->personal->region;
				
			}
			
			$wl_location		= (!$user_locale) ? false : $user_locale;
            $wl_occupation		= (!$wl_user->work[0]->employer->name) ? false : $wl_user->work[0]->employer->name;
			
			
			$wl_birth_day 		= (!$wl_user->birth_day) ? false : $wl_user->birth_day;
			$wl_birth_month 	= (!$wl_user->birth_month) ? false : $wl_user->birth_month;
			$wl_birth_year 		= (!$wl_user->birth_year) ? false :$wl_user->birth_year;
			
			if($wl_birth_day && $wl_birth_month && $wl_birth_year)
			{
				$wl_birthday = $wl_birth_day . '-' . $wl_birth_month . '-' . $wl_birth_year;
			}
			else
			{
				$wl_birthday = false;
			}
		}
		
		if($submit)
		{
			$form = request_var('form_name', '');
			
			switch($form)
			{
				case 'fb_sync':
					$al_fb_profile_sync = (request_var('fb_profile_sync', '') == 'on') ? 1 : 0;
					$al_fb_avatar_sync = (request_var('fb_avatar_sync', '') == 'on') ? 1 : 0;
					$al_fb_status_sync = (request_var('fb_status_sync', '') == 'on') ? 1 : 0;
					
					$sql_array = array(
						'al_fb_profile_sync' 	=> $al_fb_profile_sync,
						'al_fb_avatar_sync' 	=> $al_fb_avatar_sync,
						'al_fb_status_sync' 	=> $al_fb_status_sync,
					);
					
					if($al_fb_profile_sync)
					{
						
						
						$sql_array = array_merge($sql_array, array(
							'user_website'			=> (!$fb_website) ? '' : $fb_website,
							'user_from'				=> (!$fb_location) ? '' : $fb_location,
							'user_occ'				=> (!$fb_occupation) ? '' : $fb_occupation,
							'user_birthday'			=> (!$fb_birthday) ? '' : $fb_birthday,
						));
					}
					else
					{
						$sql_array = array_merge($sql_array, array(
							'user_website'			=> '',
							'user_from'				=> '',
							'user_occ'				=> '',
							'user_birthday'			=> '',
						));
					}
					
					if($al_fb_avatar_sync)
					{
						$image_info = getimagesize($fb_avatar);
						
						$sql_array = array_merge($sql_array, array(
							'user_avatar'			=> $fb_avatar,
							'user_avatar_type'		=> 2,
							'user_avatar_width'		=> $image_info[0],
							'user_avatar_height'	=> $image_info[1],
						));
					}
					else
					{
						$sql_array = array_merge($sql_array, array(
							'user_avatar'			=> '',
							'user_avatar_type'		=> 0,
							'user_avatar_width'		=> 0,
							'user_avatar_height'	=> 0,
						));
					}
					
					
					if($al_fb_status_sync)
					{
						$uid = $bitfield = $options = '';
					
						$normalized_status = utf8_normalize_nfc($fb_status);
						
						generate_text_for_storage($normalized_status, $uid, $bitfield, $options, true, true, true);
					
						$sql_array = array_merge($sql_array, array(
							'user_sig'					=> $normalized_status,
							'user_sig_bbcode_uid'		=> $uid,
							'user_sig_bbcode_bitfield'	=> $bitfield,
						));
					}
					else
					{
						$sql_array = array_merge($sql_array, array(
							'user_sig'					=> '',
							'user_sig_bbcode_uid'		=> '',
							'user_sig_bbcode_bitfield'	=> '',
						));
					}
					
					$sql = 'UPDATE ' . USERS_TABLE . ' SET ' .  $db->sql_build_array('UPDATE', $sql_array) . ' WHERE user_id=' . $user->data['user_id'];
					
					$return_url = generate_board_url() . '/' . $user->data['session_page'];
					if($db->sql_query($sql))
					{
						meta_refresh(3, $return_url);
                		trigger_error(sprintf($user->lang['PROFILE_UPDATED'] . "<br /><br />" . sprintf($user->lang['RETURN_PAGE'], "<a href='{$return_url}'>", "</a>")));
					}
					else
					{
						meta_refresh(3, $return_url);
                		trigger_error(sprintf($user->lang['ERROR'] . "<br /><br />" . sprintf($user->lang['RETURN_PAGE'], "<a href='{$return_url}'>", "</a>")));
					}
					
					
					break;
					
				case 'wl_sync':
					$al_wl_profile_sync = (request_var('wl_profile_sync', '') == 'on') ? 1 : 0;
					
					$sql_array = array(
						'al_wl_profile_sync' 	=> $al_wl_profile_sync,
					);
					
					if($al_wl_profile_sync)
					{
						$sql_array = array_merge($sql_array, array(
							'user_from'				=> (!isset($wl_location)) ? '' : $wl_location,
							'user_occ'				=> (!isset($wl_occupation)) ? '' : $wl_occupation,
							'user_birthday'			=> (!isset($wl_birthday)) ? '' : $wl_birthday,
						));
					}
					else
					{
						$sql_array = array_merge($sql_array, array(
							'user_website'			=> '',
							'user_from'				=> '',
							'user_occ'				=> '',
							'user_birthday'			=> '',
						));
					}
					
					$sql = 'UPDATE ' . USERS_TABLE . ' SET ' .  $db->sql_build_array('UPDATE', $sql_array) . ' WHERE user_id=' . $user->data['user_id'];
					
					$return_url = generate_board_url() . '/' . $user->data['session_page'];
					if($db->sql_query($sql))
					{
						meta_refresh(3, $return_url);
                		trigger_error(sprintf($user->lang['PROFILE_UPDATED'] . "<br /><br />" . sprintf($user->lang['RETURN_PAGE'], "<a href='{$return_url}'>", "</a>")));
					}
					else
					{
						meta_refresh(3, $return_url);
                		trigger_error(sprintf($user->lang['ERROR'] . "<br /><br />" . sprintf($user->lang['RETURN_PAGE'], "<a href='{$return_url}'>", "</a>")));
					}
					
					break;
					
				default:
					trigger_error($user->lang['INVALID_FORM'] . $form);
					break;
			}
		}
		
		$template->assign_vars(array(
			'AL_WL_APP_ID'					=> $config['al_wl_client_id'],
			'S_MODE_WINDOWSLIVE'			=> ($mode == 'windowslive') ? true : false,
			'S_WINDOWSLIVE_LOGIN_ENABLED'	=> $user->data['al_wl_id'] ? true : false,
			'S_UCP_WINDOWSLIVE_DESCRIPTION'	=> $user->data['al_wl_id'] ? sprintf($user->lang['UCP_DISABLE_AL_DESCRIPTION'], $user->lang['WINDOWSLIVE'], $user->lang['WINDOWSLIVE']) : sprintf($user->lang['UCP_ENABLE_AL_DESCRIPTION'], $user->lang['WINDOWSLIVE']),
			
			'S_MODE_FACEBOOK'				=> ($mode == 'facebook') ? true : false,
			'S_FACEBOOK_LOGIN_ENABLED'		=> $user->data['al_fb_id'] ? true : false,
			'S_UCP_FACEBOOK_DESCRIPTION'	=> $user->data['al_fb_id'] ? sprintf($user->lang['UCP_DISABLE_AL_DESCRIPTION'], $user->lang['FACEBOOK'], $user->lang['FACEBOOK']) : sprintf($user->lang['UCP_ENABLE_AL_DESCRIPTION'], $user->lang['FACEBOOK']),
			
			'S_FB_WEBSITE'					=> $fb_website,
			'S_FB_LOCATION'					=> $fb_location,
			'S_FB_OCCUPATION'				=> $fb_occupation,
			'S_FB_BIRTHDAY'					=> $fb_birthday,
			
			'S_FB_AVATAR'					=> $fb_avatar,
			
			'S_FB_STATUS'					=> $fb_status,
			
			'S_FB_PROFILE_SYNC'				=> $user->data['al_fb_profile_sync'],
			'S_FB_STATUS_SYNC'				=> $user->data['al_fb_status_sync'],
			'S_FB_AVATAR_SYNC'				=> $user->data['al_fb_avatar_sync'],
			
			'S_WL_LOCATION'					=> $wl_location,
			'S_WL_OCCUPATION'				=> $wl_occupation,
			
			'S_WL_BIRTHDAY'					=> (isset($wl_birth_day) and isset($wl_birth_month) and isset($wl_birth_year)) ? $wl_birth_day . '-' . $wl_birth_month . '-' . $wl_birth_year : '',
			
			'S_WL_PROFILE_SYNC'				=> $user->data['al_wl_profile_sync'],
			
            'S_HIDDEN_FIELDS'				=> (isset($s_hidden_fields)) ? $s_hidden_fields : '',
			'S_UCP_ACTION'					=> $this->u_action,
		));

		// Set desired template
		$this->tpl_name = 'ucp_alternatelogin';
		$this->page_title = 'UCP_ALTERNATELOGIN';
	}
}

?>