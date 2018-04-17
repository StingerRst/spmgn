<?php
// Pagination
$start		= request_var('start', 0);
$pagination = '&';
$total_pag_items = 1;
$purchases_per_page =50;
// end Pagination
		$arh = (int)request_var('a',0);
		$op = (int)request_var('op',0);
		$template->assign_vars(array(
			'L_TITLE'						=> $user->lang['UCP_ORGROOM_' . strtoupper($mode)],
			'PUR'			=> request_var('p',0),
			'CAT'			=> request_var('c',0)
		));
		
		$sql = 'SELECT 
		' . PURCHASES_TABLE . '.*,
		' . BRANDS_TABLE . '.brand_label,
		' . BRANDS_TABLE . '.brand_logo,
		' . RESERVS_TABLE . '.request_end,
		' . RESERVS_TABLE . '.status,
		' . RESERVS_TABLE . '.always_open,
		' . USERS_TABLE . '.username,
		' . PRODUCTCAT_TABLE . '.productcat_label
		FROM ' . RESERVS_TABLE . '
		LEFT JOIN ' . PURCHASES_TABLE . ' ON ' . PURCHASES_TABLE . '.reserv_id = ' . RESERVS_TABLE . '.reserv_id 
		JOIN ' . BRANDS_TABLE . ' ON ' . RESERVS_TABLE . '.brand_id = ' . BRANDS_TABLE . '.brand_id 
		JOIN ' . PRODUCTCAT_TABLE . ' ON ' . RESERVS_TABLE . '.productcat_id = ' . PRODUCTCAT_TABLE . '.productcat_id 
		JOIN ' . USERS_TABLE . ' ON ' . USERS_TABLE . '.user_id = ' . RESERVS_TABLE . '.user_id ';
		if ($arh)
			$sql .= ' WHERE ' . RESERVS_TABLE . '.status = 7';
		else
			$sql .= ' WHERE ' . RESERVS_TABLE . '.status > 1 AND ' . RESERVS_TABLE . '.status < 7';
		if ($op)
			$sql .= ' AND ' . PURCHASES_TABLE . '.purchase_id ='.$op;

		$sql .= ' ORDER BY purchase_id DESC';


		$sql .= ' LIMIT ' . $start . ', ' . $purchases_per_page; // Pagination
//	echo $sql;


      	$result=$db->sql_query($sql);
				$myp=1;
      	while ($row = $db->sql_fetchrow($result)) {
      		$template->assign_block_vars('mypurchases',array(
					'I'	=> $myp,
					'PURCHASE_ID'					=> $row['purchase_id'],
   					'SITE_URL'						=> $row['site_url'],
   					'NAME'							=> $row['purchase_name'],
   					'BRAND_ID'						=> $row['brand_id'],
   					'BRANDNAME'						=> $row['purchase_name'].' - '.$row['brand_label'].' / '.$row['productcat_label'],
   					'USERNAME'						=> $row['username'],
					'BRAND_DESCRIPTION' 			=> $row['purchase_description'],
					'PURCHASE_URL' 					=> append_sid($row['purchase_url']),
					'PURCHASE_STATUS_OPEN' 			=> ($row['purchase_status_open']) ? date("d-m-Y", strtotime($row['purchase_status_open'])) : '',
					'PURCHASE_STATUS_START' 		=> ($row['purchase_status_start']) ? date("d-m-Y", strtotime($row['purchase_status_start'])) : '',
					'PURCHASE_STATUS_FIXED' 		=> ($row['purchase_status_fixed']) ? date("d-m-Y", strtotime($row['purchase_status_fixed'])) : '',
					'PURCHASE_STATUS_STOP' 			=> ($row['purchase_status_stop']) ? date("d-m-Y", strtotime($row['purchase_status_stop'])) : '',
					'PURCHASE_STATUS_REORDER' 		=> ($row['purchase_status_reorder']) ? date("d-m-Y", strtotime($row['purchase_status_reorder'])) : '',
					'PURCHASE_STATUS_BILLRECIV' 	=> ($row['purchase_status_billreciv']) ? date("d-m-Y", strtotime($row['purchase_status_billreciv'])) : '',
					'PURCHASE_STATUS_PAYTO' 		=> ($row['purchase_status_payto']) ? date("d-m-Y", strtotime($row['purchase_status_payto'])) : '',
					'PURCHASE_STATUS_SHIPPING' 		=> ($row['purchase_status_shipping']) ? date("d-m-Y", strtotime($row['purchase_status_shipping'])) : '',
					'PURCHASE_STATUS_GOODSRECIV' 	=> ($row['purchase_status_goodsreciv']) ? date("d-m-Y", strtotime($row['purchase_status_goodsreciv'])) : '',
					'PURCHASE_STATUS_DISTRIBFROM' 	=> ($row['purchase_status_distribfrom']) ? date("d-m-Y", strtotime($row['purchase_status_distribfrom'])) : '',
					'PURCHASE_NEWS' 				=> $row['purchase_news'],
					'PURCHASE_COMENT' 				=> $row['purchase_coment'],
  					'REQUEST_END'					=> ($row['request_end']) ? date("d-m-Y", strtotime($row['request_end'])) : '',
   					'REQUEST_CONTROL' 				=> $control,
 					'RESERV_ID'						=> $row['reserv_id'],
					'LOGO'							=> $row['brand_logo'],
					'STATUS'						=> $row['status'],
					'DELIVERY_TO_EC'				=> $row['delivery_to_ec'],
					'NAKL_TO_EC'					=> $row['nakl_to_ec'],
					'ALWAYS_OPEN'					=> $row['always_open'],					
  			));
			for ( $i=1; $i<=9; $i++){
				$values=explode(':', $row['purchases_rule'.$i]);
				$template->assign_block_vars('mypurchases.rules',array(
						'I'			=> $i,
						'NAME'	=> $values[0],
						'VALUES'=> $values[1],
				));
			}
			
   					if ($row['purchase_id']) {
   						$sql = 'SELECT * 
   							FROM '. CATALOGS_TABLE .'
   							WHERE purchase_id = '. $row['purchase_id'] .'
   							GROUP BY '. CATALOGS_TABLE .'.catalog_id';   							
   						$result2 = $db->sql_query($sql);
							$cat=1;
   						while ($row2 = $db->sql_fetchrow($result2)) {
						
	   						$template->assign_block_vars('mypurchases.catalogs',array(
								'I' => $cat,
								'CATALOG_ID'			=> $row2['catalog_id'],
								'CATALOG_NAME'			=> $row2['catalog_name'],
								'CATALOG_ORGRATE'		=> $row2['catalog_orgrate'],
								'CATALOG_BUNDLE'		=> $row2['catalog_bundle'],
								'CATALOG_PURCHASE_ID'	=> $purchase_id,
  							));
							$prop = unserialize($row2['catalog_properties']);
							$pc=1;
							if (is_array($prop))
							foreach ($prop as $k=>$v)
							{
								$values=explode(';', $v);
								$valstr='';
								for ($j=0; $j<count($values); $j++) 
									$valstr.='	"'.$values[$j].'", ';
								$template->assign_block_vars('mypurchases.catalogs.prop',array(
											'I'				=> $pc,
											'NAME'		=> $k,
											'VALUES'	=> $valstr,
										));
							$pc=2;
							}
							$cat=2;
   						}
   					}
			$myp=2;
		}
  	$template->assign_vars(array(
  		'ARH'			=> $arh,
  	));

// Pagination


	$sql = 'SELECT count(*) AS total 
	FROM ' . RESERVS_TABLE . '
	LEFT JOIN ' . PURCHASES_TABLE . ' ON ' . PURCHASES_TABLE . '.reserv_id = ' . RESERVS_TABLE . '.reserv_id 
	JOIN ' . BRANDS_TABLE . ' ON ' . RESERVS_TABLE . '.brand_id = ' . BRANDS_TABLE . '.brand_id 
	JOIN ' . PRODUCTCAT_TABLE . ' ON ' . RESERVS_TABLE . '.productcat_id = ' . PRODUCTCAT_TABLE . '.productcat_id 
	JOIN ' . USERS_TABLE . ' ON ' . USERS_TABLE . '.user_id = ' . RESERVS_TABLE . '.user_id ';
	if ($arh)
		$sql .= ' WHERE ' . RESERVS_TABLE . '.status = 7';
	else
		$sql .= ' WHERE ' . RESERVS_TABLE . '.status > 1 AND ' . RESERVS_TABLE . '.status < 7';


	if ( !($result = $db->sql_query($sql)) ) 
	{
		message_die(GENERAL_ERROR, 'Error getting total', '', __LINE__, __FILE__, $sql);
	}

	if ( $total = $db->sql_fetchrow($result) )
	{

		$total_pag_items = $total['total'];

		// If we've got a hightlight set pass it on to pagination.

	$pagination = generate_pagination(append_sid("adm.php?i=1"), $total_pag_items, $purchases_per_page, $start);
	}

	$template->assign_vars(array(
		'PAGINATION' => $pagination,
		'PAGE_NUMBER' 	=> on_page($total_pag_items, $purchases_per_page, $start),
		'START'         => $start,
		'TOTAL_POSTS'	=> $total_pag_items		
	));

// end Pagination

	
$template->set_filenames(array(
	'body' => 'adm_purchases.html') // template file name -- See Templates Documentation
);

// Finish the script, display the page
//page_footer();
?>