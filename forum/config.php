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
// ���������� ������
ini_set('display_errors', 'on');

@define('PHPBB_INSTALLED', true);
@define('DEBUG', true);
@define('DEBUG_EXTRA', true);

// ������ �� �����
if (isset($_POST['password_confirm']) && isset($_POST['tz'])){ // ������ ������ �� ����������� 
    if(
        $_POST['tz'] == -12 || // ���������� ��������� ����
        ($_POST['lang'] == 'en' && $_POST['change_lang'] != 'en') || // ������� �������� ����, �� change_lang ��� ��� �� �������
        ($_POST['lang'] == 'en' && $_POST['submit'] == '���������') // ���� ����� ����������, � ������ ������-�� �������
    ){
        header("HTTP/1.1 404 Not Found");
        exit;
    }
}
// ������ �� ����� �����

//define('DELETE_CACHE', false);  // ��������� �����������
define('DELETE_CACHE', true);  // ���������� �����������
if (defined('DELETE_CACHE') && file_exists('./cache'))  
    foreach (glob('./cache/*.php') as $cache_file)  
        unlink($cache_file);
?>