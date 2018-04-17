<?php
//error_reporting(0); //отключает все сообщения об ошибках
//error_reporting(2047);
//error_reporting(8);
// echo phpversion(); 
define('IN_PHPBB', true); // we tell the page that it is going to be using phpBB, this is important.
//$phpbb_root_path = './'; // See phpbb_root_path documentation
$phpbb_root_path = (defined('PHPBB_ROOT_PATH')) ? PHPBB_ROOT_PATH : './';
//echo (dirname(__FILE__));
//echo ($_SERVER['DOCUMENT_ROOT']);
$phpEx = substr(strrchr(__FILE__, '.'), 1); // Set the File extension for page-wide usage.

/*include($phpbb_root_path . 'common.' . $phpEx); // include the common.php file, this is important, especially for database connects.
	$sql = 'SELECT
			  phpbb_reservs.reserv_id,
			  phpbb_lots.lot_img
			FROM phpbb_reservs
			  LEFT OUTER JOIN phpbb_purchases
				ON phpbb_reservs.reserv_id = phpbb_purchases.reserv_id
			  LEFT OUTER JOIN phpbb_catalogs
				ON phpbb_purchases.purchase_id = phpbb_catalogs.purchase_id
			  LEFT OUTER JOIN phpbb_lots
				ON phpbb_catalogs.catalog_id = phpbb_lots.catalog_id
			WHERE phpbb_reservs.reserv_id = 1987';

			$result=$db->sql_query($sql);
	while ($row = $db->sql_fetchrow($result)) {
		$sql2 = "SELECT COUNT(lot_id) AS CountLots FROM phpbb_lots WHERE lot_img ='". $row['lot_img']."'"; // проверяем сколько раз встречается лот
		$result2=$db->sql_query($sql2);
		$row2 = $db->sql_fetchrow($result2);
		if ($row2['CountLots']==1) {  // Если лот встречается 1 раз то удаляем файлы
			$prop = unserialize($row['lot_img']);
				if (is_array($prop)) {
					foreach ($prop as $k=>$v){
						unlink('images/lots/thumb/'.$v);
						unlink('images/lots/'.$v);
						echo('Delete'.$v);
						echo ('<br>');
					}
				}
		}
	} 
	*/
	$a='/var/www/html/spmgn.ru/images/l/thumb';
	echo('<img src="./../images/l/thumb/daddc09e38ef8e924b83b2d1fc245518.jpg">');
	if (unlink('./../images/l/thumb/daddc09e38ef8e924b83b2d1fc245518.jpg'))
		echo(deleted);	
	
?>