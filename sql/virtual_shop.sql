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


DROP PROCEDURE IF EXISTS update_article_discount;
DROP PROCEDURE IF EXISTS add_article_instances;

DELIMITER $$

CREATE PROCEDURE update_article_discount(IN _article_id INT, IN _discount DOUBLE, IN _discount_active_till DATETIME, IN _manager_id INT)
BEGIN
  /*DECLARE EXIT HANDLER FOR NOT FOUND ROLLBACK;
  DECLARE EXIT HANDLER FOR SQLWARNING ROLLBACK;
  DECLARE EXIT HANDLER FOR SQLEXCEPTION ROLLBACK;
  START TRANSACTION;*/
  DECLARE cur_discount DOUBLE DEFAULT 0;
  DECLARE cur_discount_active_till DATETIME DEFAULT "0000-00-00 00:00:00";
  SELECT discount, discount_active_till INTO cur_discount, cur_discount_active_till FROM articles WHERE id = _article_id;
  IF ((cur_discount != _discount) OR (cur_discount_active_till != _discount_active_till)) THEN
    UPDATE articles SET discount = _discount, discount_active_till = _discount_active_till, last_modified_by_manager_id = _manager_id WHERE id = _article_id;
  END IF;
  /*COMMIT;*/
END$$

CREATE PROCEDURE add_article_instances(IN _article_id INT, IN _new_inst_count INT, IN _manager_id INT)
BEGIN
  UPDATE articles SET avaliable = avaliable + _new_inst_count, last_modified_by_manager_id = _manager_id WHERE id = _article_id;
END$$

DELIMITER ;
