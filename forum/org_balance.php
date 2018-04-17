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



//  получаем данные
$sql = "SELECT
		  SubQuery.purchase_id,
		  SubQuery.purchase_name,
		  SubQuery.purchase_url,
		  SubQuery.purchase_admin_payment,
		  SubQuery.purchase_admin_money,
		  SubQuery.purchase_admin_money_date,
		  SubQuery.user_id,
		  SubQuery.username,
		  COUNT(SubQuery.party_id) AS party_count,
		  COUNT(SubQuery.party_id) * 5 AS Ec,
		  ROUND(SUM(SubQuery.Lcost) / 50) AS Two_Percent,
		  ROUND(SUM(SubQuery.Lcost) / 50) + COUNT(SubQuery.party_id) * 5 AS Itogo,
		  MAX(SubQuery.T_Ec_Date) AS Ec_Date
		FROM (SELECT
			phpbb_purchases.purchase_id,
			phpbb_purchases.purchase_name,
			phpbb_purchases.purchase_url,
			phpbb_purchases.purchase_admin_payment,
			phpbb_purchases.purchase_admin_money,
			phpbb_purchases.purchase_admin_money_date,
			phpbb_users.user_id,
			phpbb_users.username,
			phpbb_orders.user_id AS party_id,
			phpbb_orders.Lcost,
			phpbb_orders.T_Ec_Date
		  FROM phpbb_purchases
			LEFT OUTER JOIN phpbb_reservs
			  ON phpbb_purchases.reserv_id = phpbb_reservs.reserv_id
			LEFT OUTER JOIN phpbb_users
			  ON phpbb_reservs.user_id = phpbb_users.user_id
			LEFT OUTER JOIN (SELECT
				phpbb_orders.Purchase_id,
				phpbb_orders.user_id,
				SUM(phpbb_lots.lot_cost * phpbb_catalogs.catalog_course) AS Lcost,
				MAX(phpbb_orders.To_EC_Date) AS T_Ec_Date
			  FROM phpbb_orders
				LEFT OUTER JOIN phpbb_lots
				  ON phpbb_orders.lot_id = phpbb_lots.lot_id
				LEFT OUTER JOIN phpbb_catalogs
				  ON phpbb_lots.catalog_id = phpbb_catalogs.catalog_id
			  WHERE phpbb_orders.order_status > 3
			  AND phpbb_orders.Purchase_id IS NOT NULL
			  GROUP BY phpbb_orders.Purchase_id,
					   phpbb_orders.user_id,
					   phpbb_lots.lot_cost,
					   phpbb_catalogs.catalog_course) phpbb_orders
			  ON phpbb_purchases.purchase_id = phpbb_orders.Purchase_id
		  WHERE (phpbb_reservs.status > 4
		  OR phpbb_reservs.status = 4
		  AND phpbb_reservs.always_open = 1)
		  AND phpbb_orders.user_id IS NOT NULL) SubQuery
		WHERE SubQuery.purchase_id IS NOT NULL
		GROUP BY SubQuery.purchase_id,
				 SubQuery.purchase_name,
				 SubQuery.purchase_url,
				 SubQuery.purchase_admin_payment,
				 SubQuery.purchase_admin_money,
				 SubQuery.purchase_admin_money_date,
				 SubQuery.user_id,
				 SubQuery.username
		ORDER BY SubQuery.purchase_id DESC
		LIMIT 100";

	$result=$db->sql_query($sql);
   	while ($row = $db->sql_fetchrow($result)) {
		$template->assign_vars(dolgi,array(
			'PURCHASE_ID'				=> $row['purchase_id'],
			'PURCHASE_NAME'				=> $row['purchase_name'],
			'PURCHASE_URL'				=> $row['purchase_url'],
			'PURCHASE_ADMIN_PAYMENT'	=> $row['purchase_admin_payment'],
			'PURCHASE_ADMIN_MONEY'		=> $row['purchase_admin_money'],
			'PURCHASE_ADMIN_MONEY_DATE'	=> $row['purchase_admin_money_date'],
			'ORG_ID'					=> $row['user_id'],
			'ORG_USERNAME'				=> $row['username'],
			'PARTY_COUNT'				=> $row['party_count'],
			'EC'						=> $row['Ec'],
			'TWO_PERCENT'				=> $row['Two_Percent'],
			'ITOGO'						=> $row['Itogo'],
			'EC_DATE'					=> $row['Ec_Date']
		));
	}



$template->set_filenames(array(
	'body' => 'org_balance.html') // template file name -- See Templates Documentation
);
// Finish the script, display the page
page_footer();
?>