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

//echo (<script type="text/javascript" src="http://gsm-inform.ru/partner_program/info.php"></script>);

$login = 'spmgn.ru';
$pwd = 'JnRoust35105';

$phones = '79823398114';
$msg = urlencode('test !');
$sender='Spmgn.ru';
//$result = file_get_contents("http://smsfox.ru/ru/api/?login=$login&pwd=$pwd&phones=$phones&msg=$msg&sender=$sender");

//if((int)$result == 1) {
//    print 'Message successfully sent!'.$result;
//} else {
//   print 'Fail sent message.'.$result;
//}
echo("111");
$url = "http://www.spmgn.ru/forum/reportsmsfox.php";
$ch = curl_init(); // инициализируем сессию curl
curl_setopt($ch, CURLOPT_URL,$url); // указываем URL, куда отправлять POST-запрос
curl_setopt($ch, CURLOPT_FAILONERROR, 1);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);// разрешаем перенаправление
curl_setopt($ch, CURLOPT_RETURNTRANSFER,1); // указываем, что результат запроса следует передать в переменную, а не вывести на экран
curl_setopt($ch, CURLOPT_TIMEOUT, 3); // таймаут соединения
curl_setopt($ch, CURLOPT_POST, 1); // указываем, что данные надо передать именно методом POST
curl_setopt($ch, CURLOPT_POSTFIELDS,  array("id"=>"123","phone"=>"79823398111","status"=>"01")); // добавляем данные POST-запроса
$result = curl_exec($ch); // выполняем запрос
curl_close($ch); // завершаем сессию
echo $result;

?>