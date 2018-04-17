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
if (!$user->data['is_ec_operator']){ // если не Оператор ЕЦ нет доступа

	trigger_error( 'NOT_AUTHORISED' );

}
else
{
	$template->set_filenames(array(
	'body' => 'dostavka_body.html',
));
  }

 // выбор Доставки
$sql = "SELECT phpbb_catalogs.catalog_id\n"
    . " , phpbb_catalogs.catalog_name\n"
    . "FROM\n"
    . " phpbb_catalogs\n"
    . "WHERE\n"
    . " phpbb_catalogs.purchase_id = 600\n"
    . "ORDER BY\n"
    . " phpbb_catalogs.catalog_name DESC";  
  
	$result=$db->sql_query($sql);
	while ($row = $db->sql_fetchrow($result)) {
	 $template->assign_block_vars('dostavka', array(
           'ID' => $row['catalog_id'],
           'NAME' => $row['catalog_name'],
		 ));
	}

if (isset($_POST['dostavkaselect'])){ // если выбрана доставка - получаем список
	$dostavkaselect = $_POST['dostavkaselect']; //выбраная доставка
	
$sql = "SELECT phpbb_users.user_id\n"
. "  , phpbb_users.username\n"
. " , phpbb_users.user_phone\n"
. "  , phpbb_orders.order_comment\n"
. "  , phpbb_purchases.purchase_name\n"
. "  , SubQuery.Zakupok\n"
. " FROM\n"
. "   phpbb_catalogs\n"
. " RIGHT OUTER JOIN phpbb_lots\n"
. " ON phpbb_catalogs.catalog_id = phpbb_lots.catalog_id\n"
. " RIGHT OUTER JOIN phpbb_orders\n"
. " ON phpbb_lots.lot_id = phpbb_orders.lot_id\n"
. " RIGHT OUTER JOIN phpbb_orders phpbb_orders_1\n"
. " ON phpbb_orders.user_id = phpbb_orders_1.user_id\n"
. " LEFT OUTER JOIN phpbb_lots phpbb_lots_1\n"
. " ON phpbb_orders_1.lot_id = phpbb_lots_1.lot_id\n"
. " LEFT OUTER JOIN phpbb_catalogs phpbb_catalogs_1\n"
. " ON phpbb_lots_1.catalog_id = phpbb_catalogs_1.catalog_id\n"
. " LEFT OUTER JOIN phpbb_purchases\n"
. " ON phpbb_catalogs_1.purchase_id = phpbb_purchases.purchase_id\n"
. " LEFT OUTER JOIN phpbb_users\n"
. " ON phpbb_orders.user_id = phpbb_users.user_id\n"
. " LEFT OUTER JOIN (SELECT SubQueryCnt.user_id\n"
. "                       , count(SubQueryCnt.purchase_name) AS Zakupok\n"
. "                       , SubQueryCnt.user_id AS expr2\n"
. "                  FROM\n"
. "                    (SELECT phpbb_orders.user_id\n"
. "                          , phpbb_purchases.purchase_name\n"
. "                     FROM\n"
. "                       phpbb_catalogs\n"
. "                     RIGHT OUTER JOIN phpbb_lots\n"
. "                     ON phpbb_catalogs.catalog_id = phpbb_lots.catalog_id\n"
. "                     RIGHT OUTER JOIN phpbb_orders\n"
. "                     ON phpbb_lots.lot_id = phpbb_orders.lot_id\n"
. "                     RIGHT OUTER JOIN phpbb_orders phpbb_orders_1\n"
. "                     ON phpbb_orders.user_id = phpbb_orders_1.user_id\n"
. "                     LEFT OUTER JOIN phpbb_lots phpbb_lots_1\n"
. "                     ON phpbb_orders_1.lot_id = phpbb_lots_1.lot_id\n"
. "                     LEFT OUTER JOIN phpbb_catalogs phpbb_catalogs_1\n"
. "                     ON phpbb_lots_1.catalog_id = phpbb_catalogs_1.catalog_id\n"
. "                     LEFT OUTER JOIN phpbb_purchases\n"
. "                     ON phpbb_catalogs_1.purchase_id = phpbb_purchases.purchase_id\n"
. "                     WHERE\n"
. "                       phpbb_catalogs.catalog_id = ".$dostavkaselect."\n"
. "                       AND phpbb_orders_1.order_status = 5\n"
. "                     GROUP BY\n"
. "                       phpbb_purchases.purchase_name\n"
. "                     , phpbb_orders.user_id\n"
. "                     , phpbb_orders.user_id) SubQueryCnt\n"
. "                  GROUP BY\n"
. "                    SubQueryCnt.user_id\n"
. "                  , SubQueryCnt.user_id) SubQuery\n"
. " ON phpbb_orders.user_id = SubQuery.user_id\n"
. " WHERE\n"
. "   phpbb_catalogs.catalog_id = ".$dostavkaselect."\n"
. "   AND phpbb_orders_1.order_status = 5\n"
. "   AND phpbb_orders.order_status <> 3\n"
. "   AND phpbb_orders.order_status <> 6\n"
. " GROUP BY\n"
. "   phpbb_orders.order_comment\n"
. " , phpbb_purchases.purchase_name\n"
. " , phpbb_users.user_id\n"
. " , phpbb_users.username\n"
. " , phpbb_users.user_phone\n"
. " , SubQuery.Zakupok\n"
. " ORDER BY\n"
. "   upper(phpbb_users.username)\n"
. " , upper(phpbb_purchases.purchase_name)";
	
	$result=$db->sql_query($sql);
	$rowcount= 0;
	$oldid= null;
	while ($row = $db->sql_fetchrow($result)) {
//echo $row['user_id'];
//echo '</p>';
		if ($row['user_id']==$oldid){
			$template->assign_block_vars('dostavki', array(
				'PURCHASE_NAME' => $row['purchase_name'],
				));
		}
		else {
			$template->assign_block_vars('dostavki', array(
				'ID' => $row['user_id'],
				'CNT' => $row['Zakupok'],
				'USERNAME' => $row['username'],
				'USER_PHONE' => $row['user_phone'],
				'ORDER_COMMENT' => $row['order_comment'],
				'PURCHASE_NAME' => $row['purchase_name'],
				));
		}		
		$oldid=$row['user_id'];
	}
}
	
page_header('Сводник по доставке');

						

$template->assign_vars(array(
	'DOSTAVKASELECT'	=> $dostavkaselect,
	'U_ADMIN' 		=> append_sid("{$phpbb_root_path}adm.$phpEx"),
));


make_jumpbox(append_sid("{$phpbb_root_path}viewforum.$phpEx"));
page_footer();

?>