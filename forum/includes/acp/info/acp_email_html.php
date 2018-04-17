<?php
/**
*
* @package acp
* @version $Id: acp_email_html.php, ksar $
* @copyright (c) 2008
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

/**
* @package module_install
*/
class acp_email_html_info
{
	function module()
	{
		return array(
			'filename'	=> 'acp_email_html',
			'title'		=> 'ACP_MASS_EMAIL_HTML',
			'version'	=> '1.0.0',
			'modes'		=> array(
				'email'		=> array('title' => 'ACP_MASS_EMAIL_HTML_DO', 'auth' => 'acl_a_email', 'cat' => array('ACP_GENERAL_TASKS')),
				'view'		=> array('title' => 'ACP_MASS_EMAIL_HTML_VIEW', 'auth' => 'acl_a_email', 'cat' => array('ACP_GENERAL_TASKS')),
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