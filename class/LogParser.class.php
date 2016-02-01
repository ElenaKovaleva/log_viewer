<?php
/**
 * Класс парсинга файлов логов
 * @author Elena
 */

require_once('DataAccess.class.php');

class LogParser extends DataAccess {

    private $errors = array(
        'FILE_NOT_FOUND' => 'Файл %s не существует'
    );

    private function showError($error, $data){
        throw new Exception(sprintf($this->errors[$error], $data));
    }

    function __construct(){
        parent::__construct($this->connection);
    }

    function parse($logDirectory = 'log'){

        $log1 = $logDirectory.'/log1.log';
        $log2 = $logDirectory.'/log2.log';
        if (!file_exists($log1))
            $this->showError('FILE_NOT_FOUND', $log1);
        if (!file_exists($log2))
            $this->showError('FILE_NOT_FOUND', $log2);

        $this->startTransaction();
        try{

            $log1Data = array();
            $log2Data = array();

            //Открываем файл log1.log для чтения
            if ($handle = fopen($log1, 'r')){
                while (!feof($handle)){
                    //Читаем строку
                    if ($s = trim(fgets($handle))){
                        $arr = explode('|', $s);
                        if (count($arr) > 0){
                            $date = $arr[0].' '.$arr[1]; //склеиваем дату и время в timestamp
                            if ($this->isDate($date) && $this->isIP($arr[2]) && $this->isURL($arr[3]) && $this->isURL($arr[4])){
                                $row = '\\N|'.$date.'|'.$arr[2].'|'.$arr[3].'|'.$arr[4]; //вместо id подставляем null, чтобы сработал триггер для вычисления автономера
                                array_push($log1Data, $row);
                            }
                        }
                    }
                }
            }

            if (count($log1Data) > 0)
                $this->insertFromCSV('access_log', $log1Data);

            //Открываем файл log2.log для чтения
            if ($handle = fopen($log2, 'r')){
                while (!feof($handle)){
                    //Читаем строку
                    if ($s = trim(fgets($handle))){
                        $arr = explode('|', $s);
                        if (count($arr) > 0){
                            if ($this->isIP($arr[0]) && $this->isString($arr[1]) && $this->isString($arr[2])){
                                $row = '\\N|'.$s; //вместо id подставляем null, чтобы сработал триггер для вычисления автономера
                                array_push($log2Data, $row);
                            }
                        }
                    }
                }
            }

            $log2Data = array_unique($log2Data);
            if (count($log2Data) > 0)
                $this->insertFromCSV('client_info', $log2Data);

            $this->commitTransaction();
        }
        catch (Exception $e){
            $this->rollbackTransaction();
            throw new Exception($e->getMessage());
        }

        return true;
    }

    function isDate($value, $format = 'd.m.Y H:i:s'){
        $d = DateTime::createFromFormat($format, $value);
        return $d && $d->format($format) == $value;
    }

    function isIP($value){
        return filter_var($value, FILTER_VALIDATE_IP);
    }

    function isURL($value){
        return filter_var($value, FILTER_VALIDATE_URL); //TODO: non ASCII
    }

    function isString($value, $maxLength = 1000){
        return is_string($value) ? (mb_strlen($value) > $maxLength ? mb_substr($value, 0, $maxLength) : $value) : false;
    }

} 