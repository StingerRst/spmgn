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
	
$template->assign_var('HIDE',(isset($_POST['hide_pay'])?1:0));
$hide = (isset($_POST['hide_pay'])?1:0);
//echo $hide;

// Отчет по закупкам
$sql ='SELECT
		  SubQuery.user_id,
		  SubQuery.username AS OrgName,
		  phpbb_purchases.purchase_id,
		  phpbb_purchases.purchase_name,
		  phpbb_purchases.purchase_url,
		  phpbb_purchases.purchase_description,
		  COUNT(phpbb_orders.order_id) AS Orders,
		  DATE_FORMAT(phpbb_purchases.purchase_status_start,\'%d.%m.%Y\') AS OpenDays,
		  TO_DAYS(NOW()) - TO_DAYS(MAX(phpbb_orders.Create_Date)) AS SuspendDays,
		  phpbb_productcat.productcat_id,
		  phpbb_productcat.productcat_label,
		  phpbb_productcat.productcat_forum
		FROM (SELECT
			phpbb_reservs.brand_id,
			MAX(phpbb_reservs.reserv_id) AS Reserv,
			phpbb_users.user_id,
			phpbb_users.username,
			phpbb_reservs.productcat_id
		  FROM phpbb_reservs
			LEFT OUTER JOIN phpbb_users
			  ON phpbb_reservs.user_id = phpbb_users.user_id
		  WHERE phpbb_reservs.status = 4
		  OR phpbb_reservs.status IN (5, 6)
		  AND phpbb_reservs.always_open = 1
		  GROUP BY phpbb_reservs.brand_id,
				   phpbb_users.user_id,
				   phpbb_users.username,
				   phpbb_reservs.productcat_id) SubQuery
		  LEFT OUTER JOIN phpbb_purchases
			ON SubQuery.Reserv = phpbb_purchases.reserv_id
		  LEFT OUTER JOIN phpbb_orders
			ON phpbb_purchases.purchase_id = phpbb_orders.Purchase_id
		  LEFT OUTER JOIN phpbb_productcat
			ON SubQuery.productcat_id = phpbb_productcat.productcat_id
		WHERE TO_DAYS(NOW()) - TO_DAYS(phpbb_purchases.purchase_status_start) < 250
		AND phpbb_orders.order_status <> 3
		GROUP BY phpbb_purchases.purchase_id,
				 phpbb_purchases.purchase_name,
				 phpbb_purchases.purchase_url,
				 phpbb_purchases.purchase_description,
				 phpbb_purchases.purchase_status_start,
				 SubQuery.user_id,
				 SubQuery.username,
				 phpbb_productcat.productcat_id,
				 phpbb_productcat.productcat_label,
				 phpbb_productcat.productcat_forum';
	//var_dump($sql) ;
	$result=$db->sql_query($sql);
	while ($row = $db->sql_fetchrow($result)) {
		$sql ='SELECT catalog_id, catalog_name FROM phpbb_catalogs WHERE purchase_id ='.$row['purchase_id'];
//var_dump($sql) ;
				$result2=$db->sql_query($sql);
		$catalogs='';
		while ($row2 = $db->sql_fetchrow($result2)) {
			$catalogs.='<a target="_blank" title="Прежде чем добавлять заказ - прочитайте условия закупки!" href="catalog.php?catalog_id='.$row2['catalog_id'].'">'.$row2['catalog_name'].'</a>,&nbsp;';
		}	
		/*
		if ($row['OpenDays']<14){
				$status='< 2-х недель';
		}	
		else {
			if ($row['OpenDays']<28) {
				$status='< 4-х недель';
			}
			else {
				$status='> 4-х недель';
			}
		}	
		*/
		$template->assign_block_vars('otchet', array(
           'ORGID' => $row['OrgId'],
           'ORGNAME' => $row['OrgName'],
           'PURCHASE_ID' => $row['purchase_id'],
           'PURCHASE_NAME' => $row['purchase_name'],
           'PURCHASE_URL' => $row['purchase_url'],
		   'CATALOGS' => $catalogs,
           'ORDERS' => $row['Orders'],
           'OPENDAYS' => $row['OpenDays'],
           'PRODUCTCAT_LABEL' => $row['productcat_label'],
           'PRODUCTCAT_FORUM' => $row['productcat_forum'],
           'SUSPENDDAYS' => $row['SuspendDays']
//       'ZAPLACHENO' => number_format($row['zaplacheno'],0)
		  ));
	}
	 
$template->assign_var('DATE_PRINT',date ("d.m.Y", time()));


page_header('Активные закупки');

if (strpos($_SERVER['HTTP_REFERER'],'spmgn.ru/index.php')){

	$template->assign_var('IFRAME', 1);
}	

$template->set_filenames(array(
	'body' => 'active_purchases_body.html',
));

make_jumpbox(append_sid("{$phpbb_root_path}viewforum.$phpEx"));
page_footer();

?>