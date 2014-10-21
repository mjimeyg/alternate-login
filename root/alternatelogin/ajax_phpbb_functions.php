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
include_once($phpbb_root_path . 'includes/functions_alternatelogin.' . $phpEx);	// Custom Alternate Login functions.



// Set up a new user session.
$user->session_begin();
$auth->acl($user->data);
$user->setup('ucp');
$user->add_lang('mods/info_acp_alternatelogin');	// Global Alternate Login language file.
$user->add_lang('mods/info_ucp_alternatelogin');

$f = request_var('f', '');

switch($f)
{
	case 'validate_username':
		$username = request_var('username', '');
		
		$val = validate_username($username);
		
		$return_value = array('return_value' => $val);
		
		echo json_encode($return_value);
		
		break;
		
	default:
		echo return_error('An undefined funtion name was passed: ' . $f, null, 0, 'json');
		break;
}

function return_error($message, $trace, $code = 0, $return_type = 'array')
{
	$return_array = array(
						'error'			=> isset($message) ? $message : 'The function return_error() was called but no value for $message was passed.',
						'stack_trace'	=> isset($trace) ? $trace : null,
						'code'			=> $code
					);
				
	$return_value = null;	
	switch($return_type)
	{
		case 'json':
			$return_value = json_encode($return_array);
			break;
			
		case 'array':
			$return_value = $return_array;
		default:
			break;
	}
	
	return $return_value;
}

?>