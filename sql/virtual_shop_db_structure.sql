CREATE USER 'virtual_shop'@'%' IDENTIFIED BY 'qba5AJNjE493CywV';
GRANT USAGE ON *.* TO  'virtual_shop'@'%' IDENTIFIED BY 'qba5AJNjE493CywV';
CREATE DATABASE IF NOT EXISTS virtual_shop;
GRANT ALL PRIVILEGES ON virtual_shop.* TO 'virtual_shop'@'%';

USE virtual_shop;

CREATE TABLE IF NOT EXISTS `articles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(1024) NOT NULL,
  `description` text NOT NULL,
  `price` bigint(20) NOT NULL,
  `discount` double,
  `discount_active_till` datetime,
  `avaliable` int(11) NOT NULL DEFAULT '0',
  `booked` int(11) NOT NULL DEFAULT '0',
  `bought` int(11) NOT NULL DEFAULT '0',
  `added_by_manager_id` int(11) NOT NULL,
  `last_modified_by_manager_id` int(11),
  PRIMARY KEY (`id`),
  KEY `added_by_manager_id` (`added_by_manager_id`),
  KEY `last_modified_by_manager_id` (`last_modified_by_manager_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(1024) NOT NULL,
  `email` varchar(1024) NOT NULL,
  `md5` varchar(32) NOT NULL,
  `money` bigint(20) NOT NULL,
  `is_manager` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `booked` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `article_id` int(11) NOT NULL,
  `count` int(11) NOT NULL,
  `booked_till` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `article_id` (`article_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `bought` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `article_id` int(11) NOT NULL,
  `count` int(11) NOT NULL,
  `bought_on` datetime NOT NULL,
  `money_spent` bigint(20) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `article_id` (`article_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
