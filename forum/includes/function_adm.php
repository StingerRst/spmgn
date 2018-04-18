<?php
/**
*
* @package phpBB3
* @version $Id$
* @copyright (c) 2005 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*

**
* @ignore
*/
define('IN_PHPBB', true);
$phpbb_root_path = (defined('PHPBB_ROOT_PATH')) ? PHPBB_ROOT_PATH : './../';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
include($phpbb_root_path . 'common.' . $phpEx);
include($phpbb_root_path . 'includes/message_parser.' . $phpEx);

			$search = array ("'([\r\n])'");
			$replace = array ("\\\\\\n");

// Start session management
$user->session_begin();
$auth->acl($user->data);
$user->setup();

@header("Content-Type: text/html;charset=utf8");

if ($user->data['user_id'] == ANONYMOUS)
	echo 'Вам необходимо <a href="ucp.php?mode=login">авторизоваться</a> для просмотры этой информации';
else{

if ((!$auth->acl_get('a_')) &&( !$user->data['is_sp_admin'] ))
{
	trigger_error('NO_ADMIN');
}
	switch ($_POST['cmd']) {
	case 'brend_upd':
			$brandurl = preg_replace("/http:\/\//",'',trim($_POST['url']));
			$brandurl = HtmlSpecialChars(preg_replace("/www./",'',$brandurl));
			$arr = explode('/',$brandurl);
			$arr = explode(' ',$arr[0]);
			$brandurl = $arr[0];
		$sql = 'UPDATE ' . BRANDS_TABLE . '
				SET brand_label=\''.trim(HtmlSpecialChars ($_POST['label'])).'\'
				, brand_url=\''.$brandurl.'\'
				, brand_description=\''.trim(preg_replace($search, $replace,HtmlSpecialChars ($db->sql_escape($_POST['description'])))).'\'
				WHERE brand_id='.((int)$_POST['brend_id']);
		$db->sql_query($sql);
		$arr = array ('status'=>"ok");
		exit (json_encode($arr));
		break;
	case 'brend_del':
		$sql = 'DELETE FROM ' . BRANDS_TABLE . '
				WHERE brand_id='.((int)$_POST['brend_id']);
		$db->sql_query($sql);
		$arr = array ('status'=>"ok");
		exit (json_encode($arr));
		break;
	case 'adm_reservs_com':
		$sql = 'UPDATE ' . RESERVS_TABLE . '
				SET request_message = "' . preg_replace($search, $replace,$_POST['comment']) . '"
				WHERE reserv_id = ' . $_POST['reserv_id'];
		$db->sql_query($sql);
		exit ('ok');
		break;
	case 'adm_chorg':
		if ((int)$_POST['org']>=0){
		$sql = 'UPDATE ' . RESERVS_TABLE . '
				SET user_id = "' . (int)$_POST['org'] . '"
				WHERE reserv_id = ' . (int)$_POST['reserv_id'];
		$db->sql_query($sql);
		exit ('ok');
		}
		break;
	case 'make_substitution':
		if ((int)$_POST['catid']>=0){
		$sql = 'UPDATE ' . RESERVS_TABLE . '
				SET productcat_id = "' . (int)$_POST['catid'] . '"
				WHERE reserv_id = ' . (int)$_POST['reserv_id'];
		$db->sql_query($sql);
		exit ('ok');
		}
		break;
	case 'control':
		$sql = 'UPDATE ' . RESERVS_TABLE . '
				SET status = ' . (($_POST['flag']=='true')?1:0) . ',
					request_confirm = "'.date('Y-m-d').'"
				WHERE reserv_id = ' . $_POST['reserv_id'];
		$db->sql_query($sql);
		exit ('ok');		
		break;
		case 'change_purchase_admin_maney':
			if ($_POST['text']) {
				$sql = 'UPDATE '.PURCHASES_TABLE.'
					SET purchase_admin_money="'.preg_replace($search, $replace,$_POST['text']).'",purchase_admin_money_date = now()
					WHERE purchase_id='.$_POST['purchase_id'];
			}
			else{	
				$sql = 'UPDATE '.PURCHASES_TABLE.'
					SET purchase_admin_money= null,purchase_admin_money_date = now()
					WHERE purchase_id='.$_POST['purchase_id'];
			}

			$db->sql_query($sql);
			exit ('ok');
			break;
	
	case 'res_purchase':
			$reserv_id = $_POST['reserv_id'];
			$sql = 'UPDATE '.RESERVS_TABLE.'
			  SET status=3 
				WHERE reserv_id='.$reserv_id.'
			 ';
			 $db->sql_query($sql);
			exit ('ok');
			break;
	case 'del_purchase':

		$reserv_id=$_POST['reserv_id'];  // Выбираем все лоты

//$reserv_id=41;  // Выбираем все лоты
		
		$sql = 'SELECT  phpbb_lots.lot_img
				FROM phpbb_reservs
				  LEFT OUTER JOIN phpbb_purchases
					ON phpbb_reservs.reserv_id = phpbb_purchases.reserv_id
				  LEFT OUTER JOIN phpbb_catalogs
					ON phpbb_purchases.purchase_id = phpbb_catalogs.purchase_id
				  LEFT OUTER JOIN phpbb_lots
					ON phpbb_catalogs.catalog_id = phpbb_lots.catalog_id
				WHERE phpbb_reservs.reserv_id = '.$reserv_id;

				$result=$db->sql_query($sql);
		while ($row = $db->sql_fetchrow($result)) {   // Перебираем все лоты
			$sql2 = "SELECT COUNT(lot_id) AS CountLots FROM phpbb_lots WHERE lot_img ='". $row['lot_img']."'"; // проверяем сколько раз встречается лот
			$result2=$db->sql_query($sql2);
			$row2 = $db->sql_fetchrow($result2);
			if ($row2['CountLots']==1) {  // Если лот встречается 1 раз то удаляем файлы
				$prop = unserialize($row['lot_img']);
					if (is_array($prop)) {
						foreach ($prop as $k=>$v){
						copy('./../images/lots/thumb/'.$v,'./../images/l/thumb/'.$v);
						copy('./../images/lots/'.$v,'./../images/l/'.$v);

						if 	(unlink('./../images/lots/thumb/'.$v))
								error_log ('Delete thumb '.$v);
							else 
								error_log ('Not Delete thumb '.$v);
							
							if 	(unlink('./../images/lots/'.$v))
								error_log ('Delete '.$v);
							else 
								error_log ('Not Delete '.$v);
						}
					}
			}
		} 

	//  удаляем посты
		$sql =' DELETE t1
				  FROM phpbb_posts AS t1
				  CROSS JOIN (SELECT
				  phpbb_posts.post_id
				FROM (SELECT
				  RIGHT(phpbb_purchases.purchase_url, LENGTH(phpbb_purchases.purchase_url) - LOCATE("t=", phpbb_purchases.purchase_url) - 1) AS topic
				FROM phpbb_purchases phpbb_purchases
				WHERE phpbb_purchases.reserv_id = '.$reserv_id.' AND LENGTH(phpbb_purchases.purchase_url) > 3) SubQuery
				  LEFT OUTER JOIN phpbb_posts
					ON SubQuery.topic = phpbb_posts.topic_id) AS t2
				USING (post_id)';
		$db->sql_query($sql);

	//  удаляем темы
		$sql =' DELETE t1
				  FROM phpbb_topics AS t1
				  CROSS JOIN (SELECT
				  phpbb_topics.topic_id
				FROM (SELECT
				  RIGHT(phpbb_purchases.purchase_url, LENGTH(phpbb_purchases.purchase_url) - LOCATE("t=", phpbb_purchases.purchase_url) - 1) AS topic
				FROM phpbb_purchases phpbb_purchases
				WHERE phpbb_purchases.reserv_id ='.$reserv_id.' AND LENGTH(phpbb_purchases.purchase_url) > 3) SubQuery
				  LEFT OUTER JOIN phpbb_topics
					ON SubQuery.topic = phpbb_topics.topic_id) AS t2
				USING (topic_id)';
		$db->sql_query($sql);


	//  удаляем заказы с удаляемыми лотами
		$sql =' DELETE t1
				  FROM phpbb_orders AS t1
				  CROSS JOIN (SELECT
				  order_id
				FROM phpbb_purchases
				  LEFT OUTER JOIN phpbb_catalogs
					ON phpbb_purchases.purchase_id = phpbb_catalogs.purchase_id
				  LEFT OUTER JOIN phpbb_lots
					ON phpbb_catalogs.catalog_id = phpbb_lots.catalog_id
				  LEFT OUTER JOIN phpbb_orders
					ON phpbb_lots.lot_id = phpbb_orders.lot_id
				WHERE phpbb_purchases.reserv_id = '.$reserv_id.' AND phpbb_orders.order_id IS NOT NULL) AS t2
				USING (order_id)';
			
		$db->sql_query($sql);
		
	//  удаляем лоты
		$sql ='DELETE t1
				  FROM phpbb_lots AS t1
				  CROSS JOIN (SELECT
				  phpbb_lots.lot_id
				FROM phpbb_purchases
				  LEFT OUTER JOIN phpbb_catalogs
					ON phpbb_purchases.purchase_id = phpbb_catalogs.purchase_id
				  LEFT OUTER JOIN phpbb_lots
					ON phpbb_catalogs.catalog_id = phpbb_lots.catalog_id
				WHERE phpbb_purchases.reserv_id = '.$reserv_id.' AND phpbb_lots.lot_id IS NOT NULL) AS t2
				USING (lot_id)';
				$db->sql_query($sql);
	//  удаляем лоты
		$sql ='DELETE t1
				  FROM phpbb_lots AS t1
				  CROSS JOIN (SELECT
				  phpbb_lots.lot_id
				FROM phpbb_purchases
				  LEFT OUTER JOIN phpbb_catalogs
					ON phpbb_purchases.purchase_id = phpbb_catalogs.purchase_id
				  LEFT OUTER JOIN phpbb_lots
					ON phpbb_catalogs.catalog_id = phpbb_lots.catalog_id
				WHERE phpbb_purchases.reserv_id = '.$reserv_id.' AND phpbb_lots.lot_id IS NOT NULL) AS t2
				USING (lot_id)';
				$db->sql_query($sql);
		
	//  удаляем каталоги
		$sql ='DELETE t1
				  FROM phpbb_catalogs AS t1
				  CROSS JOIN (SELECT
				  phpbb_catalogs.catalog_id
				FROM phpbb_purchases
				  LEFT OUTER JOIN phpbb_catalogs
					ON phpbb_purchases.purchase_id = phpbb_catalogs.purchase_id
				WHERE phpbb_purchases.reserv_id = '.$reserv_id.' AND  phpbb_catalogs.catalog_id IS NOT null) AS t2
				USING (catalog_id)';
				$db->sql_query($sql);
		
	//  удаляем закупки
		$sql = 'DELETE FROM '.PURCHASES_TABLE.'
					WHERE reserv_id = '.$reserv_id;				
		$db->sql_query($sql);
		
	// Удаляем резерв
		$sql = 'DELETE FROM '.RESERVS_TABLE.'
					WHERE reserv_id = '.$reserv_id;				
		$db->sql_query($sql);

		exit ('ok');
		break;
	}
}
?>