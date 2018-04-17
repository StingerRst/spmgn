<?php

	
var_dump( $HTTP_USER_AGENT);

// Указываем всем подключающимся скриптам,
// что они вызывается из главного файла.
// Для защиты от вызова их напрямую.

define('IN_PHPBB', true);

// Создаем переменную, содержащую
// путь к корню сайта.
$phpbb_root_path = (defined('PHPBB_ROOT_PATH')) ? PHPBB_ROOT_PATH : './';
 
// Указываем расширение к подключаемым файлам.
// Обычно .php.
$phpEx = substr(strrchr(__FILE__, '.'), 1);
//echo ($phpbb_root_path . 'common.' . $phpEx); 
// Подключаем ядро phpBB.
include($phpbb_root_path . 'common.' . $phpEx);
include($phpbb_root_path . 'smsfox.' . $phpEx);
include($phpbb_root_path . 'includes/functions_display.' . $phpEx);

// Запускаем инициализацию сессии.
$user->session_begin();
$auth->acl($user->data);
$user->setup('viewforum');

if ($user->data['user_id'] == ANONYMOUS)
{
    login_box('', $user->lang['LOGIN']);
}
if (!$auth->acl_get('a_'))
{
	trigger_error('NO_ADMIN');
}
echo('start </br>');

/*  конфигурация standalone приложения Вконтакте
 *  ID приложения и ключ можно получить на странице редактирования приложения, или после добавления
 */
 
//$vkontakteApplicationId = '4610795';
//$vkontakteKey ='zCPaCW4jtGOOXTwZIhdM';

// ID юзера, к которому должно подключаться приложение
//$vkontakteUserId='9908274';
//$token='5071cf144dd5b330eb8b81a8b7200b76bd092cbc8b89e5d0f03a01b881215649e70441b8abc1a1a56120f';
//$token='3cdc117e897a699048358420ac0bf064f61ead2433a7a1fb93e117435f7beb14f15b41db3d7276a848563';

//$url = 'https://api.vkontakte.ru/method/photos.getAlbums?uid=9908274&access_token='.$token; //делаем запрос, получаем данные об альбомах паблика Хабры. gid = ID групы или паблика
//$url = 'https://api.vkontakte.ru/method/photos.get?uid=9908274&aid=221204130&extended=1&access_token='.$token; //делаем запрос, получаем данные об альбомах паблика Хабры. gid = ID групы или паблика

//$url = 'https://api.vkontakte.ru/method/photos.getComments?pid=379956482&access_token='.$token; //делаем запрос, получаем данные об альбомах паблика Хабры. gid = ID групы или паблика
//$url = 'https://api.vkontakte.ru/method/friends.get?uid=170533414&access_token='.$token; //делаем запрос, получаем данные об альбомах паблика Хабры. gid = ID групы или паблика

//$content = file_get_contents($url);
//$json = json_decode($content, true); //обрабатываем полученный json


// Получаем Список каталогов Совместных покупок
//referer:https://vk.com/dev/market.getAlbums?params[owner_id]=-103187751&params[count]=50&params[v]=5.45
/*
$owner_id="-103187751";
$v="5.45";
$count="50";
$access_token='be041ca1378a6a9a300043f6b801fb947fe99ff34966227a5a2077f55594eb65e3166fc0b4ad8ac736bcd';
$url ="https://api.vk.com/method/market.get?owner_id=".$owner_id."&v=".$v."&count=".$count."&access_token=".$access_token;
//$url ="https://vk.com/dev/market.getAlbums?params[owner_id]=-103187751&params[count]=50&params[v]=5.45";
$content = file_get_contents($url);
$json = json_decode($content, true); //обрабатываем полученный json
var_dump ($json);
*/


//$url ="https://api.vk.com/oauth/access_token?v=5.21&client_id=4610795&client_secret=zCPaCW4jtGOOXTwZIhdM&grant_type=client_credentials";
//$content = file_get_contents($url);
//echo ($content);
$owner_id="-103187751";
$v="5.45";
$count="50";
//$access_token='8e91f3e28e91f3e28eaf820a418ed7a90988e918e91f3e2d84e3297cc050376e60afadf';
//$access_token='13d23256ad97722ce93d94257cca469dee2ce83467cfbb6e42d001be5aeedb761d40f2ffc24f04c6c1c02';

// Набрать в браузере
//https://oauth.vk.com/authorize?client_id=4610795&display=page&redirect_uri=https://oauth.vk.com/blank.html &scope=offline,market,friends,groups,messages,email,notifications,ads,&response_type=token&v=5.45
//https://oauth.vk.com/authorize?client_id=5342929&display=page&redirect_uri=https://oauth.vk.com/blank.html &scope=offline,market,messages&response_type=token&v=5.45

$access_token='7dd4ae326802450379368ba33d4e0197a102067f01ac4aed3ab92aa4fd314bb2ce20a5cf3aa103b184edd';
//$url ="https://api.vk.com/method/market.get?owner_id=".$owner_id."&v=".$v."&count=".$count."&access_token=".$access_token;
$item_id='145958';
$need_likes='0';
//$url ='http://vk.com/dev/market.getComments?params[owner_id]=-103187751&params[item_id]=145958&params[need_likes]=1&params[extended]=0&params[v]=5.45&access_token='.$access_token ;




//$url = 'https://api.vk.com/method/market.get';
$url = 'https://api.vk.com/method/market.getComments';
$params = array(
	'owner_id' => "-103187751",    // Кому отправляем
	'item_id' =>'145958',
	'count' => '100',   // Что отправляем
	'extended' => '0',   // Что отправляем
	'v' => '5.45',
	'access_token'=>'76c9ec39da0da18e49cf086259e1a3da687ec0adc7b7c4ef7b0de21ec0d7c90e568916a19ef7dc667384c'
	//'access_token'=>'3468ba5588dac101a50a9a200fd5eff85712d1e5a5346f161507012dc2a166aad365f9e5a0571f7cd45d1'
	
);


$content = file_get_contents($url, false, stream_context_create(array(
	'http' => array(
		'method'  => 'POST',
		'header'  => 'Content-type: application/x-www-form-urlencoded',
		'content' => http_build_query($params)
	)
)));



//$content = file_get_contents($url);
$json = json_decode($content, true); //обрабатываем полученный json
var_dump($json);

/*
// Отправка сообщения пользователю
Echo ('<br>Отправка сообщения пользователю<br>');
$url = 'https://api.vk.com/method/messages.send';
$params = array(
	'user_id' => "9908274",    // Кому отправляем
	'message' =>'test test test',
	'v' => '5.37',
	//'access_token'=>'e9e7683230dd92b5f646561963a3b4f1f07ab634c58277296a59404eefb3cb8c3315d601a901d0726c29f'
	'access_token'=>'3468ba5588dac101a50a9a200fd5eff85712d1e5a5346f161507012dc2a166aad365f9e5a0571f7cd45d1'
//	'access_token'=>'76c9ec39da0da18e49cf086259e1a3da687ec0adc7b7c4ef7b0de21ec0d7c90e568916a19ef7dc667384c'
	
);


$content = file_get_contents($url, false, stream_context_create(array(
	'http' => array(
		'method'  => 'POST',
		'header'  => 'Content-type: application/x-www-form-urlencoded',
		'content' => http_build_query($params)
	)
)));
$json = json_decode($content, true); //обрабатываем полученный json
var_dump($json);

*/







//echo("<br><br>");
//var_export($json);
//echo("<br><br>");

//print_r ($json);

/*
$code = $_GET['code'];
$secret = 'zCPaCW4jtGOOXTwZIhdM'; //секретный ключ вашего приложения
$idapp = '4610795'; //id вашего приложения
$json = file_get_contents('https://api.vk.com/oauth/token?client_id='.$idapp.'&code='.$code.'&client_secret='.$secret);

$obj = json_decode($json);
$ololo = $obj->{'access_token'};
$access_token=$ololo;
var_dump($access_token);
echo 'Добро пожаловать';
*/

//$json = json_decode($content, true); //обрабатываем полученный json
//var_dump ($json);




/*
// получение списка друзей

$u_id="9908274";

$content = file_get_contents('https://api.vk.com/method/friends.get?user_id='.$u_id);
$json = json_decode($content, true); //обрабатываем полученный json
         
var_dump ($json);

*/
/*
$url = 'https://api.vk.com/dev/market.get';
$params = array(
	'owner_id' => "-103187751",    // Кому отправляем
	'count' => '100',   // Что отправляем
	'extended' => '0',   // Что отправляем
	'v' => '5.45',
);


$result = file_get_contents($url, false, stream_context_create(array(
	'http' => array(
		'method'  => 'POST',
		'header'  => 'Content-type: application/x-www-form-urlencoded',
		'content' => http_build_query($params)
	)
)));




$json = json_decode($result, true); //обрабатываем полученный json
         
var_dump ($json);
*/


?>
