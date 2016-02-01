<?php
/**
 * Cкрипт парсинга файлов логов
 */
header('Content-Type: text/html; charset=UTF-8');

require_once('class/LogParser.class.php');

try{
    $lpObj = new LogParser();
    $lpObj->parse();
    echo 'Логи успешно сохранены в базу данных';
}
catch (Exception $e){
   echo $e->getMessage();
}

unset($lpObj);