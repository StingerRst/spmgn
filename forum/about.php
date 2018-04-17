<?php
error_log ("about",0);
$h=getenv("HTTP_REFERER");
echo ($h);
echo ('<br>');
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
$sql = "SELECT phpbb_purchases.purchase_id, concat(phpbb_users.username,\" -\", phpbb_purchases.purchase_name) as purchase_name\n"
    . "FROM ((((phpbb_orders INNER JOIN phpbb_lots ON phpbb_orders.lot_id = phpbb_lots.lot_id) INNER JOIN phpbb_catalogs ON phpbb_lots.catalog_id = phpbb_catalogs.catalog_id) INNER JOIN phpbb_purchases ON phpbb_catalogs.purchase_id = phpbb_purchases.purchase_id) INNER JOIN phpbb_reservs ON phpbb_purchases.reserv_id = phpbb_reservs.reserv_id) INNER JOIN phpbb_users ON phpbb_reservs.user_id = phpbb_users.user_id\n"
    . "GROUP BY phpbb_purchases.purchase_id, phpbb_orders.order_status,purchase_name\n"
    . "HAVING (((phpbb_orders.order_status)=\"4\"))\n"
    . "ORDER BY purchase_name";
	
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

// если выбрана закупка - получаем список заказов
if ( isset($purchase))
{
$sql ="SELECT phpbb_purchases.purchase_id, phpbb_purchases.purchase_name, phpbb_purchases.purchase_url, phpbb_lots.lot_cost, phpbb_lots.lot_orgrate, phpbb_orders.order_delivery, phpbb_lots.lot_name, phpbb_orders.order_id, phpbb_orders.order_status, phpbb_users.user_id, phpbb_users.username\n"
    . "FROM (((phpbb_purchases RIGHT JOIN phpbb_catalogs ON phpbb_purchases.purchase_id = phpbb_catalogs.purchase_id) RIGHT JOIN phpbb_lots ON phpbb_catalogs.catalog_id = phpbb_lots.catalog_id) RIGHT JOIN phpbb_orders ON phpbb_lots.lot_id = phpbb_orders.lot_id) RIGHT JOIN phpbb_users ON phpbb_orders.user_id = phpbb_users.user_id\n"
    . "WHERE (((phpbb_purchases.purchase_id)=".$purchase.") AND ((phpbb_orders.order_status)=\"4\"))\n"
	. "ORDER BY phpbb_lots.lot_name, phpbb_users.username";
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
$template->assign_vars(array(
	'PURCHASES_COUNT'	=> $purchases_count,
	'PURCHASE_ID'	=> $purchase,
	'ORDERS_COUNT'		=> $orders_count,
	'ORDERS'		=> $orders_
));


page_header('Прием товара');


$template->assign_vars(array(
	'PURCHASE'	=> $purchase,
	'USERS'		=> $users,
	'FOO'		=> $foo,
	'FOO2'		=> $foo2
));

$template->set_filenames(array(
	'body' => 'aboutus_body.html',
));

make_jumpbox(append_sid("{$phpbb_root_path}viewforum.$phpEx"));
page_footer();

?>