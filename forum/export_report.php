<?php
	$purchase_id = $_POST['purchase_id'];

header("Content-Type: text/html;charset=windows-1251");
header("Pragma: public");
header("Expires: 0");
header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
header("Content-Type: text/csv");
header("Content-Transfer-Encoding: binary");
header('Content-disposition: attachment; filename="vendor'.$purchase_id.'_' . date("Y-m-d") . '.csv"');
$handle = fopen($filename, 'w');

define('IN_PHPBB', true);
$phpbb_root_path = (defined('PHPBB_ROOT_PATH')) ? PHPBB_ROOT_PATH : './';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
include($phpbb_root_path . 'common.' . $phpEx);

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
	echo('"�������";"�������";"������������";"���������";"�����������";"����";"���-��";"�����"');
	$summa=0;
	while ($row = $db->sql_fetchrow($result)){
		$data[0]=iconv("utf-8", "cp1251",'"'.$row['catalog_name'].'"'); // �������
		$data[1]=iconv("utf-8", "cp1251",'"'.$row['lot_article'].'"'); // �������
		$data[2]=iconv("utf-8", "cp1251",'"'.$row['lot_name'].'"'); // ������������
		//$data[3]=iconv("utf-8", "cp1251",'"'.$row['lot_description'].'"'); // ��������
		$data[4]=iconv("utf-8", "cp1251",'"'.implode(";",unserialize($row['order_properties'])).'"'); // ���������
		$data[5]=iconv("utf-8", "cp1251",'"'.$comment=preg_replace('/\s/', ' ', $row['order_comment']).'"'); // �����������
		$data[6]=iconv("utf-8", "cp1251",'"'.str_replace (".",",",$row['lot_cost']).'"'); // ����
		$data[7]=iconv("utf-8", "cp1251",'"'.$row['lot_count'].'"'); // ���-��
		$data[8]=iconv("utf-8", "cp1251",'"'.str_replace (".",",",$row['lot_cost']*$row['lot_count']).'"'); // �����
		$summa=$summa+$row['lot_cost']*$row['lot_count'];
		//fputcsv($handle,$data,";","\"");
		echo(chr(13).chr(10).implode (';',$data));
	}
	echo (chr(13).chr(10).';;;;;;"�����:";"');
	echo iconv("utf-8", "cp1251",$summa);
	echo ('"');
}
else{
		echo ("�� ������� ������");
}	


	
?>
