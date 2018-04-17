<?php
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


// Запускаем инициализацию сессии.
$user->session_begin();
$auth->acl($user->data);
$user->setup('viewforum');
		$org = 'Natalia';
		$sql = "SELECT
				  phpbb_users.username
				FROM phpbb_users
				WHERE phpbb_users.username IN ('~svetoch-ka~', 'abrikosik', 'Alenka', 'Alterance', 'Breathe', 'bsi', 'chuchkova', 'Dakiny', 'elena010101', 'elkamax', '-fialka-', 'finna', 'glushkova', 'halloween', 'Ireshka', 'irina_033', 'lecola', 'legendaria', 'Lenysenda', 'Lesa', 'Ludmila74', 'N@to4ka', 'nata800', 'Natalia', 'OlgaAn', 'olucha', 'OVOLYPOD', 'Rutina', 'samuraj', 'smggal63', 'tmaya', 'username', 'valentinka74rus', 'venerek', 'Veravto', 'VVnn4444', 'Аквамарин', 'Анфиса', 'ВАДИ', 'Галюня', 'Дарья', 'Евгеша', 'Елена68', 'Еленка-мама', 'Каренина', 'колесник', 'КСЮ', 'Марийка', 'Маринок', 'Марья', 'НадеждаSNM', 'Натали76', 'окся', 'Ольга Серкова', 'Регина', 'татка', 'Шустик', 'Эля-Илья', 'Юльчик74')
				ORDER BY phpbb_users.username";		
		
		$result = $db->sql_query($sql);
//echo ($sql);		


	$i=0;
	$users = '';
	while ($row = $db->sql_fetchrow($result)){
		$i++;
		$usr=str_replace(" ","<br>",$row['username']);
		if ($i>1) $users.=',';
		$users.= '
			'.$i.':{
			"username":"'.$usr.'",
			"brand_label":"Розы"}';
	}
	$users='{'.$users.'}';

	//var_dump ($users);
	$template->assign_var('USERS', $users);
	$template->assign_var('ORG', 'Natalia');


$template->set_filenames(array(
	'body' => 'thermo.html') // template file name -- See Templates Documentation
);	
make_jumpbox(append_sid("{$phpbb_root_path}viewforum.$phpEx"));
page_footer();
?>