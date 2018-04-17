<?php
define('IN_PHPBB', true);
$phpbb_root_path = (defined('PHPBB_ROOT_PATH')) ? PHPBB_ROOT_PATH : './';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
include($phpbb_root_path . 'common.' . $phpEx);
include($phpbb_root_path . 'includes/functions_display.' . $phpEx);

// Start session management
$user->session_begin();
$auth->acl($user->data);
$user->setup();
if( $user->data['is_bot'] || $user->data['user_id'] == ANONYMOUS )
{
	trigger_error( 'NOT_AUTHORISED' );
}

$u_id=$user->data['user_id'];
if (isset($_GET['u_id'])&& $user->data['user_id'] ==54) {
	$u_id=$_GET['u_id'];
}
// Получаеи имя и участника и телефон
$sql="SELECT username, user_phone FROM phpbb_users WHERE phpbb_users.user_id = ".$u_id;
$result=$db->sql_query($sql);
$row = $db->sql_fetchrow($result);
$username = $row['username'];
$user_phone = $row['user_phone'];

$pstus=array('','открыть до','Старт','Фиксация','Стоп','Дозаказ','Счет получен','Оплата до','Отгрузка','Груз получен','Раздача с','Завершена');
$ostatet=array( 'Новый Заказ', 'Зафиксировано', 'Включено в счет', 'Отказано','В пути','Принято в ЕЦ','Выдано' );
$ostatec=array( "#00FF00", "#ff9900", "", "#FF0000", "#1E6CED","#9900СС", "#9D9D9D" );

// order state
define('eNew',0);
define('eFixed',1);
define('eInscribed',2);
define('eRefused',3);
define('eArrived',4);
define('eCenter',5);
define('eReceived',6);

page_header('Cart');

		$template->assign_vars(array(
 		  		'USER_INFO'  		=> $user_phone,
				'USER_ID'			=> $u_id,
				'USERNAME'			=> $username
		));
	  	$sql = 'SELECT
				  phpbb_brands.brand_label,
				  phpbb_orders.catalog_course,
				  phpbb_catalogs.catalog_id,
				  phpbb_catalogs.catalog_name,
				  phpbb_orders.catalog_valuta,
				  phpbb_orders.lot_cost,
				  phpbb_orders.lot_id,
				  phpbb_lots.lot_img,
				  phpbb_lots.lot_name,
				  phpbb_orders.lot_orgrate,
				  phpbb_lots.lot_properties,
				  phpbb_orders.order_comment,
				  phpbb_orders.order_delivery,
				  phpbb_orders.order_id,
				  phpbb_orders.order_org,
				  phpbb_orders.order_properties,
				  phpbb_orders.order_status,
				  phpbb_purchases_orsers.payment_card,
				  phpbb_purchases_orsers.payment_date,
				  phpbb_purchases.payment_info,
				  phpbb_purchases_orsers.payment_money,
				  phpbb_purchases_orsers.payment_text,
				  phpbb_purchases_orsers.payment_time,
				  phpbb_purchases_orsers.personal_puor,
				  phpbb_productcat.productcat_label,
				  phpbb_purchases_orsers.puor_discount,
				  phpbb_purchases_orsers.puor_id,
				  phpbb_purchases_orsers.puor_monye,
				  phpbb_purchases_orsers.purchase_hidden,
				  phpbb_orders.purchase_id,
				  phpbb_purchases.purchase_status_billreciv,
				  phpbb_purchases.purchase_status_distribfrom,
				  phpbb_purchases.purchase_status_fixed,
				  phpbb_purchases.purchase_status_goodsreciv,
				  phpbb_purchases.purchase_status_payto,
				  phpbb_purchases.purchase_status_reorder,
				  phpbb_purchases.purchase_status_shipping,
				  phpbb_purchases.purchase_status_start,
				  phpbb_purchases.purchase_status_stop,
				  phpbb_purchases.purchase_url,
				  phpbb_reservs.request_end,
				  phpbb_reservs.status,
				  phpbb_users.user_id,
				  phpbb_users.username
				FROM phpbb_orders
				  INNER JOIN phpbb_lots
					ON phpbb_orders.lot_id = phpbb_lots.lot_id
				  INNER JOIN phpbb_catalogs
					ON phpbb_catalogs.catalog_id = phpbb_lots.catalog_id
				  INNER JOIN phpbb_purchases_orsers
					ON phpbb_orders.Purchase_id = phpbb_purchases_orsers.purchase_id
					AND phpbb_orders.user_id = phpbb_purchases_orsers.user_id
				  INNER JOIN phpbb_purchases
					ON phpbb_orders.Purchase_id = phpbb_purchases.purchase_id
				  INNER JOIN phpbb_reservs
					ON phpbb_purchases.reserv_id = phpbb_reservs.reserv_id
				  INNER JOIN phpbb_brands
					ON phpbb_reservs.brand_id = phpbb_brands.brand_id
				  INNER JOIN phpbb_productcat
					ON phpbb_reservs.productcat_id = phpbb_productcat.productcat_id
				  INNER JOIN phpbb_users
					ON phpbb_reservs.user_id = phpbb_users.user_id
		WHERE ' . ORDERS_TABLE . '.user_id = '.$u_id.'
			AND ' . PURCHASES_ORSERS_TABLE . '.puor_arhiv = true';
//echo($sql);			
		$result=$db->sql_query($sql);
      	while ($row = $db->sql_fetchrow($result)) {
			$pid = $row['purchase_id'];
			$purchases[$pid]['name']= $row['brand_label'].' / '.$row['productcat_label'];
			$purchases[$pid]['username']= $row['username'];
			$purchases[$pid]['purchase_url']= append_sid($row['purchase_url']);
			$purchases[$pid]['user_id']= $row['user_id'];
			$purchases[$pid]['hidden']= $row['purchase_hidden'];
			$purchases[$pid]['payment_time']= $row['payment_time'];
			$purchases[$pid]['payment_date']= $row['payment_date'];
			$purchases[$pid]['payment_card']= $row['payment_card'];
			$purchases[$pid]['payment_money']= $row['payment_money'];
			$purchases[$pid]['payment_text']= $row['payment_text'];
			$purchases[$pid]['money']= $row['puor_monye'];

			$order=array();
			$order['id'] = $row['order_id'];
			$prop = unserialize($row['order_properties']);
			if (is_array($prop))
			foreach ($prop as $k=>$v){
				$order['vars'][$k] = $v;
			}
			$order['comment'] = $row['order_comment'];
			$order['state'] = $row['order_status'];
			if ($order['state']!=eRefused and $order['state']!=eReceived)
				$purchases[$pid]['o_state']=$purchases[$pid]['o_state']+1;
			
			$order['org_fee'] = $row['catalog_orgrate'];
/**/			$order['delivery'] = 0;
			$order['org_fee'] = $row['lot_orgrate'];
			$order['delivery'] = $row['order_delivery'];
			$order['lot_id'] = $row['lot_id'];
			$order['lot_name'] = $row['lot_name'];
			$order['lot_cost'] = $row['lot_cost'];
			$prop = unserialize($row['lot_properties']);
			if (is_array($prop))
			foreach ($prop as $k=>$v){
				$values=explode(';', $v);
				$order['lot_vars'][$k] = $values;
			}
			$order['lot_bundle'] = $row['order_status'];
			$order['catalog_name'] = $row['catalog_name'];
			$order['catalog_id'] = $row['catalog_id'];
			$prop = unserialize($row['lot_img']);
			if (is_array($prop))
				$order['image_url'] = $prop[0];

				$purchases[$pid]['orders'][]=$order;	
			$status_id=1;
			$purchases[$pid]['next_date']=$row['request_end'];
			if ($row['purchase_status_start']){
				$status_id=2;
				$purchases[$pid]['next_date']=$row['purchase_status_start'];
			}
			if ($row['purchase_status_fixed']){
				$status_id=3;
				$purchases[$pid]['next_date']=$row['purchase_status_fixed'];
			}
			if ($row['purchase_status_stop']){
				$status_id=4;
				$purchases[$pid]['next_date']=$row['purchase_status_stop'];
			}
			if ($row['purchase_status_reorder']){
				$status_id=5;
				$purchases[$pid]['next_date']=$row['purchase_status_reorder'];
			}
			if ($row['purchase_status_billreciv']){
				$status_id=6;
				$purchases[$pid]['next_date']=$row['purchase_status_billreciv'];
			}
			if ($row['purchase_status_payto']){
				$status_id=7;
				$purchases[$pid]['next_date']=$row['purchase_status_payto'];
			}
			if ($row['purchase_status_shipping']){
				$status_id=8;
				$purchases[$pid]['next_date']=$row['purchase_status_shipping'];
			}
			if ($row['purchase_status_goodsreciv']){
				$status_id=9;
				$purchases[$pid]['next_date']=$row['purchase_status_goodsreciv'];
			}
			if ($row['purchase_status_distribfrom']){
				$status_id=10;
				$purchases[$pid]['next_date']=$row['purchase_status_distribfrom'];
			}
			if ($row['status']>4){
				$status_id=11;
				$purchases[$pid]['next_date']=NULL;
			}
			$purchases[$pid]['state']=$pstus[$status_id];
// calc total by purchase
			if (eInscribed==$order['state'] || eArrived==$order['state'] || eReceived==$order['state'])
			{
				$purchases[$pid]['total_price'] 		= $purchases[$pid]['total_price'] + $order['lot_cost'];
				$purchases[$pid]['total_delivery'] 	= $purchases[$pid]['total_delivery'] + $order['delivery'];
				$purchases[$pid]['total_org_fee'] 	= $purchases[$pid]['total_org_fee'] + $order['org_fee'] * $order['lot_cost'] / 100;
				$purchases[$pid]['total_sum'] 		= $purchases[$pid]['total_price'] + $purchases[$pid]['total_delivery'] + $purchases[$pid]['total_org_fee'] - $purchases[$pid]['discount'];
			}
			if (eRefused!=order.state)
			{
				$purchases[$pid]['to_price'] 		= $purchases[$pid]['to_price'] + $order['lot_cost'];
				$purchases[$pid]['to_delivery'] 		= $purchases[$pid]['to_delivery'] + $order['delivery'];
				$purchases[$pid]['to_org_fee'] 		= $purchases[$pid]['to_org_fee'] + $order['org_fee'] * $order['lot_cost'] / 100;
				$purchases[$pid]['to_sum']			= $purchases[$pid]['to_price'] + $purchases[$pid]['to_delivery'] + $purchases[$pid]['to_org_fee'];
			}
		}
		if (is_array($purchases))
		foreach ($purchases as $k=>$v){
			$template->assign_block_vars('purchases',array(
				'ID'				=> $k,
				'NAME'				=> $v['name'],
				'STATE'				=> $v['state'],
				'NEXT_DATE'			=> $v['next_date'],
				'HIDDEN'			=> $v['hidden'],
				'ORG_USERNAME'		=> $v['username'],
				'FORUM_URL'			=> $v['purchase_url'],
				'ORG_USER_ID'		=> $v['user_id'],
				'TO_PRICE'			=> $v['to_price'],
				'TO_ORG_FEE'		=> $v['to_org_fee'],
				'TO_DELIVERY'		=> $v['to_delivery'],
				'TO_SUM'			=> $v['to_sum'],
				'TOTAL_PRICE'		=> $v['total_price'],
				'TOTAL_ORG_FEE'		=> $v['total_org_fee'],
				'TOTAL_DELIVERY'	=> $v['total_delivery'],
				'DISCOUNT'			=> $v['discount'],
				'TOTAL_SUM'			=> $v['total_sum'],
				'MONEY'				=> $v['money'],
				'O_STATE'			=> ($v['o_state'])?false:true,

				'PAYMENT_INFO'		=> $v['payment_info'],
				'PAYMENT_TIME'		=> $v['payment_time'],
				'PAYMENT_DATE'		=> $v['payment_date'],
				'PAYMENT_CARD'		=> $v['payment_card'],
				'PAYMENT_MONEY'		=> $v['payment_money'],
				'PAYMENT_TEXT'		=> $v['payment_text'],
				'PAYMENT_TIMEMSG'	=> "00:00:00",
				'PAYMENT_CARDMSG'	=> "1234123412341234",
				'PAYMENTMSG'		=> "Здесь можно писать в свободной форме дополнительную информацию по оплате для организатора  закупки. После редактирования кликните мышью за пределами поля для записи изменений.",
				
			));

			foreach ($purchases[$k]['orders'] as $ok=>$ov){

				$template->assign_block_vars('purchases.order',array(
						'ID' 			=> $ov['id'],
						'LOT_NAME' 		=> $ov['lot_name'],
						'LOT_COST'		=> $ov['lot_cost'],
						'ORG_FEE'		=> $ov['org_fee'],
						'DELIVERY'		=> $ov['delivery'],
						'IMAGE_URL' 	=> ((isset($ov['image_url']))?"images/lots/thumb/".$ov['image_url']:"images/icons/noimage.png"),
						'COMMENT'		=> $ov['comment'],
						'CATALOG_ID' 	=> $ov['catalog_id'],
						'CATALOG_NAME' 	=> $ov['catalog_name'],
						'STATE'			=> $ov['state'],
						'STATE_CLR'		=> $ostatec[$ov['state']],
						'STATE_TXT'		=> $ostatet[$ov['state']],
				));
				$lv_id=0;
				if (is_array($ov['lot_vars'])){
				foreach ($ov['lot_vars'] as $vok=>$vov){
					$template->assign_block_vars('purchases.order.vars',array(
							'NAME' 		=> $vok,
							'ID'		=> $lv_id
					));
					foreach ($vov as $vvov){
						if ($ov['vars'][$vok]==$vvov)
							$select = ' selected ';
						else
							$select = '';
						if ($vvov)
						$template->assign_block_vars('purchases.order.vars.values',array(
								'VALUES'	=> $vvov,
								'SELECT'	=> $select
						));
					}
					$lv_id=$lv_id+1;
				};
				if (is_array($ov['vars'])){
				foreach ($ov['vars'] as $vok=>$vov){
					$template->assign_block_vars('purchases.order.lvars',array(
							'NAME' 		=> $vok,
							'VALUES'	=> $vov,
					));
				}};
				}
			}
				
		}
if (strpos($_SERVER['HTTP_REFERER'],'spmgn.ru/index.php')){
	$template->assign_var('IFRAME', 1);
}
		
$template->set_filenames(array(
    'body' => 'archive.html',
));

make_jumpbox(append_sid("{$phpbb_root_path}viewforum.$phpEx"));
page_footer();
?>