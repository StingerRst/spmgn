<?php
	$sql = 'SELECT *
		FROM ' . BRANDS_TABLE . '
		ORDER BY brand_label';
				
	$result=$db->sql_query($sql);
    while ($row = $db->sql_fetchrow($result)) {
		$sql = 'SELECT *
			FROM ' . RESERVS_TABLE . '
			WHERE brand_id='.$row['brand_id'];
		$result1=$db->sql_query($sql);
		//$db->sql_fetchrow($result1)
				
		$template->assign_block_vars(
			'brend', array(
			'ID' 			=> $row['brand_id'],
			'LABEL' 		=> $row['brand_label'],
			'LOGO'			=> $row['brand_logo'],
			'URL'			=> $row['brand_url'],
			'DESCRIPTION'	=> $row['brand_description'],
			'DEL'			=> ($db->sql_fetchrow($result1))?0:1
			)
		);
	}
	$template->set_filenames(array(
		'body' => 'adm_brend.html') // template file name -- See Templates Documentation
	);

?>