<?php
ini_set('error_reporting', E_ALL);
ini_set('display_errors',1);
error_reporting(E_ALL); 
include_once('./includes/simple_html_dom.php');
// Создаем объект DOM на основе кода, полученного по ссылке
$html = file_get_html('http://saviobags.ru/katalog/kategoriya/40-%D0%BC%D1%83%D0%B6%D1%81%D0%BA%D0%B8%D0%B5-%D1%81%D1%83%D0%BC%D0%BA%D0%B8-%D0%B8%D0%B7-%D0%B8%D1%81%D0%BA%D1%83%D1%81%D1%81%D1%82%D0%B2%D0%B5%D0%BD%D0%BD%D0%BE%D0%B9-%D0%BA%D0%BE%D0%B6%D0%B8.html');
//var_dump ($html);
// Найти все элементы <div>, у которых id=foo
$ret = $html->find('div');
var_dump (count($ret));
//var_dump ($ret);
echo ('</br>');
Echo ('Наименование;Описание;Цена;Орг%;Картинка</br>');
$repl=array('от','р.');
foreach($ret as $element) {
	//$html2= file_get_html('http://velkotex-shop.ru'.$element->find('div.item_title a')[0]->href);
	$html2=file_get_html('http://velkotex-shop.ru'.$element->find('a.item_title')[0]->href);
//var_dump ($html2);
//echo ('</br>');
	$description=$html2->find('div.tabcontent div div.row div span');
var_dump (count($description));
echo ('</br>');
	

	echo ($element->find('a.item_title')[0]->plaintext.';');
	echo ($description[0]->plaintext.';');
	//Echo (';');
	//echo (trim(str_replace($repl,'',$element->find('div.price span.item_price ')[0]->plaintext)).';');
	Echo ('15;');
	var_dump($html2->find('#downl_photo a')[0]->href); 	
	
	//echo ('http://velkotex-shop.ru'.$html2->find('p#downl_photo a')[0]->href);
	




	echo ('</br>');
	//	var_dump ($element);
};	
?>