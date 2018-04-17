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
//$template->assign_var('HIDE',(isset($_POST['hide_pay'])?1:0));

//$hide = (isset($_POST['hide_pay'])?1:0);
$hide =1;
// Отчет по закупкам
$sql ="SELECT phpbb_purchases.purchase_id, phpbb_purchases.purchase_name, phpbb_users.user_id, phpbb_users.username, phpbb_purchases.purchase_admin_payment\n"
    . "     , round(sum(SubQuery.`Сумма`) / 100 * 2, 1) AS '2percent', if(count(SubQuery.user_id) < 20, count(SubQuery.user_id) * 5, 100) AS 'v_ec'\n"
    . "     , round(sum(SubQuery.`Сумма`) / 100 * 2 + if(count(SubQuery.user_id) < 20, count(SubQuery.user_id) * 5, 100), 1) AS 'itogo'\n"
    . "     , phpbb_purchases.purchase_admin_money AS 'zaplacheno'\n"
    . "FROM\n"
    . "  phpbb_purchases\n"
    . "RIGHT OUTER JOIN (\n"
    . "SELECT phpbb_catalogs.purchase_id\n"
    . "     , sum(phpbb_lots.lot_cost) AS `Сумма`\n"
    . "     , phpbb_orders.user_id\n"
    . "FROM\n"
    . "  phpbb_catalogs\n"
    . "LEFT OUTER JOIN phpbb_lots\n"
    . "ON phpbb_catalogs.catalog_id = phpbb_lots.catalog_id\n"
    . "LEFT OUTER JOIN phpbb_orders\n"
    . "ON phpbb_lots.lot_id = phpbb_orders.lot_id\n"
    . "WHERE\n"
    . "  phpbb_orders.order_status > 3\n"
    . "GROUP BY\n"
    . "  phpbb_catalogs.purchase_id\n"
    . ", phpbb_orders.user_id) SubQuery\n"
    . "ON phpbb_purchases.purchase_id = SubQuery.purchase_id\n"
    . "LEFT OUTER JOIN phpbb_reservs\n"
    . "ON phpbb_purchases.reserv_id = phpbb_reservs.reserv_id\n"
    . "LEFT OUTER JOIN phpbb_users\n"
    . "ON phpbb_reservs.user_id = phpbb_users.user_id \n"
    . "WHERE\n"
    . "  NOT isnull(phpbb_users.user_id) ";
if ($hide) $sql .= " AND (phpbb_purchases.purchase_admin_money < 10) ";
$sql .= "GROUP BY\n"
    . "  phpbb_purchases.purchase_id\n"
    . ", phpbb_purchases.purchase_name\n"
    . ", phpbb_users.username\n"
    . ", phpbb_purchases.purchase_admin_payment\n"
    . ", phpbb_purchases.purchase_admin_money\n"
    . "ORDER BY\n"
    . "  phpbb_purchases.purchase_id desc LIMIT 200";
//if ($hide) $sql .= " AND phpbb_purchases.purchase_admin_money < 10 ";
//$sql .= "GROUP BY\n"

	//echo ($sql);

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
           'ITOGO' => number_format($row['itogo'],0),
           'ZAPLACHENO' => number_format($row['zaplacheno'],0)
		  ));
	}
	 
$template->assign_var('DATE_PRINT',date ("d.m.Y", time()));


page_header('Отчет по оргам');


$template->set_filenames(array(
	'body' => 'otchet_body.html',
));

make_jumpbox(append_sid("{$phpbb_root_path}viewforum.$phpEx"));
page_footer();

?>