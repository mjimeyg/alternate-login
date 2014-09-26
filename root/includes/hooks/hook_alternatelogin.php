<?php
session_start();


class CSAlternateLogin
{
	
	function page_header(&$hook)
	{
		try
		{
			global $template, $user, $phpbb_root_path, $phpEx, $config, $al_data, $table_prefix, $db, $fb_session, $fb_helper;
	
			include $phpbb_root_path . '/includes/functions_alternatelogin.' . $phpEx;
			//require_once($phpbb_root_path .  '/alternatelogin/facebook/autoload.php' );
			
			require_once( $phpbb_root_path .  '/alternatelogin/facebook/src/Facebook/HttpClients/FacebookHttpable.php' );
			require_once( $phpbb_root_path .  '/alternatelogin/facebook/src/Facebook/HttpClients/FacebookCurl.php' );
			require_once( $phpbb_root_path .  '/alternatelogin/facebook/src/Facebook/HttpClients/FacebookCurlHttpClient.php' );
			
			require_once( $phpbb_root_path .  '/alternatelogin/facebook/src/Facebook/Entities/AccessToken.php' );
			require_once( $phpbb_root_path .  '/alternatelogin/facebook/src/Facebook/Entities/SignedRequest.php' );
			
			require_once( $phpbb_root_path .  '/alternatelogin/facebook/src/Facebook/FacebookSession.php' );
			require_once( $phpbb_root_path .  '/alternatelogin/facebook/src/Facebook/FacebookRedirectLoginHelper.php' );
			require_once( $phpbb_root_path .  '/alternatelogin/facebook/src/Facebook/FacebookRequest.php' );
			require_once( $phpbb_root_path .  '/alternatelogin/facebook/src/Facebook/FacebookResponse.php' );
			require_once( $phpbb_root_path .  '/alternatelogin/facebook/src/Facebook/FacebookSDKException.php' );
			require_once( $phpbb_root_path .  '/alternatelogin/facebook/src/Facebook/FacebookRequestException.php' );
			require_once( $phpbb_root_path .  '/alternatelogin/facebook/src/Facebook/FacebookOtherException.php' );
			require_once( $phpbb_root_path .  '/alternatelogin/facebook/src/Facebook/FacebookAuthorizationException.php' );
			require_once( $phpbb_root_path .  '/alternatelogin/facebook/src/Facebook/GraphObject.php' );
			require_once( $phpbb_root_path .  '/alternatelogin/facebook/src/Facebook/GraphUser.php' );
			require_once( $phpbb_root_path .  '/alternatelogin/facebook/src/Facebook/GraphAlbum.php' );
			require_once( $phpbb_root_path .  '/alternatelogin/facebook/src/Facebook/GraphLocation.php' );
			require_once( $phpbb_root_path .  '/alternatelogin/facebook/src/Facebook/GraphSessionInfo.php' );
			
				
			
			
			
			
			$forum_id = request_var('f', 0);
			$topic_id = request_var('t', 0);
			$site_image = '';
			
			$result = $hook->previous_hook_result('phpbb_user_session_handler');
			
			$user->add_lang('mods/info_ucp_alternatelogin');
			
			FacebookSession::setDefaultApplication($config['al_fb_id'], $config['al_fb_secret']);
			
			$fb_helper = new FacebookRedirectLoginHelper(generate_board_url() . "/alternatelogin/al_fb_connect.{$phpEx}");
			
			if($user->data['al_fb_access_token'])
			{
				$fb_session = new FacebookSession($user->data['al_fb_access_token']);
				if(!$fb_session->validate())
				{
					$fb_session = null;
				}
			}
			elseif($user->data['session_fb_access_token'])
			{
				$fb_session = new FacebookSession($user->data['session_fb_access_token']);
				if(!$fb_session->validate())
				{
					$fb_session = null;
				}
			}
			else
			{
				$fb_session = $fb_helper->getSessionFromRedirect();
				
			}
			
			if(!$fb_session)
			{
				
				$fb_helper = new FacebookRedirectLoginHelper(generate_board_url() . "/alternatelogin/al_fb_connect.{$phpEx}", $config['al_fb_id'], $config['al_fb_secret']);
				
				
				$login_url = $fb_helper->getLoginUrl(array(
													'user_location',
													'user_activities',
													'user_birthday',
													'user_interests',
													'user_status',
													'user_website',
													'user_work_history',
													'email',
													'publish_actions',
													'manage_pages',
													'publish_stream'
												));
				
			}
			else
			{
				$login_url = $phpbb_root_path . 'alternatelogin/al_fb_connect.' . $phpEx;
			}
			if($topic_id && $forum_id)
			{
				
				$sql_array = array(
					'SELECT'		=> '*',
					'FROM'			=> array(POSTS_TABLE => 'p'),
					'WHERE'			=> 'p.forum_id = ' .$forum_id . ' AND p.topic_id = ' . $topic_id,
					'ORDER_BY'		=> 'p.post_id'
				);
				
				$sql = $db->sql_build_query('SELECT', $sql_array);
				
				$result = $db->sql_query($sql);
				
				$topic_data = $db->sql_fetchrow($result);
				
				$post_text = $topic_data['post_text'];
				
				strip_bbcode($post_text, $topic_data['bbcode_uid']);
				
				$db->sql_freeresult($result);
				
				$sql_array = array(
					'SELECT'		=> 'user_avatar_type, user_avatar',
					'FROM'			=> array(USERS_TABLE => 'u'),
					'WHERE'			=> 'u.user_id = ' . $topic_data['poster_id'],
				);
				
				$sql = $db->sql_build_query('SELECT', $sql_array);
				
				$result = $db->sql_query($sql);
				
				$poster_data = $db->sql_fetchrow($result);
				
				$site_image = get_user_avatar_filename($poster_data['user_avatar'], $poster_data['user_avatar_type']);
				
				$db->sql_freeresult($result);
			}
			$fb_site_description = ($topic_id) ? strip_tags($post_text) : $config['site_desc'];
			
			$template->assign_vars(array(
				'S_AL_FB_ENABLED'								=> isset($config['al_fb_login']) ? $config['al_fb_login'] : false,
				'S_AL_WL_ENABLED'								=> isset($config['al_wl_login']) ? $config['al_wl_login'] : false,
				'S_AL_OI_ENABLED'                               => isset($config['al_oi_login']) ? $config['al_oi_login'] : false,
				'S_AL_WL_USER'									=> isset($user->data['al_wl_id']) ? $user->data['al_wl_id'] : false,
				'S_AL_FB_USER'                                  => isset($user->data['al_fb_id']) ? $user->data['al_fb_id'] : false,
				'S_AL_OI_USER'                                  => isset($user->data['al_oi_id']) ? $user->data['al_oi_id'] : false,
				'AL_FB_APPID'									=> isset($config['al_fb_id']) ? $config['al_fb_id'] : false,
				'AL_FB_SITE_DOMAIN'                             => isset($config['al_site_domain']) ? $config['al_site_domain'] : false,
				'AL_FB_ACTIVITY'                                => isset($config['al_fb_activity']) ? $config['al_fb_activity'] : false,
				'AL_FB_FACEPILE'                                => isset($config['al_fb_facepile']) ? $config['al_fb_facepile'] : false,
				'AL_FB_LIKE_BOX'                                => isset($config['al_fb_like_box']) ? $config['al_fb_like_box'] : false,
				'AL_FB_FRIENDS_LIST'                            => isset($config['al_fb_friends_list']) ? $config['al_fb_friends_list'] : false,
				'AL_FB_FRIENDS_LIST_MESSAGE'                    => isset($config['al_fb_friends_list_messgae']) ? $config['al_fb_friends_list_messgae'] : '',
				'AL_FB_FRIENDS_LIST_LABEL'                    	=> isset($user->lang['AL_FB_FRIENDS_LIST_LABEL']) ? urlencode($user->lang['AL_FB_FRIENDS_LIST_LABEL']) : urlencode('Invite Friends'),
				'AL_FB_LOGIN_BUTTON_TEXT'						=> isset($config['al_fb_login_text']) ? $config['al_fb_login_text'] : 'Facebook',
				'AL_FB_LOGIN_URL'								=> $login_url,
				'S_AL_WL_CLIENT_ID'								=> isset($config['al_wl_client_id']) ? $config['al_wl_client_id'] : false,
				'S_AL_WL_WRAP_CHANNEL'                          => isset($config['al_wl_channel']) ? $config['al_wl_channel'] : false,
				'AL_FB_APP_ID'                                  => isset($config['al_fb_id']) ? $config['al_fb_id'] : false,
				'AL_FB_PAGE_URL'                                => isset($config['al_fb_page_url']) ? $config['al_fb_page_url'] : false,
				'AL_FB_PAGE_ID'									=> isset($config['al_fb_page_id']) ? $config['al_fb_page_id'] : false,
				'FB_APP_ID'                                     => isset($config['al_fb_id']) ? $config['al_fb_id'] : false,
				'AL_FB_USER_HIDE_ACTIVITY'                      => isset($user->data['al_fb_hide_activity']) ? $user->data['al_fb_hide_activity'] : false,
				'AL_FB_USER_HIDE_FACEPILE'                      => isset($user->data['al_fb_hide_facepile']) ? $user->data['al_fb_hide_facepile'] : false,
				'AL_FB_USER_HIDE_LIKE_BOX'                      => isset($user->data['al_fb_hide_like_box']) ? $user->data['al_fb_hide_like_box'] : false,
				'AL_FB_USER_FRIENDS_LIST_HIDE'                  => isset($user->data['al_fb_hide_friends_list']) ? $user->data['al_fb_hide_friends_list'] : false,
				'AL_FB_SITE_DESCRIPTION'						=> $fb_site_description,
				'U_AL_WL_AUTHORIZE'                             => (isset($config['al_wl_client_id']) && isset($config['al_wl_callback'])) ? "https://oauth.live.com/authorize?client_id={$config['al_wl_client_id']}&scope=wl.signin%20wl.basic%20wl.birthday%20wl.emails%20wl.work_profile%20wl.postal_addresses&response_type=code&redirect_uri=" . urlencode($config['al_wl_callback']) : '',
				'U_AL_OI_LOGIN'                                 => append_sid("{$phpbb_root_path}alternatelogin/al_oi_auth.{$phpEx}"),
				
				'S_FB_LOCALE'                                   => isset($fb_lang) ? $fb_lang : 'en_GB',
				'S_RETURN_TO_PAGE'                              => "?return_to_page=" . base64_encode(build_url()),
				
				'AL_PATH'										=> generate_board_url() . '/alternatelogin/',
				
				'U_PAGE_URL'                    				=> generate_board_url() . substr(build_url(), 1),//generate_board_url() . "/viewtopic.$phpEx?f=$forum_id&amp;t=$topic_id",
				'SITE_LOGO_SRC'									=> $site_image !== '' ? $site_image : generate_board_url() . substr($user->img('site_logo', '', false, '', 'src'), 1)
			));
		
		}
		catch(FacebookApiException $ex)
		{
			add_log('critical', json_encode($ex->getResult()));
			$result = $ex->getResult();
			trigger_error($result['message']);
		}
		catch(OAuthException $ex)
		{
			add_log('critical', json_encode($ex->getMessage()));
			$result = $ex->getMessage();
			trigger_error($result);
		}

	}
}

$phpbb_hook->register('phpbb_user_session_handler', array('CSAlternateLogin', 'page_header'));

?>