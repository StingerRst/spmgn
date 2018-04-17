<?php
// Указываем всем подключающимся скриптам,
// что они вызывается из главного файла.
// Для защиты от вызова их напрямую.
//echo ('test');
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
$template->assign_var('HIDE',(isset($_POST['hide_pay'])?1:0));
$hide = (isset($_POST['hide_pay'])?1:0);
//echo $hide;

// Отчет по закупкам
$sql ='SELECT
		  phpbb_reservs.reserv_id,
		  phpbb_users.username AS OrgName,
		  phpbb_users.user_id,
		  DATE_FORMAT(phpbb_reservs.request_send, "%d.%m.%Y") AS urequest_send,
		  DATE_FORMAT(phpbb_reservs.request_confirm, "%d.%m.%Y") AS urequest_confirm,
		  TO_DAYS(NOW()) - TO_DAYS(MAX(phpbb_orders.Create_Date)) AS NotADDDays,
		  DATE_FORMAT(MAX(phpbb_orders.Create_Date), "%d.%m.%Y") AS LastOrderDate,
		  phpbb_reservs.status,
		  phpbb_purchases.purchase_id,
		  phpbb_purchases.purchase_name,
		  phpbb_purchases.purchase_url,
		  COUNT(phpbb_orders.order_id) AS Orders,
		  phpbb_purchases.purchases_rule1 AS Minimalka,
		  ROUND(SUM(phpbb_lots.lot_cost*phpbb_catalogs.catalog_course)) AS Summa
		FROM phpbb_reservs
		  LEFT OUTER JOIN phpbb_users
			ON phpbb_reservs.user_id = phpbb_users.user_id
		  LEFT OUTER JOIN phpbb_purchases
			ON phpbb_reservs.reserv_id = phpbb_purchases.reserv_id
		  LEFT OUTER JOIN phpbb_orders
			ON phpbb_purchases.purchase_id = phpbb_orders.Purchase_id
		  LEFT OUTER JOIN phpbb_lots
			ON phpbb_orders.lot_id = phpbb_lots.lot_id
		  LEFT OUTER JOIN phpbb_catalogs
			ON phpbb_lots.catalog_id = phpbb_catalogs.catalog_id
		WHERE phpbb_reservs.status = 4 AND phpbb_reservs.status <> 0 AND phpbb_orders.order_status < 3
		GROUP BY phpbb_reservs.reserv_id,
				 phpbb_users.username,
				 phpbb_users.user_id,
				 phpbb_reservs.request_send,
				 phpbb_reservs.request_confirm,
				 phpbb_reservs.status,
				 phpbb_purchases.purchase_id,
				 phpbb_purchases.purchase_name,
				 phpbb_purchases.purchase_url,
				 phpbb_purchases.purchases_rule1
		HAVING NotADDDays > -30';
	//var_dump($sql) ;
	$result=$db->sql_query($sql);
	while ($row = $db->sql_fetchrow($result)) {
		switch ($row['status']) {
			case -1 : $status = "Заявка"; break;
			case 0 : $status = "Отказано";break;
			case 1 : $status = "Забронировано";break;;
			case 2 : $status = "Думается";break;
			case 3 : $status = "Создается";break;
			case 4 : $status = "Открыта";break;
			case 5 : $status = "Стоп";break;
			case 6  : $status = "Завершена";break;
			case 7  : $status = "В архиве";break;	
			//default: echo «Язык не установлен»;
		};
		$repl = array("минимальная партия заказа:", "Минимальная партия заказа:", "рублей");
		$template->assign_block_vars('otchet', array(
           'RESERV_ID' => $row['reserv_id'],
           'ORGNAME' => $row['OrgName'],
           'USER_ID' => $row['user_id'],
           'REQUEST_SEND' => $row['urequest_send'],
           'REQUEST_CONFIRM' => $row['urequest_confirm'],
           'NOTADDDAYS' => $row['NotADDDays'],
           'LASTORDERDATE' => $row['LastOrderDate'],
           'STATUS' => $status,
           'PURCHASE_ID' => $row['purchase_id'],
           'PURCHASE_NAME' => $row['purchase_name'],
           'PURCHASE_URL' => $row['purchase_url'],
           'ORDERS' => $row['Orders'],
           'MINIMALKA' => str_replace($repl, "", $row['Minimalka']),
           'SUMMA' => $row['Summa']
//       'ZAPLACHENO' => number_format($row['zaplacheno'],0)
		  ));
	}
	 
$template->assign_var('DATE_PRINT',date ("d.m.Y", time()));


page_header('Набираемость закупок');

if (strpos($_SERVER['HTTP_REFERER'],'spmgn.ru/index.php')){

	$template->assign_var('IFRAME', 1);
}	

$template->set_filenames(array(
	'body' => 'purchases_open_not_stop_body.html',
));

make_jumpbox(append_sid("{$phpbb_root_path}viewforum.$phpEx"));
page_footer();

?>