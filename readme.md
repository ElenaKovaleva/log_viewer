Тестовое задание от HAYS.

Для запуска приложения необходимо:

1. Подключиться к БД и с помощью утилиты выполнения SQL-скриптов выполнить скрипт sql/patch.sql.
Он создаст две таблицы:
client_info - для хранения наборов IP, браузер, ОС
access_log - для хранения информации об обращении к страницам

2. Настроить параметры подключения к БД в конфигурациооном файле include/config.ini.

3. Тестовые файлы логов находятся в директории log, в случае необходимости, можно их заменить.

4. Выполнить скрипт log_parser.php, он сохранит информацию из файлов логов в БД.
Предполагается, что выполняется ротация логов, поэтому файл log1 обрабатывается полностью, данные из него дописываются в таблицу access_log.
Из файла log2 в таблицу client_info дописываются только уникальные данные, уникальность проверяется по трем полям - IP, ОС и браузер.

5. Для просмотра грида выполнить скрипт index.html.
Грид имеет буферное store, данные в него подгружаются частично, т.к. объемы логов обычно велики.
Сортировка и фильтрация данных происходит на сервере, на клиент передается порция отсортированных/отфильтрованных записей.
Размер страницы для демонстрации равен 10 записей.

6. Фильтрация по IP реализована в контекстном меню соответствующей колонки грида.
