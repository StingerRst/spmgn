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
$hide = (isset($_POST['hide_pay'])?1:0);
$template->assign_var('HIDE',$hide);

$years = (isset($_POST['years'])?$_POST['years']:1);
$template->assign_var('YEARS',$years);


//echo $hide;

// Отчет по закупкам
$sql ="SELECT
		  SubQuery.purchase_id,
		  SubQuery.v_ec,
		  SubQuery.2percent,
		  SubQuery.ToEc,
		  phpbb_purchases.purchase_name,
		  phpbb_purchases.purchase_admin_payment,
		  phpbb_purchases.purchase_admin_payment_money,
		  phpbb_purchases.purchase_admin_money AS zaplacheno,
		  phpbb_purchases.purchase_admin_money_date,
		  phpbb_reservs.status,
		  phpbb_users.user_id,
		  phpbb_users.username
		FROM (SELECT
			phpbb_orders.Purchase_id,
			COUNT(DISTINCT phpbb_orders.user_id) * 5 AS v_ec,
			ROUND(SUM(phpbb_orders.lot_cost* phpbb_orders.catalog_course) / 50) AS 2percent,
			MIN(phpbb_orders.To_EC_Date) AS ToEc
		  FROM phpbb_orders
		  WHERE phpbb_orders.order_status NOT IN (0,1,3)
		  AND(phpbb_orders.To_EC_Date IS null OR YEAR(phpbb_orders.To_EC_Date) >= YEAR( NOW())-";
		 $sql .= $years-1;
		 $sql .= ") GROUP BY phpbb_orders.Purchase_id) SubQuery
		  INNER JOIN phpbb_purchases
			ON SubQuery.Purchase_id = phpbb_purchases.purchase_id
		  INNER JOIN phpbb_reservs
			ON phpbb_purchases.reserv_id = phpbb_reservs.reserv_id
		  INNER JOIN phpbb_users
			ON phpbb_reservs.user_id = phpbb_users.user_id";
		if (!$hide) $sql .=" where (isnull(phpbb_purchases.purchase_admin_money) or (phpbb_purchases.purchase_admin_money) <1)";
		$sql .= " ORDER BY ToEc DESC ,Ucase(phpbb_users.username) ";
	
//	var_dump($sql) ;
	

	$result=$db->sql_query($sql);
	while ($row = $db->sql_fetchrow($result)) {
		$template->assign_block_vars('otchet', array(
           'PURCHASE_ID' => $row['purchase_id'],
           'PURCHASE_NAME' => $row['purchase_name'],
           'USER_ID' => $row['user_id'],
           'USERNAME' => $row['username'],
           'PURCHASE_ADMIN_PAYMENT' => $row['purchase_admin_payment'],
           '2PERCENT' => number_format($row['2percent'],0),
           'V_EC' => number_format($row['v_ec'],0),
           'TO_EC' => $row['ToEc'],
		   'ITOGO' => $row['v_ec']+$row['2percent'],
           'ZAPLACHENO' => number_format($row['zaplacheno'],0)
		  ));
	}
	 
$template->assign_var('DATE_PRINT',date ("d.m.Y", time()));


page_header('Отчет по оргам');

if (strpos($_SERVER['HTTP_REFERER'],'spmgn.ru/index.php')){

	$template->assign_var('IFRAME', 1);
}	

$template->set_filenames(array(
	'body' => 'otchet_body.html',
));

make_jumpbox(append_sid("{$phpbb_root_path}viewforum.$phpEx"));
page_footer();

?>