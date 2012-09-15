# ************************************************************
# Sequel Pro SQL dump
# Версия 3408
#
# http://www.sequelpro.com/
# http://code.google.com/p/sequel-pro/
#
# Адрес: localhost (MySQL 5.5.25)
# Схема: timetable
# Время создания: 2012-09-15 13:49:39 +0000
# ************************************************************


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


# Дамп таблицы Dates
# ------------------------------------------------------------

DROP TABLE IF EXISTS `Dates`;

CREATE TABLE `Dates` (
  `ID` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `Date` date NOT NULL,
  `Dow` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `Date` (`Date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Дамп таблицы Files
# ------------------------------------------------------------

DROP TABLE IF EXISTS `Files`;

CREATE TABLE `Files` (
  `ID` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `Title` varchar(80) NOT NULL DEFAULT '',
  `Date` datetime NOT NULL,
  `Link` varchar(250) NOT NULL DEFAULT '',
  `Parsed` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`ID`),
  UNIQUE KEY `Constraint` (`Title`,`Date`),
  KEY `Parsed` (`Parsed`),
  KEY `Date` (`Date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Дамп таблицы Groups
# ------------------------------------------------------------

DROP TABLE IF EXISTS `Groups`;

CREATE TABLE `Groups` (
  `ID` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `Title` varchar(20) NOT NULL DEFAULT '',
  PRIMARY KEY (`ID`),
  UNIQUE KEY `Title` (`Title`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Дамп таблицы Pairs
# ------------------------------------------------------------

DROP TABLE IF EXISTS `Pairs`;

CREATE TABLE `Pairs` (
  `ID` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `FileID` int(11) unsigned NOT NULL,
  `GroupID` int(11) unsigned NOT NULL,
  `DateID` int(11) unsigned NOT NULL,
  `TimeID` int(11) unsigned NOT NULL,
  `Title` varchar(250) NOT NULL DEFAULT '',
  `StyleID` int(11) unsigned NOT NULL,
  PRIMARY KEY (`ID`),
  KEY `FileID` (`FileID`),
  KEY `GroupID` (`GroupID`),
  KEY `DateID` (`DateID`),
  KEY `TimeID` (`TimeID`),
  KEY `StyleID` (`StyleID`),
  CONSTRAINT `pairs_date` FOREIGN KEY (`DateID`) REFERENCES `Dates` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `pairs_file` FOREIGN KEY (`FileID`) REFERENCES `Files` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `pairs_group` FOREIGN KEY (`GroupID`) REFERENCES `Groups` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `pairs_style` FOREIGN KEY (`StyleID`) REFERENCES `Styles` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `pairs_time` FOREIGN KEY (`TimeID`) REFERENCES `Times` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Дамп таблицы Rooms
# ------------------------------------------------------------

DROP TABLE IF EXISTS `Rooms`;

CREATE TABLE `Rooms` (
  `ID` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `Number` varchar(10) NOT NULL DEFAULT '',
  `Building` varchar(10) NOT NULL DEFAULT '',
  PRIMARY KEY (`ID`),
  UNIQUE KEY `Constraint` (`Number`,`Building`),
  KEY `Number` (`Number`),
  KEY `Building` (`Building`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Дамп таблицы Styles
# ------------------------------------------------------------

DROP TABLE IF EXISTS `Styles`;

CREATE TABLE `Styles` (
  `ID` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `Style` varchar(250) NOT NULL DEFAULT '',
  PRIMARY KEY (`ID`),
  UNIQUE KEY `Style` (`Style`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Дамп таблицы Teachers
# ------------------------------------------------------------

DROP TABLE IF EXISTS `Teachers`;

CREATE TABLE `Teachers` (
  `ID` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `Title` varchar(250) NOT NULL DEFAULT '',
  `Link` varchar(250) DEFAULT NULL,
  PRIMARY KEY (`ID`),
  KEY `Title` (`Title`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Дамп таблицы Times
# ------------------------------------------------------------

DROP TABLE IF EXISTS `Times`;

CREATE TABLE `Times` (
  `ID` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `Number` tinyint(1) NOT NULL,
  `Time` varchar(20) NOT NULL DEFAULT '',
  PRIMARY KEY (`ID`),
  UNIQUE KEY `Time` (`Time`),
  KEY `Number` (`Number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Дамп таблицы Variables
# ------------------------------------------------------------

DROP TABLE IF EXISTS `Variables`;

CREATE TABLE `Variables` (
  `Key` varchar(20) NOT NULL DEFAULT '',
  `Value` text,
  PRIMARY KEY (`Key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Дамп таблицы Withs
# ------------------------------------------------------------

DROP TABLE IF EXISTS `Withs`;

CREATE TABLE `Withs` (
  `ID` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `PairID` int(11) unsigned NOT NULL,
  `GroupID` int(11) unsigned NOT NULL,
  PRIMARY KEY (`ID`),
  KEY `PairID` (`PairID`),
  KEY `GroupID` (`GroupID`),
  CONSTRAINT `withs_group` FOREIGN KEY (`GroupID`) REFERENCES `Groups` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `withs_pair` FOREIGN KEY (`PairID`) REFERENCES `Pairs` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;




/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
