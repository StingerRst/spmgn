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
class acp_rep_ranks_info
{
	function module()
	{
		return array(
			'filename'	=> 'acp_rep_ranks',
			'title'		=> 'ACP_MANAGE_REP_RANKS',
			'version'	=> '0.2.0',
			'modes'		=> array(
				'ranks'		=> array('title' => 'ACP_MANAGE_REP_RANKS', 'auth' => 'acl_a_reputation', 'cat' => array('')),
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