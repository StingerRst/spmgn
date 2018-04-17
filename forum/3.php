<?php
//echo ('1.php');
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
//$a= '\u043d\u0435\u0442'; 

//echo (utf8_decode($a));
//echo ('</br>'); 


$sql = "SELECT   byfpd_k2_items.extra_fields FROM byfpd_k2_items WHERE byfpd_k2_items.id = 134";
$json = '[{"id":"14","value":"1 \u0440."},{"id":"15","value":"\u043d\u0435\u0442"},{"id":"16","value":"15 %"},{"id":"18","value":"\u0420\u0423\u0411"},{"id":"19","value":"1"},{"id":"20","value":"\u041e\u0442\u043a\u0440\u044b\u0442\u0430"},{"id":"21","value":"\u043f\u043e\u043f\u043e\u043b\u0430\u043c"},{"id":"22","value":"\u0440\u0430\u0431\u043e\u0442\u0430\u0435\u043c"},{"id":"28","value":"100 % \u043f\u0440\u0435\u0434\u043e\u043f\u043b\u0430\u0442\u0430"},{"id":"23","value":"20%"},{"id":"24","value":"\u0432 \u0415\u0426 10 \u0440\u0443\u0431\u043b\u0435\u0439."},{"id":"25","value":"\u0440\u0430\u0437\u043d\u044b\u0435 \u0434\u0440\u0443\u0433\u0438\u0435 \u0443\u0441\u043b\u043e\u0432\u0438\u044f"},{"id":"26","value":["\u0422\u0435\u043c\u0430","http:\/\/www.spmgn.ru\/forum\/viewtopic.php?f=34&t=1009","new"]},{"id":"27","value":"<p><a href=\"http:\/\/www.spmgn.ru\/forum\/catalog.php?catalog_id=13856\" target=\"_blank\">\u0414\u0435\u0442\u0441\u043a\u0438\u0435 \u0440\u044e\u043a\u0437\u0430\u0447\u043a\u0438 Skip Hop Cartoon Animal<\/a>&nbsp;;&nbsp;<a href=\"http:\/\/www.spmgn.ru\/forum\/catalog.php?catalog_id=13857\" target=\"_blank\">\u0414\u0435\u0442\u0441\u043a\u0438\u0439 \u0440\u044e\u043a Skip Hop Zoo Pack<\/a>&nbsp;;&nbsp;<a href=\"http:\/\/www.spmgn.ru\/forum\/catalog.php?catalog_id=13858\" target=\"_blank\">\u0420\u044e\u043a\u0437\u0430\u0447\u043a\u0438 CaoMaoRen<\/a>"}]';
//$json = '{"id":"14","value":"1 \u0440."},{"id":"15","value":"\u043d\u0435\u0442"},{"id":"16","value":"15 %"},{"id":"18","value":"\u0420\u0423\u0411"},{"id":"19","value":"1"},{"id":"20","value":"\u041e\u0442\u043a\u0440\u044b\u0442\u0430"},{"id":"21","value":"\u043f\u043e\u043f\u043e\u043b\u0430\u043c"},{"id":"22","value":"\u0440\u0430\u0431\u043e\u0442\u0430\u0435\u043c"},{"id":"28","value":"100 % \u043f\u0440\u0435\u0434\u043e\u043f\u043b\u0430\u0442\u0430"},{"id":"23","value":"20%"},{"id":"24","value":"\u0432 \u0415\u0426 10 \u0440\u0443\u0431\u043b\u0435\u0439."},{"id":"25","value":"\u0440\u0430\u0437\u043d\u044b\u0435 \u0434\u0440\u0443\u0433\u0438\u0435 \u0443\u0441\u043b\u043e\u0432\u0438\u044f"},{"id":"26","value":["\u0422\u0435\u043c\u0430","http:\/\/www.spmgn.ru\/forum\/viewtopic.php?f=34&t=1009","new"]},{"id":"27","value":"<p><a href=\"http:\/\/www.spmgn.ru\/forum\/catalog.php?catalog_id=13856\" target=\"_blank\">\u0414\u0435\u0442\u0441\u043a\u0438\u0435 \u0440\u044e\u043a\u0437\u0430\u0447\u043a\u0438 Skip Hop Cartoon Animal<\/a>&nbsp;;&nbsp;<a href=\"http:\/\/www.spmgn.ru\/forum\/catalog.php?catalog_id=13857\" target=\"_blank\">\u0414\u0435\u0442\u0441\u043a\u0438\u0439 \u0440\u044e\u043a\u0437\u0430\u043a Skip Hop Zoo Pack<\/a>&nbsp;;&nbsp;<a href=\"http:\/\/www.spmgn.ru\/forum\/catalog.php?catalog_id=13858\" target=\"_blank\">\u0420\u044e\u043a\u0437\u0430\u0447\u043a\u0438 CaoMaoRen<\/a><\/p>"}';

//foreach(json_decode($json) as $key=>$value){
	//echo ($key);
	//echo (':&nbsp'); 
//		foreach($value as $key2=>$value2){
			//echo ($key2);
			
			//echo (':&nbsp'); 
//				var_dump ($value2);
//				echo ($value2);
		//}
	//echo ('</br>'); 
	
//}
$as=json_decode($json);
var_dump($json);
echo ('</br>'); 
echo ($as[13]-> value);
$as[13]-> value = 'ссылка';
echo ('</br>'); 
$json= json_encode($as);
var_dump($json);


//var_dump(json_decode($json));
echo ("</br>"); 
var_dump ($as[12]->value[0]);
var_dump ($as[12]->value[1]);
var_dump ($as[12]->value[2]);

//var_dump(json_decode($json, true));

//echo ($sql);
//$result=$db->sql_query($sql);
//while ($row = $db->sql_fetchrow($result)){

	//var_dump (json_decode($row));
	
//}

echo ("</br>"); 
echo ("test"); 
//echo ("</br>"); 




?>