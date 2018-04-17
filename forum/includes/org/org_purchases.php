<?php
		$arh = (int)request_var('a',0);
		$template->assign_vars(array(
			'L_TITLE'						=> $user->lang['UCP_ORGROOM_' . strtoupper($mode)],
		));
					
		$sql = 'SELECT 
		' . PURCHASES_TABLE . '.*,
		' . BRANDS_TABLE . '.*,
		' . RESERVS_TABLE . '.*,
		' . PRODUCTCAT_TABLE . '.productcat_label
		FROM ' . RESERVS_TABLE . '
		LEFT JOIN ' . PURCHASES_TABLE . ' ON ' . PURCHASES_TABLE . '.reserv_id = ' . RESERVS_TABLE . '.reserv_id 
		JOIN ' . BRANDS_TABLE . ' ON ' . RESERVS_TABLE . '.brand_id = ' . BRANDS_TABLE . '.brand_id 
		JOIN ' . PRODUCTCAT_TABLE . ' ON ' . RESERVS_TABLE . '.productcat_id = ' . PRODUCTCAT_TABLE . '.productcat_id 
		WHERE ' . RESERVS_TABLE . '.user_id = '.$user->data['user_id'] ;
		if ($arh)
			$sql .= ' AND ' . RESERVS_TABLE . '.status = 7';
		else
			$sql .= ' AND ' . RESERVS_TABLE . '.status > 1 AND ' . RESERVS_TABLE . '.status < 7';
//echo ($sql);
      	$result=$db->sql_query($sql);
	$f4=0;$mypurchases='';
      	while ($row = $db->sql_fetchrow($result)) {
					if($f4) $mypurchases.=',';
					$mypurchases.='"'.$row['reserv_id'].'":{
					"id" : "'.$row['purchase_id'].'",
					"purchase_admin_money" : "'.$row['purchase_admin_money'].'",
   					"brandname" : "'.$row['brand_label'].' / '.$row['productcat_label'].'",
   					"name" : "'.str_replace(array('"'), '\"',$row['purchase_name']).'",
					"brand_id" : "'.$row['brand_id'].'",
					"purchase_description" : "'.preg_replace(array("/[\r]/s","/[\n]/s"),array("","\\\\n"),$row['purchase_description']).'",
					"brand_url" : "'.$row['brand_url_f'].'",
					"joomla_material_id" : "'.$row['joomla_material_id'].'",
					"site_url" : "'.$row['site_url'].'",
					"purchase_url" : "'.append_sid($row['purchase_url']).'",
					"status_open" : "'.(($row['purchase_status_open']) ? date("d-m-Y", strtotime($row['purchase_status_open'])) : '').'",
					"status_start" : "'.(($row['purchase_status_start']) ? date("d-m-Y", strtotime($row['purchase_status_start'])) : '').'",
					"status_fixed" : "'.(($row['purchase_status_fixed']) ? date("d-m-Y", strtotime($row['purchase_status_fixed'])) : '').'",
					"status_stop" : "'.(($row['purchase_status_stop']) ? date("d-m-Y", strtotime($row['purchase_status_stop'])) : '').'",
					"status_reorder" : "'.(($row['purchase_status_reorder']) ? date("d-m-Y", strtotime($row['purchase_status_reorder'])) : '').'",
					"status_billreciv" : "'.(($row['purchase_status_billreciv']) ? date("d-m-Y", strtotime($row['purchase_status_billreciv'])) : '').'",
					"status_payto" : "'.(($row['purchase_status_payto']) ? date("d-m-Y", strtotime($row['purchase_status_payto'])) : '').'",
					"status_shipping" : "'.(($row['purchase_status_shipping']) ? date("d-m-Y", strtotime($row['purchase_status_shipping'])) : '').'",
					"status_goodsreciv" : "'.(($row['purchase_status_goodsreciv']) ? date("d-m-Y", strtotime($row['purchase_status_goodsreciv'])) : '').'",
					"status_distribfrom" : "'.(($row['purchase_status_distribfrom']) ? date("d-m-Y", strtotime($row['purchase_status_distribfrom'])) : '').'",
					"news" : "'.preg_replace(array("/[\r]/s","/[\n]/s"),array("","\\\\n"),$row['purchase_news']).'",
					"coment" : "'.preg_replace(array("/[\r]/s","/[\n]/s"),array("","\\\\n"),$row['purchase_coment']).'",
  					"request_end" : "'.(($row['request_end']) ? date("d-m-Y", strtotime($row['request_end'])) : '').'",
   					"request_control" : "'.$control.'",
					"logo" : "'.$row['brand_logo'].'",
					"status" : "'.$row['status'].'",
					"delivery_to_ec" :"'. $row['delivery_to_ec'].'",					
					"nakl_to_ec" :"'. $row['nakl_to_ec'].'",					
					"always_open" :"'. $row['always_open'].'",					
					"auth" : '.((($auth->acl_get('a_'))||($row['status']==3))?1:0).',
					"rules":  ';
		$f3=0;$rules='';
  		for ( $i=1; $i<=9; $i++){
				if($f3) $rules.=',';
				$values=explode(':', $row['purchases_rule'.$i]);
				$rules.='"'.$values[0].'": "'.$values[1].'"';				
				$f3=1;
			}
			$mypurchases.=($rules<>'')?('{'.$rules.'}'):'[]';
			$mypurchases.=',"catalog":';
			
   					if ($row['purchase_id']) {
   						$sql = 'SELECT * 
   							FROM '. CATALOGS_TABLE .'
   							WHERE purchase_id = '. $row['purchase_id'] .'
   							GROUP BY '. CATALOGS_TABLE .'.catalog_id';   							
   						$result2 = $db->sql_query($sql);
						$f2=0;$catalog='';   						
						while ($row2 = $db->sql_fetchrow($result2)) {
							if($f2) $catalog.=',';
							$catalog.='"'.$row2['catalog_id'].'": { 
							"catalog_id": "'.$row2['catalog_id'].'", 
							"name": "'.str_replace(array('"'), '\"',$row2['catalog_name']).'", 
							"orgrate": "'.$row2['catalog_orgrate'].'", 
							"valuta": "'.$row2['catalog_valuta'].'", 
							"course": "'.$row2['catalog_course'].'", 
							"catalog_bundle": "'.(($row2['catalog_bundle']<>null)?$row2['catalog_bundle']:-1).'", 
							"hide": "'.$row2['catalog_hide'].'", 
							"foruser": "'.$row2['catalog_foruser'].'", 
							"purchase_id": "'.$row['purchase_id'].'", 
							"vars":  ';
						
	   					
							$prop = unserialize($row2['catalog_properties']);
							$f=0;$prop1='';
							if (is_array($prop))
							{
							foreach ($prop as $k=>$v)
							{
								//$values=explode(';', $v);
								//$valstr='';
								//for ($j=0; $j<count($values); $j++) 
								//	$valstr.='	"'.$values[$j].'", ';
								 if($f) $prop1.=',';
								$prop1.='"'.$k.'": ["'. $v .'"]';//$valstr.' ]';
							$f=1;
							}}
							$catalog.=($prop1<>'')?('{'.$prop1.'}'):'[]';
							$catalog.='}';
							$f2=1;
							

   						}
   					}
		$mypurchases.=($catalog<>'')?('{'.$catalog.'}'):'[]';
		$mypurchases.='}';
		$f4=1;	
		}
  	$template->assign_vars(array(
  		'ARH'			=> $arh,
  		'MYPURCHASES'	=> $mypurchases,
		'PUR'			=> request_var('p',0),
		'CAT'			=> request_var('c',0)
  	));
		
$template->set_filenames(array(
	'body' => 'org_purchases.html') // template file name -- See Templates Documentation
);

// Finish the script, display the page
//page_footer();
?>