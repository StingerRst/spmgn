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

// баланс по ЕЦ
$sql = "SELECT
		  phpbb_users.username,
		  phpbb_lots.lot_name
		FROM phpbb_purchases
		  LEFT OUTER JOIN phpbb_catalogs
			ON phpbb_purchases.purchase_id = phpbb_catalogs.purchase_id
		  LEFT OUTER JOIN phpbb_lots
			ON phpbb_catalogs.catalog_id = phpbb_lots.catalog_id
		  LEFT OUTER JOIN phpbb_orders
			ON phpbb_lots.lot_id = phpbb_orders.lot_id
		  LEFT OUTER JOIN phpbb_users
			ON phpbb_orders.user_id = phpbb_users.user_id
		WHERE phpbb_purchases.purchase_id = 1493 AND phpbb_orders.order_status IN (0,1,2)
		GROUP BY phpbb_users.username,
				 phpbb_lots.lot_name
		ORDER BY phpbb_users.username, phpbb_lots.lot_name LIMIT 500 , 100";

	//echo ($sql);

	$result=$db->sql_query($sql);

	$i=0;
	$users = '';
	while ($row = $db->sql_fetchrow($result)){
		$i++;
		$usr=str_replace(" ","<br>",$row['username']);
		if ($i>1) $users.=',';
		$users.= '
			'.$i.':{
			"username":"'.$usr.'",
			"brand_label":"'.$row['lot_name'].'"}';
	}
	$users='{'.$users.'}';


$template->assign_var('USERS', $users);
$template->assign_var('ORG', $org);
page_header('Луковицы');


$template->set_filenames(array(
	'body' => '1luk_body.html') // template file name -- See Templates Documentation
);	

make_jumpbox(append_sid("{$phpbb_root_path}viewforum.$phpEx"));
page_footer();

?>