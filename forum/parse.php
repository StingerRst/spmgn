<?php
ini_set('error_reporting', E_ALL);
ini_set('display_errors',1);
error_reporting(E_ALL); 
include_once('./includes/simple_html_dom.php');
// Создаем объект DOM на основе кода, полученного по ссылке
$html = file_get_html('http://wood-toys.ru/pages/9/?page=7&category=32&size=100');
//var_dump ($html);
// Найти все элементы <div>, у которых id=foo
$ret = $html->find('div.items div.product-preview');
//var_dump (count($ret));
//var_dump ($ret);
//echo ('</br>');
//Echo ('Наименование;Описание;Цена;Орг%;Картинка</br>');
foreach($ret as $element) {
	echo ($element->find('h3')[0]->plaintext.';');
	Echo (';');
	echo (trim(str_replace('руб.','',$element->find('span.price,new')[0]->plaintext)).';');
	Echo ('15;');
	echo ('http://wood-toys.ru'.$element->find('img.img-responsive,scale')[0]->src);
	echo ('</br>');
	//	var_dump ($element);
};	
?>