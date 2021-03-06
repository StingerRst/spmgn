﻿<?php
define('IN_PHPBB', true);
$phpbb_root_path = (defined('PHPBB_ROOT_PATH')) ? PHPBB_ROOT_PATH : './';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
include($phpbb_root_path . 'common.' . $phpEx);
include($phpbb_root_path . 'includes/functions_posting.' . $phpEx);
include($phpbb_root_path . 'includes/message_parser.' . $phpEx);
include($phpbb_root_path . 'includes/functions_upload.' . $phpEx);

// увеличиваем максимальное время выполнения скрипта
set_time_limit(0);
//echo ini_get('max_execution_time');

// Start session management
$user->session_begin();
$auth->acl($user->data);
$user->setup();

if ($user->data['user_id'] == ANONYMOUS)
	trigger_error ('Доступ запрещен!');
if (!isset($error)) $error=array();
	$upload = new fileupload(basename($_FILES['price']['name']), array('csv'));
	$price = $upload->form_upload('price');
	$price->clean_filename('unique_ext');
	$filename=$price->get('realname');
	$destination = 'files/';
	// Adjust destination path (no trailing slash)
	if (substr($destination, -1, 1) == '/' || substr($destination, -1, 1) == '\\')
	{
		$destination = substr($destination, 0, -1);
	}
	$destination = str_replace(array('../', '..\\', './', '.\\'), '', $destination);
	if ($destination && ($destination[0] == '/' || $destination[0] == "\\"))
	{
		$destination = '';
	}
	
	// Move file and overwrite any existing image
	$price->move_file($destination, true);
	
	if (sizeof($price->error))
	{
		$price->remove();
		$error = array_merge($error, $price->error);
		echo "Ошибка загрузки";
	}else{
	
	$filename = $destination.'/'.$filename;

	$lines = file($filename);
		foreach ($lines as $line_num => $line) {
			$data[] = explode(";", $line);
		}

	
	foreach($data as $k=>$v)
	
		foreach($v as $k2=>$v2){
			$data[$k][$k2]=trim(iconv("cp1251", "utf-8", $v2));
			$data[$k][$k2]=preg_replace("/\"$/", '', $data[$k][$k2]);
			$data[$k][$k2]=preg_replace("/^\"/", '', $data[$k][$k2]);
		}
	$bundle_name ='';
	$bundle_row='';
	foreach($data[0] as $k=>$v){  //Обработка шапки
		if ($comb)
			if (strnatcasecmp($v,$comb) == 0) $conbid=$k;
	
		if (strnatcasecmp($v,'Наименование') == 0) $index['name']=$k;
		elseif (strnatcasecmp($v,'наименование') == 0) $index['name']=$k;
		elseif (strnatcasecmp($v,'Цена') == 0) $index['cost']=$k;
		elseif (strnatcasecmp($v,'цена') == 0) $index['cost']=$k;
		elseif (strnatcasecmp($v,'Орг%') == 0) $index['org']=$k;
		elseif (strnatcasecmp($v,'орг%') == 0) $index['org']=$k;
		elseif (strnatcasecmp($v,'Орг') == 0) $index['org']=$k;
		elseif (strnatcasecmp($v,'орг') == 0) $index['org']=$k;
		elseif (strnatcasecmp($v,'Картинка') == 0) $index['image']=$k;
		elseif (strnatcasecmp($v,'картинка') == 0) $index['image']=$k;
		elseif (strnatcasecmp($v,'Артикул') == 0) $index['article']=$k;
		elseif (strnatcasecmp($v,'артикул') == 0) $index['article']=$k;
		elseif (strnatcasecmp($v,'URL') == 0) $index['url']=$k;
		elseif (strnatcasecmp($v,'Url') == 0) $index['url']=$k;
		elseif (strnatcasecmp($v,'url') == 0) $index['url']=$k;
		elseif (strpos($v, ':', 1)){
			$val=explode(':', $v);
			if 	((strnatcasecmp($val[0],'Ряд') == 0) or (strnatcasecmp($val[0],'ряд') == 0) ) {
				$bundle_name =$val[1];
				$bundle_row=$v;
				$index['bundle']=$k;
			}	
		}	
		elseif (strnatcasecmp($v,'Описание') == 0) $index['desc']=$k;
		elseif (strnatcasecmp($v,'описание') == 0) $index['desc']=$k;
		elseif (strnatcasecmp(trim($v),'') != 0) $var[$k]=$v; // Если есть пустые переменные, то не добавляем поле в переменную
		//else $var[$k]=$v;
	}

	for ($i=1;$i<count($data);$i++){
		if (isset($conbid))
			$id = $data[$i][$conbid];
		else
			$id = $i;
		if ($data[$i][$index['url']])
			$cat[$id]['url']=$data[$i][$index['url']];
		if ($data[$i][$index['article']])
			$cat[$id]['article']=$data[$i][$index['article']];
		if ($data[$i][$index['name']])
			$cat[$id]['name']=$data[$i][$index['name']];
		if ($data[$i][$index['cost']])
			$cat[$id]['cost']=str_replace(',','.',$data[$i][$index['cost']])+1-1;
		if ($data[$i][$index['org']])
			$cat[$id]['org'] =(int)$data[$i][$index['org']];
		if ($data[$i][$index['image']])
			$cat[$id]['image'] =$data[$i][$index['image']];
		if ($data[$i][$index['desc']])
			$cat[$id]['desc']=$data[$i][$index['desc']];
		if ($data[$i][$index['bundle']])
			$cat[$id]['bundle']=str_replace(',',';',$data[$i][$index['bundle']]);

		if (is_array($var))
		foreach ($var as $k=>$v){
			$vars=((isset($cat[$id]['vars'][$v]))?$cat[$id]['vars'][$v].';':'').str_replace(',',';',$data[$i][$k]);
			$vars=strtolower(str_replace(';;',';',$vars));
			$vars=trim(str_replace(array('\\"','\\\'','\'','"'), '',$vars));
			$values=explode(';', $vars);
		
			if ($v!= $bundle_row) $values=array_unique($values);
			asort($values);
			$f=0;
			$vars='';
			
			foreach ($values as $vv){
				if ($f) $vars.=';';
				$vars.=$vv;
				$f=1;
			}
			if (strlen($vars)) {
				$cat[$id]['vars'][$v]=$vars;
			}
		}
	}
	// Получаем ID бренда
	if (isset($_POST['brand_id'])){
		$brand_id=$_POST['brand_id'];
	}
	else {
		// если не передели, то получаем сами
		$sql="SELECT brand_id FROM phpbb_purchases LEFT OUTER JOIN phpbb_reservs ON phpbb_purchases.reserv_id = phpbb_reservs.reserv_id WHERE phpbb_purchases.purchase_id =".$_POST['purchase_id'];
		$result = $db->sql_query($sql);
		$row = $db->sql_fetchrow($result);
		$brand_id= $row['brand_id']; 	
		//var_dump($brand_id);
	}

	if (isset($_POST['test'])){
		foreach($cat as $v){ // перебор лотов
			echo '<b>артикул: </b>'.str_replace('""','"',$v['article']).'<br>';
			echo '<b>наименование: </b>'.str_replace('""','"',$v['name']).'<br>';
			echo '<b>цена: </b>'.$v['cost'].'<br>';
			echo '<b>орг%: </b>'.$v['org'].'<br>';
			echo '<b>описание: </b>'.$v['desc'].'<br>';
			echo '<b>URL: </b>'.$v['url'].'<br>';
			if ($bundle_name) echo '<b>Ряд:'.$bundle_name.' </b>:'.$v['bundle'].'<br>';
			if (is_array($v['vars']))
			foreach ($v['vars'] as $vk=>$vv){ // перебор переменных
				if 	($v['bundle']==$vk) echo 'Ряд: ';
				echo '<b>'.$vk.': </b>'.$vv.'<br>';
			}
			if  ($v['image']) {
				echo '<b style="float: left;">Картинка: </b>';			
				foreach (explode(',',$v['image']) as $img) {
					echo '<img src="'.$img.'" style="height: 200px;float: left;">';				
				}	
			}
			echo '<br style="clear: both;">';
		}
	}else{
		$catalog_id = $_POST['catalog_id'];

			$sql = 'SELECT catalog_orgrate
				FROM '. CATALOGS_TABLE .'
				WHERE catalog_id = '. $catalog_id;
			$result = $db->sql_query($sql);
			$row = $db->sql_fetchrow($result);

			$org_fee= (int)$row['catalog_orgrate'];
				
		foreach($cat as $v){
			$lot_id = 0;
			if (isset($_POST['updp'])){
				$sqll = 'SELECT lot_id
					FROM '. LOTS_TABLE .'
					WHERE lot_name = "'. $db->sql_escape(addslashes($v['name'])).'"';
				$resultl = $db->sql_query($sqll);
				if ($resultl){
					$rowl = $db->sql_fetchrow($resultl);
					$lot_id = $rowl['lot_id'];
				}
			}
			if ($lot_id == 0){
			$v['name']=str_replace('""','"',$v['name']);	
				if ($v['bundle']) {
			if  ($v['image']) {	
				foreach (explode(',',$v['image']) as $image) {
					//var_dump ($image);
					$urls[]=GetImg ($image);
				}	
			}	
				//var_dump ($v['cost']);

				$sql = 'INSERT 
					INTO '.LOTS_TABLE.' 
					(catalog_id,lot_article,lot_url,lot_name,lot_cost,lot_img,lot_orgrate,lot_bundle,lot_description,lot_properties )
					VALUES (
						'. $catalog_id .',
						\''.$db->sql_escape(($v['article'])).'\',
						\''.$db->sql_escape(($v['url'])).'\',
						\''.$db->sql_escape(($v['name'])).'\',
						\''.$v['cost'].'\',
						\''.serialize($urls).'\',
						\''.((isset($v['org']))?$v['org']:$org_fee).'\',
						\''.serialize(array($bundle_name =>$v['bundle'])).'\',
						\''.$db->sql_escape(($v['desc'])).'\',
						\''.serialize($v['vars']).'\'
					)';
				unset($urls);	
				}
				else {
				if  ($v['image']) {
					foreach (explode(',',$v['image']) as $image) {
	//					var_dump ($image);
						$urls[]=GetImg ($image);
					}	
				}
				
				//var_dump ($v['cost']);

				$sql = 'INSERT 
					INTO '.LOTS_TABLE.' 
					(catalog_id,lot_article,lot_url,lot_name,lot_cost,lot_img,lot_orgrate,lot_description,lot_properties )
					VALUES (
						'. $catalog_id .',
						\''.$db->sql_escape(($v['article'])).'\',
						\''.$db->sql_escape(($v['url'])).'\',
						\''.$db->sql_escape(($v['name'])).'\',
						\''.$v['cost'].'\',
						\''.serialize($urls).'\',
						\''.((isset($v['org']))?$v['org']:$org_fee).'\',
						\''.$db->sql_escape(($v['desc'])).'\',
						\''.serialize($v['vars']).'\'
					)';
				unset($urls);	
				}	
//Echo ('</br>');				
			}else{
				$sql = 'UPDATE 
					'.LOTS_TABLE.' SET
					lot_cost=\''.$v['cost'].'\',
					lot_properties =\''.serialize($v['vars']).'\'
					WHERE lot_id='.$lot_id;
			}
				$db->sql_query($sql);
				$db->sql_freeresult();
		}
	echo 'Загрузка закончена.';
	}

	$price->remove();
	}

function  GetImg ($img) {
	$img=trim($img);
	$type=get_headers($img, 1);
	$type=$type['Content-Type'];
	global $brand_id;

	$path_to_save ='images/lots/'.$brand_id;
	$thumb_path_to_save ='images/lots/thumb/'.$brand_id;
	if (!file_exists($path_to_save)){
		mkdir($path_to_save);
	}
	if (!file_exists($thumb_path_to_save)){
		mkdir($thumb_path_to_save);
	}
echo($img." ");
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL,$img);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
	curl_setopt($ch, CURLOPT_BINARYTRANSFER,1);  
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); //чтобы нормально ходила по редиректам
	curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; ru; rv:1.9.0.1) Gecko/2008070208');
	
	$out = curl_exec($ch);
	curl_close($ch);
	$filename=hash("md5",$out);

	if ($type = 'image/jpeg') $filename.='.jpg';
	elseif ($type = 'image/gif') $filename.='.gif';
	elseif ($type = 'image/png') $filename.='.png';
	else {
		$arr = array ('result'=>"err");
		exit (json_encode($arr));
	}
	$imgname=$path_to_save.'/'.$filename;
	$thumbname=$thumb_path_to_save.'/'.$filename;
	$lot_img=$brand_id.'/'.$filename;
	$img_sc = file_put_contents($imgname, $out);  
	echo ($filename." ".$img_sc."!<br>");
	
	img_resize($imgname,$imgname, 400, 400);
	chmod($imgname,0644);
	
	img_resize($imgname,$thumbname, 160, 240);
	chmod($thumbname,0644);
	//var_dump($imgname);
	return ($lot_img);
}
	
/***********************************************************************************
Функция img_resize(): генерация thumbnails
Параметры:
  $src             - имя исходного файла
  $dest            - имя генерируемого файла
  $width, $height  - ширина и высота генерируемого изображения, в пикселях
Необязательные параметры:
  $rgb             - цвет фона, по умолчанию - белый
  $quality         - качество генерируемого JPEG, по умолчанию - максимальное (100)
***********************************************************************************/ 
function img_resize($src, $dest, $width, $height, $rgb=0xFFFFFF, $quality=100, $max=false)
{
  if (!file_exists($src)) return false;
 
  $size = getimagesize($src);
 
  if ($size === false) return false;
 
  // Определяем исходный формат по MIME-информации, предоставленной
  // функцией getimagesize, и выбираем соответствующую формату
  // imagecreatefrom-функцию.
  $format = strtolower(substr($size['mime'], strpos($size['mime'], '/')+1));
  $icfunc = "imagecreatefrom" . $format;
  if (!function_exists($icfunc)) return false;

  $x_ratio = $width  / $size[0];
  $y_ratio = $height / $size[1];

  $ratio       = min($x_ratio, $y_ratio);
  $use_x_ratio = ($x_ratio == $ratio);
 
  if (!$max){
	$ratio = $ratio>1 ? 1 : $ratio;
  }
 
  $new_width   = floor($size[0] * $ratio);
  $new_height  = floor($size[1] * $ratio);
  $new_left    = $use_x_ratio  ? 0 : floor(($width - $new_width) / 2);
  $new_top     = !$use_x_ratio ? 0 : floor(($height - $new_height) / 2);
 
  $isrc = $icfunc($src);
//  $idest = imagecreatetruecolor($width, $height);
  $idest = imagecreatetruecolor($new_width, $new_height);
 
  imagefill($idest, 0, 0, $rgb);
//  imagecopyresampled($idest, $isrc, $new_left, $new_top, 0, 0,  
//    $new_width, $new_height, $size[0], $size[1]);
  imagecopyresampled($idest, $isrc, 0, 0, 0, 0,  
    $new_width, $new_height, $size[0], $size[1]);
 
  imagejpeg($idest, $dest, $quality);
 
  imagedestroy($isrc);
  imagedestroy($idest);
 
  return true;
 
}	
?>