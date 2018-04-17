<?php
// Тест удаления лота

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
include($phpbb_root_path . 'smsfox.' . $phpEx);
include($phpbb_root_path . 'includes/functions_display.' . $phpEx);

// Запускаем инициализацию сессии.
$user->session_begin();
$auth->acl($user->data);
$user->setup('viewforum');

if ($user->data['user_id'] == ANONYMOUS)
{
    login_box('', $user->lang['LOGIN']);
}
//$lot_id =1112267;

$catalog_id =30586;
//$catalog_id =28961;

$sql='SELECT lot_id FROM phpbb_lots WHERE phpbb_lots.catalog_id ='.$catalog_id;
$result=$db->sql_query($sql);

while ($row = $db->sql_fetchrow($result)) {
	$lot_id =$row['lot_id'];
	//----Удаляем лот---------------------------------------------
		// проверяем, есть ли заказы
		$sql='SELECT order_id FROM phpbb_orders WHERE phpbb_orders.lot_id = '.$lot_id;
		$result=$db->sql_query($sql);
		if ($result->num_rows) { // если есть заказы, то ставим статус "скрытый"
			$sql='UPDATE phpbb_lots SET lot_hidden=1 WHERE phpbb_lots.lot_id = '.$lot_id;
			$result=$db->sql_query($sql);
		}
		else { // Если нет заказов, то можно удалять
			// Получаем список картинок
			$sql='SELECT lot_img FROM phpbb_lots WHERE phpbb_lots.lot_id = '.$lot_id;
			$result=$db->sql_query($sql);
			if ($result->num_rows) { // Есть картинки
				$row = $db->sql_fetchrow($result);
				$imgs=unserialize ($row["lot_img"]); // Получили список картинок -и удалили лот
				$sql='DELETE FROM phpbb_lots WHERE phpbb_lots.lot_id = '.$lot_id;
				$result=$db->sql_query($sql);
				foreach( $imgs as $img ){
					$sql="SELECT lot_id FROM phpbb_lots WHERE lot_img like(\"%".$img."%\")";	
					if (!$result->num_rows) { //картинок нет - можно удалять
						$filename=$phpbb_root_path."images/lots/".$img;
						$thumbname=$phpbb_root_path."images/lots/thumb/".$img;
						if (file_exists($filename)) unlink($filename);
						if (file_exists($thumbname)) unlink($thumbname);
					}
				}
			}
			else {
				$sql='DELETE FROM phpbb_lots WHERE phpbb_lots.lot_id = '.$lot_id;
				$result=$db->sql_query($sql);			
			}	
		}		
	//---------------------------------------------------	
	}
	// Смотрим, остались ли лоты в саталоге
	$sql='SELECT lot_id FROM phpbb_lots WHERE phpbb_lots.catalog_id ='.$catalog_id;
	$result=$db->sql_query($sql);
	if (!$result->num_rows) { //лотов  нет - можно удалять
		$sql='DELETE FROM phpbb_catalogs WHERE phpbb_catalogs.catalog_id = '.$catalog_id;
		$result=$db->sql_query($sql);
	}
	else {
		$sql='UPDATE phpbb_catalogs SET catalog_hide = 1 WHERE catalog_id = '.$catalog_id;
		$result=$db->sql_query($sql);
	}	
?>