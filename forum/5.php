<?php

//echo $HTTP_USER_AGENT;
//echo ('1.php');
// Указываем всем подключающимся скриптам,
// что они вызывается из главного файла.
// Для защиты от вызова их напрямую.

define('IN_PHPBB', true);

// Создаем переменную, содержащую
// путь к корню сайта.
$phpbb_root_path = (defined('PHPBB_ROOT_PATH')) ? PHPBB_ROOT_PATH : './';
 
// Указываем расширение к подключаемым файлам.
// Обычно .php.
$phpEx = substr(strrchr(__FILE__, '.'), 1);
//echo ($phpbb_root_path . 'common.' . $phpEx); 
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
/////////////////////////////////////////////
$purchase_id =3762;
// смотрим joomla_material_id
$sql='SELECT joomla_material_id,purchase_name,purchase_description FROM phpbb_purchases WHERE purchase_id ='.$purchase_id;
$result=$db->sql_query($sql);
$row = $db->sql_fetchrow($result);
if ($row) {
	if (!$row['joomla_material_id']) { // если поле с id статьи пустое, то создаем статью
		$purchase_name=$row['purchase_name'];
		$purchase_description=$row['purchase_description'];
		// получаем id пользователя Joomla
		$sql ="SELECT id FROM byfpd_users WHERE email = UCASE ( '".$user->data['user_email']."') AND username = UCASE ('".$user->data['username']."')";
		$result=$db->sql_query($sql);
		$row = $db->sql_fetchrow($result);
		if ($row) {
			$user_id= $row['id'];
			// создам статью
			$sql="INSERT INTO byfpd_k2_items
			(title,catid,published,introtext,`fulltext`, extra_fields_search,created,created_by,created_by_alias,checked_out,
			checked_out_time,modified,modified_by,publish_up,publish_down, trash,access,ordering,featured,featured_ordering,
			image_caption,image_credits,video_caption,video_credits,hits, params,metadesc,metadata,metakey,plugins,language) 
			VALUES ('".$purchase_name."',88,0,'".$purchase_description."','','',NOW(),".$user_id.",'',0,0,0,0,0,0,0,1,1,0,0,'','','','',0,'','','','','','*')";
			$db->sql_query($sql);
			$row = $db->sql_fetchrow($db->sql_query("SELECT LAST_INSERT_ID() as id"));
			$joomla_material_id= $row['id']; 
			$sql = 'UPDATE phpbb_purchases SET joomla_material_id = '.$joomla_material_id.' WHERE purchase_id ='.$purchase_id;
			$db->sql_query($sql);
			echo ($user_id);
		}	
	}
}
?>