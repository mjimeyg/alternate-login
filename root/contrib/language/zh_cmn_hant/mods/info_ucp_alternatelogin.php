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
        'ALTERNATE_LOGIN'						=> '本站可使用《多元社群網路登入系統》進行登入。',

        'AL_LOGIN'								=> '選取您要使用的社群網路並切換登入模式',
	'AL_LOGIN_EXPLAIN'						=> '您可選擇一種方式進行登入，但您仍須在本論壇建立專屬帳號密碼。',
	'AL_REGISTER_QUERY'						=> '是否要以 %s 社群帳號進行註冊？',
	'AL_REGISTRATION'						=> '您可以選擇以下的社群網路服務加入本站。',
	'AL_LOGIN_UNAVAILABLE'					=> '%s 社群網路在本站無法使用。',
	'AL_PHPBB_DB_FAILURE'					=> '連結 phpBB 資料庫時發生錯誤。',
    
        'AL_FB_HIDE_ACTIVITY'                                           => '隱藏 Facebook 最新動態',
        'AL_FB_HIDE_STREAM'                                             => '隱藏 Facebook 即時動態',
        'AL_FB_HIDE_FACEPILE'                                           => '隱藏 Facebook 好友大頭照',
        'AL_FB_HIDE_LIKE_BOX'                                           => '隱藏 Facebook Like Box',

        'FB_SYNC'                                                       => '同步化 Facebook 個人檔案',
        'FB_STATUS'                                                     => '同步化 Facebook 動態',
        'FB_AVATAR'                                                     => 'Facebook 個人檔案大頭照',
        'FB_AVATAR_EXPLAIN'                                             => '同步化 Facebook 個人檔案大頭照',
    
        'WL_SYNC'                                                       => '同步化 Windows Live 個人檔案',
        'WL_STATUS'                                                     => '同步化 Windows Live 動態',
        'WL_AVATAR'                                                     => 'Windows Live 個人檔案大頭照',
        'WL_AVATAR_EXPLAIN'                                             => '同步化 Windows Live 個人檔案大頭照',

        'TW_SYNC'                                                       => '同步化 Twitter 個人檔案',
        'TW_STATUS'                                                     => '同步化 Twitter 動態',
        'TW_AVATAR'                                                     => 'Twitter 個人檔案大頭照',
        'TW_AVATAR_EXPLAIN'                                             => '同步化 Twitter 個人檔案大頭照',

        'TITLE_ENABLE_LOGIN_PROFILE'			=> '多元社群網路登入系統與個人檔案同步化功能',
	'FACEBOOK'								=> 'Facebook',
	'WINDOWSLIVE'							=> 'Windows Live',
	'ENABLE_LOGIN'							=> '啟用登入功能',
	'ENABLE_PROFILE_SYNC'					=> '啟用個人檔案同步功能功能',
	'FACEBOOK_ID'						=> 'Facebook 應用程式 ID',
	'FACEBOOK_SECRET'						=> 'Facebook 應用程式密鑰',
        'FACEBOOK_KEY'                                                  => 'Facebook 應用程式公鑰',
        'FACEBOOK_APP_ID'                                               => 'Facebook 應用程式 ID',
	'WINDOWSLIVE_APP_ID'					=> 'Windows Live 應用程式 ID',
	'WINDOWSLIVE_SECRET'					=> 'Windows Live 密鑰',
	'NOT_REGISTERED'						=> '您尚未在本站註冊。<br /><br />%s請按此進行註冊%s',
	
	'LOGIN_FAILURE'							=> '登入失敗。',
	'LOGIN_SUCCESS'							=> '您已成功登入！',

	'WINDOWSLIVE_ERROR'							=> '連結至 Windows Live 時發生錯誤。',
	'SIGN_IN'								=> '登入',
	'LOGGED_IN'								=> '您已登入',

	'UCP_ALTERNATELOGIN'					=> '多元社群網路登入系統',
	'UCP_ALTERNATELOGIN_SETTINGS'			=> '多元社群網路登入系統基本設定',
	'UCP_AL_FACEBOOK'						=> 'Facebook 帳號管理',
	'UCP_AL_WINDOWSLIVE'					=> 'Windows Live 帳號管理',
        'UCP_AL_TWITTER'					=> 'Twitter 帳號管理',
	'UCP_AL_FACEBOOK_SETTINGS'				=> 'Facebook 設定',
	'UCP_AL_FACEBOOK_DISABLED'				=> '本站未啟用 Facebook 功能。',
	'UCP_AL_ASK_DISABLE_FACEBOOK'			=> '停用 Facebook 登入',
	'UCP_AL_ASK_ENABLE_FACEBOOK'			=> '啟用 Facebook 登入',
	'UCP_AL_ENABLE_WINDOWSLIVE_TEXT'		=> '啟用 Windows Live 登入。',
	'UCP_AL_ENABLE_FACEBOOK_TEXT'			=> '啟用 Facebook 登入。',
        'UCP_AL_ENABLE_TWITTER_TEXT'			=> '啟用 Twitter 登入。',
	'UCP_AL_DISABLE_WINDOWSLIVE_TEXT'		=> '停用 Windows Live 登入。',
	'UCP_AL_DISABLE_FACEBOOK_TEXT'			=> '停用 Facebook 登入。',
        'UCP_AL_DISABLE_TWITTER_TEXT'			=> '停用 Twitter 登入。',
	'UCP_AL_UNLINK'							=> '是否取消與 %s 社群網路帳號的連結？',
	'UCP_AL_LINK'							=> '是否與 %s 社群網路帳號進行連結？',
	'UCP_ENABLE_FACEBOOK_LOGIN'				=> '是否啟用 Facebook 登入？',
	'UCP_AL_ACCOUNT_UNLINKED'				=> '您已切斷與 %s 社群網路帳號的連結。<br/><br/>您將收到一封新密碼通知信件。',
	'UCP_AL_CONFIRM_DISABLE'				=> '是否取消 Windows Live 社群網路登入模式？如果要取消，您將收到一封新密碼通知信件。',
        'UCP_CONSOF'                                            => 'ConSof',
	'UCP_DISABLE_AL_TEXT'					=> '取消與 %s 社群網路帳號的連結。',
	'UCP_DISABLE_AL_DESCRIPTION'			=> '如果您想取消與 %s 社群網路帳號的連結，請點選該按鈕。<br/>新密碼將會寄送至您註冊時的電子郵件信箱。如果您想利用其他的社群網路帳號與論壇帳號連結，請點選您想要連結的社群帳號，然後 %s 社群網路帳號就會自動地解除連結。',
	'UCP_ENABLE_AL_DESCRIPTION'				=> '如果您想要用 %s 社群網路帳號連結您的論壇帳號，請點選該社群網路服務。',
	'DISABLE_WINDOWSLIVE'					=> '停用 Windows Live 登入',
	'DISABLE_FACEBOOK'						=> '停用 Facebook 登入',
        'DISABLE_TWITTER'						=> '停用 Twitter 登入',
	'ENABLE_FACEBOOK'						=> '啟用 Facebook 登入',
	'AL_LINK_SUCCESS'						=> '您已成功與 %s 社群網路服務帳號進行連結。<br/><br/>未來請以 %s 社群網路服務帳號進行登入。',
    
        'SOCIAL_LOGIN_OPTIONS'                          => '選擇以下社群網路服務進行登入',
    
        'FB_ERROR_ACCESS_TOKEN'                         => '在獲取登入權杖時發生錯誤。',
        'FB_ERROR_USER'                                 => '在獲取使用者資料時發生錯誤',
    
        'FB_NAME'                                       => '名稱',
        'FB_USERNAME'                                   => 'Facebook 使用者帳號',
        'FB_DESCRIPTION'                                => '請輸入要在本站使用的帳號',
        'FB_BIRTHDAY'                                   => '生日',
        'FB_GENDER'                                     => '性別',
        'FB_LOCATION'                                   => '所在地',
        'FB_EMAIL'                                      => '電子郵件',
));

?>