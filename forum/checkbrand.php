<?php
/**
*
* @package phpBB3
* @version $Id$
* @copyright (c) 2005 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

/**
* @ignore
*/
define('IN_PHPBB', true);
$phpbb_root_path = (defined('PHPBB_ROOT_PATH')) ? PHPBB_ROOT_PATH : './';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
include($phpbb_root_path . 'common.' . $phpEx);
include($phpbb_root_path . 'includes/message_parser.' . $phpEx);

// Start session management
$user->session_begin();
$auth->acl($user->data);
$user->setup();

if ($user->data['user_id'] == ANONYMOUS) login_box('', $user->lang['LOGIN']);
if (isset($_POST)) {
	$brandurl = preg_replace("/https:\/\//",'',trim($_POST['brandurl']));
	$brandurl = preg_replace("/http:\/\//",'',$brandurl);
	$brandurl = HtmlSpecialChars(preg_replace("/www./",'',$brandurl));
	$arr = explode('/',$brandurl);
	$brandurl = $arr[0];
	
 	$sql = 'SELECT * 
	FROM ' . BRANDS_TABLE . '
	JOIN ' . RESERVS_TABLE . '
	ON ' . BRANDS_TABLE . '.brand_id = ' . RESERVS_TABLE . '.brand_id
	WHERE (';
	if (trim(HtmlSpecialChars ($_POST['brandname']))<>'')
	$sql .= BRANDS_TABLE . '.brand_label like "' . trim(HtmlSpecialChars ($_POST['brandname'])) . '"';
	if ($brandurl<>''){
		if (trim(HtmlSpecialChars ($_POST['brandname']))<>'')$sql.=' OR ';
		$sql.=' (' . BRANDS_TABLE . '.brand_url like "' . $brandurl . '"
		OR ' . BRANDS_TABLE . '.brand_url like "www.' . $brandurl . '"
		OR ' . BRANDS_TABLE . '.brand_url like "http://www.' . $brandurl . '")';
	}
	$sql.='
	) AND ' . RESERVS_TABLE . '.status > 0';
	$db->sql_query($sql);
	if ($db->sql_affectedrows()) {
		echo '<b>Бренд уже забронирован</b>';
	} else echo '<b>Бренд свободен</b>';
}
?>