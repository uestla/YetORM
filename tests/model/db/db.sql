-- Adminer 3.6.3 MySQL dump

SET NAMES utf8;
SET foreign_key_checks = 0;
SET time_zone = 'SYSTEM';
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

DROP DATABASE IF EXISTS `yetorm_test`;
CREATE DATABASE `yetorm_test` /*!40100 DEFAULT CHARACTER SET utf8 COLLATE utf8_czech_ci */;
USE `yetorm_test`;

DROP TABLE IF EXISTS `author`;
CREATE TABLE `author` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(30) COLLATE utf8_czech_ci NOT NULL,
  `web` varchar(100) COLLATE utf8_czech_ci NOT NULL,
  `born` date DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

INSERT INTO `author` (`id`, `name`, `web`, `born`) VALUES
(11,	'Jakub Vrana',	'http://www.vrana.cz/',	NULL),
(12,	'David Grudl',	'http://davidgrudl.com/',	NULL),
(13,	'Geek',	'http://example.com',	NULL);

DROP TABLE IF EXISTS `book`;
CREATE TABLE `book` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `author_id` int(11) NOT NULL,
  `book_title` varchar(50) COLLATE utf8_czech_ci NOT NULL,
  `written` date DEFAULT NULL,
  `available` tinyint(1) unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `book_author` (`author_id`),
  KEY `book_title` (`book_title`),
  CONSTRAINT `book_author` FOREIGN KEY (`author_id`) REFERENCES `author` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

INSERT INTO `book` (`id`, `author_id`, `book_title`, `written`, `available`) VALUES
(1,	11,	'1001 tipu a triku pro PHP',	'2010-01-01',	1),
(2,	11,	'JUSH',	'2007-01-01',	1),
(3,	12,	'Nette',	'2004-01-01',	1),
(4,	12,	'Dibi',	'2005-01-01',	1);

DROP TABLE IF EXISTS `book_tag`;
CREATE TABLE `book_tag` (
  `book_id` int(11) NOT NULL,
  `tag_id` int(11) NOT NULL,
  PRIMARY KEY (`book_id`,`tag_id`),
  KEY `book_tag_tag` (`tag_id`),
  CONSTRAINT `book_tag_tag` FOREIGN KEY (`tag_id`) REFERENCES `tag` (`id`),
  CONSTRAINT `book_tag_book` FOREIGN KEY (`book_id`) REFERENCES `book` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

INSERT INTO `book_tag` (`book_id`, `tag_id`) VALUES
(1,	21),
(3,	21),
(4,	21),
(1,	22),
(4,	22),
(2,	23);

DROP TABLE IF EXISTS `tag`;
CREATE TABLE `tag` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(20) COLLATE utf8_czech_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

INSERT INTO `tag` (`id`, `name`) VALUES
(23,	'JavaScript'),
(22,	'MySQL'),
(24,	'Neon'),
(21,	'PHP');

-- 2013-04-07 13:56:07