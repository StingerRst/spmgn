<?php
/* MOD Forum Statistics
@package : fs (Forum Statistics)
@author : TheUniqueTiger (Nayan Ghosh)
@license : http://opensource.org/licenses/gpl-license.php GNU Public License
*/

/**
* @package module_install
*/
class fs_basic_info
{
	function module()
	{
		return array(
		'filename'	=> 'fs_basic',
		'title'		=> 'FS_BASIC',
		'version'	=> '1.0.0',
		'modes'		=> array(
			'basic'			=> array('title' => 'FS_BASIC_BASIC', 'auth' => '', 'cat' => array('FS_BASIC')),
			'advanced'		=> array('title' => 'FS_BASIC_ADVANCED', 'auth' => '', 'cat' => array('FS_BASIC')),
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