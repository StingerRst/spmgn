<?php
// phpBB 3.0.x auto-generated configuration file
// Do not change anything in this file!
$dbms = 'mysqli';
$dbhost = 'localhost';
$dbport = '';
$dbname = 'joomla_spmgn';
$dbuser = 'rustamdzh';
$dbpasswd = 'JnRoust35105';
$table_prefix = 'phpbb_';
$acm_type = 'file';
$load_extensions = '';
// показываем ошибки
ini_set('display_errors', 'on');

@define('PHPBB_INSTALLED', true);
@define('DEBUG', true);
@define('DEBUG_EXTRA', true);

// защита от спама
if (isset($_POST['password_confirm']) && isset($_POST['tz'])){ // Пришел запрос на регистрацию 
    if(
        $_POST['tz'] == -12 || // Нереальная временная зона
        ($_POST['lang'] == 'en' && $_POST['change_lang'] != 'en') || // Изменен основной язык, но change_lang при это не изменен
        ($_POST['lang'] == 'en' && $_POST['submit'] == 'Отправить') // Язык вроде английский, а кнопка почему-то русская
    ){
        header("HTTP/1.1 404 Not Found");
        exit;
    }
}
// защита от спама конец

//define('DELETE_CACHE', false);  // включение кэширования
define('DELETE_CACHE', true);  // отключение кэширования
if (defined('DELETE_CACHE') && file_exists('./cache'))  
    foreach (glob('./cache/*.php') as $cache_file)  
        unlink($cache_file);
?>