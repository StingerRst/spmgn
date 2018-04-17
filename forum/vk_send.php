<?php

echo('start <br>');

// отпровляем ЛС
$url = 'https://api.vk.com/method/messages.send';
$params = array(
	'peer_id' => "-103187751",    
	'user_id' =>'9908274',// Кому отправляем
	'message' => 'test sp',   // текст сообщения
	'v' => '5.45',
	//'access_token'=>'04968f41d88ce5627ba46a45fd9dbd2c15e7378a7ee39c13acd2cc5ca7bb2b069787a632c63c39f9c5b1a'
	'access_token'=>'04968f41d88ce5627ba46a45fd9dbd2c15e7378a7ee39c13acd2cc5ca7bb2b069787a632c63c39f9c5b1a'
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



$url =$json["error"]["redirect_uri"];
$params =$json["error"]["request_params"];

$content = file_get_contents($url, false, stream_context_create(array(
	'http' => array(
		'method'  => 'POST',
		'header'  => 'Content-type: application/x-www-form-urlencoded',
		'content' => http_build_query($params)
	)
)));
echo($content);

//$json = json_decode($content, true); //обрабатываем полученный json
//var_dump($json);

?>
