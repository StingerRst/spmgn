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
		trigger_error('Доступ организаторам');
	}	
//$template->assign_var('HIDE',(isset($_POST['hide_pay'])?1:0));
//$hide = (isset($_POST['hide_pay'])?1:0);
//echo $hide;

// Отчет по закупкам
$sql ="SELECT
		  SubQuery.user_id,
		  SubQuery.purchase_id,
		  SubQuery.purchase_name,
		  SubQuery.purchase_url,
		  SubQuery.purchase_admin_payment,
		  SubQuery.purchase_admin_payment_money,
		  SubQuery.purchase_admin_money,
		  SubQuery.purchase_admin_money_date,
		  ROUND(SUM(SubQuery.cost) / 50, 0) AS percents,
		  COUNT(SubQuery.user) * 5 AS EC,
		  DATE_FORMAT(MAX(SubQuery.ToEc),'%d.%m.%Y') AS ToEcDate
		FROM (SELECT
			phpbb_reservs.user_id,
			phpbb_purchases.purchase_id,
			phpbb_purchases.purchase_name,
			phpbb_purchases.purchase_url,
			phpbb_purchases.purchase_admin_payment,
			phpbb_purchases.purchase_admin_payment_money,
			phpbb_purchases.purchase_admin_money,
			phpbb_purchases.purchase_admin_money_date,
			SUM(phpbb_catalogs.catalog_course * phpbb_lots.lot_cost) AS cost,
			phpbb_orders.user_id AS user,
			MAX(phpbb_orders.To_EC_Date) AS ToEc
		  FROM phpbb_reservs
			LEFT OUTER JOIN phpbb_purchases
			  ON phpbb_reservs.reserv_id = phpbb_purchases.reserv_id
			LEFT OUTER JOIN phpbb_catalogs
			  ON phpbb_purchases.purchase_id = phpbb_catalogs.purchase_id
			LEFT OUTER JOIN phpbb_lots
			  ON phpbb_catalogs.catalog_id = phpbb_lots.catalog_id
			LEFT OUTER JOIN phpbb_orders
			  ON phpbb_lots.lot_id = phpbb_orders.lot_id
		  WHERE phpbb_reservs.user_id =";
		  $sql .= $user->data['user_id'];   
		// $sql .= 8612;   
		  $sql .= " AND phpbb_orders.order_status > 3
		  GROUP BY phpbb_reservs.user_id,
				   phpbb_purchases.purchase_id,
				   phpbb_purchases.purchase_name,
				   phpbb_purchases.purchase_url,
				   phpbb_purchases.purchase_admin_payment,
				   phpbb_purchases.purchase_admin_payment_money,
				   phpbb_purchases.purchase_admin_money,
				   phpbb_purchases.purchase_admin_money_date,
				   phpbb_orders.user_id) SubQuery
		GROUP BY SubQuery.user_id,
				 SubQuery.purchase_id,
				 SubQuery.purchase_name,
				 SubQuery.purchase_url,
				 SubQuery.purchase_admin_payment,
				 SubQuery.purchase_admin_payment_money,
				 SubQuery.purchase_admin_money,
				 SubQuery.purchase_admin_money_date";
	
	//var_dump($sql) ;
	

	$result=$db->sql_query($sql);
	while ($row = $db->sql_fetchrow($result)) {
		$template->assign_block_vars('otchet', array(
           'USER_ID' => $row['user_id'],
           'PURCHASE_ID' => $row['purchase_id'],
           'PURCHASE_NAME' => $row['purchase_name'],
           'PURCHASE_URL' => $row['purchase_url'],
           'PURCHASE_ADMIN_PAYMENT' => $row['purchase_admin_payment'],
           'PURCHASE_ADMIN_PAYMENT_MONEY' => $row['purchase_admin_payment_money']?$row['purchase_admin_payment_money']:0,
           'PURCHASE_ADMIN_MONEY' => $row['purchase_admin_money']?$row['purchase_admin_money']:0,
		   'PURCHASE_ADMIN_MONEY_DATE' => $row['purchase_admin_money_date'],
           'PERCENTS' => number_format($row['percents'],0),
           'EC' => number_format($row['EC'],0),
		   'ITOGO' => number_format($row['percents'],0)+number_format($row['EC'],0),
           'TO_EC_DATE' => $row['ToEcDate']
		  ));
	}
	 
$template->assign_var('DATE_PRINT',date ("d.m.Y", time()));
page_header('Баланс организатора');

$template->set_filenames(array(
	'body' => 'org_personal_balance_body.html',
));

make_jumpbox(append_sid("{$phpbb_root_path}viewforum.$phpEx"));
page_footer();

?>