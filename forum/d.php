<?php
// Включаем код для отладки и определяем объект
    require_once("PHPDebug.php");
    $debug = new PHPDebug();

    // Простое сообщение на консоль
    $debug->debug("Очень простое сообщение на консоль");

    // Вывод переменной на консоль
    $x = 3;
    $y = 5;
    $z = $x/$y;
    $debug->debug("Переменная Z: ", $z);

    // Предупреждение
    $debug->debug("Простое предупреждение", null, WARN);

    // Информация
    $debug->debug("Простое информационное сообщение", null, INFO);

    // Ошибка
    $debug->debug("Простое сообщение об ошибке", null, ERROR);

    // Выводим массив в консоль
    $fruits = array("банан", "яблоко", "клубника", "ананас");
    $fruits = array_reverse($fruits);
    $debug->debug("Массив фруктов:", $fruits);

    // Выводим объект на консоль
    $book               = new stdClass;
    $book->title        = "Гарри Потный и кто-то из Ашхабада";
    $book->author       = "Д. K. Роулинг";
    $book->publisher    = "Arthur A. Levine Books";
    $book->amazon_link  = "http://www.amazon.com/dp/0439136369/";
    $debug->debug("Объект: ", $book);
?>