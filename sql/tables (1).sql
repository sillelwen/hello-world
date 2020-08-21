SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;


CREATE TABLE IF NOT EXISTS `addresses` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `userid` bigint(20) UNSIGNED NOT NULL,
  `region` bigint(20) UNSIGNED NOT NULL,
  `cityName` varchar(30) NOT NULL,
  `address` varchar(150) NOT NULL,
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `contractpaymenttypes` (
  `contracttype` bigint(20) UNSIGNED NOT NULL,
  `paymenttype` bigint(20) UNSIGNED NOT NULL,
  `enabled` tinyint(1) NOT NULL,
  UNIQUE KEY `contracttype` (`contracttype`,`paymenttype`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `contracttypes` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `value` varchar(30) NOT NULL,
  `enabled` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `deliveryintervals` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `timeSpanFrom` tinyint(4) NOT NULL,
  `timeSpanTo` tinyint(4) NOT NULL,
  `userid` bigint(20) UNSIGNED NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `deliveryterms` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `value` varchar(30) NOT NULL,
  `enabled` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `editablefields` (
  `status` bigint(20) NOT NULL,
  `field` bigint(20) NOT NULL,
  `editable` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `fields` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `fieldName` varchar(30) NOT NULL,
  `value` varchar(30) NOT NULL,
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `log` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `creationDate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `type` enum('audit','info','warning','error','orderdata') NOT NULL DEFAULT 'info',
  `source` varchar(30) NOT NULL,
  `userid` bigint(20) UNSIGNED NOT NULL,
  `userip` varchar(15) NOT NULL,
  `message` varchar(140) NOT NULL DEFAULT '',
  `objectid` bigint(20) UNSIGNED DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=730 DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `logdetails` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `details` text,
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `orderstatuses` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `value` varchar(30) NOT NULL,
  `enabled` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `passwordreset` (
  `userid` bigint(20) UNSIGNED NOT NULL,
  `clientip` varchar(15) NOT NULL,
  `token` varchar(60) NOT NULL,
  `requestdate` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `paymenttypes` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `value` varchar(30) NOT NULL,
  `enabled` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `quickrunconnector` (
  `orderid` bigint(20) UNSIGNED NOT NULL,
  `quickrun_id` char(36) NOT NULL,
  `quickrun_status` varchar(45) NOT NULL,
  `quickrun_status_id` int(11) NOT NULL,
  `updatedOn` datetime NOT NULL,
  UNIQUE KEY `orderid` (`orderid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `receptiondates` (
  `date` date NOT NULL,
  `userid` bigint(20) UNSIGNED NOT NULL,
  UNIQUE KEY `date` (`date`,`userid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `regions` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `code` smallint(5) UNSIGNED NOT NULL,
  `value` varchar(30) NOT NULL,
  `reception` tinyint(1) NOT NULL DEFAULT '0',
  `delivery` tinyint(1) NOT NULL DEFAULT '1',
  `enabled` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`),
  UNIQUE KEY `value` (`value`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `register` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
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
  `status` int(10) UNSIGNED NOT NULL,
  `addedFrom` tinyint(4) NOT NULL,
  `creationDate` datetime NOT NULL,
  `deleted` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=165 DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `rights` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(30) NOT NULL,
  `description` varchar(140) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `settings` (
  `name` varchar(30) NOT NULL,
  `description` varchar(140) NOT NULL,
  `value` varchar(255) NOT NULL,
  PRIMARY KEY (`name`),
  UNIQUE KEY `name` (`name`),
  KEY `name_2` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `userfiles` (
  `userid` bigint(20) UNSIGNED NOT NULL,
  `filename` varchar(70) NOT NULL,
  `creationDate` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `userrights` (
  `userId` bigint(20) NOT NULL,
  `rightId` bigint(20) NOT NULL,
  UNIQUE KEY `userId` (`userId`,`rightId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `users` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `login` varchar(30) NOT NULL,
  `email` varchar(30) DEFAULT NULL,
  `companyName` varchar(30) NOT NULL,
  `password_hash` varchar(60) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`),
  UNIQUE KEY `login` (`login`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `usersettings` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` enum('deliveryterms','intervals','rows','notificationsEmail','receptionRegion','receptionCity','receptionAddress','receptionAddressee','deliveryRegion','deliveryCity','contractType','columns','columns_print','columns_commonreg') NOT NULL,
  `value` int(11) DEFAULT NULL,
  `value_str` varchar(30) DEFAULT NULL,
  `userid` bigint(20) UNSIGNED NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`),
  UNIQUE KEY `name` (`name`,`userid`)
) ENGINE=InnoDB AUTO_INCREMENT=298 DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `waybilltype` (
  `contracttype` bigint(20) UNSIGNED NOT NULL,
  `paymenttype` bigint(20) UNSIGNED NOT NULL,
  `simple` tinyint(1) NOT NULL,
  UNIQUE KEY `contracttype` (`contracttype`,`paymenttype`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
