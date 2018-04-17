<?php
		$arh = (int)request_var('a',0);
			$options='';
 		  	$sql = 'SELECT *
 		  		FROM ' . PRODUCTCAT_TABLE;
 		  	$result = $db->sql_query($sql);

	  		$options.='<option value="0">Выбрать...</option>';
 		  	while ($row = $db->sql_fetchrow($result)) {
 		  		$options.='<option value="'.$row['productcat_id'].'">'.$row['productcat_label'].'</option>';
 		  	}
 		  	
 		  	$template->assign_vars(array(
 		  		'CATID_OPTIONS'			=> $options,
 		  	));

 		  	$sql = 'SELECT ' . USERS_TABLE . '.*, ' . USER_GROUP_TABLE . '.*
				FROM ' . USERS_TABLE . '
				JOIN ' . USER_GROUP_TABLE . ' ON ' . USERS_TABLE . '.user_id = ' . USER_GROUP_TABLE . '.user_id
				WHERE ' . USER_GROUP_TABLE . '.group_id = 8
				ORDER BY username';
			$result=$db->sql_query($sql);
		$select='<option value="-1">---</option>';
		$select.='<option value="0">Нет</option>';
      	while ($row = $db->sql_fetchrow($result)) {
			$select.='<option value="'.$row['user_id'].'">'. $row['username'].'</option>';
		}
		$select.='</select>';
					
					
			$sql = 'SELECT
					  phpbb_reservs.user_id,
					  phpbb_reservs.status,
					  phpbb_users.username,
					  phpbb_reservs.reserv_id,
					  phpbb_reservs.request_send,
					  phpbb_reservs.request_confirm,
					  phpbb_reservs.request_end,
					  phpbb_reservs.request_message,
					  phpbb_purchases.purchase_id,
					  phpbb_purchases.purchase_name,
					  phpbb_brands.brand_label,
					  phpbb_brands.brand_url,
					  phpbb_brands.brand_description,
					  phpbb_productcat.productcat_label
					FROM phpbb_reservs
					  INNER JOIN phpbb_brands
						ON phpbb_reservs.brand_id = phpbb_brands.brand_id
					  LEFT OUTER JOIN phpbb_users
						ON phpbb_users.user_id = phpbb_reservs.user_id
					  INNER JOIN phpbb_productcat
						ON phpbb_reservs.productcat_id = phpbb_productcat.productcat_id
					  LEFT OUTER JOIN phpbb_purchases
						ON phpbb_reservs.reserv_id = phpbb_purchases.reserv_id';
					if ($arh)
						$sql .= ' WHERE ' . RESERVS_TABLE . '.status = 7';
					else
						$sql .= ' WHERE ' . RESERVS_TABLE . '.status < 7';
					$sql .= ' ORDER BY UCASE(phpbb_users.username)';

						$result=$db->sql_query($sql);
      	while ($row = $db->sql_fetchrow($result)) {
			if ($row['status']>0)
				$control = '<select name="chorg'.$row['reserv_id'].'" id="chorg'.$row['reserv_id'].'" >'.$select.'<br/><a href="javascript:chorg('.$row['reserv_id'].')" class="button2">Сменить орга</a><br/>';
			else
				$control = '';
			$control .= '<a href="javascript:del_purchase('.$row['reserv_id'].')" class="button2">Удалить закупку</a><br/>';
      		switch ($row['status']) {
      			case -1 : 
					$status = "Подана заявка";
					$control = '<a href="javascript:control('.$row['reserv_id'].',true)" class="button2">Подтвердить</a> <a href="javascript:control('.$row['reserv_id'].',false)" class="button2">Отказать</a>';
					break;
      			case 0 : $status = "Отказано";break;
      			case 1 : $status = "Забронировано";break;;
      			case 2 : $status = "Откроется до ".(($row['request_end']) ? date("d-m-Y", strtotime($row['request_end'])) : '...');break;
      			case 3 : $status = "Создается";break;
				case 4 : $status = "Открыта";
					$control .= '<a href="javascript:res_purchase('.$row['reserv_id'].')" class="button2">В стадию создания</a><br/>';
					break;
				case 5 : $status = "Стоп";
					$control .= '<a href="javascript:res_purchase('.$row['reserv_id'].')" class="button2">В стадию создания</a><br/>';
					break;
				case 6  : $status = "Завершена";break;
				case 7  : $status = "В архиве";break;				
     		}
			$urls = append_sid('{$phpbb_root_path}adm.php?i=1&mode=otchet&p='.$row['purchase_id']);
			if ($row['purchase_id'] >0) {
				if ($arh) 
					$url="adm.php?i=1&a=1&op=" . $row['purchase_id'];
				else 
					$url="adm.php?i=1&op=" . $row['purchase_id'];
				$control .= '<a href="'. append_sid($url).'" class="button2">Закупка</a><br/>';
				
				$url="adm.php?i=1&mode=otchet&p=" . $row['purchase_id'];
				$control .= '<a href="'. append_sid($url).'" class="button2">Отчет по закупке</a><br/>';
		
			}
	

			$user_id = $row['user_id'];
			$arr[$user_id]['id'] = $row['user_id'];
			$arr[$user_id]['name'] = $row['username'];
			$arr[$user_id]['reservs'][] = array(
   					'brandname'			=> $row['brand_label'],
   					'purchase_name'		=> $row['purchase_name'],
   					'purchase_id'		=> $row['purchase_id'],
   					'categor'			=> $row['productcat_label'],
   					'info'				=> $row['brand_description'],
   					'url'				=> $row['brand_url'],
					
   					'reserv_id'			=> $row['reserv_id'],
					'status'			=> $status,
   					'request_send'		=> date("d-m-Y", strtotime($row['request_send'])),
   					'request_confirm'	=> ($row['request_confirm']) ? date("d-m-Y", strtotime($row['request_confirm'])) : '',
   					'request_end'		=> ($row['request_end']) ? date("d-m-Y", strtotime($row['request_end'])) : '',
   					'request_control'	=> $control,
   					'request_message'	=> $row['request_message']
			);
		}
		
		if (is_array($arr))
		foreach ($arr as $k=>$v){
      		$template->assign_block_vars('user',array(
					'ID'		=> $v['id'],
					'NAME'		=> $v['name']
			));
			foreach ($v['reservs'] as $pk=>$pv)
      		$template->assign_block_vars('user.myreservs',array(
   					'BRANDNAME'			=> $pv['brandname'],
   					'PURCHASE_NAME'		=> $pv['purchase_name'],
   					'PURCHASE_ID'		=> $pv['purchase_id'],
					'CATEGOR'			=> $pv['categor'],
					'INFO'				=> $pv['info'],
					'URL'				=> $pv['url'],
					'STATUS'			=> $pv['status'],
					'RESERV_ID'			=> $pv['reserv_id'],
   					'REQUEST_SEND'		=> date("d-m-Y", strtotime($pv['request_send'])),
   					'REQUEST_CONFIRM'	=> ($pv['request_confirm']) ? date("d-m-Y", strtotime($pv['request_confirm'])) : '',
   					'REQUEST_END'		=> ($pv['request_end']) ? date("d-m-Y", strtotime($pv['request_end'])) : '',
   					'REQUEST_CONTROL' 	=> $pv['request_control'],
   					'REQUEST_MESSAGE'	=> $pv['request_message'],
   			));
		}
  	$template->assign_vars(array(
  		'ARH'			=> $arh,
  	));
		
$template->set_filenames(array(
	'body' => 'adm_reservs.html') // template file name -- See Templates Documentation
);

// Finish the script, display the page
//page_footer();
?>