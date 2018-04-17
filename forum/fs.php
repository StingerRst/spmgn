<?php
/* MOD Forum Statistics
@ file version : 0.2.0
@ package phpBB3
@ author : TheUniqueTiger (Nayan Ghosh)
@ license : http://opensource.org/licenses/gpl-license.php GNU Public License
*/

//Some code in this as well as include files is taken from phpBB3... Special thanks.

/* @ fs.php ---- first created on 24-11-2007
@ description : the page that shows Forum statistics
*/

/**
* @ignore
*/
define('IN_PHPBB', true);
$phpbb_root_path = (defined('PHPBB_ROOT_PATH')) ? PHPBB_ROOT_PATH : './';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
include($phpbb_root_path . 'common.'.$phpEx);
require($phpbb_root_path . 'includes/functions_module.' . $phpEx);
include($phpbb_root_path . 'includes/functions_fs.'.$phpEx);

//start initial vars setup
$id = request_var('i', '');
$mode = request_var('mode', '');

// Start session
$user->session_begin();
$auth->acl($user->data);
$user->setup('fs');

//check if the user has permissions to view fs, this is only check for guests and the $config['allow_guests_view_fs'] option
if ((!$user->data['is_registered'] && !$config['allow_guests_view_fs']) || $user->data['is_bot'])
{
	trigger_error('NOT_AUTHORISED');
}

$module = new p_master();

// Instantiate module system and generate list of available modules
$module->list_modules('fs');

// Select the active module
$module->set_active($id, $mode);

// Load and execute the relevant module
$module->load_active();

// Assign data to the template engine for the list of modules
$module->assign_tpl_vars(append_sid("{$phpbb_root_path}fs.$phpEx"));

// Generate the page, do not display/query online list
$module->display($module->get_page_title(), false);

?>