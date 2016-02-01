<?php
/**
 * Базовый класс для реализации работы с БД
 * @author Elena
 */

class DataAccess {

    protected $connection = null;

    function __construct($connection = null){
        if ($connection)
            $this->setConnection($connection);
        else
            $this->connect();
    }

    /**
     * Возвращает настройки подключения к БД из файла /include/config.ini
     * @return string - строка подключения к БД
     * @throws Exception
     */
    private function readDBParams(){
        $ini_file_path = 'include/config.ini';

        if (file_exists($ini_file_path)){
            $ini_file_options = parse_ini_file($ini_file_path);
            if (empty($ini_file_options)){
                throw new Exception("Wrong format of configuration file ($ini_file_path)");
            }
            else{
                $params = array_change_key_case($ini_file_options, CASE_LOWER);
                $connectionString = "host={$params['dbhost']} port={$params['dbport']} dbname={$params['dbname']} user={$params['dblogin']} password={$params['dbpassword']}";
            }
        }
        else
            throw new Exception("Configuration file ($ini_file_path) does not exist");

        return $connectionString;
    }

    /**
     * Создает подключение к БД
     * @return null|resource
     * @throws Exception
     */
    private function connect(){
        $connectionString = $this->readDBParams();

        $this->connection = pg_connect($connectionString." options='--client_encoding=UTF8'");

        return $this->connection;
    }

    /**
     * Инициализирует объект подключения к БД
     * @param $connection
     */
    protected function setConnection($connection){
        $this->connection = $connection;
    }

    function startTransaction(){
        pg_query($this->connection, 'begin');
    }

    function commitTransaction(){
        pg_query($this->connection, 'commit');
    }

    function rollbackTransaction(){
        pg_query($this->connection, 'rollback');
    }

    /**
     * Выполняет параметризованный SQL запрос и возвращает его результат
     * @param string $query   - текст запроса
     * @param array $bindvars - массив параметров
     * @return resource
     * @throws Exception
     */
    function execQuery($query, $bindvars = array()){
        $result = pg_query_params($this->connection, $query, $bindvars);

        if ($result === false)
            throw new Exception(pg_last_error());

        $result = pg_fetch_all($result);

        return $result === false ? array() : $result;
    }

    /**
     * Вставляет записи из массива в таблицу
     * @param string $tableName - имя таблицы
     * @param array  $data      - массив строк
     * @param string $delimeter - разделитель значений в строках
     * @return bool
     * @throws Exception
     */
    function insertFromCSV($tableName, $data, $delimeter = '|'){
        $result = pg_copy_from($this->connection, $tableName, $data, $delimeter);

        if ($result === false)
            throw new Exception(pg_last_error());

        return $result;
    }
} 