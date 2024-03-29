USE virtual_shop;

DROP PROCEDURE IF EXISTS update_article_price_and_discount;
DROP PROCEDURE IF EXISTS add_article_instances;
DROP PROCEDURE IF EXISTS book_article;
DROP FUNCTION IF EXISTS calc_full_price;
DROP PROCEDURE IF EXISTS buy_article;
DROP PROCEDURE IF EXISTS buy_out_article;
DROP PROCEDURE IF EXISTS remove_booking;
DROP PROCEDURE IF EXISTS remove_outdated_bookings;

DELIMITER $$

CREATE PROCEDURE update_article_price_and_discount(_article_id INT, _price BIGINT, _discount DOUBLE, _discount_active_till DATETIME, _manager_id INT)
BEGIN
  DECLARE cur_price BIGINT;
  DECLARE cur_discount DOUBLE;
  DECLARE cur_discount_active_till DATETIME;
  SELECT price, discount, discount_active_till INTO cur_price, cur_discount, cur_discount_active_till FROM articles WHERE id = _article_id;
  IF ((cur_price != _price) OR (cur_discount != _discount) OR (cur_discount_active_till != _discount_active_till)) THEN
    UPDATE articles SET price = _price, discount = _discount, discount_active_till = _discount_active_till, last_modified_by_manager_id = _manager_id WHERE id = _article_id;
  END IF;
END$$

CREATE PROCEDURE add_article_instances(_article_id INT, _new_inst_count INT, _manager_id INT)
BEGIN
  DECLARE cur_avaliable INT;
  SELECT avaliable INTO cur_avaliable FROM articles WHERE id = _article_id;
  IF (cur_avaliable + _new_inst_count >= 0) THEN
    UPDATE articles SET avaliable = cur_avaliable + _new_inst_count, last_modified_by_manager_id = _manager_id WHERE id = _article_id;
  END IF;
END$$

CREATE PROCEDURE book_article(_user_id INT, _article_id INT, _count INT, _book_till DATETIME)
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

CREATE FUNCTION calc_full_price(_price BIGINT, _discount DOUBLE, _discount_active_till DATETIME, _count INT)
  RETURNS BIGINT
  DETERMINISTIC
  NO SQL
BEGIN
  IF (_discount > 0 AND _discount_active_till >= NOW()) THEN
    RETURN CEIL(_price * (1.0 - _discount / 100.0)) * _count;
  ELSE
    RETURN _price * _count;
  END IF;
END$$

CREATE PROCEDURE buy_article(_user_id INT, _article_id INT, _count INT)
BEGIN
  DECLARE cur_user_cash, cur_price, full_price BIGINT;
  DECLARE cur_discount DOUBLE;
  DECLARE cur_discount_active_till DATETIME;
  DECLARE cur_avaliable INT;
  IF (_count > 0) THEN
    SELECT money INTO cur_user_cash FROM users WHERE id = _user_id;
    SELECT price, discount, discount_active_till, avaliable INTO cur_price, cur_discount, cur_discount_active_till, cur_avaliable FROM articles WHERE id = _article_id;

    IF (cur_avaliable >= _count) THEN
      SET full_price = calc_full_price(cur_price, cur_discount, cur_discount_active_till, _count);

      IF (cur_user_cash >= full_price) THEN
        UPDATE users SET money = cur_user_cash - full_price WHERE id = _user_id;
        UPDATE articles SET avaliable = cur_avaliable - _count, bought = bought + _count WHERE id = _article_id;
        INSERT INTO bought (user_id, article_id, `count`, bought_on, money_spent) VALUES (_user_id, _article_id, _count, NOW(), full_price);
      END IF;
    END IF;

  END IF;
END$$

CREATE PROCEDURE buy_out_article(_booking_id INT, _user_id INT, _count INT)
BEGIN
  DECLARE u, a, cur_booked_count INT;

  DECLARE cur_user_cash, cur_price, full_price BIGINT;
  DECLARE cur_discount DOUBLE;
  DECLARE cur_discount_active_till, cur_booked_till DATETIME;

  IF (_count > 0) THEN
    SELECT user_id, article_id, `count`, booked_till INTO u, a, cur_booked_count, cur_booked_till FROM booked WHERE id = _booking_id;
    IF (u = _user_id AND cur_booked_count >= _count AND cur_booked_till >= NOW()) THEN
      SELECT money INTO cur_user_cash FROM users WHERE id = u;
      SELECT price, discount, discount_active_till INTO cur_price, cur_discount, cur_discount_active_till FROM articles WHERE id = a;

      SET full_price = calc_full_price(cur_price, cur_discount, cur_discount_active_till, _count);

      IF (cur_user_cash >= full_price) THEN
        UPDATE users SET money = cur_user_cash - full_price WHERE id = u;
        UPDATE articles SET booked = booked - _count, bought = bought + _count WHERE id = a;
        INSERT INTO bought (user_id, article_id, `count`, bought_on, money_spent) VALUES (u, a, _count, NOW(), full_price);
        IF (cur_booked_count > _count) THEN
          UPDATE booked SET `count` = cur_booked_count - _count WHERE id = _booking_id;
        ELSE
          DELETE FROM booked WHERE id = _booking_id;
        END IF;
      END IF;
    END IF;
  END IF;
END$$

CREATE PROCEDURE remove_booking(_booking_id INT, _user_id INT)
BEGIN
  DECLARE u, a, cur_booked_count INT;
  SELECT user_id, article_id, `count` INTO u, a, cur_booked_count FROM booked WHERE id = _booking_id;
  IF (u = _user_id) THEN
    UPDATE articles SET booked = booked - cur_booked_count, avaliable = avaliable + cur_booked_count WHERE id = a;
    DELETE FROM booked WHERE id = _booking_id;
  END IF;
END$$

CREATE PROCEDURE remove_outdated_bookings()
BEGIN
  UPDATE articles, booked SET articles.avaliable = articles.avaliable + booked.count, booked.user_id = 0 WHERE booked.booked_till < NOW() AND articles.id = booked.article_id;
  DELETE FROM booked WHERE user_id = 0;
END$$

DELIMITER ;


SET GLOBAL event_scheduler = ON;

DROP EVENT IF EXISTS remove_outdated_bookings_event;

CREATE EVENT remove_outdated_bookings_event
  ON SCHEDULE EVERY 1 HOUR
  DO
    CALL remove_outdated_bookings();
