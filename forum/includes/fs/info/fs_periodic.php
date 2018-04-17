<?php
/* MOD Forum Statistics
@package : fs (Forum Statistics)
@author : TheUniqueTiger (Nayan Ghosh)
@license : http://opensource.org/licenses/gpl-license.php GNU Public License
*/

/**
* @package module_install
*/
class fs_periodic_info
{
	function module()
	{
		return array(
		'filename'	=> 'fs_periodic',
		'title'		=> 'FS_PERIODIC',
		'version'	=> '1.0.0',
		'modes'		=> array(
			'daily'			=> array('title' => 'FS_PERIODIC_DAILY', 'auth' => '', 'cat' => array('FS_PERIODIC')),
			'monthly'		=> array('title' => 'FS_PERIODIC_MONTHLY', 'auth' => '', 'cat' => array('FS_PERIODIC')),
			'hourly'		=> array('title' => 'FS_PERIODIC_HOURLY', 'auth' => '', 'cat' => array('FS_PERIODIC')),
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