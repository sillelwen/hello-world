-- phpMyAdmin SQL Dump
-- version 4.6.4
-- https://www.phpmyadmin.net/
--
-- Хост: 127.0.0.1
-- Время создания: Авг 22 2020 г., 00:36
-- Версия сервера: 5.7.15
-- Версия PHP: 7.0.10

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- База данных: `lk`
--

DELIMITER $$
--
-- Процедуры
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `AddRegisterRow` (IN `p_userid` BIGINT(20) UNSIGNED, IN `p_waybill` VARCHAR(30), IN `p_receptionDate` DATE, IN `p_addressee` VARCHAR(30), IN `p_address` VARCHAR(150), IN `p_contactFIO` VARCHAR(50), IN `p_contactPhone1` VARCHAR(30), IN `p_contactPhone2` VARCHAR(30), IN `p_places` INT(11), IN `p_weight` DECIMAL(6,1), IN `p_dimension1` SMALLINT(6), IN `p_dimension2` SMALLINT(6), IN `p_dimension3` SMALLINT(6), IN `p_deliveryDay` INT(10) UNSIGNED, IN `p_deliveryDate` DATE, IN `p_timeSpanFrom` INT(11), IN `p_timeSpanTo` INT(11), IN `p_paymentTypeID` INT(10) UNSIGNED, IN `p_sum` DECIMAL(9,2), IN `p_note` VARCHAR(150), IN `p_receptionRegion` INT(11), IN `p_receptionCity` VARCHAR(30), IN `p_receptionAddress` VARCHAR(150), IN `p_receptionContactFIO` VARCHAR(50), IN `p_deliveryRegion` INT(11), IN `p_deliveryCity` VARCHAR(30), IN `p_status` INT(10) UNSIGNED, IN `p_addedFrom` TINYINT(4), IN `p_direction` ENUM('delivery','reception','both',''), IN `p_takePapers` BOOLEAN, IN `p_needNotification` BOOLEAN)  NO SQL
BEGIN

INSERT INTO register
(userid, waybill, receptionDate, addressee, address, contactFIO, contactPhone1, contactPhone2, places, weight,
dimension1, dimension2, dimension3, deliveryDay, deliveryDate, timeSpanFrom, timeSpanTo, paymentTypeID, sum, note, receptionRegion, receptionCity, receptionAddress, receptionContactFIO, deliveryRegion, deliveryCity, direction, takePapers, needNotification, status, addedFrom, creationDate)
VALUES
(p_userid, p_waybill, p_receptionDate, p_addressee, p_address, p_contactFIO, p_contactPhone1, p_contactPhone2, p_places, p_weight,
p_dimension1, p_dimension2, p_dimension3, p_deliveryDay, p_deliveryDate, p_timeSpanFrom, p_timeSpanTo, p_paymentTypeID, p_sum, p_note, p_receptionRegion, p_receptionCity, p_receptionAddress, p_receptionContactFIO, p_deliveryRegion, p_deliveryCity, p_direction, p_takePapers, p_needNotification, p_status, p_addedFrom, NOW());

SELECT LAST_INSERT_ID() as rowid;

END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `BatchCloseDate` (IN `date_to_close` DATE, IN `systemuserid` BIGINT(20) UNSIGNED)  NO SQL
BEGIN
	DECLARE row_id, userid BIGINT(20) UNSIGNED;
	DECLARE oldstatusname VARCHAR(30);
	DECLARE newstatusname VARCHAR(30);
	DECLARE details, detailstext VARCHAR(90);
	DECLARE done INT DEFAULT FALSE;
	DECLARE cur1 CURSOR FOR 
	SELECT `id`, `statusname` FROM tmp_register;
	DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;

	CREATE TEMPORARY TABLE tmp_register
	SELECT register.id, register.userid, register.status, orderstatuses.value as statusname
	FROM register
	LEFT JOIN orderstatuses
	ON register.status = orderstatuses.id
	LEFT JOIN
	receptiondates
	ON receptiondates.userid = register.userid AND receptiondates.date = date_to_close
	WHERE register.receptionDate = date_to_close and register.deleted=0 and receptiondates.date IS NULL;

	UPDATE register
	INNER JOIN tmp_register
	ON register.id = tmp_register.id
	SET register.status = 1
	WHERE tmp_register.status = 0;

	SELECT orderstatuses.value INTO newstatusname
	FROM orderstatuses WHERE
	orderstatuses.id = 1;

	OPEN cur1;

    read_loop: LOOP
		FETCH cur1 INTO row_id, oldstatusname;
		IF done THEN
			LEAVE read_loop;
		END IF;
		
		SET detailstext=CONCAT_WS('', oldstatusname, ' &rarr; ', newstatusname);
		SET details = CONCAT_WS('', 's:', CAST(LENGTH(detailstext) AS CHAR), ':"', detailstext, '";');
		CALL logevent('orderdata', 'BatchCloseDate', systemuserid, '127.0.0.1', 'Изменён статус отправления', row_id, details);
	END LOOP;

	CLOSE cur1;
		
	DROP TEMPORARY TABLE tmp_register;
    
    REPLACE INTO receptiondates (`date`, `userid`)
    SELECT date_to_close, users.id
    FROM users;

	INSERT INTO log
	(type, source, userid, userip, message, objectid, creationDate)
	SELECT
	DISTINCT 'info', 'BatchCloseDate', systemuserid, '127.0.0.1', CONCAT('Выполнено закрытие дня: ', DATE_FORMAT(date_to_close, '%Y-%m-%d')), users.id, NOW()
	FROM users;
	
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `logevent` (IN `p_type` ENUM('audit','info','warning','error','orderdata'), IN `p_source` VARCHAR(30), IN `p_userid` BIGINT UNSIGNED, IN `p_userip` VARCHAR(15), IN `p_message` VARCHAR(140), IN `p_objectid` BIGINT UNSIGNED, IN `p_details` TEXT)  NO SQL
BEGIN

INSERT INTO log
(type, source, userid, userip, message, objectid, creationDate)
VALUES
(p_type, p_source, p_userid, p_userip, p_message, p_objectid, NOW());

SET @rowid=LAST_INSERT_ID();

IF p_details IS NOT NULL AND p_details != '' THEN
	INSERT INTO logdetails (id, details) VALUES (@rowid, p_details);
END IF;

END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `setUserSetting` (IN `p_name` ENUM('deliveryterms','intervals','rows','notificationsEmail','receptionRegion','receptionCity','receptionAddress','receptionAddressee','deliveryRegion','deliveryCity'), IN `p_value` INT, IN `p_userid` BIGINT UNSIGNED)  NO SQL
IF (SELECT 1=1 FROM usersettings WHERE userid=p_userid AND name=p_name) THEN

UPDATE `usersettings` SET `value`=p_value WHERE `userid`=p_userid AND `name`=p_name;

ELSE

INSERT INTO `usersettings` (`name`, `value`, `userid`) 
VALUES (p_name, p_value, p_userid);

END IF$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `updateRegisterRowStatus` (IN `p_rowid` BIGINT UNSIGNED, IN `p_newstatus` BIGINT UNSIGNED)  NO SQL
BEGIN

SELECT oldstatus.value as oldStatusName, newstatus.value as newStatusName
FROM register
LEFT JOIN orderstatuses as oldstatus
ON oldstatus.id = register.status
LEFT JOIN orderstatuses as newstatus
ON newstatus.id = p_newstatus
WHERE register.id = p_rowid;

UPDATE register
SET register.status = p_newstatus
WHERE register.id = p_rowid;

END$$

--
-- Функции
--
CREATE DEFINER=`root`@`localhost` FUNCTION `hasNamedRight` (`usr` BIGINT UNSIGNED, `rghtNm` VARCHAR(30)) RETURNS TINYINT(1) NO SQL
RETURN (SELECT COUNT(*)>0 as hasright FROM userrights INNER JOIN rights ON rights.id = rightId WHERE userid = usr AND rights.name = rghtNm)$$

CREATE DEFINER=`u0941507_default`@`localhost` FUNCTION `hasRight` (`usr` BIGINT UNSIGNED, `rght` BIGINT UNSIGNED) RETURNS TINYINT(1) NO SQL
return (select count(*)>0 as hasright
from userrights
where userid = usr
and rightid = rght)$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Структура таблицы `addresses`
--

CREATE TABLE `addresses` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `userid` bigint(20) UNSIGNED NOT NULL,
  `region` bigint(20) UNSIGNED NOT NULL,
  `cityName` varchar(30) NOT NULL,
  `address` varchar(150) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `contractpaymenttypes`
--

CREATE TABLE `contractpaymenttypes` (
  `contracttype` bigint(20) UNSIGNED NOT NULL,
  `paymenttype` bigint(20) UNSIGNED NOT NULL,
  `enabled` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `contractpaymenttypes`
--

INSERT INTO `contractpaymenttypes` (`contracttype`, `paymenttype`, `enabled`) VALUES
(0, 0, 1),
(0, 1, 1),
(0, 2, 0),
(1, 0, 1),
(1, 1, 1),
(1, 2, 1),
(2, 0, 1),
(2, 1, 1),
(3, 0, 1),
(3, 1, 1),
(3, 2, 1),
(4, 0, 0),
(4, 1, 1);

-- --------------------------------------------------------

--
-- Структура таблицы `contracttypes`
--

CREATE TABLE `contracttypes` (
  `id` int(10) UNSIGNED NOT NULL,
  `value` varchar(30) NOT NULL,
  `enabled` tinyint(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `contracttypes`
--

INSERT INTO `contracttypes` (`id`, `value`, `enabled`) VALUES
(0, 'Cотрудник ДИ', 1),
(1, 'Дэкс1', 1),
(2, 'Дедал', 1),
(3, 'Дэкс/Дедал', 1),
(4, 'Тестовый тип договора', 1);

-- --------------------------------------------------------

--
-- Структура таблицы `deliveryintervals`
--

CREATE TABLE `deliveryintervals` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `timeSpanFrom` tinyint(4) NOT NULL,
  `timeSpanTo` tinyint(4) NOT NULL,
  `userid` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `deliveryterms`
--

CREATE TABLE `deliveryterms` (
  `id` int(11) NOT NULL,
  `value` varchar(30) NOT NULL,
  `enabled` tinyint(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `deliveryterms`
--

INSERT INTO `deliveryterms` (`id`, `value`, `enabled`) VALUES
(1, 'следующий день', 1),
(2, 'день в день', 1),
(3, 'в течение 2 рабочих дней', 1),
(4, 'в течение недели', 1);

-- --------------------------------------------------------

--
-- Структура таблицы `editablefields`
--

CREATE TABLE `editablefields` (
  `status` bigint(20) NOT NULL,
  `field` bigint(20) NOT NULL,
  `editable` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `editablefields`
--

INSERT INTO `editablefields` (`status`, `field`, `editable`) VALUES
(0, 1, 1),
(0, 2, 1),
(0, 3, 1),
(0, 4, 1),
(0, 5, 1),
(0, 6, 1),
(0, 7, 1),
(0, 8, 1),
(0, 9, 1),
(0, 10, 1),
(0, 11, 1),
(0, 12, 1),
(0, 13, 1),
(0, 14, 1),
(0, 15, 1),
(0, 16, 1),
(0, 17, 1),
(0, 18, 1),
(0, 19, 1),
(0, 20, 1),
(0, 21, 1),
(0, 22, 1),
(0, 23, 1),
(1, 1, 1),
(1, 2, 1),
(1, 3, 1),
(1, 4, 1),
(1, 5, 1),
(1, 6, 1),
(1, 7, 1),
(1, 8, 1),
(1, 9, 1),
(1, 10, 1),
(1, 11, 1),
(1, 12, 1),
(1, 13, 1),
(1, 14, 1),
(1, 15, 1),
(1, 16, 1),
(1, 17, 1),
(1, 18, 1),
(1, 19, 1),
(1, 20, 1),
(1, 21, 1),
(1, 22, 1),
(1, 23, 1),
(2, 1, 1),
(2, 3, 1),
(2, 4, 1),
(2, 5, 1),
(2, 6, 1),
(2, 10, 1),
(2, 11, 1),
(2, 12, 1),
(2, 13, 1),
(2, 14, 1),
(2, 15, 1),
(2, 16, 1),
(2, 17, 1),
(2, 18, 1),
(2, 22, 1),
(2, 23, 1);

-- --------------------------------------------------------

--
-- Структура таблицы `fields`
--

CREATE TABLE `fields` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `fieldName` varchar(30) NOT NULL,
  `value` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `fields`
--

INSERT INTO `fields` (`id`, `fieldName`, `value`) VALUES
(1, 'waybill', 'Номер заказа'),
(2, 'receptionDate', 'Дата забора'),
(3, 'addressee', 'Компания получатель'),
(4, 'address', 'Адрес доставки'),
(5, 'contactFIO', 'ФИО получателя'),
(6, 'contactPhones', 'Контактные телефоны'),
(7, 'places', 'Кол-во мест'),
(8, 'weight', 'Вес'),
(9, 'dimensions', 'Габариты'),
(10, 'deliveryDate', 'Дата доставки'),
(11, 'timeSpan', 'Интервал доставки'),
(12, 'paymentTypeID', 'Тип оплаты'),
(13, 'sum', 'Сумма'),
(14, 'note', 'Примечание'),
(15, 'receptionRegion', 'Регион отправителя'),
(16, 'receptionCity', 'Город отправителя'),
(17, 'receptionAddress', 'Адрес отправителя'),
(18, 'receptionContactFIO', 'ФИО представителя отправителя'),
(19, 'deliveryRegion', 'Регион доставки'),
(20, 'deliveryCity', 'Город доставки'),
(21, 'direction', 'Направление'),
(22, 'takePapers', 'Забрать документы'),
(23, 'needNotification', 'Требуется уведомление');

-- --------------------------------------------------------

--
-- Структура таблицы `log`
--

CREATE TABLE `log` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `creationDate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `type` enum('audit','info','warning','error','orderdata') NOT NULL DEFAULT 'info',
  `source` varchar(30) NOT NULL,
  `userid` bigint(20) UNSIGNED NOT NULL,
  `userip` varchar(15) NOT NULL,
  `message` varchar(140) NOT NULL DEFAULT '',
  `objectid` bigint(20) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `logdetails`
--

CREATE TABLE `logdetails` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `details` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `orderstatuses`
--

CREATE TABLE `orderstatuses` (
  `id` int(10) UNSIGNED NOT NULL,
  `value` varchar(30) NOT NULL,
  `enabled` tinyint(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `orderstatuses`
--

INSERT INTO `orderstatuses` (`id`, `value`, `enabled`) VALUES
(0, 'регистрируется', 1),
(1, 'на складе продавца', 1),
(2, 'передан в курьерскую службу', 1),
(3, 'передан на доставку', 1),
(4, 'доставлен', 1),
(5, 'не удалось застать покупателя', 1),
(6, 'отказ покупателя', 1),
(7, 'возвращен продавцу', 1);

-- --------------------------------------------------------

--
-- Структура таблицы `passwordreset`
--

CREATE TABLE `passwordreset` (
  `userid` bigint(20) UNSIGNED NOT NULL,
  `clientip` varchar(15) NOT NULL,
  `token` varchar(60) NOT NULL,
  `requestdate` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `paymenttypes`
--

CREATE TABLE `paymenttypes` (
  `id` int(10) UNSIGNED NOT NULL,
  `value` varchar(30) NOT NULL,
  `enabled` tinyint(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `paymenttypes`
--

INSERT INTO `paymenttypes` (`id`, `value`, `enabled`) VALUES
(0, 'Оплачено', 1),
(1, 'Наличная', 1),
(2, 'Банковской картой', 1);

-- --------------------------------------------------------

--
-- Структура таблицы `quickrunconnector`
--

CREATE TABLE `quickrunconnector` (
  `orderid` bigint(20) UNSIGNED NOT NULL,
  `quickrun_id` char(36) NOT NULL,
  `quickrun_status` varchar(45) NOT NULL,
  `quickrun_status_id` int(11) NOT NULL,
  `updatedOn` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `receptiondates`
--

CREATE TABLE `receptiondates` (
  `date` date NOT NULL,
  `userid` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `regions`
--

CREATE TABLE `regions` (
  `id` int(10) UNSIGNED NOT NULL,
  `code` smallint(5) UNSIGNED NOT NULL,
  `value` varchar(30) NOT NULL,
  `reception` tinyint(1) NOT NULL DEFAULT '0',
  `delivery` tinyint(1) NOT NULL DEFAULT '1',
  `enabled` tinyint(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `regions`
--

INSERT INTO `regions` (`id`, `code`, `value`, `reception`, `delivery`, `enabled`) VALUES
(1, 78, 'СПб', 1, 1, 1),
(2, 77, 'Москва', 1, 1, 1),
(3, 48, 'Ленобласть', 1, 1, 1),
(4, 50, 'Моск. область', 0, 1, 1),
(5, 0, 'Россия', 0, 1, 1),
(6, 20, 'Чечня', 0, 0, 0);

-- --------------------------------------------------------

--
-- Структура таблицы `register`
--

CREATE TABLE `register` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `userid` bigint(20) UNSIGNED NOT NULL,
  `waybill` varchar(30) NOT NULL,
  `receptionDate` date DEFAULT NULL,
  `addressee` varchar(30) DEFAULT NULL,
  `address` varchar(150) NOT NULL,
  `contactFIO` varchar(50) DEFAULT NULL,
  `contactPhone1` varchar(30) NOT NULL,
  `contactPhone2` varchar(30) DEFAULT NULL,
  `places` int(11) DEFAULT NULL,
  `weight` decimal(6,1) NOT NULL,
  `dimension1` smallint(6) NOT NULL DEFAULT '0',
  `dimension2` smallint(6) NOT NULL DEFAULT '0',
  `dimension3` smallint(6) NOT NULL DEFAULT '0',
  `deliveryDay` int(10) UNSIGNED NOT NULL,
  `deliveryDate` date DEFAULT NULL,
  `timeSpanFrom` int(11) DEFAULT NULL,
  `timeSpanTo` int(11) DEFAULT NULL,
  `paymentTypeID` int(10) UNSIGNED NOT NULL,
  `sum` decimal(9,2) NOT NULL,
  `note` varchar(150) DEFAULT NULL,
  `receptionRegion` int(11) NOT NULL,
  `receptionCity` varchar(30) NOT NULL,
  `receptionAddress` varchar(150) NOT NULL,
  `receptionContactFIO` varchar(50) NOT NULL,
  `deliveryRegion` int(11) NOT NULL,
  `deliveryCity` varchar(30) NOT NULL,
  `direction` enum('delivery','reception','both') NOT NULL DEFAULT 'delivery',
  `takePapers` tinyint(1) NOT NULL,
  `needNotification` tinyint(1) NOT NULL,
  `status` int(10) UNSIGNED NOT NULL,
  `addedFrom` tinyint(4) NOT NULL,
  `creationDate` datetime NOT NULL,
  `deleted` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `rights`
--

CREATE TABLE `rights` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(30) NOT NULL,
  `description` varchar(140) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `rights`
--

INSERT INTO `rights` (`id`, `name`, `description`) VALUES
(1, 'admin', 'Administrative section access'),
(2, 'login', 'Sign-in to the system'),
(3, 'addregister', 'Add new registers'),
(4, 'viewregisters', 'View list of uploaded registers'),
(5, 'setstatus', 'Change orders\' statuses'),
(6, 'changesystemsettings', 'Change system settings and directories'),
(7, 'setuserrights', 'Change permissions for users'),
(8, 'viewlog', 'View event log'),
(9, 'viewstatus', 'View own register lines\' statuses'),
(10, 'setnotificationsemail', 'Set personal notifications email for user'),
(11, 'editusersettings', 'Change personal settings for users'),
(13, 'editaddress', 'Edit reception address when adding a register line'),
(14, 'editregister', 'Edit registered orders\' data');

-- --------------------------------------------------------

--
-- Структура таблицы `settings`
--

CREATE TABLE `settings` (
  `name` varchar(30) NOT NULL,
  `description` varchar(140) NOT NULL,
  `value` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `settings`
--

INSERT INTO `settings` (`name`, `description`, `value`) VALUES
('baseURL', 'Базовый URL сайта (используется при формировании писем)', 'http://lk/'),
('emailForFiles', 'Адрес электронной почты для отправки писем с файлами реестра', 'katty-kat@mail.ru'),
('emailFrom', 'Адрес электронной почты, от лица которого отправляются письма', 'katty-kat@mail.ru'),
('enableAutoBatchCloseDate', 'Разрешить автоматическое закрытие дня для всех по расписанию', '1'),
('enableAutoBatchQRStatusGet', 'Разрешить автоматическое обновление статусов из Бегунка', '1'),
('nameFrom', 'Имя (название) отправителя в почтовых заголовках From и Reply-To', 'Личный кабинет отправителя');

-- --------------------------------------------------------

--
-- Структура таблицы `userfiles`
--

CREATE TABLE `userfiles` (
  `userid` bigint(20) UNSIGNED NOT NULL,
  `filename` varchar(70) NOT NULL,
  `creationDate` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `userrights`
--

CREATE TABLE `userrights` (
  `userId` bigint(20) NOT NULL,
  `rightId` bigint(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `login` varchar(30) NOT NULL,
  `email` varchar(30) DEFAULT NULL,
  `companyName` varchar(30) NOT NULL,
  `password_hash` varchar(60) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `usersettings`
--

CREATE TABLE `usersettings` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` enum('deliveryterms','intervals','rows','notificationsEmail','receptionRegion','receptionCity','receptionAddress','receptionAddressee','deliveryRegion','deliveryCity','contractType','columns','columns_print','columns_commonreg','columns_width') NOT NULL,
  `value` int(11) DEFAULT NULL,
  `value_str` varchar(30) DEFAULT NULL,
  `userid` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `waybilltype`
--

CREATE TABLE `waybilltype` (
  `contracttype` bigint(20) UNSIGNED NOT NULL,
  `paymenttype` bigint(20) UNSIGNED NOT NULL,
  `simple` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `waybilltype`
--

INSERT INTO `waybilltype` (`contracttype`, `paymenttype`, `simple`) VALUES
(0, 0, 1),
(0, 1, 1),
(2, 0, 1),
(2, 1, 1),
(3, 0, 1),
(3, 1, 1);

-- --------------------------------------------------------

--
-- Структура таблицы `_jobs`
--

CREATE TABLE `_jobs` (
  `jobName` varchar(50) NOT NULL,
  `statusOK` tinyint(1) NOT NULL,
  `message` varchar(128) NOT NULL,
  `finishedOn` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Индексы сохранённых таблиц
--

--
-- Индексы таблицы `addresses`
--
ALTER TABLE `addresses`
  ADD UNIQUE KEY `id` (`id`);

--
-- Индексы таблицы `contractpaymenttypes`
--
ALTER TABLE `contractpaymenttypes`
  ADD UNIQUE KEY `contracttype` (`contracttype`,`paymenttype`);

--
-- Индексы таблицы `contracttypes`
--
ALTER TABLE `contracttypes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id` (`id`);

--
-- Индексы таблицы `deliveryintervals`
--
ALTER TABLE `deliveryintervals`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id` (`id`);

--
-- Индексы таблицы `deliveryterms`
--
ALTER TABLE `deliveryterms`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id` (`id`);

--
-- Индексы таблицы `editablefields`
--
ALTER TABLE `editablefields`
  ADD UNIQUE KEY `status` (`status`,`field`);

--
-- Индексы таблицы `fields`
--
ALTER TABLE `fields`
  ADD UNIQUE KEY `id` (`id`);

--
-- Индексы таблицы `log`
--
ALTER TABLE `log`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id` (`id`);

--
-- Индексы таблицы `logdetails`
--
ALTER TABLE `logdetails`
  ADD UNIQUE KEY `id` (`id`);

--
-- Индексы таблицы `orderstatuses`
--
ALTER TABLE `orderstatuses`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id` (`id`);

--
-- Индексы таблицы `paymenttypes`
--
ALTER TABLE `paymenttypes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id` (`id`);

--
-- Индексы таблицы `quickrunconnector`
--
ALTER TABLE `quickrunconnector`
  ADD UNIQUE KEY `orderid` (`orderid`);

--
-- Индексы таблицы `receptiondates`
--
ALTER TABLE `receptiondates`
  ADD UNIQUE KEY `date` (`date`,`userid`);

--
-- Индексы таблицы `regions`
--
ALTER TABLE `regions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id` (`id`),
  ADD UNIQUE KEY `value` (`value`);

--
-- Индексы таблицы `register`
--
ALTER TABLE `register`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id` (`id`);

--
-- Индексы таблицы `rights`
--
ALTER TABLE `rights`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id` (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Индексы таблицы `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`name`),
  ADD UNIQUE KEY `name` (`name`),
  ADD KEY `name_2` (`name`);

--
-- Индексы таблицы `userrights`
--
ALTER TABLE `userrights`
  ADD UNIQUE KEY `userId` (`userId`,`rightId`);

--
-- Индексы таблицы `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id` (`id`),
  ADD UNIQUE KEY `login` (`login`);

--
-- Индексы таблицы `usersettings`
--
ALTER TABLE `usersettings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id` (`id`),
  ADD UNIQUE KEY `name` (`name`,`userid`);

--
-- Индексы таблицы `waybilltype`
--
ALTER TABLE `waybilltype`
  ADD UNIQUE KEY `contracttype` (`contracttype`,`paymenttype`);

--
-- Индексы таблицы `_jobs`
--
ALTER TABLE `_jobs`
  ADD KEY `jobName` (`jobName`),
  ADD KEY `finishedOn` (`finishedOn`);

--
-- AUTO_INCREMENT для сохранённых таблиц
--

--
-- AUTO_INCREMENT для таблицы `addresses`
--
ALTER TABLE `addresses`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;
--
-- AUTO_INCREMENT для таблицы `contracttypes`
--
ALTER TABLE `contracttypes`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;
--
-- AUTO_INCREMENT для таблицы `deliveryintervals`
--
ALTER TABLE `deliveryintervals`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;
--
-- AUTO_INCREMENT для таблицы `deliveryterms`
--
ALTER TABLE `deliveryterms`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;
--
-- AUTO_INCREMENT для таблицы `fields`
--
ALTER TABLE `fields`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;
--
-- AUTO_INCREMENT для таблицы `log`
--
ALTER TABLE `log`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1161;
--
-- AUTO_INCREMENT для таблицы `orderstatuses`
--
ALTER TABLE `orderstatuses`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;
--
-- AUTO_INCREMENT для таблицы `paymenttypes`
--
ALTER TABLE `paymenttypes`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
--
-- AUTO_INCREMENT для таблицы `regions`
--
ALTER TABLE `regions`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;
--
-- AUTO_INCREMENT для таблицы `register`
--
ALTER TABLE `register`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=168;
--
-- AUTO_INCREMENT для таблицы `rights`
--
ALTER TABLE `rights`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;
--
-- AUTO_INCREMENT для таблицы `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;
--
-- AUTO_INCREMENT для таблицы `usersettings`
--
ALTER TABLE `usersettings`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=362;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
