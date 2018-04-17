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

if ($user->data['user_id'] == ANONYMOUS)
{
    login_box('', $user->lang['LOGIN']);
}
// определение группы пользователя
$sql = "SELECT " . USER_GROUP_TABLE . ".group_id" 
    . " FROM " . USER_GROUP_TABLE 
    . " WHERE " . USER_GROUP_TABLE . ".user_id=" . $user->data['user_id']." and ". USER_GROUP_TABLE . ".group_id=14";
	$result=$db->sql_query($sql);
	$row = $db->sql_fetchrow($result);

	if (!$row){ // если не Оператор ЕЦ - не пускать
		trigger_error('Доступ только Операторам ЕЦ');
	}	
$output = (!empty($_POST['output'])) ? true : false;
$marked		= $_POST['mark'];
$d=date_parse ( $_GET['d']);
$d2=getdate();
if ($d['year']){
	$date=$d['year'].'-'.$d['month'].'-'.$d['day'];
}
else {
	$date=$d2['year'].'-'.$d2['mon'].'-'.$d2['mday'];
}	

if (sizeof($marked))
{
	//$sql_in = array();
	foreach ($marked as $mark)
	{
		$param = explode("!", $mark);	
		$purchase_id=$param[0];
		$member_id=$param[1];
		// выставляем заказу статус выдано
		$sql ="UPDATE phpbb_orders"
		. " SET order_status =6 , Out_EC_Date = curdate()"
		. " WHERE phpbb_orders.order_status = \"5\" AND phpbb_orders.purchase_id =".$purchase_id." AND phpbb_orders.user_id =".$member_id;
//echo $sql;
		$db->sql_query($sql);
		// если закупка у пользователя не учтена - учитываем
		$sql = 'SELECT user_id, purchase_id FROM phpbb_user_purchases_count WHERE user_id = '.$member_id.' AND purchase_id = '.$purchase_id;
		$result = $db->sql_query($sql);
		$row = $db->sql_fetchrow($result);
		if (!$row) {
			$sql1='INSERT INTO phpbb_user_purchases_count (user_id, purchase_id) VALUES ( '.$member_id.' , ' .$purchase_id. ')';
			$result1 = $db->sql_query($sql1);
		}

		// смотрим количество закупок участника
		$sql1='SELECT  COUNT(purchase_id) AS cnt FROM phpbb_user_purchases_count WHERE user_id = '.$member_id;
		$result1 = $db->sql_query($sql1);
		$row1 = $db->sql_fetchrow($result1);
		$p_cnt=$row1['cnt'];
		if ($p_cnt >2){ // если больше 2 то добавляем участника в узкий круг
			$sql1='SELECT user_id, group_id FROM phpbb_user_group
				   WHERE  group_id = 17 AND user_id = '.$member_id;
			$result1 = $db->sql_query($sql1);
			$row1 = $db->sql_fetchrow($result1);
			if (!$row1) {
				$sql1='INSERT INTO phpbb_user_group (group_id, user_id) VALUES (17,'.$member_id.')';
				$result1 = $db->sql_query($sql1);
			}
		
		}
		$sql1 = 'SELECT
				  phpbb_purchases.reserv_id,
				  phpbb_reservs.user_id
				FROM phpbb_purchases
				  LEFT OUTER JOIN phpbb_reservs
					ON phpbb_purchases.reserv_id = phpbb_reservs.reserv_id
				WHERE phpbb_purchases.purchase_id ='.$purchase_id;
		$result1 = $db->sql_query($sql1);
		$row1 = $db->sql_fetchrow($result1);
			$reserv_id=$row1['reserv_id'];
			$org_id=$row1['user_id'];


		// Проверяем не открыта ли закупка постоянно
		$sql1='SELECT
				  phpbb_reservs.always_open
				FROM phpbb_purchases
				  LEFT OUTER JOIN phpbb_reservs
					ON phpbb_purchases.reserv_id = phpbb_reservs.reserv_id
				WHERE  phpbb_purchases.purchase_id ='.$purchase_id;
		$result1 = $db->sql_query($sql1);
		$row1 = $db->sql_fetchrow($result1);
		$always_open= $row1['always_open'];
		if (!$always_open) { //если  закупка не открыта постоянно проверяем на закрытие
			//проверяем сколько заказов в закупке не выдано и не отказано
			$sql1='SELECT  COUNT( phpbb_orders.order_status) AS cnt
					FROM phpbb_orders
					WHERE phpbb_orders.purchase_id = '.$purchase_id.' AND phpbb_orders.order_status IN (0,1,2,4,5)';
			$result1 = $db->sql_query($sql1);
			$row1 = $db->sql_fetchrow($result1);
			$orders_count= $row1['cnt'];
			if (!$orders_count) { //если в закупке заказов не осталось - закрываем закупку
				$db->sql_query('UPDATE ' . RESERVS_TABLE . ' SET status=6 WHERE reserv_id='.$reserv_id);
				// если закупка не учтена - учитываем
				$sql1='SELECT user_id, reserv_id
						FROM phpbb_org_purchases_count
						WHERE user_id = '.$org_id.' AND reserv_id ='.$reserv_id;
				$result1 = $db->sql_query($sql1);
				$row1 = $db->sql_fetchrow($result1);
				if (!$row1) {
					$db->sql_query('INSERT INTO phpbb_org_purchases_count (user_id, reserv_id) VALUES ('.$org_id.', '.$reserv_id.')');
				}
				
			}
		}			

	}
	unset($param);
	unset($marked);
	unset($mark);
}
// баланс по ЕЦ
$sql = "(SELECT
  SubQuery.user_id,
  SubQuery.username,
  SubQuery.user_realname,
  SubQuery.user_phone,
  SubQuery.org_id,
  SubQuery.org_name,
  SubQuery.purchase_id,
  SubQuery.purchase_name,
  SubQuery.brand_id,
  SubQuery.brand_label,
  SubQuery.nakl_to_ec,
  SubQuery.delivery_to_ec,
  DATE_FORMAT(SubQuery.EC_Date, '%d.%m.%y') AS In_EC_Date,
  SubQuery.Ec_Days AS In_Ec_Days,
  SubQuery.cost_count,
  SubQuery.Dostavka,
  SubQuery.Itogo - phpbb_purchases_orsers.puor_monye - phpbb_purchases_orsers.puor_discount AS delivery,
  SubQuery.deliv AS delivery2,
  0 AS considered,
  0 AS chel,
  0 AS ec,
  0 AS summ,
  SubQuery.Cnt
FROM (SELECT
  phpbb_orders.user_id,
  phpbb_users.username,
  phpbb_users.user_realname,
  phpbb_users.user_phone,
  phpbb_users_1.user_id AS org_id,
  phpbb_users_1.username AS org_name,
  phpbb_orders.Purchase_id,
  phpbb_purchases.purchase_name,
  phpbb_brands.brand_id,
  phpbb_brands.brand_label,
  phpbb_purchases.nakl_to_ec,
  phpbb_purchases.delivery_to_ec,
  MAX(phpbb_orders.In_EC_Date) AS EC_Date,
  TO_DAYS(NOW()) - TO_DAYS(MAX(phpbb_orders.In_EC_Date)) AS Ec_Days,
  COUNT(phpbb_orders.lot_cost) AS cost_count,
  phpbb_users_1.Org_Dostavka AS Dostavka,
  ROUND(SUM(phpbb_orders.order_delivery)) AS deliv,
  ROUND(SUM(phpbb_orders.lot_cost * phpbb_orders.catalog_course / 100 * IF(phpbb_orders.order_org, phpbb_orders.order_org, phpbb_orders.lot_orgrate) + phpbb_orders.lot_cost * phpbb_orders.catalog_course + phpbb_orders.order_delivery)) AS Itogo,
  UserPurchaseCnt.Cnt
FROM phpbb_orders
  INNER JOIN phpbb_users
    ON phpbb_orders.user_id = phpbb_users.user_id
  LEFT OUTER JOIN (SELECT
      SubQuery2.user_id,
      COUNT(SubQuery2.Purchase_id) AS Cnt
    FROM (SELECT
        phpbb_orders.user_id,
        phpbb_orders.Purchase_id
      FROM phpbb_orders
      WHERE phpbb_orders.order_status = 6
      GROUP BY phpbb_orders.user_id,
               phpbb_orders.Purchase_id) SubQuery2
    GROUP BY SubQuery2.user_id) UserPurchaseCnt
    ON phpbb_users.user_id = UserPurchaseCnt.user_id
  INNER JOIN phpbb_purchases
    ON phpbb_orders.Purchase_id = phpbb_purchases.purchase_id
  INNER JOIN phpbb_reservs
    ON phpbb_purchases.reserv_id = phpbb_reservs.reserv_id
  INNER JOIN phpbb_users phpbb_users_1
    ON phpbb_reservs.user_id = phpbb_users_1.user_id
  INNER JOIN phpbb_brands
    ON phpbb_reservs.brand_id = phpbb_brands.brand_id
WHERE phpbb_orders.order_status = 5
AND phpbb_purchases.purchase_name IS NOT NULL
GROUP BY phpbb_orders.user_id,
         phpbb_users.username,
         phpbb_users.user_realname,
         phpbb_users.user_phone,
         phpbb_users_1.user_id,
         phpbb_users.username,
         phpbb_orders.Purchase_id,
         phpbb_purchases.purchase_name,
         phpbb_brands.brand_id,
         phpbb_brands.brand_label,
         phpbb_purchases.nakl_to_ec,
         phpbb_purchases.delivery_to_ec,
         phpbb_users_1.Org_Dostavka,
         UserPurchaseCnt.Cnt) SubQuery
  LEFT OUTER JOIN phpbb_purchases_orsers
    ON SubQuery.user_id = phpbb_purchases_orsers.user_id
    AND SubQuery.purchase_id = phpbb_purchases_orsers.purchase_id)
 UNION (SELECT
  phpbb_kassa.user_id,
  phpbb_users.username,
  phpbb_users.user_realname,
  phpbb_users.user_phone,
  phpbb_kassa.org_id,
  phpbb_users_1.username AS org_name,
  phpbb_kassa.purshase_id,
  phpbb_purchases.purchase_name,
  phpbb_reservs.brand_id,
  phpbb_brands.brand_label,
  phpbb_purchases.nakl_to_ec,
  1 AS delivery_to_ec,
  '' AS In_EC_Date,
  '' AS In_Ec_Days,
  COUNT(phpbb_orders.order_id) AS cost_count,
  phpbb_users_1.Org_Dostavka AS Dostavka,
  phpbb_kassa.dolg AS delivery,
  0 AS delivery2,
  1 AS considered,
  phpbb_kassa.dostavka AS chel,
  phpbb_kassa.ec_summ AS ec,
  phpbb_kassa.summa AS summ,
  SubQuery.Cnt
FROM phpbb_kassa
  LEFT OUTER JOIN phpbb_users
    ON phpbb_kassa.user_id = phpbb_users.user_id
  LEFT OUTER JOIN phpbb_users phpbb_users_1
    ON phpbb_kassa.org_id = phpbb_users_1.user_id
  LEFT OUTER JOIN phpbb_purchases
    ON phpbb_kassa.purshase_id = phpbb_purchases.purchase_id
  LEFT OUTER JOIN phpbb_reservs
    ON phpbb_purchases.reserv_id = phpbb_reservs.reserv_id
  LEFT OUTER JOIN phpbb_brands
    ON phpbb_reservs.brand_id = phpbb_brands.brand_id
  LEFT OUTER JOIN (SELECT
      SubQuery2.user_id,
      COUNT(SubQuery2.Purchase_id) AS Cnt
    FROM (SELECT
        phpbb_orders.user_id,
        phpbb_orders.Purchase_id
      FROM phpbb_orders
      WHERE phpbb_orders.order_status = 6
      GROUP BY phpbb_orders.user_id,
               phpbb_orders.Purchase_id) SubQuery2
    GROUP BY SubQuery2.user_id) SubQuery
    ON phpbb_users.user_id = SubQuery.user_id
  LEFT OUTER JOIN phpbb_orders
    ON phpbb_kassa.purshase_id = phpbb_orders.Purchase_id
    AND phpbb_kassa.user_id = phpbb_orders.user_id
WHERE phpbb_orders.order_status > 5
AND TO_DAYS(FROM_UNIXTIME(phpbb_kassa.data)) = TO_DAYS('".$date."')
GROUP BY phpbb_kassa.user_id,
         phpbb_users.username,
         phpbb_users.user_realname,
         phpbb_users.user_phone,
         phpbb_kassa.org_id,
         phpbb_kassa.purshase_id,
         phpbb_purchases.purchase_name,
         phpbb_reservs.brand_id,
         phpbb_brands.brand_label,
         phpbb_purchases.nakl_to_ec,
         phpbb_kassa.dolg,
         phpbb_kassa.dostavka,
         phpbb_kassa.ec_summ,
         phpbb_kassa.summa,
         phpbb_users_1.Org_Dostavka,
         SubQuery.Cnt)
 ORDER BY UPPER(username), UPPER(purchase_name)";

//echo ('<br><br><br>');
//var_dump ($sql);

	$result=$db->sql_query($sql);

	$i=0;
	$Oldi=1;
	$OldUsername='';
	while ($row = $db->sql_fetchrow($result)) {
		$i++;
		$orders[$i]['username'] = $row['username'];
		if ($row['username'] != $OldUsername) {
			$orders[$i]['NewUser'] = '1';
			if ($i >1 )$orders[$OldI]['UCnt'] = $UCnt;
			$OldI=$i;
			$UCnt=0;
		} else {
			$orders[$i]['NewUser'] = '0';
		}
		$UCnt++;
		$OldUsername = $row['username'];
		$orders[$i]['user_id'] = $row['user_id'];
		$orders[$i]['user_realname'] = $row['user_realname'];
		$orders[$i]['user_phone'] = $row['user_phone'];
		$orders[$i]['org_id'] = $row['org_id'];
		$orders[$i]['org_name'] = $row['org_name'];
		$orders[$i]['purchase_id'] = $row['purchase_id'];
		$orders[$i]['purchase_name'] = $row['purchase_name'];
		$orders[$i]['Brand_Label'] = $row['brand_label'];
		$orders[$i]['cost'] = number_format($row['cost'],0);
		$orders[$i]['cost_count'] = $row['cost_count'];
		$orders[$i]['In_EC_Date'] = $row['In_EC_Date'];
		$orders[$i]['In_EC_Days'] = $row['In_Ec_Days'];
		$orders[$i]['Dostavka'] = number_format( $row['Dostavka'],0);
		//$orders[$i]['delivery'] = ($row['delivery_to_ec']) ? (($row['delivery']) ? ($row['delivery']) :$row['delivery2']) : '';
		$orders[$i]['delivery'] = ($row['delivery_to_ec']) ? (($row['delivery']) ? ($row['delivery']) :'') : '';
		$orders[$i]['considered'] = $row['considered'];  
		$orders[$i]['chel'] = $row['chel'];  
		$orders[$i]['Cnt'] = $row['Cnt'];  
		$orders[$i]['ec'] = $row['ec'];  
		$orders[$i]['summ'] = $row['summ'];  
	}	
	$orders[$OldI]['UCnt'] = $UCnt;
	$Ec_sum =0;
	$Ec_cnt=0;
	foreach($orders as $k=>$v){
	$template->assign_block_vars('balance', array(
		   'USER_ID' => $orders[$k]['user_id'],
           'USERNAME' => $orders[$k]['username'],
           'USER_REALNAME' => $orders[$k]['user_realname'],
           'USER_PHONE' => $orders[$k]['user_phone'],
		   'ORG_ID' => $orders[$k]['org_id'],
		   'ORG_NAME' => $orders[$k]['org_name'],
		   'PURCHASE_ID' => $orders[$k]['purchase_id'],
           'PURCHASE_NAME' => $orders[$k]['purchase_name'],
		   'BRAND_LABEL' => $orders[$k]['Brand_Label'],
           'COST' => number_format($orders[$k]['cost'],0),
           'COST_COUNT' => $orders[$k]['cost_count'],
		   'IN_EC_DATE' => $orders[$k]['In_EC_Date'],
		   'IN_EC_DAYS' => $orders[$k]['In_EC_Days'],
		   'DOLG' =>  $orders[$k]['delivery'],
		   'DELIVERY' =>number_format( $orders[$k]['Dostavka'],0),
		   'NEW_USER' => $orders[$k]['NewUser'],
		   'CONSIDERED' => $orders[$k]['considered'],
		   'CHEL' => $orders[$k]['chel'],
		   'EC' => $orders[$k]['ec'],
		   'SUMM' => $orders[$k]['summ'],
		   'CNT' => ($orders[$k]['Cnt'])?$orders[$k]['Cnt']:0 ,
		   'USER_COUNT' => $orders[$k]['UCnt']
		  ));
	$Ec_sum =$Ec_sum +	($orders[$k]['ec'])? $orders[$k]['ec']:10 ;  
	$Ec_cnt =$Ec_cnt+1;  
	}
	 
$template->assign_var('EC_SUM',$Ec_sum);
$template->assign_var('EC_CNT',$Ec_cnt);
//$template->assign_var('DATE',$date);
$template->assign_var('DATE','"'.$date.'"');
$template->assign_var('DATE_PRINT',date ("d.m.Y", time()));


page_header('Выдача');

$template->set_filenames(array(
	'body' => 'output_ec_body.html',
));

make_jumpbox(append_sid("{$phpbb_root_path}viewforum.$phpEx"));
page_footer();

?>