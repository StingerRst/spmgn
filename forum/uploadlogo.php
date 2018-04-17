<?php
/**
* @ignore
*/
define('IN_PHPBB', true);
$phpbb_root_path = (defined('PHPBB_ROOT_PATH')) ? PHPBB_ROOT_PATH : './';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
include($phpbb_root_path . 'common.' . $phpEx);
include($phpbb_root_path . '/includes/functions_upload.' . $phpEx);

// Start session management
$user->session_begin();
$auth->acl($user->data);
$user->setup();

if ($user->data['user_id'] == ANONYMOUS) login_box('', $user->lang['LOGIN']);

$sql = 'SELECT 
	' . BRANDS_TABLE . '.*
	FROM ' . BRANDS_TABLE . '
	JOIN ' . RESERVS_TABLE . '
	ON ' . BRANDS_TABLE . '.brand_id = ' . RESERVS_TABLE . '.brand_id';
if (!$auth->acl_get('a_')){
	$sql.=' WHERE ' . RESERVS_TABLE . '.user_id = ' . $user->data['user_id'] .'
	AND ' . RESERVS_TABLE . '.brand_id = '. $_POST['brand_id'];
}else{
	$sql.=' WHERE ' . RESERVS_TABLE . '.brand_id = '. $_POST['brand_id'];
}
$result = $db->sql_query($sql);	
$row = $db->sql_fetchrow($result);
if ($row) {
	$upload = new fileupload(basename($_FILES['userfile']['name']), array('jpg', 'jpeg', 'gif', 'png'));
	$logo = $upload->form_upload('userfile');
	$logo->clean_filename('unique_ext');
	
	$filename=$logo->get('realname');

	$destination = 'images/brands/';

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
	$logo->move_file($destination, true);

	if (sizeof($file->error))
	{
		$file->remove();
		$error = array_merge($error, $file->error);
	}

	$url = $destination.'/'.$filename;

	$sql = 'UPDATE 
	' . BRANDS_TABLE . '
	SET brand_logo = "' . $url . '"
	WHERE brand_id = '. $_POST['brand_id'];
	$db->sql_query($sql);
	img_resize($url, $url, 200, 200);
	chmod($url,0644);
	//echo ($sql);
	echo ';ok;'.$url;
	
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