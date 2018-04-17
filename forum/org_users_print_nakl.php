<?php
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

$purchase_id = $_POST['purchase_id'];
//var_dump ($purchase_id);
//var_dump ($_POST['selected_orders']);
//var_dump ($_POST['dostavka']);

$date = date_create_from_format('Y-m-j', $_POST['nakl_date']); // Получение даты для накладной

if ($_POST['selected_orders']){
	$sql = 'SELECT
			  phpbb_purchases.purchase_id,
			  phpbb_purchases.purchase_name,
			  phpbb_users.username,
			  phpbb_catalogs.catalog_name,
			  phpbb_lots.lot_name,
			  phpbb_lots.lot_cost,
			  COUNT(phpbb_orders.order_id) AS kolvo,
			  phpbb_orders.order_comment,
			  phpbb_orders.order_delivery,
			  phpbb_orders.order_properties,
			  phpbb_purchases_orsers.puor_id,
			  phpbb_purchases_orsers.puor_discount,
			  phpbb_purchases_orsers.dolg,
			  phpbb_users_1.username AS org_name,
			  phpbb_users_1.user_realname AS org_realname,
			  phpbb_users.user_realname,
			  phpbb_catalogs.catalog_orgrate,
			  phpbb_catalogs.catalog_course,
			  phpbb_lots.lot_orgrate
		FROM phpbb_orders
		  LEFT OUTER JOIN phpbb_users
			ON phpbb_orders.user_id = phpbb_users.user_id
		  LEFT OUTER JOIN phpbb_lots
			ON phpbb_orders.lot_id = phpbb_lots.lot_id
		  LEFT OUTER JOIN phpbb_catalogs
			ON phpbb_lots.catalog_id = phpbb_catalogs.catalog_id
		  LEFT OUTER JOIN phpbb_purchases
			ON phpbb_catalogs.purchase_id = phpbb_purchases.purchase_id
		  LEFT OUTER JOIN phpbb_purchases_orsers
			ON phpbb_orders.user_id = phpbb_purchases_orsers.user_id
			AND phpbb_purchases.purchase_id = phpbb_purchases_orsers.purchase_id
		  LEFT OUTER JOIN phpbb_reservs
			ON phpbb_purchases.reserv_id = phpbb_reservs.reserv_id
		  LEFT OUTER JOIN phpbb_users phpbb_users_1
			ON phpbb_reservs.user_id = phpbb_users_1.user_id
				WHERE phpbb_purchases.purchase_id ='.$purchase_id.' AND phpbb_orders.order_id IN ('.$_POST['selected_orders'].')
		GROUP BY phpbb_purchases.purchase_id,
				 phpbb_purchases.purchase_name,
				 phpbb_users.username,
				 phpbb_catalogs.catalog_name,
				 phpbb_lots.lot_name,
				 phpbb_orders.order_comment,
				 phpbb_orders.order_delivery,
				 phpbb_orders.order_properties,
				 phpbb_purchases_orsers.puor_id,
				 phpbb_purchases_orsers.puor_discount,
				 phpbb_purchases_orsers.dolg,
				 phpbb_users.username,
				 phpbb_users_1.user_realname,
				 phpbb_users_1.user_realname,
				 phpbb_catalogs.catalog_orgrate,
				 phpbb_catalogs.catalog_course,
				 phpbb_lots.lot_orgrate
		ORDER BY UCASE(phpbb_users.username), UCASE(phpbb_catalogs.catalog_name), UCASE(phpbb_lots.lot_name)';
		$result = $db->sql_query($sql);
	//echo ($sql);
	$i=0;
	$Oldi=1;
	$OldUsername='';
	while ($row = $db->sql_fetchrow($result)){
		$i++;
		$orders[$i]['LastRecord'] = 0;
		$orders[$i]['username'] = $row['username'];
		$orders[$i]['user_realname'] = $row['user_realname'];
		if ($row['username'] != $OldUsername) {
			$orders[$i]['NewUser'] = 1;
			$tovarcount=0;
			if ($i >1 ) {
			$orders[$OldI]['UCnt'] = $UCnt;
			$orders[$i-1]['LastRecord'] = 1;
			}
			$OldI=$i;
			$UCnt=0;
		} else {
			$orders[$i]['NewUser'] = 0;
		}
		//$orders[$i]['user_realname'] = $row['user_realname'];


	
		$UCnt++;
		$tovarcount= $tovarcount+(int)($row['kolvo']);
		$OldUsername = $row['username'];
		$orders[$i]['order_comment'] = $row['order_comment'];
		$massiv= unserialize($row['order_properties']);
		$order_properties='';
		foreach($massiv as $key => $value)
		{
		   $order_properties.="<b>".$key.":</b> ". $value. ";";
		} 
		
		$orders[$i]['order_properties'] = $order_properties;
		$orders[$i]['lot_name'] = $row['lot_name'];
		$orders[$i]['catalog_name'] = $row['catalog_name'];
		$cost = ($row['lot_cost'])?$row['lot_cost']:0;
		$orgrate = ($row['lot_orgrate'])?$row['lot_orgrate']:$row['catalog_orgrate'];
		$course = ($row['catalog_course'])?$row['catalog_course']:1;
		//echo ($row['lot_cost']);
		//echo ('</br>');
		$price=($cost/100*$orgrate+$cost)*$course;
		$orders[$i]['lot_cost'] = round(($cost/100*$orgrate+$cost)*$course*$row['kolvo'],0);
		$orders[$i]['dostavka'] = ($_POST['dostavka'])?$row['order_delivery']:0;
//		echo ($orders[$i]['dostavka']);
		$orders[$i]['puor_discount'] = number_format($row['puor_discount'],0);
		$orders[$i]['kolvo'] = number_format($row['kolvo'],0);
		$orders[$i]['TovarCount']=$tovarcount;
		$orders[$i]['puor_id']=$row['puor_id'];
		
		$purchase_name=$row['purchase_name'];
		$purchase_id=$row['purchase_id'];
		$org_name=$row['org_name'];
		$org_realname=$row['org_realname'];
		
//var_dump ($orders[$i]['catalog_name']);
//echo ('<br>');

	}	
	$orders[$OldI]['UCnt'] = $UCnt;
	$orders[$i]['LastRecord'] = '1';
//echo ($i);
//echo ('<br>');	
//var_dump($purchase_name);
//var_dump($orders);
	$template->assign_var('PURCHASE_NAME', $purchase_name);
	$template->assign_var('PURCHASE_ID', $purchase_id);
	$template->assign_var('ORG_NAME', $org_name);
	$template->assign_var('ORG_REALNAME', $org_realname);
	$template->assign_var('DATE', date_format($date, 'd.m.Y'));
	
	$n_pp=0;
	$summa =0;
	foreach($orders as $k=>$v){
	if ($orders[$k]['NewUser']) {
		$n_pp=0;
		$summa=0;
		$dostavka=0;
	}	
	++$n_pp;
	$summa= $summa + round ($orders[$k]['lot_cost']);
	$dostavka= $dostavka + Round($orders[$k]['dostavka']);
	$template->assign_block_vars('order', array(
		   'USERNAME' => $orders[$k]['username'],
           'USER_REALNAME' => $orders[$k]['user_realname'],
           'ORDER_COMMENT' => $orders[$k]['order_comment'],
           'ORDER_PROPERTIES' =>$orders[$k]['order_properties'],
		   'CATALOG_NAME' => $orders[$k]['catalog_name'],
		   'LOT_NAME' => $orders[$k]['lot_name'],
           'LOT_COST' => $orders[$k]['lot_cost'],
           'DISKOUNT' => round(number_format($orders[$k]['puor_discount'],0)),
           'UPKOUNT' => round(number_format($orders[$k]['puor_discount'] * (-1),0)),
           'KOLVO' => number_format($orders[$k]['kolvo'],0),
		   'NEW_USER' => $orders[$k]['NewUser'],
		   'LAST_RECORD' => $orders[$k]['LastRecord'],
		   'LOT_COUNT' => $orders[$k]['UCnt'],
		   'TOVAR_COUNT' => $orders[$k]['TovarCount'],
		   'PURCHASE_ID' => $orders[$k]['purchase_id'],
		   'DOSTAVKA' => $dostavka,
		   'PUOR_ID' => $orders[$k]['puor_id'],
		   'N_PP' => $n_pp,
		   'SUMMA' => $summa+$dostavka-round($orders[$k]['puor_discount']),
	   ));
	}

}

$template->set_filenames(array(
	'body' => 'org_users_print_nakl_body.html') // template file name -- See Templates Documentation
);	
make_jumpbox(append_sid("{$phpbb_root_path}viewforum.$phpEx"));
page_footer();
?>