<?php
// Указываем всем подключающимся скриптам,
// что они вызывается из главного файла.
// Для защиты от вызова их напрямую.
//echo ('test');
define('IN_PHPBB', true);

// Создаем переменную, содержащую
// путь к корню сайта.
$phpbb_root_path = (defined('PHPBB_ROOT_PATH')) ? PHPBB_ROOT_PATH : './';
 
// Указываем расширение к подключаемым файлам.
// Обычно .php.
$phpEx = substr(strrchr(__FILE__, '.'), 1);
 
// Подключаем ядро phpBB.
include($phpbb_root_path . 'common.' . $phpEx);
include($phpbb_root_path . 'includes/functions_display.' . $phpEx);


// Запускаем инициализацию сессии.
$user->session_begin();
$auth->acl($user->data);
$user->setup('viewforum');

if ($user->data['user_id'] == ANONYMOUS)
{
    login_box('', $user->lang['LOGIN']);
}
// определение группы пользователя
$sql = "SELECT " . USER_GROUP_TABLE . ".group_id" 
    . " FROM " . USER_GROUP_TABLE 
    . " WHERE " . USER_GROUP_TABLE . ".user_id=" . $user->data['user_id']." and ". USER_GROUP_TABLE . ".group_id=14";
	$result=$db->sql_query($sql);
	$row = $db->sql_fetchrow($result);

	if (!$row){ // если не Оператор ЕЦ - не пускать
		trigger_error('Доступ только Операторам ЕЦ');
	}
$sql ='SELECT
		  phpbb_users.user_id,
		  phpbb_users.username,
		  FROM_UNIXTIME(phpbb_privmsgs.message_time) AS Mtime,
		  phpbb_privmsgs.message_text,
		  phpbb_users_1.username AS ToUser
		FROM phpbb_users
		  LEFT OUTER JOIN phpbb_privmsgs
			ON phpbb_users.user_id = phpbb_privmsgs.author_id
		  LEFT OUTER JOIN phpbb_privmsgs_to
			ON phpbb_privmsgs.msg_id = phpbb_privmsgs_to.msg_id
		  LEFT OUTER JOIN phpbb_users phpbb_users_1
			ON phpbb_privmsgs_to.user_id = phpbb_users_1.user_id
		WHERE phpbb_users.user_id = 9003
		AND phpbb_users_1.username <> phpbb_users.username
		ORDER BY Mtime DESC';	
	$result=$db->sql_query($sql);
	echo ('<table width="100%" border="1px">');
		echo ('<tr>');
			echo ('<th width="10%">Автор</th>');
			echo ('<th width="10%">Дата</th>');
			echo ('<th width="68%">Текст</th>');
			echo ('<th width="10%">Получатель</th>');
			//echo ('</br>');
		echo ('</tr>');

	
	while ($row = $db->sql_fetchrow($result)) {	
		echo ('<tr>');
			echo ('<td>'.$row['username'].'</td>');
			echo ('<td>'.$row['Mtime'].'</td>');
			echo ('<td>'.$row['message_text'].'</td>');
			echo ('<td>'.$row['ToUser'].'</td>');
			//echo ('</br>');
		echo ('</tr>');
	};
	
	echo ('</table>');

?>	