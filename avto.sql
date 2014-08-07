CREATE DATABASE IF NOT EXISTS `avto` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
USE `avto`;

DROP TABLE IF EXISTS `avto`;
CREATE TABLE IF NOT EXISTS `avto` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `brand` varchar(50) NOT NULL,
  `model` varchar(50) NOT NULL,
  `price` varchar(30) NOT NULL,
  `typecarbody` varchar(50) DEFAULT NULL,
  `description` text,
  `photoname` varchar(18) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `avto_color`;
CREATE TABLE IF NOT EXISTS `avto_color` (
  `avto_id` int(11) NOT NULL,
  `color_id` int(3) NOT NULL,
  KEY `avto_id` (`avto_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `color`;
CREATE TABLE IF NOT EXISTS `color` (
  `id` int(3) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

TRUNCATE TABLE `color`;

INSERT INTO `color` (`id`, `name`) VALUES
(1, 'Черный'),
(2, 'Синий'),
(3, 'Красный'),
(4, 'Фиолетовый'),
(5, 'Зеленый'),
(6, 'Голубой'),
(7, 'Серый'),
(8, 'Желтый'),
(9, 'Белый');