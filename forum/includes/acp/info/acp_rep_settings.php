<?php
/**
*
* @author idiotnesia pungkerz@gmail.com - http://www.phpbbindonesia.com
*
* @package acp
* @version 0.2.0a
* @copyright (c) 2008 phpbbindonesia.com
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
* 
*/

/**
* @package module_install
*/
class acp_rep_settings_info
{
	function module()
	{
		return array(
			'filename'	=> 'acp_rep_settings',
			'title'		=> 'ACP_REPUTATION_SETTINGS',
			'version'	=> '0.2.0',
			'modes'		=> array(
				'reputation'		=> array('title' => 'ACP_REPUTATION_SETTINGS', 'auth' => 'acl_a_reputation', 'cat' => array('')),
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