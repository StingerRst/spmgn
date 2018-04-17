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

$purchase_id = $_POST['purchase_id'];
if ($_POST['selected_orders']){
		$sql ='SELECT  username FROM phpbb_purchases   INNER JOIN phpbb_reservs ON phpbb_purchases.reserv_id = phpbb_reservs.reserv_id
				LEFT OUTER JOIN phpbb_users ON phpbb_reservs.user_id = phpbb_users.user_id WHERE phpbb_purchases.purchase_id ='.$purchase_id;
		$result = $db->sql_query($sql);
		$row = $db->sql_fetchrow($result);
		$org = $row['username'];
		$sql = 'SELECT
				  phpbb_users.username,
				  phpbb_brands.brand_label
				FROM phpbb_orders
				  LEFT OUTER JOIN phpbb_users
					ON phpbb_orders.user_id = phpbb_users.user_id
				  LEFT OUTER JOIN phpbb_lots
					ON phpbb_orders.lot_id = phpbb_lots.lot_id
				  LEFT OUTER JOIN phpbb_catalogs
					ON phpbb_lots.catalog_id = phpbb_catalogs.catalog_id
				  LEFT OUTER JOIN phpbb_purchases
					ON phpbb_catalogs.purchase_id = phpbb_purchases.purchase_id
				  LEFT OUTER JOIN phpbb_reservs
					ON phpbb_purchases.reserv_id = phpbb_reservs.reserv_id
				  LEFT OUTER JOIN phpbb_brands
					ON phpbb_reservs.brand_id = phpbb_brands.brand_id
				WHERE phpbb_orders.order_id IN ('.$_POST['selected_orders'].')
				GROUP BY phpbb_users.username,
						 phpbb_brands.brand_label
				ORDER BY UCASE(phpbb_users.username)';	
		$result = $db->sql_query($sql);
//echo ($sql);		

	$i=0;
	$users = '';
	while ($row = $db->sql_fetchrow($result)){
		$i++;
		$usr=str_replace(" ","<br>",$row['username']);
		if ($i>1) $users.=',';
		$users.= '
			'.$i.':{
			"username":"'.$usr.'",
			"brand_label":"'.$row['brand_label'].'"}';
	}
	$users='{'.$users.'}';

	$template->assign_var('USERS', $users);
	$template->assign_var('ORG', $org);
}

$template->set_filenames(array(
	'body' => 'org_users_labels_body.html') // template file name -- See Templates Documentation
);	
make_jumpbox(append_sid("{$phpbb_root_path}viewforum.$phpEx"));
page_footer();
?>