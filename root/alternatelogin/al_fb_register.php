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
$user->add_lang('mods/info_ucp_alternatelogin');

include($phpbb_root_path . 'includes/functions_profile_fields.' . $phpEx);

$coppa			= (isset($_REQUEST['coppa'])) ? ((!empty($_REQUEST['coppa'])) ? 1 : 0) : false;
$agreed			= (!empty($_POST['agreed'])) ? 1 : 0;
$submit			= (isset($_POST['submit'])) ? true : false;
$change_lang            = request_var('change_lang', '');
$user_lang		= request_var('lang', $user->lang_name);
$al_login		= (request_var('al_login', 0) == 1) ? true : false;
$al_login_type		= request_var('al_login_type', 0);
$fb_user 		= request_var('al_fb_user', '');
$signed_request         = request_var('signed_request', '');

if($signed_request != '')
{
    
    list($encoded_sig, $payload) = explode('.', $signed_request, 2);

    $sig = base64_decode(strtr($encoded_sig, '-_', '+/'));

    $fb_reg_data = json_decode(base64_decode(strtr($payload, '-_', '+/')), true);

    if(strtoupper($fb_reg_data['algorithm']) !== 'HMAC-SHA256')
    {
        trigger_error('Unknown algorithm. Expected HMAC-SHA256');

        return null;
    }

    $expected_sig = $expected_sig = hash_hmac('sha256', $payload, $config['al_fb_secret'], $raw = true);
    if ($sig !== $expected_sig)
    {
        trigger_error('Bad Signed JSON signature!');
        return null;
    }

}
else
{
    trigger_error($user->lang['ERROR']);
}

$data = array(
    'username'			=> $fb_reg_data['registration']['name_for_username'] ? utf8_normalize_nfc($fb_reg_data['registration']['name']) : utf8_normalize_nfc($fb_reg_data['registration']['username']),
    'email'			=> strtolower($fb_reg_data['registration']['email']),
    'email_confirm'		=> strtolower($fb_reg_data['registration']['email']),
    'lang'                      => basename(request_var('lang', $user->lang_name)),
    'tz'			=> request_var('tz', (float) $timezone),
);



$new_password = $fb_reg_data['registration']['password'];


$data['new_password'] = $new_password;
$data['password_confirm'] = $new_password;

$error = validate_data($data, array(
    'username'			=> array(
                                        array('string', false, $config['min_name_chars'], $config['max_name_chars']),
                                        array('username', '')),
    
    'email'                     => array(
                                        array('string', false, 6, 60),
                                        array('email')),
    'email_confirm'		=> array('string', false, 6, 60),
    'tz'			=> array('num', false, -14, 14),
    'lang'			=> array('match', false, '#^[a-z_\-]{2,}$#i'),
));



// DNSBL check
if ($config['check_dnsbl'])
{
    if (($dnsbl = $user->check_dnsbl('register')) !== false)
    {
        $error[] = sprintf($user->lang['IP_BLACKLISTED'], $user->ip, $dnsbl[1]);
    }
}

if (!sizeof($error))
{
    if ($data['new_password'] != $data['password_confirm'])
    {
        $error[] = $user->lang['NEW_PASSWORD_ERROR'];
    }

    if ($data['email'] != $data['email_confirm'])
    {
        $error[] = $user->lang['NEW_EMAIL_ERROR'];
    }
}

if (!sizeof($error))
{
    $server_url = generate_board_url();

    // Which group by default?
    $group_name = ($coppa) ? 'REGISTERED_COPPA' : 'REGISTERED';

    $sql = 'SELECT group_id
            FROM ' . GROUPS_TABLE . "
            WHERE group_name = '" . $db->sql_escape($group_name) . "'
                    AND group_type = " . GROUP_SPECIAL;
    $result = $db->sql_query($sql);
    $row = $db->sql_fetchrow($result);
    $db->sql_freeresult($result);

    if (!$row)
    {
        trigger_error('NO_GROUP');
    }

    $group_id = $row['group_id'];

    if (($coppa ||
            $config['require_activation'] == USER_ACTIVATION_SELF ||
            $config['require_activation'] == USER_ACTIVATION_ADMIN) && $config['email_enable'])
    {
        $user_actkey = gen_rand_string(10);
        $key_len = 54 - (strlen($server_url));
        $key_len = ($key_len < 6) ? 6 : $key_len;
        $user_actkey = substr($user_actkey, 0, $key_len);

        $user_type = USER_INACTIVE;
        $user_inactive_reason = INACTIVE_REGISTER;
        $user_inactive_time = time();
    }
    else
    {
        $user_type = USER_NORMAL;
        $user_actkey = '';
        $user_inactive_reason = 0;
        $user_inactive_time = 0;
    }

    $user_row = array(
        'username'				=> $data['username'],
        'user_password'			=> phpbb_hash($data['new_password']),
        'user_email'			=> $data['email'],
        'group_id'				=> (int) $group_id,
        'user_timezone'			=> (float) $data['tz'],
        'user_dst'				=> $is_dst,
        'user_lang'				=> $data['lang'],
        'user_type'				=> $user_type,
        'user_actkey'			=> $user_actkey,
        'user_ip'				=> $user->ip,
        'user_regdate'			=> time(),
        'user_inactive_reason'	=> $user_inactive_reason,
        'user_inactive_time'	=> $user_inactive_time,
        'al_fb_id'              => $fb_user,
    );

    if ($config['new_member_post_limit'])
    {
        $user_row['user_new'] = 1;
    }

    // Register user...
    $user_id = user_add($user_row, $cp_data);

    // This should not happen, because the required variables are listed above...
    if ($user_id === false)
    {
        trigger_error('NO_USER', E_USER_ERROR);
    }
    

    // DB Error
    if(!$result)
    {
            trigger_error('Unable to connect with phpBB database.');
    }

    $al_email_lang = $user->lang['FACEBOOK'];

    if ($coppa && $config['email_enable'])
    {
        $message = $user->lang['ACCOUNT_COPPA'];
        if($al_login)
        {
                $email_template = 'coppa_welcome_inactive_alternatelogin';
        }
        else
        {
                $email_template = 'coppa_welcome_inactive';
        }
    }
    else if ($config['require_activation'] == USER_ACTIVATION_SELF && $config['email_enable'])
    {
        $message = $user->lang['ACCOUNT_INACTIVE'];
        if($al_login)
        {
                $email_template = 'user_welcome_inactive_alternatelogin';
        }
        else
        {
                $email_template = 'user_welcome_inactive';
        }
    }
    else if ($config['require_activation'] == USER_ACTIVATION_ADMIN && $config['email_enable'])
    {
        $message = $user->lang['ACCOUNT_INACTIVE_ADMIN'];
        if($al_login)
        {
                $email_template = 'admin_welcome_inactive_alternatelogin';
        }
        else
        {
                $email_template = 'admin_welcome_inactive';
        }
    }
    else
    {
        $message = $user->lang['ACCOUNT_ADDED'];
        if($al_login)
        {
                $email_template = 'user_welcome_alternatelogin';
        }
        else
        {
                $email_template = 'user_welcome';
        }
    }

    if ($config['email_enable'])
    {
            include_once($phpbb_root_path . 'includes/functions_messenger.' . $phpEx);

            $messenger = new messenger(false);

            $messenger->template($email_template, $data['lang']);

            $messenger->to($data['email'], $data['username']);

            $messenger->anti_abuse_headers($config, $user);

            $messenger->assign_vars(array(
                    'WELCOME_MSG'	=> htmlspecialchars_decode(sprintf($user->lang['WELCOME_SUBJECT'], $config['sitename'])),
                    'USERNAME'		=> htmlspecialchars_decode($data['username']),
                    'PASSWORD'		=> htmlspecialchars_decode($data['new_password']),
                    'AL_LOGIN_TYPE'		=> $al_email_lang,
                    'U_ACTIVATE'	=> "$server_url/ucp.$phpEx?mode=activate&u=$user_id&k=$user_actkey")
            );

            if ($coppa)
            {
                    $messenger->assign_vars(array(
                            'FAX_INFO'		=> $config['coppa_fax'],
                            'MAIL_INFO'		=> $config['coppa_mail'],
                            'EMAIL_ADDRESS'	=> $data['email'])
                    );
            }

            $messenger->send(NOTIFY_EMAIL);

            if ($config['require_activation'] == USER_ACTIVATION_ADMIN)
            {
                    // Grab an array of user_id's with a_user permissions ... these users can activate a user
                    $admin_ary = $auth->acl_get_list(false, 'a_user', false);
                    $admin_ary = (!empty($admin_ary[0]['a_user'])) ? $admin_ary[0]['a_user'] : array();

                    // Also include founders
                    $where_sql = ' WHERE user_type = ' . USER_FOUNDER;

                    if (sizeof($admin_ary))
                    {
                            $where_sql .= ' OR ' . $db->sql_in_set('user_id', $admin_ary);
                    }

                    $sql = 'SELECT user_id, username, user_email, user_lang, user_jabber, user_notify_type
                            FROM ' . USERS_TABLE . ' ' .
                            $where_sql;
                    $result = $db->sql_query($sql);

                    while ($row = $db->sql_fetchrow($result))
                    {
                            $messenger->template('admin_activate', $row['user_lang']);
                            $messenger->to($row['user_email'], $row['username']);
                            $messenger->im($row['user_jabber'], $row['username']);

                            $messenger->assign_vars(array(
                                    'USERNAME'			=> htmlspecialchars_decode($data['username']),
                                    'U_USER_DETAILS'	=> "$server_url/memberlist.$phpEx?mode=viewprofile&u=$user_id",
                                    'U_ACTIVATE'		=> "$server_url/ucp.$phpEx?mode=activate&u=$user_id&k=$user_actkey")
                            );

                            $messenger->send($row['user_notify_type']);
                    }
                    $db->sql_freeresult($result);
            }
    }

    
    
    

    $message = $message . '<br /><br />' . sprintf($user->lang['RETURN_INDEX'], '<a href="' . append_sid("{$phpbb_root_path}index.$phpEx") . '">', '</a>');
    trigger_error($message);
}
else
{
	$message = '';
        foreach($error as $e)
        {
            $message .= $user->lang[$e] . '<br />';
        }
        $message = $message . '<br /><br />' . sprintf($user->lang['PREV_PAGE'], '<a href="' . append_sid("{$phpbb_root_path}alternatelogin/al_fb_registration.$phpEx") . '">', '</a>');
        trigger_error($message);
}
?>
