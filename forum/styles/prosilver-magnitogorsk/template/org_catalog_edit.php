<?php

 		$options='';
      	$catalog_id = request_var('cat', '');
      	$sql = 'SELECT
			'. BRANDS_TABLE . '.*,
			'. PURCHASES_TABLE .'.*,
			'. PRODUCTCAT_TABLE .'.productcat_label,
			'. USERS_TABLE .'.user_id,
			'. USERS_TABLE .'.username
      		FROM '. CATALOGS_TABLE .'
      		JOIN '. PURCHASES_TABLE .'
      			ON '. CATALOGS_TABLE .'.purchase_id = '. PURCHASES_TABLE .'.purchase_id
      		JOIN '. RESERVS_TABLE .'
      			ON '. PURCHASES_TABLE .'.reserv_id = '. RESERVS_TABLE .'.reserv_id 
      		JOIN ' . BRANDS_TABLE . ' 
				ON ' . RESERVS_TABLE . '.brand_id = ' . BRANDS_TABLE . '.brand_id 
			JOIN ' . USERS_TABLE . '
				ON ' . RESERVS_TABLE . '.user_id = ' . USERS_TABLE . '.user_id 
			JOIN ' . PRODUCTCAT_TABLE . '
				ON ' . RESERVS_TABLE . '.productcat_id = ' . PRODUCTCAT_TABLE . '.productcat_id 
			WHERE '. CATALOGS_TABLE .'.catalog_id = '. $catalog_id;// .'
//      		AND '. RESERVS_TABLE .'.user_id = '. $user->data['user_id'];
      	$result = $db->sql_query($sql);
      	$row = $db->sql_fetchrow($result); 
		$purchase_id=$row['purchase_id'];
		$template->assign_vars(array(
 		  		'PURCHASES_NAME'  	=> $row['brand_label'].' / '.$row['productcat_label'],
 		  		'PURCHASES_ID'  	=> $row['purchase_id'],
				'USER_ID'			=> $row['user_id'],
				'USERNAME'			=> $row['username'],
 		  	));
		if ($user->data['user_id'] <> $row['user_id'])	$edit=0;
      	$db->sql_freeresult();
      	$sql = 'SELECT catalog_name,catalog_orgrate, catalog_properties as prop,catalog_bundle
      		FROM '. CATALOGS_TABLE .'
      		WHERE catalog_id = '. $catalog_id;
      	$result = $db->sql_query($sql);
      	$row = $db->sql_fetchrow($result);
      	$prop = unserialize($row['prop']);
		if (is_array($prop))
		foreach ($prop as $k=>$v)
  		{
      		$values=explode(';', $v);
      		$valstr='';
      		for ($j=0; $j<count($values); $j++) 
      			$valstr.='	"'.$values[$j].'" ';
      			//$valstr.='	<input name="propvalues['.$i.'][]" type="checkbox" checked value="'.$values[$j].'">'.$values[$j];
      		$template->assign_block_vars('prop',array(
	   					'NAME'		=> $k,
	   					'VALUES'	=> $valstr,
   					));
      		}

      	$template->assign_vars(array(
 		  		'CATALOG_ID'		=> request_var('cat', ''),
 		  		'CATALOG_NAME'  	=> stripslashes($row['catalog_name']),
 		  		'CATALOG_ORGRATE'  	=> $row['catalog_orgrate'],
 		  		'CATALOG_BUNDLE'  	=> $row['catalog_bundle'],
 		  	));
		$cat_org = $row['catalog_orgrate'];
			$sql = 'SELECT
      		'. LOTS_TABLE .'.*,
			'. BRANDS_TABLE . '.*,
			'. PURCHASES_TABLE .'.*
      		FROM '. LOTS_TABLE .'
      		JOIN '. CATALOGS_TABLE .'
      			ON '. LOTS_TABLE .'.catalog_id = '. CATALOGS_TABLE .'.catalog_id
      		JOIN '. PURCHASES_TABLE .'
      			ON '. CATALOGS_TABLE .'.purchase_id = '. PURCHASES_TABLE .'.purchase_id
      		JOIN '. RESERVS_TABLE .'
      			ON '. PURCHASES_TABLE .'.reserv_id = '. RESERVS_TABLE .'.reserv_id 
      		JOIN ' . BRANDS_TABLE . ' 
				ON ' . RESERVS_TABLE . '.brand_id = ' . BRANDS_TABLE . '.brand_id 
			WHERE '. LOTS_TABLE .'.catalog_id = '. $catalog_id; //.'
//      		AND '. RESERVS_TABLE .'.user_id = '. $user->data['user_id'];
      	$result = $db->sql_query($sql);
		$n=0;
      	while ($row = $db->sql_fetchrow($result)) {
			if ($n) $lot.=',';
			$prop = unserialize($row['lot_img']);
				$valstr='';
				$f=0;
				if (is_array($prop))
				foreach ($prop as $v)
				{
					if ($f) $valstr.=', ';
					$valstr.='	"'.$v.'" ';
					$f=1;
				}
				//				"desc": "'.addslashes(preg_replace(array("/[\r]/s","/[\n]/s"),array("","<br>"),$row['lot_description'])).'", 
      		$lot.='"'.$row['lot_id'].'": { 
					"id": "'.$row['lot_id'].'", 
					"purchase_id": "'.$purchase_id.'", 
					"name": "'.addslashes($row['lot_name']).'", 
					"price": "'.$row['lot_cost'].'", 
					"article": "'.addslashes($row['lot_article']).'", 
					"image_urls": '.(($valstr=='')?'[]':'[ '.$valstr.' ]').', 
					"desc": "'.addslashes($row['lot_description']).'", 
					"bundle": {';
				$prop = unserialize($row['lot_bundle']);
				$f=0;
				if (is_array($prop))
				foreach ($prop as $k=>$v)
				{
					if ($f) $lot.=',';
					$values=explode(';', $v);
					$valstr='';
					$f2=0;
					for ($j=0; $j<count($values); $j++){
						if ($values[$j]){
							if ($f2) $valstr.=',';
							$valstr.=' "'.$values[$j].'" ';
							$f2=1;
						}
					}
						//$valstr.='	<input name="propvalues['.$i.'][]" type="checkbox" checked value="'.$values[$j].'">'.$values[$j];
					$lot.='"'.$k.'": [ '.$valstr.' ]';
					$f=1;
				}
			$lot.='},';
			$lot.='"org_fee": "'.$row['lot_orgrate'].'", 
					"hidden": '.$row['lot_hidden'].',
					"vars": { ';
			//-------Проверка переменных
			$a=$row['lot_properties'];
			$s=1;
			$st=false;
			for ($k = 1; $k <= substr_count($a,'s:'); $k++) {
				$c=stripos($a,'s:',$s)+2;  // позиция начала поиска
				$d=stripos($a,':"',$c); // Позиция конца поиска байта
				$f=stripos($a,'";',$c); // Позиция конца поиска подстроки
				$e=substr($a,$c,$d-$c);  // Байты
				$h=$f-$d-2;
				$g=substr($a,$d+2,$h);  // Подстрока
				if ($e!= $h) {
					$a=substr_replace ( $a,$h,$c,$d-$c);
					$st=true;
				}
				$s=$f;
			}
			if ($st){
				$usql = 'UPDATE phpbb_lots SET lot_properties =\''.$a.'\' WHERE lot_id = ' .$row['lot_id'];
				//echo ($usql); 
				$db->sql_query($usql);
			}

		//---------------
			
			$prop = unserialize($a);
				$f=0;
				if (is_array($prop))
				foreach ($prop as $k=>$v)
				{
					if ($f) $lot.=',';
					$values=explode(';', $v);
					$valstr='';
					$f2=0;
					for ($j=0; $j<count($values); $j++){
						if ($values[$j]){
							if ($f2) $valstr.=',';
							$valstr.=' "'.str_replace(array('"'), '\"',$values[$j]).'" ';
							$f2=1;
						}
					}
						//$valstr.='	<input name="propvalues['.$i.'][]" type="checkbox" checked value="'.$values[$j].'">'.$values[$j];
					$lot.='"'.$k.'": [ '.$valstr.' ]';
					$f=1;
				}
			$lot.='}}';
			$n++;
			
      	}
      	$db->sql_freeresult();

		$template->assign_vars(array(
 		  		'NOM' 	=> $n,
				'EDIT'	=> $edit,
 		  		'LOTS'	=> $lot,
 		  	));
		
$template->set_filenames(array(
	'body' => 'org_catalog_edit.html') // template file name -- See Templates Documentation
);

// Finish the script, display the page
//page_footer();
?>