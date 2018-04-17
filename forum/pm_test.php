<?php
ini_set('log_errors', 'On');
ini_set('error_log', '/var/log/httpd/spmgn.ru/php_errors.log');
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

include($phpbb_root_path . 'includes/functions_privmsgs.' . $phpEx);
include($phpbb_root_path . 'includes/ucp/ucp_pm_compose.' . $phpEx);
include($phpbb_root_path . 'includes/message_parser.' . $phpEx);

// Запускаем инициализацию сессии.
$user->session_begin();
$auth->acl($user->data);
$user->setup('viewforum');
//echo('user_id:'+$user->data['user_id']);
//echo ('<br>');
if ($user->data['user_id'] == ANONYMOUS)
{
    login_box('', $user->lang['LOGIN']);
}
/*
$pm_data = array(
	'msg_id'				=> (int) $msg_id,
	'from_user_id'			=> $user->data['user_id'],
	'from_user_ip'			=> $user->ip,
	'from_username'			=> $user->data['username'],
	'reply_from_root_level'	=> (isset($post['root_level'])) ? (int) $post['root_level'] : 0,
	'reply_from_msg_id'		=> (int) $msg_id,
	'icon_id'				=> (int) $icon_id,
	'enable_sig'			=> (bool) $enable_sig,
	'enable_bbcode'			=> (bool) $enable_bbcode,
	'enable_smilies'		=> (bool) $enable_smilies,
	'enable_urls'			=> (bool) $enable_urls,
	'bbcode_bitfield'		=> $message_parser->bbcode_bitfield,
	'bbcode_uid'			=> $message_parser->bbcode_uid,
	'message'				=> $message_parser->message,
	'attachment_data'		=> $message_parser->attachment_data,
	'filename_data'			=> $message_parser->filename_data,
	'address_list'			=> $address_list
);
*/
$msg_id="0";
$action="post";
$subject="Test urs bbcode 2";
$message = "Тестовое сообщение3 [url=http://spmgn.ru/index.php/informatsiya/pravila]ссылка[/url] ";

$message_parser = new parse_message();
$message_parser->message = utf8_normalize_nfc($message);

// Parse message
$message_parser->parse(true, true, false,false);


$pm_data = array(
	'msg_id'				=> (int) $msg_id,
	'from_user_id'			=> $user->data['user_id'],
	'from_user_ip'			=> $user->ip,
	'from_username'			=> $user->data['username'],
	'reply_from_root_level'	=> (isset($post['root_level'])) ? (int) $post['root_level'] : 0,
	'reply_from_msg_id'		=> (int) $msg_id,
	'icon_id'				=> (int) 0,
	'enable_sig'			=> true,
	'enable_bbcode'			=> true,
	'enable_smilies'		=> true,
	'enable_urls'			=> true,
	'bbcode_bitfield'		=> $message_parser->bbcode_bitfield,
	'bbcode_uid'			=> $message_parser->bbcode_uid,
	'message'				=> $message_parser->message,
	'attachment_data'		=> $message_parser->attachment_data,
	'filename_data'			=> $message_parser->filename_data,
	'address_list'			=> array ('u' => array (6194 => 'to' ) )
);



			
$msg_id = submit_pm($action, $subject, $pm_data);
echo ($msg_id);
echo ('</br>end');
?>