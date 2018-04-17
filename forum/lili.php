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



		$sql = 'SELECT
  phpbb_users.username AS username ,
  phpbb_lots.lot_name AS brand_label,
  COUNT(phpbb_orders.order_id) AS kol
FROM phpbb_catalogs
  LEFT OUTER JOIN phpbb_lots
    ON phpbb_catalogs.catalog_id = phpbb_lots.catalog_id
  LEFT OUTER JOIN phpbb_orders
    ON phpbb_lots.lot_id = phpbb_orders.lot_id
  LEFT OUTER JOIN phpbb_users
    ON phpbb_orders.user_id = phpbb_users.user_id
WHERE phpbb_catalogs.purchase_id IN (2298, 2604) AND phpbb_users.username IS NOT NULL AND phpbb_orders.order_status = 4
GROUP BY phpbb_catalogs.purchase_id,
         phpbb_lots.lot_name,
         phpbb_users.username,
         phpbb_catalogs.catalog_id,
         phpbb_lots.lot_id,
         phpbb_lots.catalog_id,
         phpbb_orders.user_id,
         phpbb_orders.lot_id,
         phpbb_orders.order_status,
         phpbb_users.user_id
ORDER BY phpbb_users.username';		
		
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
			"brand_label":"'.$row['brand_label'].'",
			"kol":"'.$row['kol'].'"}';
	}
	$users='{'.$users.'}';

	//var_dump ($users);
	$template->assign_var('USERS', $users);



$template->set_filenames(array(
	'body' => 'lili.html') // template file name -- See Templates Documentation
);	
make_jumpbox(append_sid("{$phpbb_root_path}viewforum.$phpEx"));
page_footer();
?>