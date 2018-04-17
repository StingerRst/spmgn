<?php
/* MOD Forum Statistics
@package : fs (Forum Statistics)
@author : TheUniqueTiger (Nayan Ghosh)
@license : http://opensource.org/licenses/gpl-license.php GNU Public License
*/

/**
* @package module_install
*/
class fs_settings_info
{
	function module()
	{
		return array(
		'filename'	=> 'fs_settings',
		'title'		=> 'FS_SETTINGS',
		'version'	=> '1.0.0',
		'modes'		=> array(
			'board'			=> array('title' => 'FS_SETTINGS_BOARD', 'auth' => '', 'cat' => array('FS_SETTINGS')),
			'profile'		=> array('title' => 'FS_SETTINGS_PROFILE', 'auth' => '', 'cat' => array('FS_SETTINGS')),
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