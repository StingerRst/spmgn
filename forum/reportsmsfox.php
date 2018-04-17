<?php
/*
Данный сервис получает отчет об отправленных SMS через сервис WWW.smsfox.ru
Получает параметры POST
id - ID сообщения
phone - Номер телефона
status - Статус сообщения. Параметр в виде строки и может иметь значение (1, 2, 3 или 4):

01 - Сообщение успешно доставлено;
02 - Абонент не существует, заблокирован или не поддерживает СМС;
03 - Сообщение запрещено;
04 - Сообщение на указанный номер не может быть доставлено.

*/
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
// если на входе есть все параметры то пишем а таблицу
if ((isset($_REQUEST['id'])) AND (isset($_REQUEST['phone'])) AND (isset($_REQUEST['status']))){
	$sql='INSERT INTO phpbb_sms_reports (sms_id, phone, status, date_key) VALUES ('.$_REQUEST['id'].', '.$_REQUEST['phone'].', '.$_REQUEST['status'].', NOW())';
	$db->sql_query($sql);
	echo ('OK');
}
?>