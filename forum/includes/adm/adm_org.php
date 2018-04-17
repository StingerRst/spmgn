<?php

		  	 		  	
 		  	$sql = 'SELECT ' . USERS_TABLE . '.*, ' . USER_GROUP_TABLE . '.*, ' . DOGS_TABLE . '.dg_id, ' . DOGS_TABLE . '.dg_param, ' . DOGS_TABLE . '.dg_date, ' . DOGS_TABLE . '.dg_activ
				FROM ' . USERS_TABLE . '
				JOIN ' . USER_GROUP_TABLE . ' ON ' . USERS_TABLE . '.user_id = ' . USER_GROUP_TABLE . '.user_id
				LEFT JOIN ' . DOGS_TABLE . ' ON ' . DOGS_TABLE . '.user_id = ' . USERS_TABLE . '.user_id
				WHERE ' . USER_GROUP_TABLE . '.group_id = 8
				ORDER BY username';
				
			$result=$db->sql_query($sql);
      	while ($row = $db->sql_fetchrow($result)) {
			if ($row['dg_id']===null){
				$dg='<form method="post">
					<input type="hidden" value="creatd" name="cmd">
					<input type="hidden" value="'.$row['user_id'].'" name="id">
					<input type="submit" name="submit" value="Создать договор" class="button1" />
				</form>';
			}else if ($row['dg_activ']==0){
				$dg='Договор на подписании';
			}else{
				$dg='<a href="dog.php?u='.$row['user_id'].'">Договор подписан '.date("d-m-Y H:i",$row['dg_date']).'</a>';
			}
      		if ($row['user_pending']==0){
			$template->assign_block_vars('orguser',array(
   					'NAME'			=> $row['username'],
   					'NAME_ID'		=> $row['user_id'],
   					'DAT'			=> date("d-m-Y H:i",$row['user_regdate']),
   					'POST'			=> $row['user_posts'],
					'DOG'			=> $dg
   			));
			}
      		if ($row['user_pending']==1){
      		$template->assign_block_vars('orguser1',array(
   					'NAME'			=> $row['username'],
   					'NAME_ID'		=> $row['user_id'],
   					'DAT'			=> date("d-m-Y H:i",$row['user_regdate']),
   					'POST'			=> $row['user_posts'],
					'DOG'			=> $dg
   			));
			}
      		if ($row['user_pending']==2){
      		$template->assign_block_vars('orguser2',array(
   					'NAME'			=> $row['username'],
   					'NAME_ID'			=> $row['user_id'],
   					'DAT'			=> date("d-m-Y H:i",$row['user_regdate']),
   					'POST'			=> $row['user_posts'],
					'DOG'			=> $dg
   			));
			}
		}
$template->set_filenames(array(
	'body' => 'adm_org.html') // template file name -- See Templates Documentation
);

?>