<?php
        // $_POST['title'] содержит данные из поля "Тема"
                $title = 'mymail.php';
                $mess =  'mymail.php';
        // $to - кому отправляем
                $to = 'spmgn.ru@yandex.ru';
        // $from - от кого
                $from='support@spmgn.ru';
        // функция, которая отправляет наше письмо.
                echo mail($to, $title, $mess, 'From:'.$from);
                echo 'Спасибо! Ваше письмо отправлено.';
?>
