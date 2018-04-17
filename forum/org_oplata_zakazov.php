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
    . " WHERE " . USER_GROUP_TABLE . ".user_id=" . $user->data['user_id']." and ". USER_GROUP_TABLE . ".group_id=8";
	$result=$db->sql_query($sql);
	$row = $db->sql_fetchrow($result);

	if (!$row){ // если не Оператор ЕЦ - не пускать
		trigger_error('Доступ только Операторам ЕЦ');
	}	
$template->assign_var('SHOW',(isset($_POST['show_pay'])?1:0));
$show = (isset($_POST['show_pay'])?1:0);
$template->assign_var('SHOWOPEN',(isset($_POST['show_open'])?1:0));
$showopen = (isset($_POST['show_open'])?1:0);

//echo $show;

// Отчет по закупкам
$sql = "SELECT
		  phpbb_orders.purchase_id,
		  phpbb_purchases.purchase_name,
		  phpbb_purchases.purchase_url,
		  phpbb_purchases_orsers.user_id,
		  phpbb_users.username,
		  ROUND(SUM(phpbb_orders.lot_cost * (1 + phpbb_orders.lot_orgrate / 100) * phpbb_orders.catalog_course + phpbb_orders.order_delivery)) AS SummaZakaza,		  
		  phpbb_purchases_orsers.puor_discount,
		  phpbb_purchases_orsers.payment_money,
		  phpbb_purchases_orsers.puor_monye,
		  phpbb_purchases_orsers.payment_date,
		  phpbb_purchases_orsers.payment_time,
		  phpbb_purchases_orsers.payment_card,
		  phpbb_purchases_orsers.payment_text,
		  phpbb_purchases_orsers.dolg,
		  phpbb_users.user_realname,
		  phpbb_users.user_phone,
		  phpbb_purchases_orsers.puor_comment
		FROM phpbb_reservs
		  LEFT OUTER JOIN phpbb_purchases
			ON phpbb_reservs.reserv_id = phpbb_purchases.reserv_id
		  LEFT OUTER JOIN phpbb_purchases_orsers
			ON phpbb_purchases.purchase_id = phpbb_purchases_orsers.purchase_id
		  LEFT OUTER JOIN phpbb_users
			ON phpbb_purchases_orsers.user_id = phpbb_users.user_id
		  LEFT OUTER JOIN phpbb_orders
			ON phpbb_purchases_orsers.user_id = phpbb_orders.user_id
			AND phpbb_purchases_orsers.purchase_id = phpbb_orders.Purchase_id"
	." WHERE phpbb_reservs.user_id = ".$user->data['user_id'].""
	." AND phpbb_reservs.status IN (";
	if ($showopen) $sql .=" 4,";
	$sql .=" 5)"
	//." AND phpbb_reservs.status IN (4, 5,6, 7)"
	." AND phpbb_reservs.brand_id NOT IN (390)"
	." AND phpbb_orders.order_status IN (2, 4, 5, 6, 7)";
if (!$show) $sql .=" AND phpbb_purchases_orsers.puor_monye = 0";
$sql .=" GROUP BY phpbb_purchases.purchase_id,
         phpbb_purchases.purchase_name,
         phpbb_purchases.purchase_url,
         phpbb_purchases_orsers.user_id,
         phpbb_users.username,
         phpbb_users.user_realname,
         phpbb_users.user_phone,
         phpbb_purchases_orsers.puor_discount,
         phpbb_purchases_orsers.payment_money,
         phpbb_purchases_orsers.puor_monye,
         phpbb_purchases_orsers.payment_date,
         phpbb_purchases_orsers.payment_time,
         phpbb_purchases_orsers.payment_card,
         phpbb_purchases_orsers.payment_text,
         phpbb_purchases_orsers.dolg,
         phpbb_purchases_orsers.puor_comment";

//if (!$show) $sql .=" WHERE phpbb_purchases_orsers.puor_monye = 0";
//$sql .=" AND phpbb_purchases_orsers.user_id = 5767";	
//	var_dump($sql) ;
//echo ($sql);	

	$result=$db->sql_query($sql);
	while ($row = $db->sql_fetchrow($result)) {
		$template->assign_block_vars('otchet', array(
           'PURCHASE_ID' => $row['purchase_id'],
           'PURCHASE_NAME' => $row['purchase_name'],
           'USER_ID' => $row['user_id'],
           'USERNAME' => $row['username'],
           'USER_REALNAME' => $row['user_realname'],
           'USER_PHONE' => $row['user_phone'],
           'OPLATA' => trim($row['SummaZakaza'] -  $row['puor_discount']) ,
           'PUOR_DISCOUNT' => $row['puor_discount'],
           'PUOR_MONYE' => trim($row['puor_monye']),
           'PAYMENT_MONEY' =>trim($row['payment_money']),
           'PAYMENT_DATE' =>$row['payment_date'],
           'PAYMENT_TIME' =>$row['payment_time'],
           'PAYMENT_CARD' =>$row['payment_card'],
           'PAYMENT_TEXT' =>$row['payment_text'],
           'PUOR_COMMENT' =>$row['puor_comment'],
           'PERSONAL_PUOR' =>$row['personal_puor'],
           'DOLG' =>$row['dolg']
		  ));
	}
	 
$template->assign_var('DATE_PRINT',date ("d.m.Y", time()));


page_header('Отчет по оргам');

if (strpos($_SERVER['HTTP_REFERER'],'spmgn.ru/index.php')){

	$template->assign_var('IFRAME', 1);
}	

$template->set_filenames(array(
	'body' => 'org_oplata_zakazov.html',
));

make_jumpbox(append_sid("{$phpbb_root_path}viewforum.$phpEx"));
page_footer();

?>