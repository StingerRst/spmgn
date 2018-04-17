<?php
	
$purchase_id = $_POST['purchase_id'];

header("Content-Type: text/html;charset=windows-1251");
header("Pragma: public");
header("Expires: 0");
header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
header("Content-Type: text/csv");
header("Content-Transfer-Encoding: binary");
header('Content-disposition: attachment; filename="report'.$purchase_id.'_' . date("Y-m-d") . '.csv"');

define('IN_PHPBB', true);
$phpbb_root_path = (defined('PHPBB_ROOT_PATH')) ? PHPBB_ROOT_PATH : './';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
include($phpbb_root_path . 'common.' . $phpEx);
$ostatet=array( 'Новый Заказ', 'Зафиксировано', 'Включено в счет', 'Отказано','В пути','Принято в ЕЦ','Выдано' );
$ostatec=array( "#00FF00", "#ff9900", "", "#FF0000", "#1E6CED","#9900СС", "#9D9D9D" );
$purchase_id = $_POST['purchase_id'];
if ($_POST['selected_orders']){
	  	$sql = 'SELECT
				  phpbb_catalogs.catalog_name,
				  phpbb_lots.lot_img,
				  phpbb_lots.lot_name,
				  phpbb_orders.lot_cost,
				  phpbb_orders.lot_orgrate,
				  phpbb_orders.order_comment,
				  phpbb_orders.order_delivery,
				  phpbb_orders.order_id,
				  phpbb_orders.order_org,
				  phpbb_orders.order_properties,
				  phpbb_orders.order_status,
				  phpbb_orders.user_id,
				  phpbb_purchases_orsers.payment_card,
				  phpbb_purchases_orsers.payment_date,
				  phpbb_purchases_orsers.payment_money,
				  phpbb_purchases_orsers.payment_text,
				  phpbb_purchases_orsers.payment_time,
				  phpbb_purchases_orsers.puor_discount,
				  phpbb_purchases_orsers.puor_monye,
				  phpbb_users.user_phone,
				  phpbb_users.username
				FROM phpbb_orders
				  INNER JOIN phpbb_users
					ON phpbb_orders.user_id = phpbb_users.user_id
				  INNER JOIN phpbb_lots
					ON phpbb_orders.lot_id = phpbb_lots.lot_id
				  INNER JOIN phpbb_catalogs
					ON phpbb_lots.catalog_id = phpbb_catalogs.catalog_id
				  INNER JOIN phpbb_purchases_orsers
					ON phpbb_orders.Purchase_id = phpbb_purchases_orsers.purchase_id
					AND phpbb_orders.user_id = phpbb_purchases_orsers.user_id
				WHERE phpbb_orders.Purchase_id = '.$purchase_id.' 
					AND ' . ORDERS_TABLE . '.order_id IN ('.$_POST['selected_orders'].') 
				ORDER BY LCASE('. USERS_TABLE .'.username)';
		//var_dump($sql);
		$result = $db->sql_query($sql);
	while ($row = $db->sql_fetchrow($result)){
			$us_id=$row['user_id'];
			$us[$us_id]['username']= iconv("utf-8", "cp1251",$row['username']);
			$us[$us_id]['userinfo']= $row['user_phone'];
			$us[$us_id]['user_id']= $row['user_id'];
			$us[$us_id]['payment_card']= $row['payment_card'];
			$us[$us_id]['payment_time']= $row['payment_time'];
			$us[$us_id]['payment_date']= $row['payment_date'];
			$us[$us_id]['payment_money']= $row['payment_money'];
			$us[$us_id]['payment_text']= iconv("utf-8", "cp1251",$row['payment_text']);
			$us[$us_id]['money']= $row['puor_monye'];
			$us[$us_id]['discount']= (($row['puor_discount'])?$row['puor_discount']:0);
			$order=array();
			$order['id'] = $row['order_id'];
			$prop = unserialize($row['order_properties']);
			$f=0;
			$order['vars']='';
			if (is_array($prop))
			foreach ($prop as $k=>$v){
				if ($f) $order['vars'].', ';
				$order['vars'].= iconv("utf-8", "cp1251",$k .': '. $v);
				$f=1;
			}
			$order['comment'] = iconv("utf-8", "cp1251",(($row['order_comment'])?($row['order_comment']):'-'));
			$order['state'] = $row['order_status'];
			if ($order['state']!=eRefused and $order['state']!=eReceived)
				$us[$us_id]['o_state']=$us[$us_id]['o_state']+1;
			
			$order['org_fee'] = (($row['order_org']!=0)?$row['order_org']:$row['lot_orgrate']);
			$order['delivery'] = $row['order_delivery'];
			$order['lot_name'] = iconv("utf-8", "cp1251",$row['lot_name']);
			$order['lot_cost'] = $row['lot_cost'];
			$order['catalog_name'] = iconv("utf-8", "cp1251",$row['catalog_name']);
			$prop = unserialize($row['lot_img']);
			if (is_array($prop))
				$order['image_url'] = $prop[0];

			$us[$us_id]['orders'][]=$order;	
			if (!isset($us[$us_id]['to_price'])) 		$us[$us_id]['to_price']=0;
			if (!isset($us[$us_id]['to_delivery'])) 	$us[$us_id]['to_delivery']=0;
			if (!isset($us[$us_id]['to_org_fee'])) 		$us[$us_id]['to_org_fee']=0;
			if (!isset($us[$us_id]['to_sum'])) 			$us[$us_id]['to_sum']=0;
			
			if (eRefused!=order.state)
			{
				$us[$us_id]['to_price'] 		= $us[$us_id]['to_price'] + $order['lot_cost'];
				$us[$us_id]['to_delivery'] 		= $us[$us_id]['to_delivery'] + $order['delivery'];
				$us[$us_id]['to_org_fee'] 		= $us[$us_id]['to_org_fee'] + $order['org_fee'] * $order['lot_cost'] / 100;
				$us[$us_id]['to_sum']			= $us[$us_id]['to_price'] + $us[$us_id]['to_delivery'] + $us[$us_id]['to_org_fee'];
			}
	}
?>
;
<?php
	if (is_array($us))
	foreach ($us as $k=>$v){
	
?>
"<?php echo $v['username'];?>";"<?php echo $v['userinfo'];?>";
"Каталог";"Название";"Парметры";"Цена";"Доставка";"Орг %";"Статус";"Комментарий";
<?php
		foreach($v['orders'] as $ok=>$ov){
?>
"<?php echo $ov['catalog_name'];?>";"<?php echo $ov['lot_name'];?>";"<?php echo $ov['vars'];?>";"<?php echo  str_replace('.',',',$ov['lot_cost']);?>";"<?php echo  str_replace('.',',',$ov['delivery']);?>";"<?php echo  str_replace('.',',',$ov['org_fee']);?>";"<?php echo $ostatet[$ov['state']];?>";"<?php echo $ov['comment'];?>";
<?php
		}
?>
;"Сумма заказа";"Орг взнос";"Скидка";"Доставка";"Итого";"Денег сдано";"Сдача";
;"<?php echo str_replace('.',',',round($v['to_price'],2));?>";"<?php echo str_replace('.',',',round($v['to_org_fee'],2));?>";"<?php echo str_replace('.',',',round($v['discount'],2));?>";"<?php echo str_replace('.',',',round($v['to_delivery'],2));?>";"<?php echo str_replace('.',',',round($v['to_sum'],2));?>";"<?php echo str_replace('.',',',round($v['money'],2));?>";"<?php echo str_replace('.',',',round(($v['money']-$v['to_sum']),2));?>";
;"Карта";"<?php echo $v['payment_card'];?>";
;"Дата";"<?php echo $v['payment_date'];?>";
;"Время";"<?php echo $v['payment_time'];?>";
;"Сумма";"<?php echo $v['payment_money'];?>";
;"Комментарий";"<?php echo $v['payment_text'];?>";
;		
<?php
	}
}else{
?>
;
"не выбраны заказы";
<?php
}
?>