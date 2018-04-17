<?php

//echo $HTTP_USER_AGENT;
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
//echo ($phpbb_root_path . 'common.' . $phpEx); 
// Подключаем ядро phpBB.
include($phpbb_root_path . 'common.' . $phpEx);

/////////////////////////////////////////////
$purchase_id =3762;
error_log ("!!!!!!!!!!!!!",0);
make_catalogs_for_material($db,$purchase_id);

function make_catalogs_for_material($db,$purchase_id)
{
var_dump ($purchase_id);
	// получаем каталоги по закупке
	$sql='SELECT catalog_id, catalog_name FROM phpbb_catalogs WHERE phpbb_catalogs.catalog_hide = 0 AND purchase_id = '.$purchase_id;
	$result = $db->sql_query($sql);
	$catalogs_links.='';
	while ($row = $db->sql_fetchrow($result)) { // создаем ссылки на каталоги
		$catalogs_links.='<a href="catalog.php?catalog_id='.$row['catalog_id'].'">'.$row['catalog_name'].'</a><br>';
	}
	echo ("</br>"); 
	var_dump ($catalogs_links);
	echo ("</br>"); 
	//получаем id стьати
	$sql = 'SELECT joomla_material_id FROM phpbb_purchases WHERE purchase_id ='.$purchase_id;
	$result = $db->sql_query($sql);
	$row = $db->sql_fetchrow($result);
	$joomla_material_id=$row['joomla_material_id'];
	var_dump ($joomla_material_id);
	echo ("</br>"); 
	
	// Получение содержимого доп полей
	$sql ='SELECT extra_fields FROM byfpd_k2_items WHERE id = '.$joomla_material_id;
	$result = $db->sql_query($sql);
	$row = $db->sql_fetchrow($result);
	$extra_fields= json_decode($row['extra_fields']);
	var_dump ($extra_fields);
	echo ("</br>"); 
	
	// пишем каталоги в доп поле
	$extra_fields[13]->value=$catalogs_links;
	$sql=" UPDATE byfpd_k2_items SET extra_fields='". json_encode($extra_fields,JSON_UNESCAPED_UNICODE)."'";
	$sql.= " WHERE id=". $joomla_material_id;			
	$result = $db->sql_query($sql);

}



////////////////////////////////////////////

//include($phpbb_root_path . 'includes/functions_display.' . $phpEx);
//echo ("test"); 


//var_dump ($a);

//$a=json_decode($a);
//var_dump ($a[12]->value[0]);
//var_dump ($a[5]);
//$a=json_encode($a);

//var_dump ($a);
//echo phpversion() ;
//echo ("</br>"); 
//$a1='"test"';
//var_dump($a1);
//echo ("</br>"); 

			// Получение содержимого доп полей
			$sql ='SELECT extra_fields FROM byfpd_k2_items WHERE id = 231';
			$result = $db->sql_query($sql);
			$row = $db->sql_fetchrow($result);
			$e_input_string=$row['extra_fields'];
echo mb_detect_encoding(e_input_string, "auto");
echo (" </br>"); 
var_dump ($e_input_string);
echo ("</br>"); 
			$extra_fields= json_decode($row['extra_fields']);		
			$extra_fields[12]->value[0]='Тема';
			$extra_fields[12]->value[1]='http://www.spmgn.ru/forum/viewtopic.php?f=70&t=9072';
			$extra_fields[5]->value='заказов';
echo ("</br>"); 
var_dump ($extra_fields);
			$e_output_string = json_encode($extra_fields,JSON_UNESCAPED_UNICODE);
			
echo (" </br>"); 
echo mb_detect_encoding($e_output_string, "auto");
echo (" </br>"); 
var_dump ($e_output_string);
echo ("</br>"); 
			
			$sql="UPDATE byfpd_k2_items SET extra_fields='". $e_output_string."' WHERE id=231";
			//$result = $db->sql_query($sql);
			
//$str='[{"id":"14","value":"part"},{"id":"15","value":"ryad"},{"id":"16","value":"org"},{"id":"18","value":"3"},{"id":"19","value":"4"},{"id":"20","value":"\u041e\u0442\u043a\u0440\u044b\u0442\u0430"},{"id":"21","value":"transport"},{"id":"22","value":"riski"},{"id":"28","value":"black"},{"id":"23","value":"oplata"},{"id":"24","value":"razdaxhi"},{"id":"25","value":"dr uslov"},{"id":"26","value":["\u0422\u0435\u043c\u0430","http:\/\/www.spmgn.ru\/forum\/viewtopic.php?f=70&t=9073","new"]},{"id":"27","value":"13<\/p>"}]';

//$a='[{"id":"14","value":"part"},{"id":"15","value":"ryad"},{"id":"16","value":"org"},{"id":"18","value":"3"},{"id":"19","value":"4"},"Открыта для заказов",{"id":"21","value":"transport"},{"id":"22","value":"riski"},{"id":"28","value":"black"},{"id":"23","value":"oplata"},{"id":"24","value":"razdaxhi"},{"id":"25","value":"dr uslov"},{"id":"26","value":["Тема","http://www.spmgn.ru/forum/viewtopic.php?f=70&t=9072","new"]},{"id":"27","value":"13"}]';
//$q1="Привет";
//echo ($q1);
//echo (" </br>"); 
//echo mb_detect_encoding($q1, "auto");
//echo (" </br>"); 
//$q2=iconv ("UTF-8","ASCII",$q1);
//$q2=mb_convert_encoding($q1, "ASCII","UTF-8");
//$q2 = mb_convert_encoding($q1, 'ASCII', mb_detect_encoding($q1));
//echo (" </br>"); 
//var_dump ($q2); 
//echo (" </br>"); 
//echo mb_detect_encoding($q2, "auto");
//echo (" </br>"); 
//echo (" </br>"); 
//echo (" </br>"); 


//echo ("Кодировка доп. полей </br>"); 
//echo mb_detect_encoding($row['extra_fields'], "auto");
//echo (" </br>"); 
//$ef=$row['extra_fields'];
//var_dump ($ef); 
//echo ("</br>"); 
//$ef2 = mb_convert_encoding($ef, "UTF-8","ASCII");
//$ef2 = iconv ("ASCII","UTF-8",$ef);
//echo ("</br>"); 
//echo mb_detect_encoding($ef2, "auto");
//echo ("</br>"); 
//var_dump ($ef2); 
//echo ("</br>"); 
//$extra_fields= json_decode($ef2);		
//var_dump ($extra_fields);
//echo ("</br>"); 




	
//var_dump (stripcslashes($row['extra_fields']));			
//echo ("</br>"); 
//echo ("распарсеные поля </br>"); 
//var_dump ($extra_fields);			

			// устанавливаем в статье ссылку на тему
			$extra_fields[12]->value[0]='Тема';
			$extra_fields[12]->value[1]='http://www.spmgn.ru/forum/viewtopic.php?f=70&t=9072';
			//$extra_fields[12]->value[2]='New';
			// устанавливаем статус закупки "Открыта для заказов"
			$extra_fields[5]='Открыта для заказов';
			// пишем в табдицу
//echo ("</br>"); 
//echo ("обновленные поля</br>"); 
//var_dump ($extra_fields);			
//echo ("</br>"); 
//echo ("Запакованый Json</br>"); 
//$extra_fields = preg_replace('/\\\u([0-9a-z]{4})/', '&#x$1;', $extra_fields);
//var_dump (json_encode(stripcslashes($row['extra_fields'])));
//var_dump (json_encode($row['extra_fields'],JSON_HEX_QUOT));
//$s1=preg_replace ('"[','[',json_encode($row['extra_fields']));
//$s1=str_replace (array('"[',']"'),array('[',']'),stripcslashes(json_encode($row['extra_fields'])));
//$s1=str_replace (array('"[',']"'),array('[',']'),json_encode($extra_fields));
//var_dump ($s1);
//var_dump (preg_replace_callback('/\\\u([01-9a-fA-F]{4})/', 'prepareUTF8',stripcslashes( json_encode($row['extra_fields'],JSON_FORCE_OBJECT))));
//var_dump(
	//preg_replace_callback('/\\\u([0-9a-fA-F]{4})/',
	//create_function('$match', 'return mb_convert_encoding("&amp;#" . intval($match[1], 16) . ";", "UTF-8", "HTML-ENTITIES");'),
	//json_encode($extra_fields)
	//)
	//);
$s1=preg_replace_callback('/\\\u([0-9a-fA-F]{4})/',
  create_function('$match', 'return mb_convert_encoding("&amp;#" . intval($match[1], 16) . ";", "UTF-8", "HTML-ENTITIES");'),
json_encode($extra_fields)
);
//var_dump($a);
//var_dump (stripslashes(json_encode($row['extra_fields'])));

			$sql="UPDATE byfpd_k2_items SET extra_fields='". $a."'";
			$sql .= " WHERE id=231";
//			error_log (json_encode($extra_fields),0);
//			error_log ($sql);
			//$result = $db->sql_query($sql);

			
			
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