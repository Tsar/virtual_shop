USE virtual_shop;

CREATE OR REPLACE VIEW articles_with_outdated_discounts AS
    SELECT a.id, a.name, a.description, a.price, a.discount, a.discount_active_till, a.avaliable, a.booked, a.bought,
           u1.name AS u1name, u2.name AS u2name, IF(a.discount_active_till >= NOW(), CEIL(a.price * (1.0 - a.discount / 100.0)), a.price),
           IF(a.discount > 0 AND a.discount_active_till < NOW(), 0, 1) FROM articles AS a
           LEFT OUTER JOIN users AS u1 ON a.added_by_manager_id = u1.id
           LEFT OUTER JOIN users AS u2 ON a.last_modified_by_manager_id = u2.id;

CREATE OR REPLACE VIEW articles_without_outdated_discounts AS
    SELECT a.id, a.name, a.description, a.price, IF(a.discount_active_till >= NOW(), a.discount, NULL), IF(a.discount_active_till >= NOW(), a.discount_active_till, NULL),
           a.avaliable, a.booked, a.bought, u1.name AS u1name, u2.name AS u2name, IF(a.discount_active_till >= NOW(), CEIL(a.price * (1.0 - a.discount / 100.0)), a.price),
           IF(a.discount > 0 AND a.discount_active_till < NOW(), 0, 1) FROM articles AS a
           LEFT OUTER JOIN users AS u1 ON a.added_by_manager_id = u1.id
           LEFT OUTER JOIN users AS u2 ON a.last_modified_by_manager_id = u2.id;
