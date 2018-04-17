<?php

/**
* @ignore
*/
define('IN_PHPBB', true); // we tell the page that it is going to be using phpBB, this is important.
$phpbb_root_path = './'; // See phpbb_root_path documentation
$phpEx = substr(strrchr(__FILE__, '.'), 1); // Set the File extension for page-wide usage.
include($phpbb_root_path . 'common.' . $phpEx); // include the common.php file, this is important, especially for database connects.
//include($phpbb_root_path . 'includes/functions_calendar.' . $phpEx); // contains the functions that "do the work".

// Start session management -- This will begin the session for the user browsing this page.
$user->session_begin();
$auth->acl($user->data);

// Language file (see documentation related to language files)
$user->setup('cat');

// If users such as bots don't have permission to view any events
// you don't want them wasting time in the calendar...
// Is the user able to view ANY events?
/*if ( !$auth->acl_get('u_calendar_view_events') )
{
	trigger_error( 'NO_AUTH_OPERATION' );
}
*/
if( !$user->data['is_bot'] && $user->data['user_id'] != ANONYMOUS )
{
      	$template->assign_vars(array(
 		  		'REGISTERED_USER'		=> 1,
 		  	));

}else{
      	$template->assign_vars(array(
 		  		'REGISTERED_USER'		=> 0,
 		  	));
}
$pstus=array('','открыть до','Старт','Фиксация','Стоп','Дозаказ','Счет получен','Оплата до','Отгрузка','Груз получен','Раздача с');

 		$options='';
      	$catalog_id = request_var('catalog_id', 0);
      	$item_id = request_var('lot_id', -1);
      	$sql = 'SELECT
			'. BRANDS_TABLE . '.*,
			'. PURCHASES_TABLE .'.*,
			'. RESERVS_TABLE .'.request_end,
			'. RESERVS_TABLE .'.status,
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
			WHERE '. CATALOGS_TABLE .'.catalog_id = '. $catalog_id.'
				AND '. CATALOGS_TABLE .'.catalog_hide = 0
				AND ' . RESERVS_TABLE . '.status in (4,5)';
			$result = $db->sql_query($sql);
      	$row = $db->sql_fetchrow($result); 
//echo ($sql);
		if (!$row){
			trigger_error( 'Доступ к каталогу запрещен!' );
		}
		$template->assign_vars(array(
 				'PURCHASE_ID' 		=> $row['purchase_id'],
 				'RESERV_STATUS' 	=> $row['status'],
		  		'PURCHASES_NAME'  	=> $row['purchase_name'],//.' - '.$row['brand_label'],
				'PURCHASES_URL'		=> $row['purchase_url'],
				'USER_ID'			=> $row['user_id'],
				'USERNAME'			=> $row['username'],
				'ITEM_ID'			=> $item_id,
 		  	));
		if ($user->data['user_id'] == $row['user_id']){
			$template->assign_vars(array(
					'IS_ORGANIZER' 		=> 1));
		}else{
			$template->assign_vars(array(
					'IS_ORGANIZER' 		=> 0));
		}
		$status_id=1;
		$sdate=$row['request_end'];
		if ($row['purchase_status_start']){
			$status_id=2;
			$sdate=$row['purchase_status_start'];
		}
		if ($row['purchase_status_fixed']){
			$status_id=3;
			$sdate=$row['purchase_status_fixed'];
		}
		if ($row['purchase_status_stop']){
			$status_id=4;
			$sdate=$row['purchase_status_stop'];
		}
		if ($row['purchase_status_reorder']){
			$status_id=5;
			$sdate=$row['purchase_status_reorder'];
		}
		if ($row['purchase_status_billreciv']){
			$status_id=6;
			$sdate=$row['purchase_status_billreciv'];
		}
		if ($row['purchase_status_payto']){
			$status_id=7;
			$sdate=$row['purchase_status_payto'];
		}
		if ($row['purchase_status_shipping']){
			$status_id=8;
			$sdate=$row['purchase_status_shipping'];
		}
		if ($row['purchase_status_goodsreciv']){
			$status_id=9;
			$sdate=$row['purchase_status_goodsreciv'];
		}
		if ($row['purchase_status_distribfrom']){
			$status_id=10;
			$sdate=$row['purchase_status_distribfrom'];
		}


			$db->sql_freeresult();
      	$sql = 'SELECT catalog_name,catalog_orgrate, catalog_properties as prop,catalog_bundle,catalog_foruser
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
 		  		'CATALOG_ID'		=> $catalog_id,
 		  		'CATALOG_NAME'  => $row['catalog_name'],
 		  		'CATALOG_ORGRATE'  => ($row['catalog_orgrate'])?$row['catalog_orgrate']:0,
 		  		'CATALOG_BUNDLE'  => $row['catalog_bundle'],
				'STATUS'=> $pstus[$status_id].' '.$sdate,
				'FORUSER'=> $row['catalog_foruser'],
 		  	));
		$cat_org = ($row['catalog_orgrate'])?$row['catalog_orgrate']:0;
      	$sql = 'SELECT
      		'. LOTS_TABLE .'.*,
			'. BRANDS_TABLE . '.*,
			'. CATALOGS_TABLE . '.*,
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
			WHERE '. LOTS_TABLE .'.catalog_id = '. $catalog_id.'
			AND '. LOTS_TABLE .'.lot_hidden = 0
			ORDER BY '. LOTS_TABLE .'.lot_id';
      	$result = $db->sql_query($sql);
		$n=0;
      	while ($row = $db->sql_fetchrow($result)) {
			if ($n) $lot.=",";
			$prop = unserialize($row['lot_img']);
				$valstr='';
				if (is_array($prop))
				foreach ($prop as $v)
				{
					$valstr.='	"'.$v.'", ';
				}
      		$lot.='"'.$row['lot_id'].'": { 
					"id": '.$row['lot_id'].', 
					"name": "'.addslashes($row['lot_name']).'", 
					"price": "'.$row['lot_cost'].'", 
					"article": "'.addslashes($row['lot_article']).'", 
					"valuta": "'.addslashes($row['catalog_valuta']).'", 
					"course": "'.$row['catalog_course'].'", 
					"image_urls": [ '.$valstr.' ], 
					"desc": "'.addslashes(preg_replace(array("'([\r\n])'"),array("<br>"),$row['lot_description'])).'", 
					"bundle": null, 
					"org_fee": "'.$row['lot_orgrate'].'", 
					"hidden": '.$row['lot_hidden'].',
					"vars": { ';
			//$lot=nl2br(stripslashes($lot));		
			//var_dump (nl2br(stripslashes($lot)));		
			$prop = unserialize($row['lot_properties']);
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
			$lot.='}}';
			$n++;
      	}
      	$db->sql_freeresult();


      	$template->assign_vars(array(
 		  		'LOTS'		=> $lot,
  		  		'NOM'  => $n,
				'EDIT' =>$edit,
 		  	));
		
if (strpos($_SERVER['HTTP_REFERER'],'spmgn.ru/index.php')){
	$template->assign_var('IFRAME', 1);
}
$template->set_filenames(array(
	'body' => 'catalog.html') // template file name -- See Templates Documentation
);

// Output the page
page_header($user->lang['PAGE_TITLE']); // Page title, this language variable should be defined in the language file you setup at the top of this page.



// Finish the script, display the page
page_footer();


?>
