<?php
	$purchase_id = $_POST['purchase_id'];

header("Content-Type: text/html;charset=windows-1251");
header("Pragma: public");
header("Expires: 0");
$filename = "vendor".$purchase_id."_" . date("Y-m-d") . ".csv";
$handle = fopen($filename, 'w');


define('IN_PHPBB', true);
$phpbb_root_path = (defined('PHPBB_ROOT_PATH')) ? PHPBB_ROOT_PATH : './';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
include($phpbb_root_path . 'common.' . $phpEx);
	$purchase_id = $_POST['purchase_id'];
$_POST['selected_orders']="675452,675463,675467,675538,675539,675540,675541,675720,675735,675740,675742,675743,675843,676226,676227,676228,676229,676230,676231,676610,676685,676686,676692,676693,
676696,678241,678242,678291,678305,678306,678424,678766,679105,679386,679387,679388,679389,679390,679427,679428,679429,679730,680190,680191,680201,680202,680755,680756,680905,683069,683071,683072,683692,
683780,683794,684275";
if ($_POST['selected_orders']){
	$sql = 'SELECT
			  phpbb_catalogs.catalog_name,
			  phpbb_lots.lot_name,
			  phpbb_lots.lot_description,
			  phpbb_lots.lot_article,
			  phpbb_orders.order_properties,
			  phpbb_orders.order_comment,
			  phpbb_orders.lot_cost,
			  COUNT(phpbb_lots.lot_id) AS lot_count,
			  COUNT(phpbb_lots.lot_id)* phpbb_orders.lot_cost AS lot_summ
			FROM phpbb_orders
			  LEFT OUTER JOIN phpbb_lots
				ON phpbb_orders.lot_id = phpbb_lots.lot_id
			  LEFT OUTER JOIN phpbb_catalogs
				ON phpbb_lots.catalog_id = phpbb_catalogs.catalog_id
			WHERE phpbb_orders.order_id IN ('.$_POST['selected_orders'].')
			GROUP BY phpbb_catalogs.catalog_name,
					 phpbb_lots.lot_name,
					 phpbb_lots.lot_description,
					 phpbb_orders.order_properties,
					 phpbb_orders.order_comment,
					 phpbb_orders.lot_cost
			ORDER BY phpbb_catalogs.catalog_name, phpbb_lots.lot_name, phpbb_orders.order_properties, phpbb_orders.order_comment';
	$result = $db->sql_query($sql);
	//echo ($sql);
	fputcsv($handle,array("Каталог","Артикул","Наименование","Описание","Параметры","Комментарий","Цена","Кол-во","Сумма"),";","\"");
	$summa=0;
	while ($row = $db->sql_fetchrow($result)){
		$data[0]=iconv("utf-8", "cp1251",$row['catalog_name']); // Каталог
		$data[1]=iconv("utf-8", "cp1251",$row['lot_article']); // Артикул
		$data[2]=iconv("utf-8", "cp1251",$row['lot_name']); // Наименование
		$data[3]=iconv("utf-8", "cp1251",$row['phpbb_lots.lot_description']); // Описание
		$data[4]=iconv("utf-8", "cp1251",implode(";",unserialize($row['order_properties']))); // Параметры
		$data[5]=iconv("utf-8", "cp1251",$comment=preg_replace('/\s/', ' ', $row['order_comment'])); // Комментарий
		$data[6]=iconv("utf-8", "cp1251",str_replace (".",",",$row['lot_cost'])); // Цена
		$data[7]=iconv("utf-8", "cp1251",$row['lot_count']); // Кол-во
		$data[8]=iconv("utf-8", "cp1251",str_replace (".",",",$row['lot_cost']*$row['lot_count'])); // Сумма
		$summa=$summa+$row['lot_cost']*$row['lot_count'];

		fputcsv($handle,$data,";","\"");
	}
		fputcsv($handle,array('','','','','','','','Итого:',$summa),";","\"");
}
else{
	fputcsv($handle,"не выбраны заказы",";","\"");
}	
fclose($handle);

header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
header('Content-type: application/csv');
header('Content-Disposition: attachment; filename="'.$filename.'"');

readfile($filename);

unlink($filename);


	
?>
