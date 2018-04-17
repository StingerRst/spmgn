<?php
	
	
var_dump( $HTTP_USER_AGENT);

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
if (!$auth->acl_get('a_'))
{
	trigger_error('NO_ADMIN');
}
echo('start <br>');


$client_id = '5342929';
$scope = 'offline,messages,market'
?>
<a href="https://oauth.vk.com/authorize?client_id=<?=$client_id;?>&display=page&redirect_uri=https://oauth.vk.com/blank.html&scope=<?=$scope;?>&response_type=token&v=5.45">Push the button</a>