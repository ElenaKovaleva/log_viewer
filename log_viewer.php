<?php
/**
 * Cкрипт заполнения store для Ext.Grid
 */

require_once('class/DataAccess.class.php');

$dataAccess = new DataAccess();

$records = array();

$start  = $_GET['start'] ? intval($_GET['start']) : 0;
$limit  = $_GET['limit'] ? intval($_GET['limit']) : 50;

//Серверная сортировка
$allowedSortProps = array('client', 'os', 'ip');
$sort = $_GET['sort'] ? json_decode($_GET['sort'], true) : array(0 => array('property' => 'ip', 'direction' => 'ASC'));
$sort = $sort[0];

if (!in_array($sort['property'], $allowedSortProps))
    $sort['property'] = 'ip';

if (!in_array($sort['direction'], array('ASC', 'DESC')))
    $sort['direction'] = 'ASC';

$sortStr = $sort['property'].' '.$sort['direction'];

//Серверная фильтрация по IP
$allowedFilterProps = array('ip');
$filter = $_GET['filter'] ? json_decode($_GET['filter'], true) : '';

$filterStr = '';

if (is_array($filter)){
    $filter = $filter[0];
    if (!in_array($filter['property'], $allowedFilterProps))
        $filter['property'] = 'ip';

    if ($filter['operator'] != 'like')
        $filter['operator'] = 'like';

    if (strlen($filter['value']) > 0)
        $filterStr = 'WHERE al.'.$filter['property'].'::text '.$filter['operator'].' \'%'.$filter['value'].'%\'';
}

//Запрос для подсчета общего кол-ва записей для пейджинга
$sqltext = 'SELECT count(*) FROM (
              SELECT DISTINCT al.ip, ci.client, ci.os
              FROM access_log al
              LEFT JOIN client_info ci ON ci.ip = al.ip
              '.$filterStr.'
            ) m';
$a = $dataAccess->execQuery($sqltext, array());

$sqltext = 'SELECT DISTINCT
                al.ip,
                ci.client,
                ci.os,
                (SELECT al1.url_from FROM access_log al1 WHERE al1.ip = al.ip ORDER BY al1.access_date ASC LIMIT 1)  AS url_from,
                (SELECT al2.url_to   FROM access_log al2 WHERE al2.ip = al.ip ORDER BY al2.access_date DESC LIMIT 1) AS url_to,
                count (DISTINCT al.url_to) AS url_count
            FROM access_log al
            LEFT JOIN client_info ci ON ci.ip = al.ip -- выбираем записи независимо есть ли для них браузер
            '.$filterStr.'
            GROUP BY al.ip, ci.client, ci.os
            ORDER BY '.$sortStr.'
            LIMIT $1 OFFSET $2';

$records = $dataAccess->execQuery($sqltext, array($limit, $start));

echo json_encode(array('records' => $records, 'totalCount' => $a[0]['count']));