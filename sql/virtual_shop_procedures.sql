DROP PROCEDURE IF EXISTS update_article_price_and_discount;
DROP PROCEDURE IF EXISTS add_article_instances;
DROP PROCEDURE IF EXISTS book_article;
DROP PROCEDURE IF EXISTS buy_article;
DROP PROCEDURE IF EXISTS buy_out_article;

DELIMITER $$

CREATE PROCEDURE update_article_price_and_discount(IN _article_id INT, IN _price BIGINT, IN _discount DOUBLE, IN _discount_active_till DATETIME, IN _manager_id INT)
BEGIN
  DECLARE cur_price BIGINT;
  DECLARE cur_discount DOUBLE;
  DECLARE cur_discount_active_till DATETIME;
  SELECT price, discount, discount_active_till INTO cur_price, cur_discount, cur_discount_active_till FROM articles WHERE id = _article_id;
  IF ((cur_price != _price) OR (cur_discount != _discount) OR (cur_discount_active_till != _discount_active_till)) THEN
    UPDATE articles SET price = _price, discount = _discount, discount_active_till = _discount_active_till, last_modified_by_manager_id = _manager_id WHERE id = _article_id;
  END IF;
END$$

CREATE PROCEDURE add_article_instances(IN _article_id INT, IN _new_inst_count INT, IN _manager_id INT)
BEGIN
  DECLARE cur_avaliable INT;
  SELECT avaliable INTO cur_avaliable FROM articles WHERE id = _article_id;
  IF (cur_avaliable + _new_inst_count >= 0) THEN
    UPDATE articles SET avaliable = cur_avaliable + _new_inst_count, last_modified_by_manager_id = _manager_id WHERE id = _article_id;
  END IF;
END$$

CREATE PROCEDURE book_article(IN _user_id INT, IN _article_id INT, IN _count INT, IN _book_till DATETIME)
BEGIN
  DECLARE cur_avaliable INT;
  IF (_count > 0) THEN
    SELECT avaliable INTO cur_avaliable FROM articles WHERE id = _article_id;
    IF (cur_avaliable >= _count) THEN
      UPDATE articles SET avaliable = cur_avaliable - _count, booked = booked + _count WHERE id = _article_id;
      INSERT INTO booked (user_id, article_id, `count`, booked_till) VALUES (_user_id, _article_id, _count, _book_till);
    END IF;
  END IF;
END$$

CREATE PROCEDURE buy_article(IN _user_id INT, IN _article_id INT, IN _count INT)
BEGIN
  DECLARE cur_user_cash BIGINT;
  DECLARE cur_price BIGINT;
  DECLARE cur_discount DOUBLE;
  DECLARE cur_discount_active_till DATETIME;
  DECLARE cur_avaliable INT;
  DECLARE full_price BIGINT;
  IF (_count > 0) THEN
    SELECT money INTO cur_user_cash FROM users WHERE id = _user_id;
    SELECT price, discount, discount_active_till, avaliable INTO cur_price, cur_discount, cur_discount_active_till, cur_avaliable FROM articles WHERE id = _article_id;

    IF (cur_avaliable >= _count) THEN
      BEGIN

        SET full_price = cur_price * _count;
        IF ((cur_discount > 0) AND (cur_discount_active_till >= NOW())) THEN
          SET full_price = CEIL(cur_price * (1.0 - cur_discount / 100.0)) * _count;
        END IF;

        IF (cur_user_cash >= full_price) THEN
          BEGIN
            UPDATE users SET money = cur_user_cash - full_price WHERE id = _user_id;
            UPDATE articles SET avaliable = cur_avaliable - _count, bought = bought + _count WHERE id = _article_id;
            INSERT INTO bought (user_id, article_id, `count`, bought_on, money_spent) VALUES (_user_id, _article_id, _count, NOW(), full_price);
          END;
        END IF;

      END;
    END IF;
  END IF;
END$$

CREATE PROCEDURE buy_out_article(IN _booking_id INT, IN _user_id INT, IN _count INT)
BEGIN
  
END$$

DELIMITER ;
