  <style>
	table.stable {
    table-layout: fixed; /* Фиксированная ширина ячеек */
    width: 100%; /* Ширина таблицы */
	page-break-inside: avoid;
	border:1px solid black;
	border-collapse: collapse;
	
	}

	td.user 	{ width: 10%; word-break:break-all;border:1px solid black }
	td.purchase { border:1px solid black}
	td.date 	{ width: 10%; word-break:break-all;border:1px solid black}
  </style>
 <script>
function fresh() {
    location.reload();
}
 setInterval("fresh()",2000);
</script> 
<?php
ini_set('log_errors', 'On');
ini_set('error_log', '/var/log/httpd/spmgn.ru/php_errors.log');
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
//echo('user_id:'+$user->data['user_id']);
//echo ('<br>');
//if ($user->data['user_id'] == ANONYMOUS)
//{
//    login_box('', $user->lang['LOGIN']);
//}
// определение группы пользователя
//$sql = "SELECT " . USER_GROUP_TABLE . ".group_id"
  //  . " FROM " . USER_GROUP_TABLE
    //. " WHERE " . USER_GROUP_TABLE . ".user_id=" . $user->data['user_id']." and ". USER_GROUP_TABLE . ".group_id=14";
	//$result=$db->sql_query($sql);
	//$row = $db->sql_fetchrow($result);

	//if (!$row){ // если не Оператор ЕЦ - не пускать
		//trigger_error('Доступ только Операторам ЕЦ');
	//}
	$sql ="SELECT
				  phpbb_users_out.UserId,
				  phpbb_users.username,
				  phpbb_orders.Purchase_id,
				  phpbb_purchases.purchase_name,
				  phpbb_orders.To_EC_Date
				FROM (SELECT
					phpbb_users_out.UserId,
					phpbb_users_out.Date
				  FROM phpbb_users_out
				  WHERE phpbb_users_out.DATE > NOW()-600
				  ORDER BY phpbb_users_out.Date DESC
				  LIMIT 3) phpbb_users_out
				  INNER JOIN phpbb_users
					ON phpbb_users_out.UserId = phpbb_users.user_id
				  INNER JOIN phpbb_orders
					ON phpbb_users_out.UserId = phpbb_orders.user_id
				  INNER JOIN phpbb_purchases
					ON phpbb_orders.Purchase_id = phpbb_purchases.purchase_id
				WHERE phpbb_orders.order_status = 5
				GROUP BY phpbb_users_out.UserId,
						 phpbb_users_out.Date,
						 phpbb_users.username,
						 phpbb_orders.Purchase_id,
						 phpbb_purchases.purchase_name,
						 phpbb_orders.To_EC_Date
				ORDER BY phpbb_users_out.Date DESC";
	$result=$db->sql_query($sql);

echo('<table class="stable" style=" width: 98%;font-size: 2em; line-height: 1.5em;">');

	while ($row = $db->sql_fetchrow($result)) {
	//	echo ($row['username']);
		echo ('<tr>	<td class = "user" style=" width: 200px;" ><b>'.$row['username'].'</td>
					<td class = "purchase">'.$row['purchase_name'].'</td>
					<td class = "date" style=" width: 200px;">'.$row['To_EC_Date'].'</td></tr>');
	}
echo("</table>");

	//var_dump ($row);
?>