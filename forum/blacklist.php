<?php
/**
*
* @author alightner
*
* @package phpBB Calendar
* @version CVS/SVN: $Id: $
* @copyright (c) 2009 alightner
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
include($phpbb_root_path . 'includes/functions_display.' . $phpEx);
include($phpbb_root_path . 'includes/functions_privmsgs.' . $phpEx);
include($phpbb_root_path . 'includes/ucp/ucp_pm_compose.' . $phpEx);

// Start session management
$user->session_begin();
$auth->acl($user->data);

//$user->setup('viewforum');
$user->setup('bl');

/**
* All of your coding will be here, setting up vars, database selects, inserts, etc...
*/
$user_name = $_POST['userid'];
$text = (isset($_POST['description']))?$_POST['description']:'';
$act = request_var('act', '');

if ((!$user->data['is_organizer']) and (!$auth->acl_get('a_')))
{
	trigger_error('Доступ закрыт');
}

if ($act=='add'){
	$sql = 'SELECT * FROM '. USERS_TABLE .'
		WHERE '. USERS_TABLE .'.username="'.$user_name.'"';
	$result = $db->sql_query($sql);
	$row = $db->sql_fetchrow($result);
	if ($row){
		$user_id=$row['user_id'];
		$sql = 'SELECT * FROM 
			'. BLACKLIST_TABLE .'
			WHERE '. BLACKLIST_TABLE .'.user_id = '. $user_id;
		$result = $db->sql_query($sql);
		$row = $db->sql_fetchrow($result);
		if (!$row){
			$sql =  'INSERT INTO '. BLACKLIST_TABLE .'
					VALUES ('.$user_id.',"'.date("Y-m-d").'",\'<b>'.$user->data['username'].':</b> '.$text.'\')';
		}else{
			$sql =  'UPDATE '. BLACKLIST_TABLE .'
					SET text=\''.$db->sql_escape($row['text']).'<br/><b>'.$user->data['username'].':</b> '.$text.'\'
					WHERE user_id='.$user_id;
		}
		$db->sql_query($sql);

		$sql =  'INSERT IGNORE INTO phpbb_user_group (group_id,user_pending, user_id)  VALUES (20,0,'.$user_id.')';
		$db->sql_query($sql);
		$sql =  'UPDATE phpbb_users SET group_id = 20 WHERE user_id = '.$user_id;
		$db->sql_query($sql);
		
		$msg_id="0";
		$action="post";
		$subject="Черный список";
		$pm_data = array(
			'msg_id'				=> (int) $msg_id,
			'from_user_id'			=> $user->data['user_id'],
			'from_user_ip'			=> $user->ip,
			'from_username'			=> $user->data['username'],
			'reply_from_root_level'	=> (isset($post['root_level'])) ? (int) $post['root_level'] : 0,
			'reply_from_msg_id'		=> (int) $msg_id,
			'icon_id'				=> 0,
			'enable_sig'			=> true,
			'enable_bbcode'			=> true,
			'enable_smilies'		=> true,
			'enable_urls'			=> true,
			'bbcode_bitfield'		=> '',
			//'bbcode_uid'			=> '3kuqgr4s',
			'bbcode_uid'			=> '29oazmrr',
			'message'				=> 'Вы добавлены в черный список согласно п. 2.18.1.1 правил с формулировкой:'.$text,
			'attachment_data'		=> array (),
			'filename_data'			=> array ('filecomment' => ''),
			'address_list'			=> array ('u' => array ($user_id => 'to' ) )
		);
					
		$msg_id = submit_pm($action, $subject, $pm_data);
		
		
		$url = append_sid("{$phpbb_root_path}blacklist.$phpEx");
		header("Location:".$url);
		
		echo '<script type="text/javascript">
		window.location = '.$url.'
		</script>';
	}
}
if ($act=='del'){
	$sql =  'DELETE FROM '. BLACKLIST_TABLE .'
			WHERE user_id='.$user_name;
	$db->sql_query($sql);
	
	$sql =  'DELETE FROM phpbb_user_group WHERE group_id = 20 AND user_id ='.$user_name;
	$db->sql_query($sql);
	
	$sql =  'UPDATE phpbb_users SET group_id = 2 WHERE user_id = '.$user_name;
	$db->sql_query($sql);

		
	$msg_id="0";
	$action="post";
	$subject="Черный список";
	$pm_data = array(
		'msg_id'				=> (int) $msg_id,
		'from_user_id'			=> $user->data['user_id'],
		'from_user_ip'			=> $user->ip,
		'from_username'			=> $user->data['username'],
		'reply_from_root_level'	=> (isset($post['root_level'])) ? (int) $post['root_level'] : 0,
		'reply_from_msg_id'		=> (int) $msg_id,
		'icon_id'				=> 0,
		'enable_sig'			=> true,
		'enable_bbcode'			=> true,
		'enable_smilies'		=> true,
		'enable_urls'			=> true,
		'bbcode_bitfield'		=> '',
		'bbcode_uid'			=> '3kuqgr4s',
		'message'				=> 'Вы удалены из черного списка',
		'attachment_data'		=> array (),
		'filename_data'			=> array ('filecomment' => ''),
		'address_list'			=> array ('u' => array ($user_name => 'to' ) )
	);
				
	$msg_id = submit_pm($action, $subject, $pm_data);	
	
}
	$sql = 'SELECT * FROM 
		'. BLACKLIST_TABLE .', '. USERS_TABLE .'
		WHERE '. BLACKLIST_TABLE .'.user_id = '. USERS_TABLE .'.user_id';
	$user_id = request_var('user_id',0);
	if ($user_id)
		$sql .= ' and '. BLACKLIST_TABLE .'.user_id = '.$user_id;
	$result = $db->sql_query($sql);
	while($row = $db->sql_fetchrow($result))
	{
		$template->assign_block_vars('blacklist',array(
			'ID'		=> $row['user_id'],
			'NAME'		=> $row['username'],
			'TEXT'		=> $row['text'],
			'DATA'		=> $row['data']
		));
	}
	$template->assign_vars(array(
		'ADMUSE'					=> ($auth->acl_get('a_'))?1:0
	));
	$template->set_filenames(array(
		'body' => 'blacklist.html'));
// Output the page
page_header('Черный список'); // Page title, this language variable should be defined in the language file you setup at the top of this page.



// Finish the script, display the page
page_footer();


?>