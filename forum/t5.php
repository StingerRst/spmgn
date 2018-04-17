<?php
ini_set("display_errors",1);
error_reporting(E_ALL);
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

/*
// определение группы пользователя
$sql = "SELECT " . USER_GROUP_TABLE . ".group_id" 
    . " FROM " . USER_GROUP_TABLE 
    . " WHERE " . USER_GROUP_TABLE . ".user_id=" . $user->data['user_id']." and ". USER_GROUP_TABLE . ".group_id=14";
	$result=$db->sql_query($sql);
	$row = $db->sql_fetchrow($result);

	if (!$row){ // если не Оператор ЕЦ - не пускать
		trigger_error('Доступ только Операторам ЕЦ');
	}	
*/
//echo $hide;

//var_dump ('123'); 

//echo ($phpbb_root_path.'includes/JqGrigPHP/config.php'); 


require "/var/www/html/spmgn.ru/forum/includes/JqGridPHP/jqGridLoader.php";
$loader = new jqGridLoader;
#Set grid directory
$loader->set("grid_path", '/var/www/html/spmgn.ru/forum/includes/JqGridPHP/grids/');
 
#Use PDO for database connection
$loader->set("db_driver", "PDO");
 
#Set PDO-specific settings
$loader->set("pdo_dsn"  , "mysql:dbname=test;host=127.0.0.1");
$loader->set("pdo_user" , "rustamdzh");
$loader->set("pdo_pass" , "JnRoust35105");
#Turn on debug output
$loader->set('debug_output', true);

//$loader->autorun();
$grid_name='jqSimple.php';

$loader->output($grid_name);
$loader->render($grid_name, null, 2);
var_dump ($loader);

//trigger_error('error', E_USER_ERROR);
//echo ('end');



$template->set_filenames(array(
	'body' => 't5.html',
));

make_jumpbox(append_sid("{$phpbb_root_path}viewforum.$phpEx"));
page_footer();

?>