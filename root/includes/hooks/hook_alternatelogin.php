<?php

class CSAlternateLogin
{
	function page_header(&$hook)
	{
		global $template, $user, $phpbb_root_path, $phpEx, $config, $al_data, $table_prefix;

		include $phpbb_root_path . '/includes/functions_alternatelogin.' . $phpEx;
		
		$forum_id = request_var('f', 0);
		$topic_id = request_var('t', 0);
		
		$result = $hook->previous_hook_result('phpbb_user_session_handler');
		
		// Begin Alternate Login code
        require_once($phpbb_root_path . 'includes/functions_alternatelogin.php'); // Include the functions for Alternate Login module
        $user->add_lang('mods/info_ucp_alternatelogin');
        if(isset($user->data['session_fb_access_token']))
        {
            $graph_url = "https://graph.facebook.com/me?" . $user->data['session_fb_access_token'];


            $fb_user = json_decode(get_fb_data($graph_url));
            
            $fb_lang = $fb_user->locale;
        }
		
		
		
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
			'AL_FB_LOGIN_BUTTON_TEXT'						=> isset($config['al_fb_login_text']) ? $config['al_fb_login_text'] : 'Facebook',
			'S_AL_WL_CLIENT_ID'								=> isset($config['al_wl_client_id']) ? $config['al_wl_client_id'] : false,
			'S_AL_WL_WRAP_CHANNEL'                          => isset($config['al_wl_channel']) ? $config['al_wl_channel'] : false,
			'AL_FB_APP_ID'                                  => isset($config['al_fb_id']) ? $config['al_fb_id'] : false,
			'AL_FB_PAGE_URL'                                => isset($config['al_fb_page_url']) ? $config['al_fb_page_url'] : false,
			'FB_APP_ID'                                     => isset($config['al_fb_id']) ? $config['al_fb_id'] : false,
			'AL_FB_USER_HIDE_ACTIVITY'                      => isset($user->data['al_fb_hide_activity']) ? $user->data['al_fb_hide_activity'] : false,
			'AL_FB_USER_HIDE_FACEPILE'                      => isset($user->data['al_fb_hide_facepile']) ? $user->data['al_fb_hide_facepile'] : false,
			'AL_FB_USER_HIDE_LIKE_BOX'                      => isset($user->data['al_fb_hide_like_box']) ? $user->data['al_fb_hide_like_box'] : false,
			'U_AL_WL_AUTHORIZE'                             => (isset($config['al_wl_client_id']) && isset($config['al_wl_callback'])) ? "https://oauth.live.com/authorize?client_id={$config['al_wl_client_id']}&scope=wl.signin%20wl.basic%20wl.birthday%20wl.emails%20wl.work_profile%20wl.postal_addresses&response_type=code&redirect_uri=" . urlencode($config['al_wl_callback']) : '',
			'U_AL_OI_LOGIN'                                 => append_sid("{$phpbb_root_path}alternatelogin/al_oi_auth.{$phpEx}"),
			'S_FB_LOCALE'                                   => isset($fb_lang) ? $fb_lang : 'en_GB',
			'S_RETURN_TO_PAGE'                              => "?return_to_page=" . base64_encode(build_url()),
			
			'U_PAGE_URL'                    				=> generate_board_url() . "/viewtopic.$phpEx?f=$forum_id&amp;t=$topic_id",
		));
		
		
	}
}

$phpbb_hook->register('phpbb_user_session_handler', array('CSAlternateLogin', 'page_header'));

?>