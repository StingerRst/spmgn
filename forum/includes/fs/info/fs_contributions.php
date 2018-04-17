<?php
/* MOD Forum Statistics
@package : fs (Forum Statistics)
@author : TheUniqueTiger (Nayan Ghosh)
@license : http://opensource.org/licenses/gpl-license.php GNU Public License
*/

/**
* @package module_install
*/
class fs_contributions_info
{
	function module()
	{
		return array(
		'filename'	=> 'fs_contributions',
		'title'		=> 'FS_CONTRIBUTIONS',
		'version'	=> '1.0.0',
		'modes'		=> array(
			'attachments'			=> array('title' => 'FS_CONTRIBUTIONS_ATTACHMENTS', 'auth' => '', 'cat' => array('FS_CONTRIBUTIONS')),
			'polls'		=> array('title' => 'FS_CONTRIBUTIONS_POLLS', 'auth' => '', 'cat' => array('FS_CONTRIBUTIONS')),
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