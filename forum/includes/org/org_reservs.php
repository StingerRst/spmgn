<?php
			$search = array ("'([\r\n])'");
			$replace = array ("\\\\\\n");
 		  	if (isset($_POST['brandname'])&&trim($_POST['brandname'])<>''&&(int)$_POST['catid']<>0) {
				$brandurl = preg_replace("/http:\/\//",'',trim($_POST['brandurl']));
				$brandurl = HtmlSpecialChars(preg_replace("/www./",'',$brandurl));
				$arr = explode('/',$brandurl);
				$arr = explode(' ',$arr[0]);
				$brandurl = $arr[0];

		  		$sql = 'SELECT * 
						FROM ' . BRANDS_TABLE . '
						JOIN ' . RESERVS_TABLE . '
						ON ' . BRANDS_TABLE . '.brand_id = ' . RESERVS_TABLE . '.brand_id
						WHERE (' . BRANDS_TABLE . '.brand_label like "' . trim(HtmlSpecialChars ($_POST['brandname'])) . '"';
				if ($brandurl <> '')
				$sql .= 'OR (' . BRANDS_TABLE . '.brand_url like "' . $brandurl . '"
						OR ' . BRANDS_TABLE . '.brand_url like "www.' . $brandurl . '"
						OR ' . BRANDS_TABLE . '.brand_url like "http://www.' . $brandurl . '")';
				$sql .= ') AND ' . RESERVS_TABLE . '.productcat_id = ' . (int)$_POST['catid'] .'
						AND ' . RESERVS_TABLE . '.status > 0';
					$db->sql_query($sql);
					if (!($db->sql_affectedrows())) {
						$sql = 'SELECT * FROM '. BRANDS_TABLE .' 
								WHERE brand_label LIKE "'.trim(HtmlSpecialChars ($_POST['brandname'])).'" ';
						if ($brandurl <> ''){
						$sql .=	'OR brand_url like "' . $brandurl . '"
								OR brand_url like "www.' . $brandurl . '"';
						}
						$result = $db->sql_query($sql);
						$row = $db->sql_fetchrow($result);
						$db->sql_freeresult();
						if (!$row) {
							$sql = 'INSERT INTO ' . BRANDS_TABLE . ' (
									`brand_id` ,
									`brand_label` ,
									`brand_url` ,
									`brand_description` ,
									`brand_logo` 
								)
								VALUES (
									NULL ,
									"'.trim(HtmlSpecialChars ($_POST['brandname'])).'",
									"'.$brandurl.'",
									"'.trim(preg_replace($search, $replace,HtmlSpecialChars ($_POST['description']))).'",
									"")';
							$db->sql_query($sql);
							$brand_id = $db->sql_nextid();
							$db->sql_freeresult();
						}else
							$brand_id = $row['brand_id'];
						$sql = 'SELECT * FROM 
							'. RESERVS_TABLE .' 
							WHERE brand_id = '.$brand_id.'
							AND user_id = '.$user->data['user_id'];
						$result = $db->sql_query($sql);
						$row = $db->sql_fetchrow($result);
						$db->sql_freeresult();
						if (!$row) {
							$sql = 'INSERT INTO ' . RESERVS_TABLE . '(
									`reserv_id` ,
									`brand_id` ,
									`user_id` ,
									`request_send` ,
									`request_confirm` ,
									`request_end` ,
									`request_message` ,
									`status` ,
									`productcat_id` 
								)
								VALUES (
									NULL ,
									'. $brand_id .',
									'. $user->data['user_id'] .',
									"'. date("Y-m-d") .'",
									NULL ,
									"'.date("Y-m-d", strtotime($_POST['request_end'])).'" ,
									NULL ,
									-1,
									'. (int)$_POST['catid'] .')';
								$db->sql_query($sql);
							}
							
					} else 
					{};//echo '<b>Бренд свободен</b>';
 		  	}
 
 		  	$sql = 'SELECT *
 		  		FROM ' . INFO_TABLE;
 		  	$result = $db->sql_query($sql);
 		  	$row = $db->sql_fetchrow($result);
 		  	$template->assign_vars(array(
 		  		'RESERVS_INFO'		=> (($row['reservs_info'])?$row['reservs_info']:''),
 		  		'U_RESERVS_RULES'	=> (($row['reservs_rules_url'])?append_sid($row['reservs_rules_url']):''),
 		  		'RESERVS_RULES'		=> (($row['reservs_rules'])?$row['reservs_rules']:''),
 		  		'NEWS'				=> (($row['news'])?$row['news']:''),
 		  	));
			
 
 
			$options='';
 		  	$sql = 'SELECT *
 		  		FROM ' . PRODUCTCAT_TABLE.' ORDER BY  '. PRODUCTCAT_TABLE.'.productcat_label';
			$result = $db->sql_query($sql);

	  		$options.='<option value="0">Выбрать...</option>';
 		  	while ($row = $db->sql_fetchrow($result)) {
 		  		$options.='<option value="'.$row['productcat_id'].'">'.$row['productcat_label'].'</option>';
 		  	}
 		  	
 		  	$template->assign_vars(array(
 		  		'L_TITLE'				=> $user->lang['UCP_ORGROOM_' . strtoupper($mode)],
 		  		'CATID_OPTIONS'			=> $options,
// 		  		'S_UCP_ACTION'			=> $this->u_action,
 		  	));
 		  	 		  	
 		  	$sql = 'SELECT 
      		' . RESERVS_TABLE . '.*,
      		' . BRANDS_TABLE . '.*,
      		' . PRODUCTCAT_TABLE . '.productcat_label
      		FROM ' . RESERVS_TABLE . '
      		JOIN ' . BRANDS_TABLE . ' ON ' . RESERVS_TABLE . '.brand_id = ' . BRANDS_TABLE . '.brand_id 
			JOIN ' . PRODUCTCAT_TABLE . ' ON ' . PRODUCTCAT_TABLE . '.productcat_id = ' . RESERVS_TABLE . '.productcat_id
      		WHERE ' . RESERVS_TABLE . '.user_id = '.$user->data['user_id'].' and ' . RESERVS_TABLE . '.status <> 7';
      	$result=$db->sql_query($sql);
      	while ($row = $db->sql_fetchrow($result)) {
      		switch ($row['status']) {
      			case -1 : $status = "Подана заявка";break;
      			case 0  : $status = "Отказано";break;
      			case 1  : $status = "Забронировано";break;
      			case 2  : $status = "Откроется до ".(($row['request_end']) ? date("d-m-Y", strtotime($row['request_end'])) : '...');break;
      			case 3  : $status = "Создается";break;
      			case 4  : $status = "Открыта";break;
				case 5  : $status = "Стоп";break;
      			case 6  : $status = "Завершена";break;
      			case 7  : $status = "В архиве";break;
      		}
			$control = '';
			if ($row['status']==1)
				$control = "<a href='".append_sid("{$phpbb_root_path}org.$phpEx","i=1&mode=start&rid=".$row['reserv_id'])."' class='button2'>Закупка</a>";
		//		$control = "<a href='./org.php?i=1&mode=start&rid=".$row['reserv_id']."' class='button2'>Закупка</a><br><a href='' class='button2'>Продлить бронь</a>";
		//	if ($row['status']>1)
		//		$control = "<a href='' class='button2'>Продлить бронь</a>";
			$row['brand_url'] = (!preg_match("/^http:\/\/(.*)$/",$row['brand_url'],$m))?'http://'.$row['brand_url']:$row['brand_url'];
			if ($row['brand_url']=='http://') $row['brand_url']='';
      		$template->assign_block_vars('myreservs',array(
   					'BRANDNAME'			=> $row['brand_label'],
   					'PRODUCT_LABEL'		=> $row['productcat_label'],
   					'BRANDURL'			=> $row['brand_url'],
   					'STATUS'			=> $status,
   					'REQUEST_SEND'		=> date("d-m-Y", strtotime($row['request_send'])),
   					'REQUEST_CONFIRM'	=> ($row['request_confirm']) ? date("d-m-Y", strtotime($row['request_confirm'])) : '',
   					'REQUEST_END'		=> ($row['request_end']) ? date("d-m-Y", strtotime($row['request_end'])) : '',
   					'REQUEST_CONTROL' 	=> $control,
   					'REQUEST_MESSAGE'	=> $row['request_message'],
   			));
		}
 		  	$sql = 'SELECT 
      		' . RESERVS_TABLE . '.*,
      		' . BRANDS_TABLE . '.*,
      		' . PRODUCTCAT_TABLE . '.productcat_label
      		FROM ' . RESERVS_TABLE . '
      		JOIN ' . BRANDS_TABLE . ' ON ' . RESERVS_TABLE . '.brand_id = ' . BRANDS_TABLE . '.brand_id 
			JOIN ' . PRODUCTCAT_TABLE . ' ON ' . PRODUCTCAT_TABLE . '.productcat_id = ' . RESERVS_TABLE . '.productcat_id
      		WHERE ' . RESERVS_TABLE . '.user_id = 0';
      	$result=$db->sql_query($sql);
      	while ($row = $db->sql_fetchrow($result)) {
      		switch ($row['status']) {
      			case -1 : $status = "Подана заявка";break;
      			case 0  : $status = "Свободна";break;
      			case 1  : $status = "Забронировано";break;
      			case 2  : $status = "Откроется до ".(($row['request_end']) ? date("d-m-Y", strtotime($row['request_end'])) : '...');break;
      			case 3  : $status = "Создается";break;
      			case 4  : $status = "Открыта";break;
      			case 5  : $status = "Стоп";break;
      			case 6  : $status = "Завершена";break;
      		}
			$control = '';
			if ($row['status']==1)
				$control = "";
		//		$control = "<a href='./org.php?i=1&mode=start&rid=".$row['reserv_id']."' class='button2'>Закупка</a><br><a href='' class='button2'>Продлить бронь</a>";
		//	if ($row['status']>1)
		//		$control = "<a href='' class='button2'>Продлить бронь</a>";
      		$template->assign_block_vars('freereservs',array(
   					'BRANDNAME'			=> $row['brand_label'],
   					'PRODUCT_LABEL'		=> $row['productcat_label'],
   					'BRANDURL'			=> (!preg_match("/^http:\/\/(.*)$/",$row['brand_url'],$m))?'http://'.$row['brand_url']:$row['brand_url'],
   					'STATUS'			=> $status,
   					'REQUEST_SEND'		=> date("d-m-Y", strtotime($row['request_send'])),
   					'REQUEST_CONFIRM'	=> ($row['request_confirm']) ? date("d-m-Y", strtotime($row['request_confirm'])) : '',
   					'REQUEST_END'		=> ($row['request_end']) ? date("d-m-Y", strtotime($row['request_end'])) : '',
   					'REQUEST_CONTROL' 	=> $control,
   					'REQUEST_MESSAGE'	=> $row['request_message'],
   			));
		}
$template->set_filenames(array(
	'body' => 'org_reservs.html') // template file name -- See Templates Documentation
);

// Finish the script, display the page
//page_footer();
?>