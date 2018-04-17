
<?php
$test='test 1.php';
// Включаем код для отладки и определяем объект
    require_once("PHPDebug.php");
    $debug = new PHPDebug();
    // Простое сообщение на консоль
    $debug->debug("Очень простое сообщение на консоль");

	
	
//echo $HTTP_USER_AGENT;
echo ('1.php');
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
include($phpbb_root_path . 'smsfox.' . $phpEx);
include($phpbb_root_path . 'includes/functions_display.' . $phpEx);

// Запускаем инициализацию сессии.
$user->session_begin();
$auth->acl($user->data);
$user->setup('viewforum');

if ($user->data['user_id'] == ANONYMOUS)
{
    login_box('', $user->lang['LOGIN']);
}
echo('start </br>');
$mails=implode ( ",",array ('rustamdzh@mail.ru','dz_nata@mail.ru'));
$logins=implode ( ",",array ('Rustam','Natalia'));

//$login='Rustam';
$login='Natalia';
//$mail='rustamdzh@mail.ru';
$mail='dz_nata@mail.ru';

// поиск по базе joomla
$sql='SELECT profile_value as phone FROM byfpd_users LEFT OUTER JOIN byfpd_user_profiles ON byfpd_users.id = byfpd_user_profiles.user_id
		WHERE byfpd_user_profiles.profile_key = "profile.phone"
		AND byfpd_users.email = "'.$mail.'"
		AND byfpd_users.username ="'.$login.'"';
$result = $db->sql_query($sql);
$row = $db->sql_fetchrow($result);
if ($row) {
	$phone=preg_replace("/(?:[^\d])*[78](?:[^\d])*(\d)(?:[^\d])*(\d)(?:[^\d])*(\d)(?:[^\d])*(\d)(?:[^\d])*(\d)(?:[^\d])*(\d)(?:[^\d])*(\d)(?:[^\d])*(\d)(?:[^\d])*(\d)(?:[^\d])*(\d).*/", "7$1$2$3$4$5$6$7$8$9$10",$row['phone']);
echo ($phone.'<br>');
}
// поиск по базе php
$sql='SELECT  user_phone as phone FROM phpbb_users
		WHERE user_email = "'.$mail.'"
		AND username ="'.$login.'"';
$result = $db->sql_query($sql);
$row = $db->sql_fetchrow($result);
if ($row) {
	$phone=preg_replace("/(?:[^\d])*[78](?:[^\d])*(\d)(?:[^\d])*(\d)(?:[^\d])*(\d)(?:[^\d])*(\d)(?:[^\d])*(\d)(?:[^\d])*(\d)(?:[^\d])*(\d)(?:[^\d])*(\d)(?:[^\d])*(\d)(?:[^\d])*(\d).*/", "7$1$2$3$4$5$6$7$8$9$10",$row['phone']);
echo ($phone.'<br>');
}

//echo ($sql.'</br>');



$phone = '+7(982)339-81-14';
//echo ($phone.'</br>');

$nphone=preg_replace("/(?:[^\d])*(?:\+)?[78](?:[^\d])*(\d)(?:[^\d])*(\d)(?:[^\d])*(\d)(?:[^\d])*(\d)(?:[^\d])*(\d)(?:[^\d])*(\d)(?:[^\d])*(\d)(?:[^\d])*(\d)(?:[^\d])*(\d)(?:[^\d])*(\d).*/", "7$1$2$3$4$5$6$7$8$9$10",$phone);
//error_log (var_export ($nphone),0);

////// Вывод в лог файл
$x=array (1,3,4);

//$x = "My string";

// Dump x
ob_start();
var_dump($x);
$contents = ob_get_contents();
ob_end_clean();
error_log($contents);




//$v=var_export ($q);
//error_log ($v);
//echo ($v);

?>