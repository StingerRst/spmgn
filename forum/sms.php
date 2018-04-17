<?php

define('IN_PHPBB', true);
$phpbb_root_path = (defined('PHPBB_ROOT_PATH')) ? PHPBB_ROOT_PATH : './';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
include($phpbb_root_path . 'common.' . $phpEx);
include($phpbb_root_path . 'includes/functions_sms.' . $phpEx);

// Start session management
$user->session_begin();
$auth->acl($user->data);
$user->setup();

$mode		= request_var('mode', '');
$phone		= request_var('phone', '');
$acode		= request_var('acode', '');

if (!in_array($mode, array('activate', 'send')))
{
	trigger_error('NO_MODE');
}

header("Content-type: text/xml");
header("Cache-Control: no-store, no-cache, must-revalidate"); 
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: -1");
echo("<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n");
switch ($mode)
{
	case 'activate':
		$result = sms::activate((int)$user->data['user_id'], $acode);
		
		if ($result === true) {
			die('<activate result="1" />');
		} else if ($result === false) {
			die('<activate result="0" />');
		} else {
			die('<activate result="' . $result . '" />');
		}
		
		break;
		
	case 'send':
		if (utf8_strlen(htmlspecialchars_decode($phone)) >= 10 && utf8_strlen(htmlspecialchars_decode($phone)) <= 15 && preg_match('#^\+[0-9]+$#i', $phone)) {
			$result = sms::initActivation((int)$user->data['user_id'], $phone);
		} else {
			$result = -7;
		}
		
		if ($result === true) {
			die('<send result="1" />');
		} else if ($result === false) {
			die('<send result="0" />');
		} else {
			die('<send result="' . $result . '" />');
		}
		
		break;
}
?>