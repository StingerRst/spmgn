<?php
/* MOD Forum Statistics
@package : fs (Forum Statistics)
@author : TheUniqueTiger (Nayan Ghosh)
@license : http://opensource.org/licenses/gpl-license.php GNU Public License
*/

/**
* @package module_install
*/
class fs_activity_info
{
	function module()
	{
		return array(
		'filename'	=> 'fs_activity',
		'title'		=> 'FS_ACTIVITY',
		'version'	=> '1.0.0',
		'modes'		=> array(
			'forums'		=> array('title' => 'FS_ACTIVITY_FORUMS', 'auth' => '', 'cat' => array('FS_ACTIVITY')),
			'topics'		=> array('title' => 'FS_ACTIVITY_TOPICS', 'auth' => '', 'cat' => array('FS_ACTIVITY')),
			'users'			=> array('title' => 'FS_ACTIVITY_USERS', 'auth' => '', 'cat' => array('FS_ACTIVITY')),
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