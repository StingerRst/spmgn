<?php

 		$options='';
      	$catalog_id = request_var('cat', '');
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
			WHERE '. LOTS_TABLE .'.catalog_id = '. $catalog_id .'
      		AND '. RESERVS_TABLE .'.user_id = '. $user->data['user_id'];
      	$result = $db->sql_query($sql);
		$n=0;
      	while ($row = $db->sql_fetchrow($result)) {
			$prop = unserialize($row['lot_img']);
				$valstr='';
				if (is_array($prop))
				foreach ($prop as $v)
				{
					$valstr.='	"'.$v.'", ';
				}
      		$template->assign_block_vars('lots',array(
	   					'LOT_ID'		=> $row['lot_id'],
	   					'LOT_NAME'		=> $row['lot_name'],
	   					'LOT_IMG'		=> $valstr,
	   					'LOT_COST'		=> $row['lot_cost'],
	   					'LOT_DESCR'		=> $row['lot_description'],
   					));
			$prop = unserialize($row['lot_properties']);
				if (is_array($prop))
				foreach ($prop as $k=>$v)
				{
					$values=explode(';', $v);
					$valstr='';
					for ($j=0; $j<count($values); $j++) 
						$valstr.='	"'.$values[$j].'", ';
						//$valstr.='	<input name="propvalues['.$i.'][]" type="checkbox" checked value="'.$values[$j].'">'.$values[$j];
					$template->assign_block_vars('lots.prop',array(
								'NAME'		=> $k,
								'VALUES'	=> $valstr,
							));
				}
			$n++;
      	}
      	$db->sql_freeresult();

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
			WHERE '. CATALOGS_TABLE .'.catalog_id = '. $catalog_id .'
      		AND '. RESERVS_TABLE .'.user_id = '. $user->data['user_id'];
      	$result = $db->sql_query($sql);
      	$row = $db->sql_fetchrow($result); 
		$template->assign_vars(array(
 		  		'PURCHASES_NAME'  	=> $row['brand_label'].' / '.$row['productcat_label'],
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
 		  		'CATALOG_NAME'  => $row['catalog_name'],
 		  		'CATALOG_ORGRATE'  => $row['catalog_orgrate'],
 		  		'CATALOG_BUNDLE'  => $row['catalog_bundle'],
 		  		'NOM'  => $n,
				'EDIT' =>$edit,
 		  	));
		
$template->set_filenames(array(
	'body' => 'org_catalog_edit.html') // template file name -- See Templates Documentation
);

// Finish the script, display the page
//page_footer();
?>