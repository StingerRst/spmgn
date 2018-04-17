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
    . " WHERE " . USER_GROUP_TABLE . ".user_id=" . $user->data['user_id']." and ". USER_GROUP_TABLE . ".group_id=14";
	$result=$db->sql_query($sql);
	$row = $db->sql_fetchrow($result);

	if (!$row){ // если не Оператор ЕЦ - не пускать
		trigger_error('Доступ только Операторам ЕЦ');
	}	
$sql='SELECT user_id, username,  FROM_UNIXTIME( user_regdate) AS regdate FROM phpbb_users_copy';
	
	
	
if($_search){
	if (isset($_POST['filters'])) $filters = $_POST['filters'];// Фильтры для поиска
	if (isset($_POST['searchField'])) $searchField = $_POST['searchField']; // Фильтр по одному полю (имя)
	if (isset($_POST['searchOper'])) $searchOper = $_POST['searchOper']; // Фильтр по одному полю (операция)
	if (isset($_POST['searchString'])) $searchString = $_POST['searchString']; // Фильтр по одному полю (значение)
	
	$searchString = generateSearchString($filters, $searchField, $searchOper, $searchString);
}
//echo ($searchString);	


function generateSearchString($filters, $searchField, $searchOper, $searchString){
		$where = '';
	if($filters){
		$filters = json_decode($filters);
		$where .= self::generateSearchStringFromObj($filters);
	}
	
	return $where;
}	
	
?>