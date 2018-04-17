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
//var_dump ($purchase_id);
//var_dump ($_POST['selected_orders']);
if ($_POST['selected_orders']){
		$sql = 'SELECT
				  phpbb_purchases.purchase_name,
				  phpbb_users.username,
				  phpbb_catalogs.catalog_name,
				  phpbb_lots.lot_name,
				  phpbb_lots.lot_article,
				  COUNT(phpbb_orders.order_id) AS kolvo,
				  phpbb_orders.order_comment,
				  phpbb_orders.order_properties
				FROM phpbb_orders
				  LEFT OUTER JOIN phpbb_users
					ON phpbb_orders.user_id = phpbb_users.user_id
				  LEFT OUTER JOIN phpbb_lots
					ON phpbb_orders.lot_id = phpbb_lots.lot_id
				  LEFT OUTER JOIN phpbb_catalogs
					ON phpbb_lots.catalog_id = phpbb_catalogs.catalog_id
				  LEFT OUTER JOIN phpbb_purchases
					ON phpbb_orders.Purchase_id = phpbb_purchases.purchase_id
				WHERE phpbb_orders.purchase_id ='.$purchase_id.' AND phpbb_orders.order_id IN ('.$_POST['selected_orders'].')
				GROUP BY phpbb_purchases.purchase_name,
						 phpbb_users.username,
						 phpbb_catalogs.catalog_name,
						 phpbb_lots.lot_name,
						 phpbb_lots.lot_article,
						 phpbb_orders.order_comment,
						 phpbb_orders.order_properties				  
				ORDER BY Ucase(phpbb_users.username),
						 Ucase(phpbb_catalogs.catalog_name),
						 Ucase(phpbb_lots.lot_name)';
		//echo ($sql);				 
		$result = $db->sql_query($sql);
	$i=0;
	$Oldi=1;
	$OldUsername='';
	while ($row = $db->sql_fetchrow($result)){
		$i++;
		$orders[$i]['LastRecord'] = 0;
		$orders[$i]['username'] = $row['username'];
		if ($row['username'] != $OldUsername) {
			$orders[$i]['NewUser'] = 1;
			$tovarcount=0;
			if ($i >1 ) {
			$orders[$OldI]['UCnt'] = $UCnt;
			$orders[$i-1]['LastRecord'] = 1;
			}
			$OldI=$i;
			$UCnt=0;
		} else {
			$orders[$i]['NewUser'] = 0;
		}
		//$orders[$i]['user_realname'] = $row['user_realname'];


	
		$UCnt++;
		$tovarcount= $tovarcount+(int)($row['kolvo']);
		$OldUsername = $row['username'];
		$orders[$i]['order_comment'] = $row['order_comment'];
		$massiv= unserialize($row['order_properties']);
		$order_properties='';
		foreach($massiv as $key => $value)
		{
		   $order_properties.="<b>".$key.":</b> ". $value. ";";
		} 
		
		$orders[$i]['order_properties'] = $order_properties;
		$orders[$i]['lot_name'] =($row['lot_article'])? "(".$row['lot_article'].") ".$row['lot_name']:  $row['lot_name'];
		$orders[$i]['catalog_name'] = $row['catalog_name'];
		$orders[$i]['kolvo'] = number_format($row['kolvo'],0);
		$orders[$i]['TovarCount']=$tovarcount;
		$purchase_name=$row['purchase_name'];
//var_dump ($orders[$i]['catalog_name']);
//echo ('<br>');

	}	
	$orders[$OldI]['UCnt'] = $UCnt;
	$orders[$i]['LastRecord'] = '1';
//echo ($i);
//echo ('<br>');	
//var_dump($purchase_name);
//var_dump($orders);
	$template->assign_var('PURCHASE_NAME', $purchase_name);
	foreach($orders as $k=>$v){
	$template->assign_block_vars('order', array(
           'USERNAME' => $orders[$k]['username'],
           'ORDER_COMMENT' => $orders[$k]['order_comment'],
           'ORDER_PROPERTIES' =>$orders[$k]['order_properties'],
		   'CATALOG_NAME' => $orders[$k]['catalog_name'],
		   'LOT_NAME' => $orders[$k]['lot_name'],
           'KOLVO' => number_format($orders[$k]['kolvo'],0),
		   'NEW_USER' => $orders[$k]['NewUser'],
		   'LAST_RECORD' => $orders[$k]['LastRecord'],
		   'LOT_COUNT' => $orders[$k]['UCnt'],
		   'TOVAR_COUNT' => $orders[$k]['TovarCount'],
		   ));
	}

}

$template->set_filenames(array(
	'body' => 'org_users_print_body.html') // template file name -- See Templates Documentation
);	
make_jumpbox(append_sid("{$phpbb_root_path}viewforum.$phpEx"));
page_footer();
?>