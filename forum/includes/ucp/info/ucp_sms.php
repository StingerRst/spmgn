<?php
/**
* @package ucp
* @version $Id$
* @copyright (c) 2005 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

/**
* @package module_install
*/
class ucp_sms_info
{
	function module()
	{
		return array(
			'filename'	=> 'ucp_sms',
			'title'		=> 'UCP_SMS',
			'version'	=> '1.0.0',
			'modes'		=> array(
				'compose'	=> array('title' => 'UCP_SMS', 'auth' => 'acl_u_send_sms', 'cat' => array('UCP_SMS')),
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