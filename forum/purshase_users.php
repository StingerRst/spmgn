<?php
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
    . " WHERE " . USER_GROUP_TABLE . ".user_id=" . $user->data['user_id']." and ". USER_GROUP_TABLE . ".group_id=5";
	$result=$db->sql_query($sql);
	$row = $db->sql_fetchrow($result);

	if (!$row){ // если не Администратор - не пускать
		trigger_error('Доступ только администраторам');
	}	
//$template->assign_var('HIDE',(isset($_POST['hide_pay'])?1:0));
//$hide = (isset($_POST['hide_pay'])?1:0);
//echo $hide;

// Отчет по закупкам
	$sql="SELECT
			SubQuery.brand_id,SubQuery.brand_label,	SubQuery.brand_url,	phpbb_users.username
		FROM (SELECT phpbb_brands.brand_id,	phpbb_brands.brand_label, phpbb_brands.brand_url, MAX(phpbb_reservs.reserv_id) AS r_id
		  FROM phpbb_brands
			LEFT OUTER JOIN phpbb_reservs
			  ON phpbb_brands.brand_id = phpbb_reservs.brand_id
		  GROUP BY phpbb_brands.brand_id,
				   phpbb_brands.brand_label,
				   phpbb_brands.brand_url) SubQuery
		  LEFT OUTER JOIN phpbb_reservs
			ON SubQuery.r_id = phpbb_reservs.reserv_id
		  LEFT OUTER JOIN phpbb_users
			ON phpbb_reservs.user_id = phpbb_users.user_id
		ORDER BY SubQuery.brand_label";

	$result=$db->sql_query($sql);
	while ($row = $db->sql_fetchrow($result)) {
		$template->assign_block_vars('otchet', array(
           'BRAND_ID' => $row['brand_id'],
           'BRAND_LABEL' => $row['brand_label'],
           'BRAND_URL' => $row['brand_url'],
           'ORG_NAME' => $row['username']
		  ));
	}
	 
$template->assign_var('DATE_PRINT',date ("d.m.Y", time()));

page_header('Участники какупок');

$template->set_filenames(array(
	'body' => 'purshase_users.html',
));

make_jumpbox(append_sid("{$phpbb_root_path}viewforum.$phpEx"));
page_footer();

?>