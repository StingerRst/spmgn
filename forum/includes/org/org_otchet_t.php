<?php
	$purchase_id = request_var('p', 0); // Получаем номер закупки
	
  	$sql = 'SELECT 
			' . PURCHASES_TABLE . '.*,' . RESERVS_TABLE . '.user_id
			FROM ' . PURCHASES_TABLE . '
      		JOIN ' . RESERVS_TABLE . ' ON ' . PURCHASES_TABLE . '.reserv_id = ' . RESERVS_TABLE . '.reserv_id 
      		WHERE ' . RESERVS_TABLE . '.user_id = '.$user->data['user_id'].'
			AND ' . PURCHASES_TABLE . '.purchase_id = '.$purchase_id;
   	$result=$db->sql_query($sql);
	$row = $db->sql_fetchrow($result);
	
	if (!$row){
		trigger_error('Нет доступа');
	}
	// передаем в шаблон отребуты по закупке
  	$template->assign_vars(array(
  		'OPLATA_DATE'				=> date("d.m.Y",(time()+3600*24*2))  ,
  		'PURCHASE_NAME'				=> $row['purchase_name'],
  		'PURCHASE_ID'				=> $row['purchase_id'],
  		'ORG_ID'					=> $row['user_id'],
  		'PURCHASE_URL'				=> append_sid($row['purchase_url']),
  		'PAYMENT_INFO'				=> $row['payment_info'],
  		'PURCHASE_ADMIN_PAYMENT'	=> $row['purchase_admin_payment'],
  		'PURCHASE_ADMIN_MONEY'		=> $row['purchase_admin_money'],
		'SORT'						=> (isset($_POST['sort_lot'])?1:0),
		'HIDE'						=> (isset($_POST['hide_refusals'])?1:0),
  	));
	$hide = (isset($_POST['hide_refusals'])?1:0); // Получаем из шаблока параметр "скрыть отказы"
	// Получаем основные данные по закупке
	$sql = 'SELECT 
		' . CATALOGS_TABLE . '.catalog_id,
		' . CATALOGS_TABLE . '.catalog_name,
		' . CATALOGS_TABLE . '.catalog_course,
		' . CATALOGS_TABLE . '.catalog_valuta,
		' . CATALOGS_TABLE . '.catalog_orgrate,
		' . LOTS_TABLE . '.lot_cost,
		' . LOTS_TABLE . '.lot_id,
		' . LOTS_TABLE . '.lot_name,
		' . LOTS_TABLE . '.lot_orgrate,
		' . ORDERS_TABLE . '.order_comment,
		' . ORDERS_TABLE . '.order_delivery,
		' . ORDERS_TABLE . '.order_id,
		' . ORDERS_TABLE . '.order_org,
		' . ORDERS_TABLE . '.order_properties,
		' . ORDERS_TABLE . '.order_status,
		' . PURCHASES_ORSERS_TABLE . '.payment_card,
		' . PURCHASES_ORSERS_TABLE . '.payment_date,
		' . PURCHASES_ORSERS_TABLE . '.payment_money,
		' . PURCHASES_ORSERS_TABLE . '.payment_text,
		' . PURCHASES_ORSERS_TABLE . '.payment_time,
		' . PURCHASES_ORSERS_TABLE . '.puor_comment,
		' . PURCHASES_ORSERS_TABLE . '.puor_discount,
		' . PURCHASES_ORSERS_TABLE . '.puor_monye,
		' . PURCHASES_ORSERS_TABLE . '.puor_id,
		' . PURCHASES_ORSERS_TABLE . '.personal_puor,
		' . USERS_TABLE . '.user_id,
		' . USERS_TABLE . '.username,
		' . USERS_TABLE . '.user_phone,
		' . USERS_TABLE . '.user_realname,
		phpbb_user_purchases_count_group.PCount
		FROM ' . ORDERS_TABLE . '
   		JOIN ' . LOTS_TABLE . ' ON ' . ORDERS_TABLE . '.lot_id = ' . LOTS_TABLE . '.lot_id 
   		JOIN ' . CATALOGS_TABLE . ' ON ' . CATALOGS_TABLE . '. catalog_id = ' . LOTS_TABLE . '. catalog_id
   		JOIN ' . PURCHASES_TABLE . ' ON ' . CATALOGS_TABLE . '. purchase_id = ' . PURCHASES_TABLE . '. purchase_id
   		JOIN ' . RESERVS_TABLE . ' ON ' . PURCHASES_TABLE . '.reserv_id = ' . RESERVS_TABLE . '.reserv_id 
   		JOIN ' . BRANDS_TABLE . ' ON ' . RESERVS_TABLE . '.brand_id = ' . BRANDS_TABLE . '.brand_id 
   		JOIN ' . PRODUCTCAT_TABLE . ' ON ' . RESERVS_TABLE . '.productcat_id = ' . PRODUCTCAT_TABLE . '.productcat_id
   		JOIN ' . PURCHASES_ORSERS_TABLE . ' ON ' . PURCHASES_ORSERS_TABLE . '.purchase_id = ' . PURCHASES_TABLE . '.purchase_id
			AND ' . PURCHASES_ORSERS_TABLE . '.user_id = ' . ORDERS_TABLE . '.user_id
		JOIN ' . USERS_TABLE . ' ON ' . ORDERS_TABLE . '.user_id = ' . USERS_TABLE . '.user_id
		LEFT OUTER JOIN phpbb_user_purchases_count_group
			ON phpbb_users.user_id = phpbb_user_purchases_count_group.user_id	
		WHERE ' . PURCHASES_TABLE . '.purchase_id = '.$purchase_id;
	if ($hide) $sql .= ' AND ' . ORDERS_TABLE . '.order_status <> 3 ';
	$sql .= ' ORDER BY ' . ORDERS_TABLE . '.order_id';
//	echo $sql;
		$result=$db->sql_query($sql);
	// Перебираем результат запроса и создаем массив с данными
	while ($row = $db->sql_fetchrow($result)) {
		$orders[$row['order_id']]['id'] = $row['order_id'];
		$orders[$row['order_id']]['state'] = $row['order_status'];
		$orders[$row['order_id']]['org_fee'] = (($row['order_org']!=0)?$row['order_org']:$row['lot_orgrate']);
		$orders[$row['order_id']]['delivery'] = $row['order_delivery'];
		$orders[$row['order_id']]['user_id'] = $row['user_id'];
		$orders[$row['order_id']]['lot_id'] = $row['lot_id'];
		$orders[$row['order_id']]['lot_name'] = str_replace(array('"'), '\"',$row['lot_name']);
		$orders[$row['order_id']]['catalog_id'] = $row['catalog_id'];
		$orders[$row['order_id']]['catalog_name'] = str_replace(array('"'), '\"',$row['catalog_name']);
		$orders[$row['order_id']]['price_u'] = ($row['lot_cost'])?($row['lot_cost']):'0';
		$orders[$row['order_id']]['price'] = ($row['lot_cost']*$row['catalog_course'])?($row['lot_cost']*$row['catalog_course']):'0';
		$orders[$row['order_id']]['comment'] = preg_replace(array("/[\r]/s","/[\n]/s","/\"/s"),array("","\\\\n","\\\""),$row['order_comment']);
		$var=unserialize($row['order_properties']);
		$orders[$row['order_id']]['vars']=$var;
		$users[$row['user_id']]['id'] = $row['user_id'];	
		$users[$row['user_id']]['username'] = $row['username'];	
		$users[$row['user_id']]['userphone'] = $row['user_phone'];	
		$users[$row['user_id']]['userfio'] = $row['user_realname'];	
		$users[$row['user_id']]['PCount'] = ($row['PCount'])?($row['PCount']):'0';

		$sqlbl = 'SELECT * FROM 
			'. BLACKLIST_TABLE .'
			WHERE '. BLACKLIST_TABLE .'.user_id = '. $row['user_id'];
		$resultbl = $db->sql_query($sqlbl);
		$rowbl = $db->sql_fetchrow($resultbl);
		
		$users[$row['user_id']]['bl_info'] = preg_replace(array("/[\r]/s","/[\n]/s","/\"/s"),array("","\\\\n","\\\""),(($rowbl)?$rowbl['text']:''));	
		$users[$row['user_id']]['discount'] = ($row['puor_discount'])?$row['puor_discount']:0;	
		$users[$row['user_id']]['cinfo'] = '';	
		$users[$row['user_id']]['comment'] = preg_replace(array("/[\r]/s","/[\n]/s","/\"/s"),array("","\\\\n","\\\""),$row['puor_comment']);	
		$users[$row['user_id']]['payment_time'] = $row['payment_time'];	
		$users[$row['user_id']]['payment_date'] = (($row['payment_date']) ? date("d-m-Y", strtotime($row['payment_date'])) : "");
		$users[$row['user_id']]['payment_money'] = $row['payment_money'];	
		$users[$row['user_id']]['money'] = ($row['puor_monye'])?$row['puor_monye']:0;	
		$users[$row['user_id']]['payment_card'] = $row['payment_card'];	
		$users[$row['user_id']]['pf_card_num'] = '-';	
		$users[$row['user_id']]['payment_text'] = $row['payment_text'];	
		$users[$row['user_id']]['personal_puor'] = $row['personal_puor'];	
		$users[$row['user_id']]['puor_id'] = $row['puor_id'];	

		if ($row['order_status']> 3) 
			$users[$row['user_id']]['user_ec'] =1;
		else
			$users[$row['user_id']]['user_ec'] =0;
			$users[$row['user_id']]['order'][$row['order_id']] = $orders[$row['order_id']];

		$sql = 'SELECT count(DISTINCT ' . PURCHASES_TABLE . '.purchase_id) AS cnt
				FROM ' . ORDERS_TABLE . '
				JOIN ' . LOTS_TABLE . ' ON ' . ORDERS_TABLE . '.lot_id = ' . LOTS_TABLE . '.lot_id 
				JOIN ' . CATALOGS_TABLE . ' ON ' . CATALOGS_TABLE . '.catalog_id = ' . LOTS_TABLE . '.catalog_id
				JOIN ' . PURCHASES_TABLE . ' ON ' . CATALOGS_TABLE . '.purchase_id = ' . PURCHASES_TABLE . '.purchase_id
				JOIN ' . RESERVS_TABLE . ' ON ' . PURCHASES_TABLE . '.reserv_id = ' . RESERVS_TABLE . '.reserv_id 
				WHERE (' . RESERVS_TABLE . '.status = 6
				OR ' . RESERVS_TABLE . '.status = 7)
				AND ' . ORDERS_TABLE . '.user_id = '.$row['user_id'];

				//				AND ' . ORDERS_TABLE . '.order_status IN (4,5)';

//echo $sql;			
		$result1=$db->sql_query($sql);
		$row1 = $db->sql_fetchrow($result1);
		$users[$row['user_id']]['rating'] = ($row1['cnt'])?$row1['cnt']:0;	
	}
//var_dump ($orders[436548]);
$q_order= array ('lot_id'=>'584165','R'=>'Ряд','R_val'=>'красный');
//var_dump ($q_order);
echo ('<br>');
$q_row=array ('01_1'=>'0','02_красный'=>'0','03_1'=>'0',);


//$q_rows[1]=array ('01_1'=>'1','02_1'=>'1','03_1'=>'1',);
$q_rows[1]=$q_row;
//var_dump ($q_rows);
echo ('<br>');
// 
$out='no';
for ($i=1; $i<99; $i++) {   // переборка рядов
	if ($out=='yes') break; // флаг выхода из переборки рядоа
	foreach ($q_rows[$i] as $key => $value) {   // перебираем ряд
	if (substr($key, 3)==$q_order['R_val']) { 	// если значение ряда совпадает
			if ($value=="0") {					// если елемент в ряду не учтен
				$q_rows[$i][$key]='1';			// Учитываем
				$out='yes';						// ставим флаг выхода из ряда
				break;							// Прерываем цикл
			}
		$q_rows[$i+1]=	$q_row;					// не влезло - добавляем ряд
		}
	}
}

//var_dump ($q_rows);


// Получаем список лотов звкупки
$sql = 'SELECT phpbb_orders.lot_id, phpbb_lots.lot_bundle
		FROM phpbb_catalogs
		  LEFT OUTER JOIN phpbb_lots
			ON phpbb_catalogs.catalog_id = phpbb_lots.catalog_id
		  LEFT OUTER JOIN phpbb_orders
			ON phpbb_lots.lot_id = phpbb_orders.lot_id
		WHERE phpbb_catalogs.purchase_id = 4439
		GROUP BY phpbb_orders.lot_id,
				 phpbb_lots.lot_bundle';
$result1=$db->sql_query($sql);
while ($row = $db->sql_fetchrow($result1)) {
	$lotsx['ID']=$row['lot_id'];
	$lotsx['S_key']=array_keys(unserialize($row['lot_bundle']))[0];
	$lotsx['Rows']=explode(";",unserialize($row['lot_bundle'])[$lotsx['S_key']]);
	
	//$lotsx[$row['lot_id']]=unserialize($row['lot_bundle']);
//$var=unserialize($row2['lot_properties']);
}
//var_dump ($lotsx);



	$sql = 'SELECT *
   		FROM ' . CATALOGS_TABLE . '
   		JOIN ' . PURCHASES_TABLE . ' ON ' . CATALOGS_TABLE . '. purchase_id = ' . PURCHASES_TABLE . '. purchase_id
		WHERE ' .PURCHASES_TABLE . '. purchase_id = ' . $purchase_id . '
 		ORDER BY ' . CATALOGS_TABLE . '.catalog_id';
	$result=$db->sql_query($sql);
   	while ($row = $db->sql_fetchrow($result)) {
		$catalogs[$row['catalog_id']]['id']		= $row['catalog_id'];
		$catalogs[$row['catalog_id']]['name']	= str_replace(array('"'), '\"',$row['catalog_name']);
		$sqll = 'SELECT *
			FROM ' . LOTS_TABLE . '
			JOIN ' . CATALOGS_TABLE . ' ON ' . LOTS_TABLE . '. catalog_id = ' . CATALOGS_TABLE . '. catalog_id
			WHERE ' .CATALOGS_TABLE . '. catalog_id = ' . $row['catalog_id'] . '
			ORDER BY ' . LOTS_TABLE . '.lot_name';
		$result2=$db->sql_query($sqll);
		while ($row2 = $db->sql_fetchrow($result2)) {
			$lots[$row2['lot_id']]['valuta'] 		= $row2['catalog_valuta'];
			$lots[$row2['lot_id']]['price_u'] 		= ($row2['lot_cost'])?($row2['lot_cost']):'0';
			$lots[$row2['lot_id']]['price'] 		= ($row2['lot_cost']*$row2['catalog_course'])?($row2['lot_cost']*$row2['catalog_course']):'0';
			$lots[$row2['lot_id']]['lot_id'] 		= $row2['lot_id'];
			$lots[$row2['lot_id']]['lot_name'] 		= str_replace(array('"'), '\"',$row2['lot_name']);
			$lots[$row2['lot_id']]['catalog_id'] 	= $row2['catalog_id'];
			$var=unserialize($row2['lot_properties']);
			$lots[$row2['lot_id']]['vars']			=$var;
		}
	}

	$f=0;
	if (is_array($orders)){
		$order = '';
		foreach($orders as $k=>$v){
			if ($f) $order.=',';
			$order.='
				'.$k.':{
				"id":'.$v['id'].',
				"state":'.$v['state'].',
				"user_id":'.$v['user_id'].',
				"lot_id":'.$v['lot_id'].',
				"delivery":'.$v['delivery'].',
				"org_fee":'.$v['org_fee'].',
				"comment":"'.$v['comment'].'",
				"vars":{';
					$f3=0;
					if (is_array($v['vars']))
					foreach($v['vars'] as $vk=>$vv){
						if ($f3) $order.=',';
						$order.='"'.$vk.'":"'.$vv.'"';
						$f3=1;
					}
				$order.='}
				}';
				$f=1;
		}
		$order='{'.$order.'}';
	}else{
		$order= '[ ]';
	}
	$f=0;
	if (is_array($lots)){
		$lot = '';
		foreach($lots as $k=>$v){
			if ($f) $lot.=',';
			$lot.='
				'.$k.':{
					"id":'.$v['lot_id'].',
					"name":"'.$v['lot_name'].'",
					"catalog_id":'.$v['catalog_id'].',
					"price":'.$v['price'].',
					"price_u":'.$v['price_u'].',
					"valuta":"'.$v['valuta'].'",
					"vars":{';
						$f3=0;
						if (is_array($v['vars']))
						foreach($v['vars'] as $vk=>$vv){
							if ($f3) $lot.=',';
							$values=explode(';', $vv);
							$valstr='';
							$f2=0;
							for ($j=0; $j<count($values); $j++){
								if ($values[$j]){
									if ($f2) $valstr.=',';
									$valstr.=' "'.$values[$j].'" ';
									$f2=1;
								}
							}
							$lot.='"'.$vk.'":['.$valstr.']';
							$f3=1;
						}
					$lot.='}
					}';
			$f=1;
		}
		$lot='{'.$lot.'}';
	}else{
		$lot= '[ ]';
	}
	$f=0;
	if (is_array($catalogs)){
		$catalog = '';
		foreach($catalogs as $k=>$v){
			if ($f) $catalog.=',';
			$catalog.= '"'.$k.'":{';
			$f2=0;
			foreach ($v as $k2=>$v2){
				if ($f2) $catalog.= ', ';
				$catalog.= '"'.$k2.'": "'.$v2.'"';
				$f2 = 1;
			}
			$catalog.= '}';
			$f=1;
		}
		$catalog='{'.$catalog.'}';
	}else{
		$catalog= '[ ]';
	}

	$f=0;
	if (is_array($users)){
		$user_ = '';
		foreach($users as $k=>$v){
			if ($f) $user_.=',';
			$user_.= ''.$k.':{
				"id":'.$v['id'].',
				"name":"'.$v['username'].'",
				"phone":"'.$v['userphone'].'",
				"fio":"'.$v['userfio'].'",
				"rating":'.$v['rating'].', 
				"pcount":'.$v['PCount'].', 
				"bl_info":"'.$v['bl_info'].'",
				"discount":'.(str_replace(',','.',$v['discount'])+0.1-0.1).',
				"cinfo":"'.$v['cinfo'].'",
				"comment":"'.$v['comment'].'",
				"payment_time":"'.$v['payment_time'].'",
				"payment_date":"'.$v['payment_date'].'",
				"payment_money":"'.$v['payment_money'].'",
				"money":'.$v['money'].',
				"payment_card":"'.$v['payment_card'].'",
				"pf_card_num":"'.$v['pf_card_num'].'",
				"puor_id":"'.$v['puor_id'].'",
				"payment_text":"'.preg_replace(array("/[\r]/s","/[\n]/s","/\"/s"),array("","\\\\n","\\\""),$v['payment_text']).'",
				"personal_puor":"'.preg_replace(array("/[\r]/s","/[\n]/s","/\"/s"),array("","\\\\n","\\\""),$v['personal_puor']).'",
				"orders":{';
			$f2=0;
			foreach ($v['order'] as $ok=>$ov){
				if ($f2) $user_.=',';
				$user_.='
					'.$ok.':{
					"id":'.$ov['id'].',
					"ec":'.$v['user_ec'].',}';
				$f2=1;
			}
			$user_.='}
				}';
			$f=1;
		}
		$user_='{'.$user_.'}';
	}else{
		$user_= '[ ]';
	}
  	$sql = 'SELECT *
			FROM ' . INFO_TABLE;
   	$result=$db->sql_query($sql);
	$row = $db->sql_fetchrow($result);

  	$template->assign_vars(array(
  		'ORDERS'			=> $order,
  		'ORDERS_CNT'		=> count($orders),
  		'USERS'				=> $user_,
  		'USERS_CNT'			=> count($users),
  		'CATALOGS'			=> $catalog,
  		'CATALOGS_CNT'		=> count($catalogs),
  		'LOTS'				=> $lot,
  		'LOTS_CNT'			=> count($lot),
  		'F_RATE'			=> $row['f_rate'],
		'URL'				=> $_SERVER['SERVER_NAME'].'/forum'
  	));
	
$template->set_filenames(array(
	'body' => 'org_otchet_t.html') // template file name -- See Templates Documentation
);

// Finish the script, display the page
//page_footer();
?>