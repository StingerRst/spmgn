<?php
ini_set('error_reporting', E_ALL);
ini_set('display_errors',1);
error_reporting(E_ALL); 
include_once('./includes/simple_html_dom.php');
// Создаем объект DOM на основе кода, полученного по ссылке
$html = file_get_html('http://fishmany.ru/internet_magazin');
//var_dump ($html);
// Найти все элементы <div>, у которых id=foo
$ret = $html->find('div.catalog-wr form.catalog-block');
//var_dump (count($ret));
//var_dump ($ret);
//echo ('</br>');
Echo ('Наименование;Описание;Цена;Орг%;Картинка</br>');
$repl=array('руб.');
foreach($ret as $element) {
	$html2=file_get_html('http://fishmany.ru'.$element->find('div.catalog-block-name a')[0]->href);
//var_dump ($html2);
//echo ('</br>');
	$description=$html2->find('div.prod-card-discription-body');
//var_dump (count($description));
//echo ('</br>');
	
// Наименование
	echo ($element->find('div.catalog-block-name a')[0]->plaintext.';');
//Описание	
	echo ($description[0]->plaintext.';');
	//Echo (';');
//Цена	
	echo (trim(str_replace($repl,'',$element->find('div.catalog-block-price span.new-price')[0]->plaintext)).';');
	//echo (trim(str_replace($repl,'',$element->find('div.price span.item_price ')[0]->plaintext)).';');
//Орг сбор	
	Echo ('15;');
	//var_dump($html2->find('#downl_photo a')[0]->href); 	
//Картинка
	echo ('http://fishmany.ru'.$element->find('a.highslide')[0]->href);
	




	echo ('</br>');
	//	var_dump ($element);
};	
?>