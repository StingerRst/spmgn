<?php
/*
������ ������ �������� ����� �� ������������ SMS ����� ������ WWW.smsfox.ru
�������� ��������� POST
id - ID ���������
phone - ����� ��������
status - ������ ���������. �������� � ���� ������ � ����� ����� �������� (1, 2, 3 ��� 4):

01 - ��������� ������� ����������;
02 - ������� �� ����������, ������������ ��� �� ������������ ���;
03 - ��������� ���������;
04 - ��������� �� ��������� ����� �� ����� ���� ����������.

*/
// ��������� ���� �������������� ��������,
// ��� ��� ���������� �� �������� �����.
// ��� ������ �� ������ �� ��������.

define('IN_PHPBB', true);

// ������� ����������, ����������
// ���� � ����� �����.
$phpbb_root_path = (defined('PHPBB_ROOT_PATH')) ? PHPBB_ROOT_PATH : './';
 
// ��������� ���������� � ������������ ������.
// ������ .php.
$phpEx = substr(strrchr(__FILE__, '.'), 1);

// ���������� ���� phpBB.
include($phpbb_root_path . 'common.' . $phpEx);
// ���� �� ����� ���� ��� ��������� �� ����� � �������
if ((isset($_REQUEST['id'])) AND (isset($_REQUEST['phone'])) AND (isset($_REQUEST['status']))){
	$sql='INSERT INTO phpbb_sms_reports (sms_id, phone, status, date_key) VALUES ('.$_REQUEST['id'].', '.$_REQUEST['phone'].', '.$_REQUEST['status'].', NOW())';
	$db->sql_query($sql);
	echo ('OK');
}
?>