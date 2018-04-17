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

// выбор пользователя с заказами в ЕЦ
$sql = "SELECT phpbb_users.user_id, phpbb_users.username, phpbb_users.user_realname\n"
    . "FROM phpbb_orders LEFT JOIN phpbb_users ON phpbb_orders.user_id = phpbb_users.user_id\n"
    . "WHERE (((phpbb_orders.order_status)=\"5\"))\n"
    . "GROUP BY phpbb_users.user_id, phpbb_users.username, phpbb_users.user_realname\n"
    . "ORDER BY upper(phpbb_users.username)";
	$result=$db->sql_query($sql);
	while ($row = $db->sql_fetchrow($result)) {
	 $template->assign_block_vars('users', array(
           'ID' => $row['user_id'],
           'NAME' => $row['username'],
           'FULLNAME' => $row['user_realname']
		 ));
	}

if (strpos($_SERVER['HTTP_REFERER'],'spmgn.ru/index.php')){

	$template->assign_var('IFRAME', 1);
}
// определение группы пользователя
if (!$user->data['is_ec_operator']){ // если не Оператор ЕЦ то показываем список закупок
  $userselect = $user->data['user_id']; //выбраная закупка

  $template->set_filenames(array(
	'body' => 'ec_user_body.html',
));

}
else
{
  $userselect = $_POST['UserSelect']; //выбраная закупка
$template->set_filenames(array(
	'body' => 'ec_user_body.html',
));

  }
// если выбран участник - получаем список заказов
if ( isset($userselect))
{
$sql ="SELECT phpbb_purchases.purchase_id,phpbb_purchases.purchase_name,phpbb_purchases.purchase_url, phpbb_lots.lot_name, phpbb_lots.lot_cost, phpbb_orders.order_delivery, phpbb_orders.order_id\n"
    . "FROM ((((phpbb_orders LEFT JOIN phpbb_lots ON phpbb_orders.lot_id = phpbb_lots.lot_id) LEFT JOIN phpbb_catalogs ON phpbb_lots.catalog_id = phpbb_catalogs.catalog_id) LEFT JOIN phpbb_purchases ON phpbb_catalogs.purchase_id = phpbb_purchases.purchase_id) LEFT JOIN phpbb_reservs ON\n"
	. "phpbb_purchases.reserv_id = phpbb_reservs.reserv_id) LEFT JOIN phpbb_users ON phpbb_reservs.user_id = phpbb_users.user_id\n"
    . "WHERE (((phpbb_orders.user_id)=".$userselect.") AND ((phpbb_orders.order_status)=\"5\"))\n"
	. "ORDER BY upper(phpbb_purchases.purchase_name), upper(phpbb_lots.lot_name)";

	//echo ($sql);

	$result=$db->sql_query($sql);
	$sum_cost=0;
	$sum_delivery=0;
	$sum_ec=0;
	$old_purchase_id=0;
	while ($row = $db->sql_fetchrow($result)) {
	    
		$orders[$row['order_id']]['purchase_id']=$row['purchase_id'];
	    $orders[$row['order_id']]['purchase_name']=$row['purchase_name'];
	    $orders[$row['order_id']]['lot_cost']=$row['lot_cost'];
	    $orders[$row['order_id']]['order_delivery']=$row['order_delivery'];
	    $orders[$row['order_id']]['lot_name']=$row['lot_name'];
	    $orders[$row['order_id']]['order_id']=$row['order_id'];
	    $orders[$row['order_id']]['order_status']=$row['order_status'];
	    $orders[$row['order_id']]['user_id']=$row['user_id'];
	    $orders[$row['order_id']]['username']=$row['username'];
		if ($row['purchase_id'] != $old_purchase_id){
			$ec=10;
			$old_purchase_id=$row['purchase_id'];
		}
		else {
			$ec=0;
		}
		$sum_cost=$sum_cost+$row['lot_cost'];
		$sum_delivery=$sum_delivery+$row['order_delivery'];
		$sum_ec=$sum_ec+$ec;
		
		$template->assign_block_vars('order', array(
           'PURCHASE_ID' => $row['purchase_id'],
           'PURCHASE_NAME' => $row['purchase_name'],
           'PURCHASE_URL' => append_sid($row['purchase_url']),
           'LOT_COST' => number_format($row['lot_cost'],0),
           'LOT_ORGRATE' => number_format($row['lot_orgrate'],0),
           'ORDER_DELIVERY' => number_format($row['order_delivery'],0),
           'LOT_NAME' => $row['lot_name'],
           'ORDER_ID' => $row['order_id'],
           'ORDER_STATUS' => $row['order_status'],
           'USER_ID' => $row['user_id'],
		   'EC'		=> $ec,
		   'SUM_COST'		=> number_format($sum_cost,0),
		   'SUM_DELIVERY'	=> number_format($sum_delivery,0),
		   'SUM_EC'			=> number_format($sum_ec,0),
		   'ITOGO'			=> number_format($sum_delivery+$sum_ec,0),
           'USERNAME' => $row['username']
		  ));
		$orders_count++;
	}
	if (is_array($orders)){
		$orders_ = '{';
		foreach($orders as $k=>$v){
		  if ($orders_ != '{') $orders_ =$orders_ .','; 	
		  $orders_ =$orders_.$k.':{ "order_id":'.$v['order_id'].',"purchase_id":'.$v['purchase_id'].'}';
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
	'USERSELECT'	=> $userselect,
	'U_ADMIN' 		=> append_sid("{$phpbb_root_path}adm.$phpEx"),
	'USERS'		=> $users,
	'FOO'		=> $foo,
	'FOO2'		=> $foo2
));


make_jumpbox(append_sid("{$phpbb_root_path}viewforum.$phpEx"));
page_footer();

?>