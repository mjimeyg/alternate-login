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
        'ACP_CONSOF'                                            => 'ConSof',
	'ACP_ALTERNATELOGIN'					=> '多元社群網路登入系統',
	'ACP_AL_MAIN_MANAGE'					=> '《多元社群網路登入系統》管理',
	'ACP_AL_FACEBOOK_MANAGE'				=> 'Facebook 模組管理',
	'ACP_AL_WINDOWSLIVE_MANAGE'				=> 'Windows Live 模組管理',
	'ACP_AL_OPENID_MANAGE'					=> 'OpenID 模組管理',
	'ACP_AL_MYSPACE_MANAGE'					=> 'MySpace 模組管理',
        'ACP_AL_TWITTER_MANAGE'                                 => 'Twitter 模組管理',
    
        'ACP_AL_SAVE_ERROR'                                     => '設定錯誤，無法順利變更。',
        'ACP_AL_SAVE_SUCCESS'                                   => '已成功變更設定。',

        'ACP_ALTERNATELOGIN_SETTINGS_UPDATED'                   => '已更新設定。',

        'TITLE_ENABLE_LOGIN'                                    => '管理登入模式',

        'ENABLE_LOGIN'                                          => '啟用登入系統',

        'FACEBOOK'                                              => 'Facebook',
        'TWITTER'                                               => 'Twitter',
        'WINDOWSLIVE'                                           => 'Windows Live',

        'FACEBOOK_APP_ID'                                       => 'Facebook 應用程式 ID',
        'FACEBOOK_KEY'                                          => 'Facebook 應用程式公鑰',
        'FACEBOOK_SECRET'                                       => 'Facebook 應用程式密鑰',
        'FACEBOOK_PAGE_URL'                                     => 'Facebook 專頁網址',
        'SITE_DOMAIN'                                           => '所屬網域',

        'FACEBOOK_ACTIVITY'                                     => 'Facebook 最新動態',
        'FACEBOOK_FACEPILE'                                     => 'Facebook 好友大頭照',
        'FACEBOOK_STREAM'                                       => 'Facebook 即時動態',
        'FACEBOOK_LIKE_BOX'                                     => 'Facebook Like Box',
    
        'TWITTER_KEY'                                           => 'Twitter 應用程式公鑰',
        'TWITTER_SECRET'                                        => 'Twitter 應用程式密鑰',
        'TWITTER_CALLBACK'                                      => 'Twitter 回呼檔案路徑',

        'WINDOWSLIVE_CLIENT_ID'                                 => 'Windows Live 應用程式 ID',
        'WINDOWSLIVE_SECRET'                                    => 'Windows Live 應用程式密鑰',
        'WINDOWSLIVE_CALLBACK'                                  => 'Windows Live 回呼檔案路徑',
        'WINDOWSLIVE_CHANNEL'                                   => 'Windows Live 頻道檔案路徑',
    
        'TITLE_GENERAL_SETTINGS'                                => '綜合設定',
        'REQUIRE_PASSWORD'                                      => '需要密碼',
        
));

?>