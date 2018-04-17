<?php
error_reporting(E_ALL);
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

function json_encode_($string) {

  $arrayUtf = array('\u0410', '\u0430', '\u0411', '\u0431', '\u0412', '\u0432', '\u0413', '\u0433', '\u0414', '\u0434', '\u0415', '\u0435', '\u0401', '\u0451', '\u0416', '\u0436', '\u0417', '\u0437', '\u0418', '\u0438', '\u0419', '\u0439', '\u041a', '\u043a', '\u041b', '\u043b', '\u041c', '\u043c', '\u041d', '\u043d', '\u041e', '\u043e', '\u041f', '\u043f', '\u0420', '\u0440', '\u0421', '\u0441', '\u0422', '\u0442', '\u0423', '\u0443', '\u0424', '\u0444', '\u0425', '\u0445', '\u0426', '\u0446', '\u0427', '\u0447', '\u0428', '\u0448', '\u0429', '\u0449', '\u042a', '\u044a', '\u042b', '\u044b', '\u042c', '\u044c', '\u042d', '\u044d', '\u042e', '\u044e', '\u042f', '\u044f');

  $arrayCyr = array('А', 'а', 'Б', 'б', 'В', 'в', 'Г', 'г', 'Д', 'д', 'Е', 'е', 'Ё', 'ё', 'Ж', 'ж', 'З', 'з', 'И', 'и', 'Й', 'й', 'К', 'к', 'Л', 'л', 'М', 'м', 'Н', 'н', 'О', 'о', 'П', 'п', 'Р', 'р', 'С', 'с', 'Т', 'т', 'У', 'у', 'Ф', 'ф', 'Х', 'х', 'Ц', 'ц', 'Ч', 'ч', 'Ш', 'ш',  'Щ', 'щ', 'Ъ', 'ъ', 'Ы', 'ы', 'Ь', 'ь', 'Э', 'э', 'Ю', 'ю', 'Я', 'я');

  return str_replace($arrayUtf,$arrayCyr,json_encode($string));
}

//$purchase_id=3435; //id закупки
function update_article($db, $purchase_id) {
echo ($purchase_id);
$rootpath="http://www.spmgn.ru/forum/";
echo ($purchase_id);

// если, в закупке есть тема и есть id материала то обрабатываем
$sql = "SELECT joomla_material_id AS material_id FROM phpbb_purchases WHERE purchase_url <> '...'
		AND joomla_material_id IS NOT NULL AND purchase_id =". $purchase_id;
echo ($sql);

$result = $db->sql_query($sql) ;
echo ("sss");
if ($row = $db->sql_fetchrow($result)) { //id материала
	$material_id=($row['material_id']);
	var_dump ($ItemId);
	//echo ('</br>!!!');
	// Проверяем, актуальна ли занная закупка
	$sql="SELECT MAX(purchase_id) AS Purchaseid FROM phpbb_purchases WHERE purchase_url <> '...' AND joomla_material_id =".$material_id;
	$result = $db->sql_query($sql);
	if ($row = $db->sql_fetchrow($result) AND $row['Purchaseid']==$purchase_id) {
		echo ('Все правильно');
		// Можно обновлять доп поля
		//Получаем доп поля
		$sql="SELECT  purchase_url, purchases_rule1, purchases_rule2, purchases_rule3, purchases_rule4, purchases_rule5,
				purchases_rule6, purchases_rule7, purchases_rule8, purchases_rule9,purchase_name,purchase_description FROM phpbb_purchases
				WHERE purchase_id = ".$purchase_id;
		$result = $db->sql_query($sql) ;
		if ($row = $db->sql_fetchrow($result)) { // Сохраняем путь темы и доп поля закупки
			$themepath= ($rootpath.$row['purchase_url']);
			$rule1= (str_replace("минимальная партия заказа:","",$row['purchases_rule1']));
			$rule2= (str_replace("сбор товара рядами:","",$row['purchases_rule2']));
			$rule3= (str_replace("орг %:","",$row['purchases_rule3']));
			$rule4= (str_replace("транспортные расходы:","",$row['purchases_rule4']));
			$rule5= (str_replace("риски и пересорт, брак:","",$row['purchases_rule5']));
			$rule6= (str_replace("работа с ЧС и СС:","",$row['purchases_rule6']));
			$rule7= (str_replace("форма оплаты штрафы:","",$row['purchases_rule7']));
			$rule8= (str_replace("раздачи:","",$row['purchases_rule8']));
			$rule9= (str_replace("другие условия:","",$row['purchases_rule9']));
			$purchase_name=$row['purchase_name'];
			$purchase_description=$row['purchase_description'];
			
			// Обрабатываем каталоги
			$sql = "SELECT catalog_id, catalog_name , catalog_course,catalog_valuta
					FROM phpbb_catalogs WHERE catalog_hide <> 1 AND purchase_id =".$purchase_id ;
			$result = $db->sql_query($sql) ;
			$catalogs='';
			while ($row = $db->sql_fetchrow($result)) { // Есть каталоги
				//echo ('Есть каталоги');
				$valuta=$row['catalog_valuta'];
				$course=$row['catalog_course'];
				$catalogs.='<a href="'.$rootpath.'catalog.php?catalog_id='.$row['catalog_id'].'" target="_blank">'.$row['catalog_name'].'</a>&nbsp;;';
			}
			// Получаем доп поля материала
			$sql = 'SELECT byfpd_k2_items.extra_fields FROM byfpd_k2_items WHERE byfpd_k2_items.id ='. $material_id;
/*			
			$result = $db->sql_query($sql);
			if ($row = $db->sql_fetchrow($result)) { 
				$extra_fields= json_decode ( $row['extra_fields']);
//	var_dump($extra_fields);		
//	echo ('</br>');
	
				$extra_fields[0]-> value=$rule1;
				$extra_fields[1]-> value=$rule2;
				$extra_fields[2]-> value=$rule3;
				$extra_fields[3]-> value=$valuta;
				$extra_fields[4]-> value=$course;
				$extra_fields[5]-> value='Открыта';
				$extra_fields[6]-> value=$rule4;
				$extra_fields[7]-> value=$rule5;
				$extra_fields[8]-> value=$rule6;
				$extra_fields[9]-> value=$rule7;
				$extra_fields[10]-> value=$rule8;
				$extra_fields[11]-> value=$rule9;
				$extra_fields[12]-> value[0]='Тема';
				$extra_fields[12]-> value[1]=$themepath;
				$extra_fields[12]-> value[2]='new';
				$extra_fields[13]-> value=$catalogs;
				// Пишем доп поля в статью
				$sql="UPDATE byfpd_k2_items SET extra_fields = '".json_encode_($extra_fields)."',title = '".$purchase_name."',`fulltext` ='"
						.$purchase_description.	"' WHERE id =".$material_id;
				echo ($sql);
				echo ('</br>');
				//$db->sql_query($sql);
				
			}
			*/
		}
	}
}
}


update_article($db,21);
echo ('ok');

?>