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
$size = isset($_GET['size'])?$_GET['size']:100;
$page = isset($_GET['size'])?$_GET['page']:1;

	//var_dump ($_GET['filter']);
	//var_dump ($_GET['column']);
	//var_dump ($_GET['page']);
	//var_dump ($_GET['size']);
//debug_to_console( $_GET['filter'][8] )	;
function MakeSql ($row,$input){
	if ($input=='NULL' | $input=='null'| $input=='=0') {
		return ' AND '.$row.'  IS NULL';
	}
	if (substr($input,1,1)=='='){
		switch(substr($input,0,1)) {
			case '!': return ' AND '.$row.' <> UCASE ('.substr($input,2).')';  break;
			case '<': return ' AND '.$row.' <= '.floatval(substr($input,2));  break;
			case '>': return ' AND '.$row.' >= '.floatval(substr($input,2));  break;
			case '0': return ' AND '.$row.'  IS NULL';  break;
		}		
	}
	else {
		switch(substr($input,0,1)) {
			case '=': return ' AND '.$row.' = '.substr($input,1);  break;
			case '<': return ' AND '.$row.' < '.floatval(substr($input,1));  break;
			case '>': return ' AND '.$row.' > '.floatval(substr($input,1));  break;
		}		
		
		return " AND UCASE(".$row.") like UCASE ('%".$input."%')";
	}
}	
//var_dump ($_GET['filter'][0]);
//var_dump (MakeSql ($_GET['filter'][8]));
//var_dump (substr($_GET['filter'][8],1,1));

$rows = array("SubQuery.Purchase_id","phpbb_purchases.purchase_name","phpbb_users.username","SubQuery.To_Ec_D","phpbb_purchases.purchase_admin_payment",
    "SubQuery.two_percent","SubQuery.ToEc","SubQuery.itogo","phpbb_purchases.purchase_admin_money"
);

// Отчет по закупкам
	$sql ="SELECT SQL_CALC_FOUND_ROWS"
			. "	SubQuery.Purchase_id,"
			. "	phpbb_purchases.purchase_name,"
			. "	phpbb_users.username,"
			. "	SubQuery.To_Ec_D,"
			. "	phpbb_purchases.purchase_admin_payment,"
			. "	SubQuery.two_percent,"
			. "	SubQuery.ToEc,"
			. "	SubQuery.itogo,"
			. "	phpbb_purchases.purchase_admin_money AS 'zaplacheno',"
			. "	phpbb_reservs.user_id";
//var_dump ($sql);
	$sql .=" FROM "
		. "		(SELECT phpbb_orders.Purchase_id,"
		. "		COUNT(DISTINCT phpbb_orders.user_id) AS cnt,"
		. "		ROUND(SUM(phpbb_lots.lot_cost * phpbb_catalogs.catalog_course)) AS cost,"
		. "		ROUND(ROUND(SUM(phpbb_lots.lot_cost * phpbb_catalogs.catalog_course)) / 50) AS two_percent,"
		. "		COUNT(DISTINCT phpbb_orders.user_id) * 5 AS ToEc,"
		. "		ROUND(ROUND(SUM(phpbb_lots.lot_cost * phpbb_catalogs.catalog_course)) / 50) + "
		. "		COUNT(DISTINCT phpbb_orders.user_id) * 5 AS itogo,"
		. "		MIN(phpbb_orders.To_EC_Date) AS To_Ec_D"
		. "	  FROM phpbb_orders"
		. "		LEFT OUTER JOIN phpbb_lots"
		. "		  ON phpbb_orders.lot_id = phpbb_lots.lot_id"
		. "		LEFT OUTER JOIN phpbb_catalogs"
		. "		  ON phpbb_lots.catalog_id = phpbb_catalogs.catalog_id"
		. "	  WHERE phpbb_orders.order_status > 3 AND phpbb_orders.Payment_Archiv=0"
		. "	  AND phpbb_orders.Purchase_id IS NOT NULL"
		. "	  GROUP BY phpbb_orders.Purchase_id) SubQuery"
		. "	  LEFT OUTER JOIN phpbb_purchases"
		. "		ON SubQuery.Purchase_id = phpbb_purchases.purchase_id"
		. "	  LEFT OUTER JOIN phpbb_reservs"
		. "		ON phpbb_purchases.reserv_id = phpbb_reservs.reserv_id"
		. "	  LEFT OUTER JOIN phpbb_users"
		. "		ON phpbb_reservs.user_id = phpbb_users.user_id";
//var_dump ($sql);

$where="";
if (count($_GET['filter']) and ($_GET['filter']!="") ) {
	foreach ($_GET['filter'] as $key => $value) {
		$where.=MakeSql ($rows[$key],$value);
	};
	$where=" where".substr ($where,4);
	$sql .=$where;
};	

//var_dump ($_GET['filter']);

//var_dump ($where);


//var_dump ($sql);	
//	var_dump ($_GET['filter']));

if (count($_GET['column'])){
	$order = "";
	foreach ($_GET['column'] as $key => $value) {
		$order .=" ". $rows[$key] ;  
		$order .= ($value)?' desc':' ';  
		$order .=',';
	}
	$order=" order by " . substr($order,0,-1);
	$sql.=$order;
}
	
//	var_dump ($order);
	
    $sql .= " LIMIT ".$size*$page.",".$size;
//    $sql .=" LIMIT 0,".$size;
//var_dump($sql) ;

	$result=$db->sql_query($sql);

var_dump ($result);

	unset($str);
	while ($row = $db->sql_fetchrow($result)) {
		$str.=' <tr>'
			  . ' <td align="left">'. $row['Purchase_id'] .'&nbsp;</td>'
			  . ' <td align="left"><a target="_blank" href="adm.php?i=1&amp;mode=otchet&amp;p='. $row['Purchase_id'] .'#site">'. $row['purchase_name'] .'</a></td>'
			  . ' <td align="center" class="org"> <a href="ucp.php?i=pm&amp;mode=compose&amp;u='. $row['user_id'] .'">'. $row['username'] .'</a></td>'
			  . ' <td align="center">'. $row['To_Ec_D'] .'</td>'
			  . ' <td align="lefft">'. $row['purchase_admin_payment'] .'</td>'
			  . ' <td align="center">'. $row['two_percent'] .'&nbsp; р.</td>'
			  . ' <td align="center">'. $row['ToEc'] .'&nbsp; р.</td>'
			  . ' <td align="center">'. $row['itogo'] .'&nbsp; р.</td>'
			  . ' <td align="center">'. $row['zaplacheno'] .'</td>'
			. ' </tr>';
	}
	

//var_dump ($str);	
	
	//  Получаем общее количество строк в запросе
	$result=$db->sql_query('SELECT FOUND_ROWS()');
	$row_col = $db->sql_fetchrow($result);

	//$data->total_rows = $row_col[];
	$data->total_rows = $row_col ["FOUND_ROWS()"];
//	$data->rows = $str;
	
	//$data->rows = '<tr><td>a123</td><td>abc</td><td>xyz</td><td>999</td></tr>';
	echo json_encode($data); 
	//echo ('{    "total_rows"    : 100,  "data": "<tr><td>a123</td><td>abc</td><td>xyz</td><td>999</td></tr>"}');

function debug_to_console( $data ) {
    if ( is_array( $data ) )
        $output = "<script>console.log( 'Debug Objects: " . implode( ',', $data) . "' );</script>";
    else
        $output = "<script>console.log( 'Debug Objects: " . $data . "' );</script>";
    echo $output;
}	 
?>