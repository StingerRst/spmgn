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
$template->assign_var('SHOW',(isset($_POST['show_pay'])?1:0));
$show = (isset($_POST['show_pay'])?1:0);
//echo $show;

// Отчет по закупкам
$sql ="SELECT orders.purchase_id, orders.purchase_name, orders.user_id, orders.username, round(orders.oplata - phpbb_purchases_orsers.puor_discount) AS k_oplate, phpbb_purchases_orsers.puor_monye,"
		." phpbb_purchases_orsers.payment_money, phpbb_purchases_orsers.payment_date, phpbb_purchases_orsers.payment_time, phpbb_purchases_orsers.payment_card,"
		." phpbb_purchases_orsers.puor_comment, phpbb_purchases_orsers.payment_text, phpbb_purchases_orsers.personal_puor, phpbb_purchases_orsers.dolg\n"
."FROM (SELECT\n"
."    phpbb_purchases.purchase_id,\n"
."    phpbb_purchases.purchase_name,\n"
."    phpbb_orders.user_id,\n"
."    SUM((phpbb_orders.order_delivery + phpbb_lots.lot_cost + phpbb_lots.lot_cost / 100 * phpbb_lots.lot_orgrate) * phpbb_catalogs.catalog_course) AS oplata,\n"
."    phpbb_users.username\n"
."  FROM phpbb_reservs\n"
."    LEFT OUTER JOIN phpbb_purchases\n"
."      ON phpbb_reservs.reserv_id = phpbb_purchases.reserv_id\n"
."    LEFT OUTER JOIN phpbb_orders\n"
."      ON phpbb_purchases.purchase_id = phpbb_orders.Purchase_id\n"
."    LEFT OUTER JOIN phpbb_lots\n"
."      ON phpbb_orders.lot_id = phpbb_lots.lot_id\n"
."    LEFT OUTER JOIN phpbb_catalogs\n"
."      ON phpbb_lots.catalog_id = phpbb_catalogs.catalog_id\n"
."    LEFT OUTER JOIN phpbb_users\n"
."      ON phpbb_orders.user_id = phpbb_users.user_id\n"
."  WHERE phpbb_reservs.user_id = ".$user->data['user_id']."\n"
."  AND (phpbb_orders.order_status > 3 or phpbb_orders.order_status = 2)\n"
."  AND phpbb_lots.lot_cost IS NOT NULL\n"
."  AND phpbb_reservs.brand_id NOT IN (390)\n"
."  GROUP BY phpbb_purchases.purchase_id,\n"
."           phpbb_purchases.purchase_name,\n"
."           phpbb_orders.user_id,\n"
."           phpbb_users.username) orders\n"
."  LEFT OUTER JOIN phpbb_purchases_orsers\n"
."    ON orders.user_id = phpbb_purchases_orsers.user_id\n"
."    AND orders.purchase_id = phpbb_purchases_orsers.purchase_id\n";
if (!$show) $sql .=" WHERE phpbb_purchases_orsers.puor_monye = 0";
//$sql .=" AND phpbb_purchases_orsers.user_id = 5767";	
//	var_dump($sql) ;
	

	$result=$db->sql_query($sql);
	while ($row = $db->sql_fetchrow($result)) {
		$template->assign_block_vars('otchet', array(
           'PURCHASE_ID' => $row['purchase_id'],
           'PURCHASE_NAME' => $row['purchase_name'],
           'USER_ID' => $row['user_id'],
           'USERNAME' => $row['username'],
           'OPLATA' =>number_format( $row['k_oplate'],0),
           'PUOR_DISCOUNT' =>number_format( $row['puor_discount'],0),
           'PUOR_MONYE' =>number_format( $row['puor_monye'],0),
           'PAYMENT_MONEY' =>number_format( $row['payment_money'],0),
           'PAYMENT_DATE' =>$row['payment_date'],
           'PAYMENT_TIME' =>$row['payment_time'],
           'PAYMENT_CARD' =>$row['payment_card'],
           'PAYMENT_TEXT' =>$row['payment_text'],
           'PUOR_COMMENT' =>$row['puor_comment'],
           'PERSONAL_PUOR' =>$row['personal_puor'],
           'DOLG' =>number_format( $row['dolg'],0)
		  ));
	}
	 
$template->assign_var('DATE_PRINT',date ("d.m.Y", time()));


page_header('Отчет по оргам');

if (strpos($_SERVER['HTTP_REFERER'],'spmgn.ru/index.php')){

	$template->assign_var('IFRAME', 1);
}	

$template->set_filenames(array(
	'body' => 'oplati_body.html',
));

make_jumpbox(append_sid("{$phpbb_root_path}viewforum.$phpEx"));
page_footer();

?>