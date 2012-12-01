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


// Basic setup of phpBB variables.
define('IN_PHPBB', true);
$phpbb_root_path = (defined('PHPBB_ROOT_PATH')) ? PHPBB_ROOT_PATH : '../';
$phpEx = substr(strrchr(__FILE__, '.'), 1);

// Load include files.
include($phpbb_root_path . 'common.' . $phpEx);
include_once($phpbb_root_path . 'includes/functions_user.' . $phpEx);

// Set up a new user session.
$user->session_begin();
$auth->acl($user->data);
$user->setup('ucp');

$access_token = request_var('access_token', '');

if($access_token == '')
{
	echo json_encode(array(
		'status'		=> -1,
		'error_message'	=> 'Invalid access token supplied.',
	));
}

$graph_url = "https://graph.facebook.com/me?" . $access_token;

$fb_user = json_decode(get_fb_data($graph_url));

//echo("Hello " . $fb_user->name);
//print_r($fb_user);
// Check to see if we have a valid Facebook user.
if(!$fb_user)
{
    add_log('critical', $user->data['user_id'], 'FB_ERROR_USER');
    // Inform the user that we couldn't get their Facebook Id.
    echo json_encode(array(
		'status'		=> -1,
		'error_message'	=> 'Could not get Facebook user.',
	));
}

$sql = 'SELECT user_id, username, user_password, user_passchg, user_pass_convert, user_email, user_type, user_login_attempts
		FROM ' . USERS_TABLE . "
		WHERE al_fb_id = " . $fb_user->id;
        

// Execute the query.
$result = $db->sql_query($sql);

// Retrieve the row data.
$row = $db->sql_fetchrow($result);

// Free up the result handle from the query.
$db->sql_freeresult($result);

// Check to see if we found a user_id with the associated Facebook Id.
if ($row)   // User is registered already, let's log him in!
{
        
	$old_session_id = $user->session_id;

        if ($admin)
        {
                global $SID, $_SID;

                $cookie_expire = time() - 31536000;
                $user->set_cookie('u', '', $cookie_expire);
                $user->set_cookie('sid', '', $cookie_expire);
                unset($cookie_expire);

                $SID = '?sid=';
                $user->session_id = $_SID = '';
        }

        $admin = false;
		$autologin = true;
		$viewonline = true;
        $result = $user->session_create($row['user_id'], $admin, $autologin, $viewonline);
		
        // Successful session creation
        if ($result === true)
        {
                // If admin re-authentication we remove the old session entry because a new one has been created...
                if ($admin)
                {
                        // the login array is used because the user ids do not differ for re-authentication
                        $sql = 'DELETE FROM ' . SESSIONS_TABLE . "
                                WHERE session_id = '" . $db->sql_escape($old_session_id) . "'
                                AND session_user_id = {$row['user_id']}";
                        $db->sql_query($sql);
                }

                // Store the access token for use with this session.
                $sql_array = array(
                    'session_fb_access_token'   => $access_token,
                );

                $sql = "UPDATE " . SESSIONS_TABLE . " SET " . $db->sql_build_array('UPDATE', $sql_array) . " WHERE session_id='" . $user->data['session_id'] . "'";

                $db->sql_query($sql);
                $data = array();
                // Update the stored data such as profile and signatures.  Avatar is a dynamic field and doesn't require changing.

                if($user->data['al_fb_profile_sync'])
                {

                    $graph_url = "https://graph.facebook.com/me?" . $access_token;

                    $fb_user = json_decode(get_fb_data($graph_url));

                    $data['user_website']                    = (!$fb_user->website) ? '' : $fb_user->website;
                    $data['user_from']                   = (!$fb_user->location->name) ? '' : $fb_user->location->name;
                    $data['user_occ']                 = (!$fb_user->work[0]->employer->name) ? '' : $fb_user->work[0]->employer->name;
                    $bday = explode('/', $fb_user->birthday);
                    $data['user_birthday']              = sprintf('%2d-%2d-%4d', $bday[1], $bday[0], $bday[2]);

                }

                if($user->data['al_fb_status_sync'])
                {
                    include($phpbb_root_path . 'includes/message_parser.' . $phpEx);
                    $graph_url = "https://graph.facebook.com/me/statuses?" . $access_token;

                    $fb_user = json_decode(get_fb_data($graph_url));

                    $signature = $fb_user->data[0]->message;

                    $enable_bbcode                      = ($config['allow_sig_bbcode']) ? (bool) $user->optionget('sig_bbcode') : false;
                    $enable_smilies                     = ($config['allow_sig_smilies']) ? (bool) $user->optionget('sig_smilies') : false;
                    $enable_urls                        = ($config['allow_sig_links']) ? (bool) $user->optionget('sig_links') : false;

                    $message_parser = new parse_message($signature);

                    // Allowing Quote BBCode
                    $message_parser->parse($enable_bbcode, $enable_urls, $enable_smilies, $config['allow_sig_img'], $config['allow_sig_flash'], true, $config['allow_sig_links'], true, 'sig');

                    $data['user_sig']                   = (string) $message_parser->message;
                    $data['user_options']               = $user->data['user_options'];
                    $data['user_sig_bbcode_uid']	= (string) $message_parser->bbcode_uid;
                    $data['user_sig_bbcode_bitfield']	= $message_parser->bbcode_bitfield;
                }

                if($user->data['al_fb_profile_sync'] || $user->data['al_fb_status_sync'])
                {
                    $sql = 'UPDATE ' . USERS_TABLE . '
                            SET ' . $db->sql_build_array('UPDATE', $data) . '
                            WHERE user_id = ' . $user->data['user_id'];

                    $db->sql_query($sql);
                }
                
                json_encode(array(
					'status'		=> 1,
					'data'			=> $user->data,
				));
        }
        else
        {
            json_encode(array(
				'status'		-1,
				'error_message'	=> 'Failed to log user into board.',
			));
        }
        

        

}
else
{
	echo json_encode(array(
						'status'		=> -1,
						'error_message'	=> 'User was not found.',
	));
}
?>
