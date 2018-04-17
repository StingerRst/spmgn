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
		  DATE_FORMAT(phpbb_reservs.request_send, "%d.%m.%Y") as urequest_send,
		  DATE_FORMAT(phpbb_reservs.request_confirm, "%d.%m.%Y") as urequest_confirm,
		  TO_DAYS(NOW()) - TO_DAYS(IFNULL(SubQuery.LastOrderDate,IFNULL( phpbb_reservs.request_confirm,phpbb_reservs.request_send))) AS NotOpenDays,
		  phpbb_reservs.status,
		  phpbb_purchases.purchase_id,
		  phpbb_purchases.purchase_name,
		  phpbb_purchases.purchase_url,
		  phpbb_reservs.request_message,
		  phpbb_brands.brand_id,
		  phpbb_brands.brand_url,
		  phpbb_brands.brand_label,
		  phpbb_brands.brand_description
		FROM phpbb_reservs
		  LEFT OUTER JOIN phpbb_users
			ON phpbb_reservs.user_id = phpbb_users.user_id
		  LEFT OUTER JOIN phpbb_purchases
			ON phpbb_reservs.reserv_id = phpbb_purchases.reserv_id
		  LEFT OUTER JOIN phpbb_brands
			ON phpbb_reservs.brand_id = phpbb_brands.brand_id
		  LEFT OUTER JOIN (SELECT
			  phpbb_reservs.reserv_id,
			  MAX(phpbb_orders.Create_Date) AS LastOrderDate
			FROM phpbb_reservs
			  LEFT OUTER JOIN phpbb_purchases
				ON phpbb_reservs.reserv_id = phpbb_purchases.reserv_id
			  LEFT OUTER JOIN phpbb_orders
				ON phpbb_purchases.purchase_id = phpbb_orders.Purchase_id
			GROUP BY phpbb_reservs.reserv_id) SubQuery
			ON phpbb_reservs.reserv_id = SubQuery.reserv_id
		WHERE phpbb_reservs.status < 4
			AND phpbb_reservs.status <> 0';
	//$sql.=' AND TO_DAYS(NOW()) - TO_DAYS(IFNULL(SubQuery.LastOrderDate,IFNULL( phpbb_reservs.request_confirm,phpbb_reservs.request_send))) > 31';
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
		$template->assign_block_vars('otchet', array(
           'RESERV_ID' => $row['reserv_id'],
           'ORGNAME' => $row['OrgName'],
           'USER_ID' => $row['user_id'],
           'REQUEST_SEND' => $row['urequest_send'],
           'REQUEST_CONFIRM' => $row['urequest_confirm'],
           'NOTOPENDAYS' => $row['NotOpenDays'],
           'STATUS' => $status,
           'PURCHASE_ID' => $row['purchase_id'],
           'PURCHASE_NAME' => $row['purchase_name'],
           'PURCHASE_URL' => $row['purchase_url'],
           'REQUEST_MESSAGE' => $row['request_message'],
           'BRAND_ID' => $row['brand_id'],
           'BRAND_URL' => $row['brand_url'],
           'BRAND_LABEL' => $row['brand_label'],
           'BRAND_DESCRIPTION' => $row['brand_description']
//       'ZAPLACHENO' => number_format($row['zaplacheno'],0)
		  ));
	}
	 
$template->assign_var('DATE_PRINT',date ("d.m.Y", time()));


page_header('Неоткрытые закупки');

if (strpos($_SERVER['HTTP_REFERER'],'spmgn.ru/index.php')){

	$template->assign_var('IFRAME', 1);
}	

$template->set_filenames(array(
	'body' => 'purchases_not_open_body.html',
));

make_jumpbox(append_sid("{$phpbb_root_path}viewforum.$phpEx"));
page_footer();

?>