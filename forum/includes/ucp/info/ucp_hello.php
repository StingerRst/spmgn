<?php
 
class ucp_hello_info
{
	// Данная функция возвращает массив, содержащий
	// информацию о текущем модуле.
	function module()
	{
                 return array(
                        // Имя файла основного модуля.
			'filename'	=> 'ucp_hello',
                        // Название модуля.
			// В рабочих модулях указывается языковая переменная, которая впоследствии заменяется
			// на текст соответствующего языка.
			'title'		=> 'Привет, мир!',
                        // Версия модуля.
			'version'	=> '1.0.0',
                        // Список подразделов этого модуля.
			// Определим подраздел main, выводящий строчку "HelloWorld"
			'modes'		=> array(
			   'main' => array(
                                  // Заголовок подраздела.
                                  'title' => 'Вывод строки',
                                  // Необходимые привилегии для доступа к этому подразделу.
				  // Если отсутствуют, то используются привилегии для доступа
				  // к UCP(User Control Panel).
                                  'auth' => '',
                                  // Категория, в которую входит этот подраздел.
                                  'cat' => array('UCP_HELLO')
                                  ),
			   ),
			);
	}
 
        // Данная функция вызывается при установке модуля в
        // администраторской панели.
        // В этом месте Ваш модуль может создавать необходимые
        // ему файлы, таблицы в базе данных и прочие действия.
	function install()
	{
	}
 
        // Эта функция вызывается при деактивации Вашего модуля
        // в администраторской панеле.
        // Позаботьтесь, пожалуйста, чтобы Ваш модуль не оставлял
        // после себя никакого мусора в системе.
	function uninstall()
	{
	}
}
 
?>