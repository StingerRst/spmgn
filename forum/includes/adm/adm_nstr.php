<?php
if (!$auth->acl_get('a_'))
{
	trigger_error('NO_ADMIN');
}
if (isset($_POST['cmdn'])){
	$reservs_info 		= (request_var('reservs_info','',true));
	$reservs_rules_url 	= (request_var('reservs_rules_url','',true));
	$reservs_rules 		= (request_var('reservs_rules','',true));
	$news 				= (request_var('news','',true));
	$f_rate 			= (int)request_var('f_rate',0);
	$sql = 'UPDATE ' . INFO_TABLE . 
		" SET reservs_info='$reservs_info', reservs_rules_url='$reservs_rules_url', reservs_rules='$reservs_rules', news='$news', f_rate=$f_rate
		WHERE info_id=1";
	$db->sql_query($sql);
	$template->assign_vars (array(
		'MESSAGE_TITLE'	=> 'Настройки сохранены',
		'MESSAGE_TEXT'	=> 'Настройки успешно сохранены.<br /><br /><a href="./adm.php?i=2">Назад</a>',
	));
	$template->set_filenames(array(
		'body' => 'message_body.html'));
}else{
	  	$sql = 'SELECT *
				FROM ' . INFO_TABLE;
		$result=$db->sql_query($sql);
      	$row = $db->sql_fetchrow($result);
		$template->assign_vars(array(
				'RESERVS_INFO'		=> $row['reservs_info'],
				'RULES_URL'			=> $row['reservs_rules_url'],
				'RULES'				=> $row['reservs_rules'],
				'NEWS'				=> $row['news'],
				'F_RATE'			=> $row['f_rate'],
		));
		
$template->set_filenames(array(
	'body' => 'adm_nstr.html') // template file name -- See Templates Documentation
);
}
// Finish the script, display the page
//page_footer();
?>