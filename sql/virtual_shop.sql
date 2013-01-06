CREATE TABLE IF NOT EXISTS `articles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(1024) NOT NULL,
  `description` text NOT NULL,
  `price` bigint(20) NOT NULL,
  `discount` double NOT NULL,
  `discount_active_till` datetime NOT NULL,
  `added_by_manager_id` int(11) NOT NULL,
  `last_modified_by_manager_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `article_instances` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `article_id` int(11) NOT NULL,
  `booked_till` datetime DEFAULT NULL,
  `booked_by_user_id` int(11) DEFAULT NULL,
  `bought_time` datetime DEFAULT NULL,
  `bought_by_user_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(1024) NOT NULL,
  `email` varchar(1024) NOT NULL,
  `md5` varchar(32) NOT NULL,
  `money` bigint(20) NOT NULL,
  `is_manager` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
