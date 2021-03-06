<?php

	$purchase_id = request_var('p', 0);;
  	$sql = 'SELECT 
			' . PURCHASES_TABLE . '.purchase_name,
			' . PURCHASES_TABLE . '.purchase_id,
			' . PURCHASES_TABLE . '.purchase_url,
			' . PURCHASES_TABLE . '.payment_info,
			' . PURCHASES_TABLE . '.purchase_admin_payment,
			' . PURCHASES_TABLE . '.purchase_admin_money 
			FROM ' . PURCHASES_TABLE . '
      		JOIN ' . RESERVS_TABLE . ' ON ' . PURCHASES_TABLE . '.reserv_id = ' . RESERVS_TABLE . '.reserv_id 
      		WHERE ' . PURCHASES_TABLE . '.purchase_id = '.$purchase_id;
   	$result=$db->sql_query($sql);
	$row = $db->sql_fetchrow($result);
	
	if (!$row){
		trigger_error('Нет доступа');
	}
	
  	$template->assign_vars(array(
  		'PURCHASE_NAME'				=> $row['purchase_name'],
  		'PURCHASE_ID'				=> $row['purchase_id'],
  		'PURCHASE_URL'				=> append_sid($row['purchase_url']),
  		'PAYMENT_INFO'				=> $row['payment_info'],
  		'PURCHASE_ADMIN_PAYMENT'	=> $row['purchase_admin_payment'],
  		'PURCHASE_ADMIN_MONEY'		=> $row['purchase_admin_money'],
		'SORT'						=> (isset($_POST['sort_lot'])?1:0),
		'HIDE'						=> (isset($_POST['hide_refusals'])?1:0),
  	));

	$sql = 'SELECT 
			  phpbb_catalogs.catalog_id,
			  phpbb_catalogs.catalog_name,
			  phpbb_lots.lot_id,
			  phpbb_lots.lot_name,
			  phpbb_orders.catalog_course,
			  phpbb_orders.catalog_valuta,
			  phpbb_orders.lot_cost,
			  phpbb_orders.lot_orgrate,
			  phpbb_orders.order_comment,
			  phpbb_orders.order_delivery,
			  phpbb_orders.order_id,
			  phpbb_orders.order_org,
			  phpbb_orders.order_properties,
			  phpbb_orders.order_status,
			  phpbb_purchases_orsers.payment_card,
			  phpbb_purchases_orsers.payment_date,
			  phpbb_purchases_orsers.payment_money,
			  phpbb_purchases_orsers.payment_text,
			  phpbb_purchases_orsers.payment_time,
			  phpbb_purchases_orsers.personal_puor,
			  phpbb_purchases_orsers.puor_comment,
			  phpbb_purchases_orsers.puor_discount,
			  phpbb_purchases_orsers.puor_id,
			  phpbb_purchases_orsers.puor_monye,
			  phpbb_user_purchases_count_group.PCount,
			  phpbb_users.user_id,
			  phpbb_users.user_phone,
			  phpbb_users.user_realname,
			  phpbb_users.username
			FROM phpbb_orders
			  INNER JOIN phpbb_lots
				ON phpbb_orders.lot_id = phpbb_lots.lot_id
			  INNER JOIN phpbb_catalogs
				ON phpbb_lots.catalog_id = phpbb_catalogs.catalog_id
			  LEFT OUTER JOIN phpbb_users
				ON phpbb_orders.user_id = phpbb_users.user_id
			  LEFT OUTER JOIN phpbb_user_purchases_count_group
				ON phpbb_orders.user_id = phpbb_user_purchases_count_group.user_id
			  LEFT OUTER JOIN phpbb_purchases_orsers
				ON phpbb_orders.user_id = phpbb_purchases_orsers.user_id
				AND phpbb_orders.Purchase_id = phpbb_purchases_orsers.purchase_id
			  LEFT OUTER JOIN phpbb_purchases
				ON phpbb_orders.Purchase_id = phpbb_purchases.purchase_id
		WHERE ' . PURCHASES_TABLE . '.purchase_id = '.$purchase_id.'
		AND phpbb_lots.lot_id IS NOT NULL
		ORDER BY ' . LOTS_TABLE . '.lot_name';
//echo $sql;
//echo ('</br>');
		$result=$db->sql_query($sql);
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
		
		
		$lots[$row['lot_id']]['id'] = $row['lot_id'];
		$lots[$row['lot_id']]['price'] = $row['lot_cost']*$row['catalog_course'];
		$lots[$row['lot_id']]['price_u'] = $row['lot_cost'];
		$lots[$row['lot_id']]['valuta'] = $row['catalog_valuta'];
		
		$catalogs[$row['catalog_id']]['id']=$row['catalog_id'];
		$catalogs[$row['catalog_id']]['name']=str_replace(array('"'), '\"',$row['catalog_name']);;
		
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
		$users[$row['user_id']]['bl_info'] = preg_replace(array("/[\r]/s","/[\n]/s","/\"/s"),array("","\\\\n","\\\""),($rowbl)?$rowbl['text']:'');	
		//$users[$row['user_id']]['bl_info'] = ($rowbl)?$rowbl['text']:'';	
		$users[$row['user_id']]['discount'] = ($row['puor_discount'])?$row['puor_discount']:0;	
		$users[$row['user_id']]['cinfo'] = '';	
		$users[$row['user_id']]['comment'] = preg_replace(array("/[\r]/s","/[\n]/s","/\"/s"),array("","\\\\n","\\\""),$row['puor_comment']);	
		$users[$row['user_id']]['payment_time'] = $row['payment_time'];	
		$users[$row['user_id']]['payment_date'] = (($row['payment_date']) ? date("d-m-Y", strtotime($row['payment_date'])) : date("d-m-Y"));
		$users[$row['user_id']]['payment_money'] = $row['payment_money'];	
		$users[$row['user_id']]['money'] = ($row['puor_monye'])?$row['puor_monye']:0;	
		$users[$row['user_id']]['payment_card'] = $row['payment_card'];	
		$users[$row['user_id']]['pf_card_num'] = '-';	
		$users[$row['user_id']]['puor_id'] = $row['puor_id'];	
		$users[$row['user_id']]['payment_text'] = $row['payment_text'];	
		$users[$row['user_id']]['personal_puor'] = $row['personal_puor'];	
		$users[$row['user_id']]['order'][$row['order_id']] = $orders[$row['order_id']];
		$users[$row['user_id']]['order'][$row['order_id']]['id'] = $row['order_id'];
		$users[$row['user_id']]['order'][$row['order_id']]['state'] = $row['order_status'];
		$users[$row['user_id']]['order'][$row['order_id']]['org_fee'] = (($row['order_org']!=0)?$row['order_org']:$row['lot_orgrate']);
		$users[$row['user_id']]['order'][$row['order_id']]['delivery'] = $row['order_delivery'];
		$users[$row['user_id']]['order'][$row['order_id']]['lot_id'] = $row['lot_id'];
		$users[$row['user_id']]['order'][$row['order_id']]['lot_name'] = str_replace(array('"'), '\"',$row['lot_name']);
		$users[$row['user_id']]['order'][$row['order_id']]['catalog_id'] = $row['catalog_id'];
		$users[$row['user_id']]['order'][$row['order_id']]['catalog_name'] = str_replace(array('"'), '\"',$row['catalog_name']);
		$users[$row['user_id']]['order'][$row['order_id']]['price'] = $row['lot_cost']*$row['catalog_course'];
		$users[$row['user_id']]['order'][$row['order_id']]['price_u'] = $row['lot_cost'];
		$users[$row['user_id']]['order'][$row['order_id']]['valuta'] = $row['catalog_valuta'];
		$users[$row['user_id']]['order'][$row['order_id']]['comment'] = preg_replace(array("/[\r]/s","/[\n]/s","/\"/s","/\\\\/s"),array("","\\\\n","\\\"","\\"),$row['order_comment']);
		
		$var=unserialize($row['order_properties']);
		$users[$row['user_id']]['order'][$row['order_id']]['vars']=$var;
//echo ('!!!');
//echo ('</br>');
		$sql = 'SELECT count(DISTINCT ' . PURCHASES_TABLE . '.purchase_id) AS cnt
				FROM ' . ORDERS_TABLE . '
				JOIN ' . LOTS_TABLE . ' ON ' . ORDERS_TABLE . '.lot_id = ' . LOTS_TABLE . '.lot_id 
				JOIN ' . CATALOGS_TABLE . ' ON ' . CATALOGS_TABLE . '. catalog_id = ' . LOTS_TABLE . '. catalog_id
				JOIN ' . PURCHASES_TABLE . ' ON ' . CATALOGS_TABLE . '. purchase_id = ' . PURCHASES_TABLE . '. purchase_id
				JOIN ' . RESERVS_TABLE . ' ON ' . PURCHASES_TABLE . '.reserv_id = ' . RESERVS_TABLE . '.reserv_id 
				WHERE (' . RESERVS_TABLE . '.status = 6
				OR ' . RESERVS_TABLE . '.status = 7)
				AND ' . ORDERS_TABLE . '.user_id = '.$row['user_id'];
//				AND ' . ORDERS_TABLE . '.order_status IN (4,5)';
//echo $sql;
//echo ('</br>');
		$result1=$db->sql_query($sql);
		$row1 = $db->sql_fetchrow($result1);
		$users[$row['user_id']]['rating'] = ($row1['cnt'])?$row1['cnt']:0;	
	}
	$sql = 'SELECT *
   		FROM ' . CATALOGS_TABLE . '
   		JOIN ' . PURCHASES_TABLE . ' ON ' . CATALOGS_TABLE . '. purchase_id = ' . PURCHASES_TABLE . '. purchase_id
		WHERE ' .PURCHASES_TABLE . '. purchase_id = ' . $purchase_id . '
 		ORDER BY ' . CATALOGS_TABLE . '.catalog_id';
	$sql = 'SELECT
		  phpbb_catalogs.catalog_id,
		  phpbb_orders.Purchase_id,
		  phpbb_catalogs.catalog_orgrate,
		  phpbb_catalogs.catalog_hide,
		  phpbb_catalogs.catalog_bundle,
		  phpbb_catalogs.catalog_name,
		  phpbb_catalogs.catalog_properties,
		  phpbb_catalogs.catalog_foruser,
		  phpbb_catalogs.catalog_course,
		  phpbb_catalogs.catalog_valuta,
		  phpbb_catalogs.Create_Date
		FROM phpbb_orders
		  LEFT OUTER JOIN phpbb_lots
			ON phpbb_orders.lot_id = phpbb_lots.lot_id
		  LEFT OUTER JOIN phpbb_catalogs
			ON phpbb_lots.catalog_id = phpbb_catalogs.catalog_id
		WHERE phpbb_orders.Purchase_id = ' . $purchase_id . '
		GROUP BY phpbb_catalogs.catalog_id,
				 phpbb_orders.Purchase_id,
				 phpbb_catalogs.catalog_orgrate,
				 phpbb_catalogs.catalog_hide,
				 phpbb_catalogs.catalog_bundle,
				 phpbb_catalogs.catalog_name,
				 phpbb_catalogs.catalog_properties,
				 phpbb_catalogs.catalog_foruser,
				 phpbb_catalogs.catalog_course,
				 phpbb_catalogs.catalog_valuta,
				 phpbb_catalogs.Create_Date
		ORDER BY phpbb_catalogs.catalog_id';	
	$result=$db->sql_query($sql);
   	while ($row = $db->sql_fetchrow($result)) {
		$catalogs[$row['catalog_id']]['id']		= $row['catalog_id'];
		$catalogs[$row['catalog_id']]['name']	= str_replace(array('"'), '\"',$row['catalog_name']);
		if ($row['catalog_id']){
			$sqll = 'SELECT *
				FROM ' . LOTS_TABLE . '
				JOIN ' . CATALOGS_TABLE . ' ON ' . LOTS_TABLE . '. catalog_id = ' . CATALOGS_TABLE . '. catalog_id
				WHERE ' .CATALOGS_TABLE . '. catalog_id = ' . $row['catalog_id'] . '
				ORDER BY ' . LOTS_TABLE . '.lot_name';
			$result2=$db->sql_query($sqll);
		}
//echo $sqll;
//echo ('</br>');
		
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
					"id":'.$ov['id'].',}';
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
	'body' => 'adm_otchet.html') // template file name -- See Templates Documentation
);

// Finish the script, display the page
//page_footer();
?>