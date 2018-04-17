<?php

echo ('
 <head>
  <style>
	#table { display: table; border-collapse: collapse; sursor:wait;}
	.row {display: table-row;}
	.cell {display: table-cell;border: 1px #cfcf95 solid;padding-left: 10px; padding-right: 20px;}
	.header {border: 2px #cfcf95 solid;}
  </style>
 </head> ');

// Указываем всем подключающимся скриптам,
// что они вызывается из главного файла.
// Для защиты от вызова их напрямую.
define('IN_PHPBB', true);

// Создаем переменную, содержащую
// путь к корню сайта.
$phpbb_root_path = (defined('PHPBB_ROOT_PATH')) ? PHPBB_ROOT_PATH : './';
 
 //var_dump ($_POST['date']);
 
// Указываем расширение к подключаемым файлам.
// Обычно .php.
$phpEx = substr(strrchr(__FILE__, '.'), 1);
// Подключаем ядро phpBB.
include($phpbb_root_path . 'common.' . $phpEx);
include($phpbb_root_path . 'includes/functions_display.' . $phpEx);
$sql="SELECT
		  phpbb_users.username,
		  SUM(phpbb_kassa.dolg) AS summa
		FROM phpbb_kassa
		  LEFT OUTER JOIN phpbb_users
			ON phpbb_kassa.org_id = phpbb_users.user_id
		WHERE phpbb_kassa.dolg !=0 AND TO_DAYS(FROM_UNIXTIME(phpbb_kassa.data)) = TO_DAYS('".$_POST['date']."') 
		GROUP BY phpbb_users.username
		ORDER BY phpbb_users.username";
//var_dump($sql);		
	$result=$db->sql_query($sql);
	$summ=0;
	echo ('<div id = "table">');
	echo ('<div class = "row header">');
		echo ('<div class = "cell">');
			echo('Организатор');
		echo ('</div>');
		echo ('<div class = "cell">');
			echo('Сумма');
		echo ('</div>');
	echo ('</div>');
	
	while ($row = $db->sql_fetchrow($result)) {
		echo ('<div class = "row">');
			echo ('<div class = "cell">');
				echo ($row['username']);
			echo ('</div>');
			echo ('<div class = "cell">');
				echo ($row['summa'].' р.');
				$summ +=$row['summa'];
			echo ('</div>');
		echo ('</div>');

	}
	echo ('<div class = "row header">');
		echo ('<div class = "cell">');
			echo('Итого:');
		echo ('</div>');
		echo ('<div class = "cell">');
			echo($summ.' р.');
		echo ('</div>');
	echo ('</div>');
	echo ('</div>');

	echo ('</br>');

	// Включаем код для отладки и определяем объект
//require_once("PHPDebug.php");
//$debug = new PHPDebug();
// Простое сообщение на консоль
//$debug->debug("test Console");

# скрипт выполняется, переменные создаются
//$a = array('name'=>'PHP дебаггер console_log()', 'txt'=>'Пример использования');
//$b = isset($a); # т.е. TRUE
//$c = time();
# а теперь узнаем, как у нас дела
//console_log('$a',$a,'$b',$b,'А это $c',$c);

?>