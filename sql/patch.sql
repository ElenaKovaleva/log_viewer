/* Считаем, что БД существует и соединение с ней установлено */

/* Создаем таблицу CLIENT_INFO для хранения наборов IP, браузер, ОС */
DO $$
BEGIN
  IF NOT EXISTS (SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = 'client_info')
    THEN
      CREATE SEQUENCE gen_client_info_id START 1;
      CREATE TABLE client_info (
        id INTEGER PRIMARY KEY NOT NULL DEFAULT nextval('gen_client_info_id'::regclass),
        ip INET NOT NULL,
        client VARCHAR (1000) NOT NULL,
        os VARCHAR (1000) NOT NULL,
        CONSTRAINT unq_client_info UNIQUE(ip, os, client) --наборы ip + браузер + ОС уникальны
      );
      CREATE INDEX idx_client_info_ip ON client_info USING btree (ip); --индекс по полю ip, т.к. выборка будет осуществляться по нему
    END IF;
END$$;

CREATE OR REPLACE FUNCTION client_info_bi()
RETURNS trigger AS
$body$
BEGIN
  IF (SELECT 1 FROM client_info WHERE ip = NEW.ip AND client = NEW.client AND os = NEW.os) THEN
      RETURN OLD; --для обеспечения уникальности записей
  ELSEIF (NEW.id IS NULL) THEN
      NEW.id = nextval('gen_client_info_id');
  END IF;

  RETURN NEW;
END;
$body$
LANGUAGE 'plpgsql';

DROP TRIGGER IF EXISTS client_info_bi ON client_info;
CREATE TRIGGER client_info_bi BEFORE INSERT ON client_info FOR EACH ROW EXECUTE PROCEDURE client_info_bi();

/* Создаем таблицу ACCESS_LOG для хранения информации об обращении к страницам */
DO $$
BEGIN
  IF NOT EXISTS (SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = 'access_log')
    THEN
      CREATE SEQUENCE gen_access_log_id START 1;
      CREATE TABLE access_log (
        id INTEGER PRIMARY KEY NOT NULL DEFAULT nextval('gen_access_log_id'::regclass),
        access_date TIMESTAMP NOT NULL,
        ip INET NOT NULL,
        url_from VARCHAR (2000),
        url_to VARCHAR (2000)
      );
      CREATE INDEX idx_access_log_ip ON access_log USING btree (ip);
      CREATE INDEX idx_access_log_access_date ON access_log USING btree (access_date);
    END IF;
END$$;

CREATE OR REPLACE FUNCTION access_log_bi()
RETURNS trigger AS
$body$
BEGIN
  IF (NEW.id IS NULL) THEN
      NEW.id = nextval('gen_access_log_id');
  END IF;

  RETURN NEW;
END;
$body$
LANGUAGE 'plpgsql';

DROP TRIGGER IF EXISTS access_log_bi ON access_log;
CREATE TRIGGER access_log_bi BEFORE INSERT ON access_log FOR EACH ROW EXECUTE PROCEDURE access_log_bi();

/* Замечание:
* - в файле1 (лог обращения к страницам) нет информации, из какой ОС и браузера клиент обратился
* - насчет файла2 нет ограничения, что с одного ip могут быть обращения из одной ОС и одного браузера
* Поэтому, связывая информацию в данных файлах по ip, мы не получим однозначного соответсвия между обращением к URL и браузером (ОС)
*/