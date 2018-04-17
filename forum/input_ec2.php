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

// выбор закупок, отправленных в ЕЦ
$sql = "SELECT
		  phpbb_orders.purchase_id,
		  CONCAT(phpbb_users.username, \" -\", phpbb_purchases.purchase_name) AS purchase_name
		FROM phpbb_purchases
		  INNER JOIN phpbb_reservs
			ON phpbb_purchases.reserv_id = phpbb_reservs.reserv_id
		  INNER JOIN phpbb_users
			ON phpbb_reservs.user_id = phpbb_users.user_id
		  INNER JOIN phpbb_orders
			ON phpbb_orders.purchase_id = phpbb_purchases.purchase_id
		WHERE phpbb_orders.order_status = 4

		GROUP BY phpbb_orders.purchase_id,
				 2
		ORDER BY purchase_name";
//echo ('<br/>');
//echo ($sql);
//echo ('<br/>');
	
	$result=$db->sql_query($sql);
	$purchases_count=0;
	while ($row = $db->sql_fetchrow($result)) {
	 $purchases[$row['purchase_id']]= $row['purchase_name'];
	 $template->assign_block_vars('purchases', array(
           'ID' => $row['purchase_id'],
           'NAME' => $row['purchase_name']
		 ));
		$purchases_count++;
	}
	 
	 
$purchase = $_POST['PurchaseSelect']; //выбраная закупка
//echo ($purchase);
//var_dump($_POST['PurchaseSelect']);
// если выбрана закупка - получаем список заказов
if ( $purchase >0)
{
$sql ="SELECT
		  phpbb_orders.purchase_id,
		  phpbb_purchases.purchase_name,
		  phpbb_purchases.purchase_url,
		  phpbb_orders.lot_cost,
		  phpbb_orders.lot_orgrate,
		  phpbb_orders.order_delivery,
		  phpbb_lots.lot_name,
		  phpbb_orders.order_id,
		  phpbb_orders.order_status,
		  phpbb_users.user_id,
		  phpbb_users.username
		FROM phpbb_lots
		  INNER JOIN phpbb_orders
			ON phpbb_lots.lot_id = phpbb_orders.lot_id
		  INNER JOIN phpbb_users
			ON phpbb_orders.user_id = phpbb_users.user_id
		  INNER JOIN phpbb_purchases
			ON phpbb_orders.Purchase_id = phpbb_purchases.purchase_id"
    . " WHERE phpbb_orders.purchase_id=".$purchase." AND phpbb_orders.order_status=4 "
	. " ORDER BY  phpbb_users.username,phpbb_lots.lot_name";
//echo ($sql);

	$result=$db->sql_query($sql);
	$orders_count=0;
	while ($row = $db->sql_fetchrow($result)) {
	    
		$orders[$row['order_id']]['purchase_id']=$row['purchase_id'];
	    $orders[$row['order_id']]['purchase_name']=$row['purchase_name'];
	    $orders[$row['order_id']]['purchase_url']=$row['purchase_url'];
	    $orders[$row['order_id']]['lot_cost']=$row['lot_cost'];
	    $orders[$row['order_id']]['lot_orgrate']=$row['lot_orgrate'];
	    $orders[$row['order_id']]['order_delivery']=$row['order_delivery'];
	    $orders[$row['order_id']]['lot_name']=$row['lot_name'];
	    $orders[$row['order_id']]['order_id']=$row['order_id'];
	    $orders[$row['order_id']]['order_status']=$row['order_status'];
	    $orders[$row['order_id']]['user_id']=$row['user_id'];
	    $orders[$row['order_id']]['username']=$row['username'];

		$template->assign_block_vars('order', array(
           'PURCHASE_ID' => $row['purchase_id'],
           'PURCHASE_NAME' => $row['purchase_name'],
           'PURCHASE_URL' => $row['purchase_url'],
           'LOT_COST' => $row['lot_cost'],
           'LOT_ORGRATE' => $row['lot_orgrate'],
           'ORDER_DELIVERY' => $row['order_delivery'],
           'LOT_NAME' => $row['lot_name'],
           'ORDER_ID' => $row['order_id'],
           'ORDER_STATUS' => $row['order_status'],
           'USER_ID' => $row['user_id'],
           'USERNAME' => $row['username']
		  ));
		$orders_count++;
	}
		
	
	if (is_array($orders)){
		$orders_ = '{';
		foreach($orders as $k=>$v){
		  if ($orders_ != '{') $orders_ =$orders_ .','; 	
		  $orders_ =$orders_.$k.':{ "order_id":'.$v['order_id'].'}';
		}
		$orders_=$orders_.'}';
	}
	 
}
else { // закупка не выбрана - показываем то, что пришло с челябинска
$sql = "SELECT
		  SubQuery.username AS Орг,
		  SubQuery.Purchase_id AS Закупка,
		  SubQuery.purchase_name AS Наименование,
		  SubQuery.To_EC_Date AS Отправлено,
		  COUNT(SubQuery.user_id) AS Заказы,
		  SUM(SubQuery.lots) AS Позиции
		FROM (SELECT
			phpbb_users.username,
			phpbb_purchases.purchase_name,
			phpbb_orders.Purchase_id,
			phpbb_orders.user_id,
			COUNT(phpbb_orders.order_id) AS lots,
			phpbb_orders.To_EC_Date
		  FROM phpbb_orders
			LEFT OUTER JOIN phpbb_purchases
			  ON phpbb_orders.Purchase_id = phpbb_purchases.purchase_id
			LEFT OUTER JOIN phpbb_reservs
			  ON phpbb_purchases.reserv_id = phpbb_reservs.reserv_id
			LEFT OUTER JOIN phpbb_users
			  ON phpbb_reservs.user_id = phpbb_users.user_id
		  WHERE phpbb_orders.order_status = 4 AND phpbb_orders.In_EC_Date IS NULL AND phpbb_users.Org_Dostavka = 1
		  GROUP BY phpbb_users.username,
				   phpbb_purchases.purchase_name,
				   phpbb_orders.Purchase_id,
				   phpbb_orders.user_id,
				   phpbb_orders.order_status,
				   phpbb_orders.In_EC_Date,
				   phpbb_purchases.purchase_id,
				   phpbb_purchases.reserv_id,
				   phpbb_reservs.reserv_id,
				   phpbb_reservs.user_id,
				   phpbb_users.user_id,
				   phpbb_orders.To_EC_Date) SubQuery
		GROUP BY SubQuery.username,
				 SubQuery.purchase_name,
				 SubQuery.Purchase_id,
				 SubQuery.To_EC_Date
		ORDER BY Орг, Закупка";
	//echo ($sql);

	$result=$db->sql_query($sql);
	while ($row = $db->sql_fetchrow($result)) {

	$template->assign_block_vars('dostavka', array(
	   'ORG' => $row['Орг'],
	   'ZAKUPKA' => $row['Закупка'],
	   'NAIMENOVANIE' => $row['Наименование'],
	   'OTPRAVLENO' => $row['Отправлено'],
	   'ZAKAZI' => $row['Заказы'],
	   'POZICII' => $row['Позиции']
	  ));

	}	
	
}	
$template->assign_vars(array(
	'PURCHASES_COUNT'	=> $purchases_count,
	'PURCHASE_ID'	=> ($purchase)?$purchase:0,
	'ORDERS_COUNT'		=> $orders_count,
	'ORDERS'		=> ($orders_)?$orders_:0
));


page_header('Прием товара');



if (strpos($_SERVER['HTTP_REFERER'],'spmgn.ru/index.php')){

	$template->assign_var('IFRAME', 1);
}
$template->set_filenames(array(
	'body' => 'input_ec2_body.html',
));

make_jumpbox(append_sid("{$phpbb_root_path}viewforum.$phpEx"));
page_footer();

?>