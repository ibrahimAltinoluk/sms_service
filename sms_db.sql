 

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

 

CREATE TABLE IF NOT EXISTS `Chrome` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `member` int(11) NOT NULL,
  `token` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1   ;
 

CREATE TABLE IF NOT EXISTS `Contact` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `device` int(11) NOT NULL,
  `title` varchar(255) COLLATE utf8_turkish_ci NOT NULL,
  `phone` varchar(22) COLLATE utf8_turkish_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_turkish_ci ;
 
 

CREATE TABLE IF NOT EXISTS `Device` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `member` int(11) NOT NULL,
  `title` varchar(255) COLLATE utf8_turkish_ci DEFAULT 'Mobil Cihazım',
  `deviceId` varchar(255) CHARACTER SET latin1 NOT NULL,
  `register` text COLLATE utf8_turkish_ci,
  `token` text COLLATE utf8_turkish_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_turkish_ci   ;
 
CREATE TABLE IF NOT EXISTS `Member` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_turkish_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8_turkish_ci DEFAULT NULL,
  `fbid` varchar(255) COLLATE utf8_turkish_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_turkish_ci ;
 
 

CREATE TABLE IF NOT EXISTS `Notification` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `device` int(11) NOT NULL,
  `who` varchar(255) COLLATE utf8_turkish_ci NOT NULL,
  `at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `message` text COLLATE utf8_turkish_ci,
  `number` varchar(255) COLLATE utf8_turkish_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_turkish_ci  ;
 