<?php
ini_set('log_errors', 'On');
ini_set('error_log', '/var/log/httpd/spmgn.ru/php_errors.log');
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

// баланс по ЕЦ
$sql = "SELECT
  SubQuery.username,
  SubQuery.user_realname,
  SubQuery.user_phone,
  SubQuery.purchase_name,
  SubQuery.cost_count,
  SubQuery.cost,
  SubQuery.delivery_to_ec,
  ROUND(SubQuery.Itogo - phpbb_purchases_orsers.puor_monye - phpbb_purchases_orsers.puor_discount) AS delivery,
  SubQuery.Dostavka,
  SubQuery.org_name,
  SubQuery.EC_Date as In_EC_Date,
  SubQuery.brand_label,
  SubQuery.nakl_to_ec
FROM (SELECT
    phpbb_users.username,
    phpbb_users.user_realname,
    phpbb_users.user_phone,
    phpbb_purchases.purchase_name,
    COUNT(phpbb_lots.lot_cost) AS cost_count,
    ROUND(SUM(phpbb_lots.lot_cost)) AS cost,
    phpbb_purchases.delivery_to_ec,
    ROUND(SUM(phpbb_orders.order_delivery)) AS deliv,
    ROUND(SUM(phpbb_lots.lot_cost * phpbb_catalogs.catalog_course / 100 * IF(phpbb_orders.order_org, phpbb_orders.order_org, phpbb_lots.lot_orgrate) + phpbb_lots.lot_cost * phpbb_catalogs.catalog_course + phpbb_orders.order_delivery)) AS Itogo,
    phpbb_users_1.Org_Dostavka AS Dostavka,
    phpbb_users_1.username AS org_name,
    MIN(phpbb_orders.In_EC_Date) AS EC_Date,
    phpbb_brands.brand_label,
    phpbb_purchases.nakl_to_ec,
    phpbb_orders.user_id,
    phpbb_users_1.user_id AS org_id,
    phpbb_catalogs.purchase_id,
    phpbb_brands.brand_id
  FROM phpbb_orders
    LEFT OUTER JOIN phpbb_users
      ON phpbb_orders.user_id = phpbb_users.user_id
    LEFT OUTER JOIN phpbb_lots
      ON phpbb_orders.lot_id = phpbb_lots.lot_id
    LEFT OUTER JOIN phpbb_catalogs
      ON phpbb_lots.catalog_id = phpbb_catalogs.catalog_id
    LEFT OUTER JOIN phpbb_purchases
      ON phpbb_catalogs.purchase_id = phpbb_purchases.purchase_id
    LEFT OUTER JOIN phpbb_reservs
      ON phpbb_purchases.reserv_id = phpbb_reservs.reserv_id
    LEFT OUTER JOIN phpbb_users phpbb_users_1
      ON phpbb_reservs.user_id = phpbb_users_1.user_id
    LEFT OUTER JOIN phpbb_brands
      ON phpbb_reservs.brand_id = phpbb_brands.brand_id
  WHERE phpbb_orders.order_status = 5 AND phpbb_purchases.purchase_name  is NOT NULL
  GROUP BY phpbb_orders.user_id,
           phpbb_users.username,
           phpbb_users.user_phone,
           phpbb_users_1.user_id,
           phpbb_users.username,
           phpbb_catalogs.purchase_id,
           phpbb_purchases.purchase_name,
           phpbb_brands.brand_id,
           phpbb_brands.brand_label,
           phpbb_purchases.nakl_to_ec,
           phpbb_purchases.delivery_to_ec,
           phpbb_users_1.Org_Dostavka) SubQuery
  LEFT OUTER JOIN phpbb_purchases_orsers
    ON SubQuery.user_id = phpbb_purchases_orsers.user_id
    AND SubQuery.purchase_id = phpbb_purchases_orsers.purchase_id
  ORDER BY UPPER(SubQuery.username), UPPER(SubQuery.purchase_name)";

	//echo ($sql);

	$result=$db->sql_query($sql);

	$i=0;
	$Oldi=1;
	$OldUsername='';
	while ($row = $db->sql_fetchrow($result)) {
		$i++;
		$orders[$i]['LastRecord'] = 0;
		$orders[$i]['username'] = $row['username'];
		$orders[$i]['org_name'] = $row['org_name'];
		if ($row['username'] != $OldUsername) {
			$orders[$i]['NewUser'] = '1';
			if ($i >1 ) {
			$orders[$OldI]['UCnt'] = $UCnt;
			$orders[$i-1]['LastRecord'] = 1;
			$orders[$i-1]['LastRecord'] = 1;
			}
			$OldI=$i;
			$UCnt=0;
		} else {
			$orders[$i]['NewUser'] = '0';
		}
		$orders[$i]['user_realname'] = $row['user_realname'];
		$UCnt++;
		$OldUsername = $row['username'];
		$orders[$i]['user_realname'] = $row['user_realname'];
		$orders[$i]['purchase_name'] = $row['purchase_name'];
		$orders[$i]['user_phone'] = $row['user_phone'];
		$orders[$i]['cost'] = number_format($row['cost'],0);
		$orders[$i]['cost_count'] = $row['cost_count'];
		//$orders[$i]['delivery'] = ($row['delivery_to_ec']) ? (($row['delivery']) ? ($row['delivery']) :'? '. $row['delivery2']) : '';
		$orders[$i]['delivery'] = ($row['delivery_to_ec']) ? (($row['delivery']) ? ($row['delivery']) :'') : '';
		$orders[$i]['Dostavka'] = number_format( $row['Dostavka'],0);
		$orders[$i]['nakl_to_ec'] = number_format( $row['nakl_to_ec'],0);
		$orders[$i]['In_EC_Date'] = $row['In_EC_Date'];
		$orders[$i]['Brand_Label'] = $row['brand_label'];
		
//var_dump ($i);
//echo ('<br>');
//var_dump ($i-1);
//echo ('<br>');
//var_dump ($orders[$i]);
//echo ('<br>');

	}	
	$orders[$OldI]['UCnt'] = $UCnt;
	$orders[$i]['LastRecord'] = 1;
	
//	foreach($orders as $k=>$v){
//	echo ('<br>');
//	var_dump ($orders[$k]);
//	echo ('<br>');

//	}
	foreach($orders as $k=>$v){
	$template->assign_block_vars('balance', array(
           'USERNAME' => $orders[$k]['username'],
           'ORG_NAME' => $orders[$k]['org_name'],
           'USER_REALNAME' => $orders[$k]['user_realname'],
           'PURCHASE_NAME' => $orders[$k]['purchase_name'],
           'USER_PHONE' => $orders[$k]['user_phone'],
           'COST' => number_format($orders[$k]['cost'],0),
           'COST_COUNT' => $orders[$k]['cost_count'],
           'DELIVERY' =>  $orders[$k]['delivery'],
		   'DOSTAVKA' =>number_format( $orders[$k]['Dostavka'],0),
		   'NAKL_TO_EC' =>number_format( $orders[$k]['nakl_to_ec'],0),
		   'IN_EC_DATE' => $orders[$k]['In_EC_Date'],
		   'NEW_USER' => $orders[$k]['NewUser'],
		   'LAST_RECORD' => $orders[$k]['LastRecord'],
		   'USER_COUNT' => $orders[$k]['UCnt'],
		   'BRAND_LABEL' => $orders[$k]['Brand_Label']		   
		  ));
	}
//echo ($i);
//var_dump ($orders);

$template->assign_var('DATE_PRINT',date ("d.m.Y", time()));


page_header('Остаток в ЕЦ');


$template->set_filenames(array(
//	'body' => 'balance_ec_body.html',
));

make_jumpbox(append_sid("{$phpbb_root_path}viewforum.$phpEx"));
page_footer();

?>