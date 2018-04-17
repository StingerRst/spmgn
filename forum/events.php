<?php
/**
*
* @package ucp
* @version $Id$
* @copyright (c) 2005 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

/**
* @ignore
*/

// todo: При создании события отправлять уведомление на почту
define('IN_PHPBB', true);
$phpbb_root_path = (defined('PHPBB_ROOT_PATH')) ? PHPBB_ROOT_PATH : './';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
include($phpbb_root_path . 'common.' . $phpEx);
include($phpbb_root_path . 'includes/functions_posting.' . $phpEx);
include($phpbb_root_path . 'includes/functions_display.' . $phpEx);
include($phpbb_root_path . 'includes/message_parser.' . $phpEx);

// Start session management
$user->session_begin();
$auth->acl($user->data);
$user->setup();

if ($user->data['user_id'] == ANONYMOUS) login_box('', $user->lang['LOGIN']);

// Request variables

if (isset($_POST['username_list'])){
	$username_list = explode("\n", ($_POST['username_list']));
}else{
	$username_list = '';
}
$mode = request_var('mode', 'new');
$event_subject = utf8_normalize_nfc(request_var('subject', ' ', true));
$event_begin = date("Y-m-d H:i", strtotime (request_var('date_begin', '').' '.request_var('time_begin', '')));
$event_end = date("Y-m-d H:i", strtotime (request_var('date_end', '').' '.request_var('time_end', '')));
$event_begin2 = date("H:i d.m.Y", strtotime (request_var('date_begin', '').' '.request_var('time_begin', '')));
$event_end2 = date("H:i d.m.Y", strtotime (request_var('date_end', '').' '.request_var('time_end', '')));
$event_type_id = (int)(request_var('event_type_id', '6'));
$purchase_id = request_var('purchase_id','');
$purchase_name = utf8_normalize_nfc(request_var('purchase_name', '', true));
$purchase_url = utf8_normalize_nfc(request_var('purchase_url', '', true));
$event_body = utf8_normalize_nfc(request_var('message', '', true));
$event_id = request_var('event_id', 0);
$event_user_list_id = request_var('event_user_list_id', '');
$event_i = '0';
if (isset($_POST['event_ids'])){
	foreach ($_POST['event_ids'] as $event_ids){
		$event_i .= ', '.(int)$event_ids;
	}
}
$username_list2 = '';
if (isset($_POST['new_event_user_lname'])){
	$username_list2 = str_replace(',', "\n",$_POST['new_event_user_lname']);
}

page_header("Календарь событий", false);

generate_smilies('inline', 0);

$template->assign_vars (array(
	'CALENDAR_ADMIN' => ($auth->acl_get('a_')||$user->data['is_organizer'])?true:false,
));

switch ($mode) {
	case 'new' :
			//var_dump($purchase_id);
			//var_dump($purchase_name);
			//var_dump($purchase_url);
		
			$l='';
			$sql = 'SELECT * 
				FROM ' . EVENTS_TYPE_TABLE .'
				ORDER BY event_type_id';
			$result = $db->sql_query($sql);
			while($row = $db->sql_fetchrow($result)) {
				$l.='<label for="icon-' . $row['event_type_id'] . '">
					<input name="event_type_id" id="icon-' . $row['event_type_id'] . '" type="radio" value="' . $row['event_type_id'] . '" '.(($row['event_type_id'] == 0)?'checked':'').'>
					<img src="./images/events/' . $row['event_type_id'] . '_color.png" alt="' . $row['event_type_label'] . '" title="' . $row['event_type_label'] . '" border="0"></label>';
			}
	
		$template->assign_vars (array(
			'USER_LIST'			=> $username_list2,
			'NOW_TIME1'			=> date("H:i"),
			'TODAY1'			=> date("d-m-Y"),
			'NOW_TIME2'			=> date("H:i"),
			'TODAY2'			=> date("d-m-Y"),
			'SUBJECT' 			=> $event_subject,
			'MESSAGE' 			=> $event_body,
			'FLAG' 				=> $l,
			'PURCHASE_ID' => $purchase_id,
			'PURCHASE_NAME' => $purchase_name,
			'PURCHASE_URL' => $purchase_url,
			'S_BBCODE_ALLOWED' 	=> true,
			'L_EVENT_A' 		=> 'Новое событие',
			'S_EDIT_POST' 		=> true,
			'S_SMILIES_ALLOWED' => true,
			'S_SHOW_SMILEY_LINK'=> false,
			'U_MORE_SMILIES' 	=> append_sid("{$phpbb_root_path}posting.$phpEx", 'mode=smilies&amp;f=0'),
			'U_ACTION' 			=> 'add',
		));
		$template->set_filenames(array(
			'body' => 'event_new.html'));
	break;
	case 'edit' :
		if ($event_id>0){
			$sql = 'SELECT *
				FROM ' . EVENTS_TABLE . '
				JOIN ' . USERS_TABLE . '
				ON ' . EVENTS_TABLE . '.author_id = ' . USERS_TABLE . '.user_id 
				WHERE ' . EVENTS_TABLE . '.event_id = '.$event_id;
				$result = $db->sql_query($sql);
				$rowt = $db->sql_fetchrow($result);
			$l='';
			$sql = 'SELECT * 
				FROM ' . EVENTS_TYPE_TABLE . '
				WHERE ' . EVENTS_TYPE_TABLE . '.event_type_id = ' .$rowt['event_type_id']. '
				ORDER BY event_type_id';
			$result = $db->sql_query($sql);
			$row = $db->sql_fetchrow($result); 
				$l.='<label for="icon-' . $row['event_type_id'] . '">
					<input name="event_type_id" id="icon-' . $row['event_type_id'] . '" type="radio" value="' . $row['event_type_id'] . '" checked
					<img src="./images/events/' . $row['event_type_id'] . '_color.png" alt="' . $row['event_type_label'] . '" title="' . $row['event_type_label'] . '" border="0"></label>';
		}
		$template->assign_vars (array(
			'AUTHOR' => '<a href="memberlist.php?do_=188&amp;mode=viewprofile&amp;u='.$rowt['user_id'].'">'.$rowt['username'].'</a>',
			'NOW_TIME1'	=> date("H:i", strtotime($rowt['event_begin'])),
			'TODAY1'	=> date("d-m-Y", strtotime($rowt['event_begin'])),
			'NOW_TIME2'	=> date("H:i", strtotime($rowt['event_end'])),
			'TODAY2'	=> date("d-m-Y", strtotime($rowt['event_end'])),
			'SUBJECT' => $rowt['event_subject'],
			'MESSAGE' => $rowt['event_text'],
			'FLAG' => $l,
			'PURCHASE_ID' => $purchase_id,
			'PURCHASE_NAME' => $purchase_name,
			'PURCHASE_URL' => $purchase_url,
			'EVENT' => $rowt['event_id'],
			'S_BBCODE_ALLOWED' => true,
			'L_EVENT_A' => 'Редактирование события',
			'S_EDIT_POST' => true,
			'S_SMILIES_ALLOWED' => true,
			'S_SHOW_SMILEY_LINK' => false,
			'S_EDIT' => true,
			'U_MORE_SMILIES' 		=> append_sid("{$phpbb_root_path}posting.$phpEx", 'mode=smilies&amp;f=0'),
			'U_ACTION' => 'eadd',
		));
		$template->set_filenames(array(
			'body' => 'event_new.html'));
	break;
	case 'add' :
		$sql = 'INSERT INTO ' . EVENTS_TABLE . '
			VALUES ( 
			NULL, "' .
			$user->data['user_id'] .'","'.
			$event_begin.'","'.
			$event_end.'","'.
			$event_type_id.'","'.
			date("Y-m-d H:i").'","'.
			$event_subject.'","'.
			$event_body.'")';
		$db->sql_query($sql);
		$event_id=$db->sql_nextid();
		$sql = 'INSERT INTO ' . EVENTS_TO_TABLE . '
			VALUES ( '.
			$event_id .','.
			$user->data['user_id'].',
			1, 0, NULL)';
		$db->sql_query($sql);
		if ($auth->acl_get('a_')||$user->data['is_organizer']){
		if (is_array($username_list))
		foreach ($username_list as $name){
			$sql = "SELECT user_id FROM ". USERS_TABLE ."
					WHERE username like '". trim($name)."'";
			$result = $db->sql_query($sql);
			$row = $db->sql_fetchrow($result);
			$user_id = $row['user_id'];
			if (isset($user_id))
				if ($user_id != $user->data['user_id']) {
					$sql = 'INSERT INTO ' . EVENTS_TO_TABLE . '
					VALUES ( '.
					$event_id .','.
					$user_id.',
					1, 0, NULL)';
					$db->sql_query($sql);
				}
		}
		}
		$db->sql_freeresult();
		if ($event_type_id==3){ //если событие по оплате, то отправляем уведомление на почту
			// получаем список email адресов	
			$sql = "SELECT username,user_email FROM phpbb_users WHERE username IN ( '".preg_replace('/([^\pL\pN\pP\pS\pZ])|([\xC2\xA0])/u','',implode("','",$username_list))."')";
			$result = $db->sql_query($sql);
			//var_dump($sql);
			$mail_to = array();
			while($row = $db->sql_fetchrow($result)) {
				$msg="Здравствуйте, ".$row['username'].".<br>";
				$msg.="В закупке ".$purchase_name." объявляется оплата <br>";
				$msg.="c ".$event_begin2 ." до ".$event_end2."<br>";
				$msg.='<a href="http://www.spmgn.ru/forum/cart.php#purchase'.$purchase_id.'">Корзина</a> <a href="'.$purchase_url.'">Тема закупки</a><br>';
				$msg.='С уважением, администрация сайта <a href="http://www.spmgn.ru">spmgn.ru</a> .';
				$headers  = "Content-type: text/html; charset=utf-8 \r\n"; 
				if (!(mail ($row['user_email'], "Оплата ".$event_subject,$msg,$headers)))
				{
					echo("<SCRIPT language='Javascript'> alert('При отправке почты произошла ошибка !');</script>");
				}					
					
				$mail_to[]=$row['user_email'];
			}	
	
	
		}

		$template->assign_vars (array(
			'MESSAGE_TITLE'	=> 'События созданы',
			'MESSAGE_TEXT'	=> 'События успешно добавлены.<br /><br /><a href="./event_calendar.php">Перейти к списку событий</a>',
		));
		$template->set_filenames(array(
			'body' => 'message_body.html'));
	break;
	case 'del' :
		$sql = 'DELETE 
			FROM ' . EVENTS_TO_TABLE . '
			WHERE	event_id IN ('. $event_i .')
			AND user_id = '.$user->data['user_id'];
		$db->sql_query($sql);
		$template->assign_vars (array(
			'MESSAGE_TITLE'	=> 'События удалены',
			'MESSAGE_TEXT'	=> 'События успешно удалены.<br /><br /><a href="./event_calendar.php">Перейти к списку событий</a>',
		));
		$template->set_filenames(array(
			'body' => 'message_body.html'));
	break;
	case 'eadd' :
		$sql = 'UPDATE ' . EVENTS_TABLE . '
			SET event_begin = "'. $event_begin.'",
			event_end = "'. $event_end.'",
			event_subject = "'.$event_subject.'",
			event_text = "'.$event_body.'"
			WHERE event_id = "'.$event_id.'"';
		$db->sql_query($sql);

	  $db->sql_freeresult();
	  $template->assign_vars (array(
			'MESSAGE_TITLE'	=> 'События созданы',
			'MESSAGE_TEXT'	=> 'События успешно исправлены.<br /><br /><a href="./event_calendar.php">Перейти к списку событий</a>',
		));
		$template->set_filenames(array(
			'body' => 'message_body.html'));
	break;
	case 'del' :
		$sql = 'DELETE 
			FROM ' . EVENTS_TO_TABLE . '
			WHERE	event_id IN ('. $event_i .')
			AND user_id = '.$user->data['user_id'];
		$db->sql_query($sql);
		$template->assign_vars (array(
			'MESSAGE_TITLE'	=> 'События удалены',
			'MESSAGE_TEXT'	=> 'События успешно удалены.<br /><br /><a href="./event_calendar.php">Перейти к списку событий</a>',
		));
		$template->set_filenames(array(
			'body' => 'message_body.html'));
	break;
	case 'done' :
		$sql = 'UPDATE ' . EVENTS_TO_TABLE . '
			SET event_done = 1
			WHERE	event_id IN ('. $event_i .')
			AND user_id = '.$user->data['user_id'];
		$db->sql_query($sql);
		$template->assign_vars (array(
			'MESSAGE_TITLE'	=> 'События выполненны',
			'MESSAGE_TEXT'	=> 'События успешно выполненны.<br /><br /><a href="./event_calendar.php">Перейти к списку событий</a>',
		));
		$template->set_filenames(array(
			'body' => 'message_body.html'));
	break;
}	

page_footer();
?>
 