<?php
	$purchase_id = $_POST['purchase_id'];

header("Content-Type: text/html;charset=windows-1251");
header("Pragma: public");
header("Expires: 0");
header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
header("Content-Type: text/csv");
header("Content-Transfer-Encoding: binary");
header('Content-disposition: attachment; filename="vendor'.$purchase_id.'_' . date("Y-m-d") . '.csv"');

define('IN_PHPBB', true);
$phpbb_root_path = (defined('PHPBB_ROOT_PATH')) ? PHPBB_ROOT_PATH : './';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
include($phpbb_root_path . 'common.' . $phpEx);
	$purchase_id = $_POST['purchase_id'];

if ($_POST['selected_orders']){
	$sql = 'SELECT ' . CATALOGS_TABLE . '.catalog_name,
			' . CATALOGS_TABLE . '.catalog_id
			FROM '.PURCHASES_TABLE.'
			JOIN ' . CATALOGS_TABLE . ' ON ' . CATALOGS_TABLE . '. purchase_id = ' . PURCHASES_TABLE . '. purchase_id
			JOIN ' . LOTS_TABLE . ' ON ' . CATALOGS_TABLE . '. catalog_id = ' . LOTS_TABLE . '. catalog_id
			JOIN ' . ORDERS_TABLE . ' ON ' . ORDERS_TABLE . '.lot_id = ' . LOTS_TABLE . '.lot_id 
			WHERE '.PURCHASES_TABLE.'.purchase_id = '.$purchase_id.'
				AND ' . ORDERS_TABLE . '.order_id IN ('.$_POST['selected_orders'].')
			GROUP BY catalog_id
			';
				$result = $db->sql_query($sql);
?>
;
<?php
	while ($row = $db->sql_fetchrow($result)){

	?>
"<?php echo iconv("utf-8", "cp1251",$row['catalog_name']);?>";
"Название";"Парметры";"Цена";"Комментарий";
<?php
		$sql = 'SELECT *
				FROM '.LOTS_TABLE.'
				JOIN ' . ORDERS_TABLE . ' ON ' . ORDERS_TABLE . '.lot_id = ' . LOTS_TABLE . '.lot_id 
				WHERE catalog_id = '.$row['catalog_id'].'
					AND ' . ORDERS_TABLE . '.order_id IN ('.$_POST['selected_orders'].')
				ORDER BY ' . LOTS_TABLE . '.lot_id ';

			$result2 = $db->sql_query($sql);
		$sum=0;
		while ($row2 = $db->sql_fetchrow($result2)){
			$sum+=$row2['lot_cost'];
			$prop = unserialize($row2['order_properties']);
			$f=0;
			$var='';
			if (is_array($prop))
			foreach ($prop as $k=>$v){
				if ($f) $var.=', ';
				$var.=$k.': '.$v;
				$f=1;
			}

		
?>
"<?php echo iconv("utf-8", "cp1251",$row2['lot_name']);?>";"<?php echo iconv("utf-8", "cp1251",$var);?>";"<?php echo $row2['lot_cost'];?>";"<?php echo iconv("utf-8", "cp1251",$row2['order_comment']);?>";
<?php
		}
?>
;"Итого: ";"<?php echo $sum;?>";
;		
<?php
	}
}else{
?>
;"не выбраны заказы";
<?php
}
?>