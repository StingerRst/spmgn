<?php
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
 
// Подключаем ядро phpBB.
include($phpbb_root_path . 'common.' . $phpEx);
include($phpbb_root_path . 'includes/functions_display.' . $phpEx);


// Запускаем инициализацию сессии.
$user->session_begin();
$auth->acl($user->data);
$user->setup('viewforum');

$input  = $_POST['input'];
//var_dump ($_POST);
//echo('input:'+$input);
if ($input)
{
$input=rtrim($input);
//echo ($input[strlen($input)-1]);

//if ($input[strlen($input)-1] == ':') {
//echo(strlen($input));
//echo mb_substr ( $input , 0,200 );
//}
//var_dump($input);
$sql ='SELECT   tb.question,   tb.module,   tb.answer FROM tb WHERE UCASE(tb.question) LIKE( "%'.$input.'%")';
$result=$db->sql_query($sql,"RESOURCE");
$cnt=0;
while ($row = $db->sql_fetchrow($result)) {
	$cnt=$cnt+1;
	$output = $row['module'].' - '. $row['answer'];
	$input_t = $row['question'];

}
if ($cnt==1){
	$input=$input_t;
	}
// echo($cnt);
if ($cnt > 1) {
	$output = 'Ответов: '.$cnt; 
}

$template->assign_var('INPUT',$input);
}
//$output= 'jvblajebfquefgqefb     efuhewfkqjbwefhiuqfhqlkjbwe;ufgqw';
$template->assign_var('OUTPUT',$output);

$template->set_filenames(array(
	'body' => 'ya_body.html',
));

page_footer();

?>