<?php

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
//echo ($phpbb_root_path . 'common.' . $phpEx); 
// ���������� ���� phpBB.
include($phpbb_root_path . 'common.' . $phpEx);
include($phpbb_root_path . 'smsfox.' . $phpEx);
include($phpbb_root_path . 'includes/functions_display.' . $phpEx);

// ��������� ������������� ������.
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



$url = 'https://api.vk.com/method/market.getComments';
$params = array(
	'owner_id' => "-103187751",    // ���� ����������
	'item_id' =>'145958',
	'count' => '100',   // ��� ����������
	'extended' => '0',   // ��� ����������
	'v' => '5.45',
	'access_token'=>'fc1e28b99a67169217bdbb4c5c97840e7a30df80a9478674a55e0ec4eb7c278896c26f67a501135396b4a'
);


$content = file_get_contents($url, false, stream_context_create(array(
	'http' => array(
		'method'  => 'POST',
		'header'  => 'Content-type: application/x-www-form-urlencoded',
		'content' => http_build_query($params)
	)
)));


$json = json_decode($content, true); //������������ ���������� json
//var_dump($json);

$url =$json["error"]["redirect_uri"];
$params =$json["error"]["request_params"];

$content = file_get_contents($url, false, stream_context_create(array(
	'http' => array(
		'method'  => 'POST',
		'header'  => 'Content-type: application/x-www-form-urlencoded',
		'content' => http_build_query($params)
	)
)));
echo($content);

//$json = json_decode($content, true); //������������ ���������� json
//var_dump($json);

?>
