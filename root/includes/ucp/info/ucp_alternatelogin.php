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

class ucp_alternatelogin_info
{
	function module()
	{
		return array(
			'filename'	=> 'ucp_alternatelogin',
			'title'		=> 'UCP_ALTERNATELOGIN',
			'version'	=> '1.0.0',
			'modes'		=> array(
				'main'		=> array('title' => 'UCP_AL_MAIN', 'auth' => '', 'cat' => array('UCP_ALTERNATELOGIN')),
			),
		);
	}

	function install()
	{
	}

	function uninstall()
	{
	}
}

?>