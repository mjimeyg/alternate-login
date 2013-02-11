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
$phpbb_root_path = (defined('PHPBB_ROOT_PATH')) ? PHPBB_ROOT_PATH : '../../';
$phpEx = substr(strrchr(__FILE__, '.'), 1);

// Load include files.
include($phpbb_root_path . 'common.' . $phpEx);
include_once($phpbb_root_path . 'includes/functions_user.' . $phpEx);

// Set up a new user session.
$user->session_begin();
$auth->acl($user->data);
$user->setup('ucp');

try
{
	$al_type = /*request_var('al_type', '')*/ $_REQUEST['al_type'];
	$al_id = /*request_var('al_id', '')*/ $_REQUEST['al_id'];
	/*echo json_encode(array('al_id' => $al_type));
	return;*/
	$sql_array = array(
		'SELECT'	=> 'COUNT(*) AS registered',
		'FROM'		=> array(USERS_TABLE => 'u'),
		'WHERE'		=> $al_type . "='" . $al_id . "'"
	);
	
	$sql = $db->sql_build_query('SELECT', $sql_array);
	
	$result = $db->sql_query($sql);
	
	$registered = $db->sql_fetchfield('registered');
	
	$return_val = array();
	
	if(mysql_errno() > 0)
	{
		$error = array(
			'error_code'	=> 0,
			'error_message'	=> 'A database query error occurred: ' . mysql_error(),
		);
		
		echo json_encode($error);
		return;
	}
	
	
	if($registered == 0)
	{
		$return_val['status'] = 0;
	}
	else
	{
		$return_val['status'] =  1;
	}
	echo json_encode($return_val);
	return;
}
catch(Exception $ex)
{
	$error = array(
		'error_code'	=> 1,
		'error_message'	=> $ex->getMessage(),
	);
	
	echo json_encode($error);
	return;
}
?>
