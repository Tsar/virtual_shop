<?php

class RegistrationResult {
    const OK                = 0;
    const ERR_EMAIL_EXISTS  = 1;
    const ERR_DB_ERROR      = 2;
}

class UpdateUserResult {
    const OK                = 0;
    const ERR_EMAIL_EXISTS  = 1;
    const ERR_DB_ERROR      = 2;
}

class UserCheckResult {
    const DB_ERROR           = -2;
    const USER_NOT_LOGGED_IN = -1;
    const USER_INVALID       = 0;
    // positive result is user id in database
    const MIN_VALID_USER_ID  = 1;
}

class UserInfo {
    public $name;
    public $email;
    public $md5;
    public $money;
    public $isManager;

    function __construct($name, $email, $md5, $money, $isManager) {
        $this->name = $name;
        $this->email = $email;
        $this->md5 = $md5;
        $this->money = $money;
        $this->isManager = $isManager;
    }
}

class DatabaseManager {
    private $mysqli;
    private $connError;
    
    private function query($q) {
        if (strlen($q) < 1024) {
            file_put_contents('sql_queries.log', date("Y-m-d H:i:s") . ': ' . $q . chr(10), FILE_APPEND);
        }
        return $this->mysqli->query($q);
    }
    
    private function escapeStr($s) {
        return $this->mysqli->real_escape_string($s);
    }

    // returns true or false
    public function connect($db_server, $db_user, $db_passwd, $db_name) {
        $this->db_user = $db_user;
        $this->db_passwd_md5 = md5($db_passwd);

        $this->mysqli = new mysqli($db_server, $db_user, $db_passwd, $db_name);
        if ($this->mysqli->connect_errno) {
            $this->connError = array($this->mysqli->connect_errno, $this->mysqli->connect_error);
            return false;
        }
        return true;
    }

    public function getConnError() {
        return $this->connError;
    }

    // returns UserCheckResult
    public function checkUserMD5($email, $md5) {
        $email = $this->escapeStr($email);
        $md5   = $this->escapeStr($md5);

        if ($result = $this->query('SELECT id FROM users WHERE LOWER(email) = "' . strtolower($email) . '" AND md5 = "' . $md5 . '"')) {
            if ($result->num_rows == 1) {
                $row = $result->fetch_assoc();
                return $row['id'];
            } else {
                return UserCheckResult::USER_INVALID;
            }
        } else {
            return UserCheckResult::DB_ERROR;
        }
    }

    // returns UserInfo or false
    public function getUserInfo($id) {
        $id = $this->escapeStr($id);

        if ($result = $this->query('SELECT * FROM users WHERE id = ' . $id)) {
            if ($result->num_rows == 1) {
                $row = $result->fetch_assoc();
                return new UserInfo($row['name'], $row['email'], $row['md5'], $row['money'], $row['is_manager'] ? true : false);
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    // returns RegistrationResult
    public function registerNewUser($userInfo) {
        $name  = $this->escapeStr($userInfo->name);
        $email = $this->escapeStr($userInfo->email);
        $md5   = $this->escapeStr($userInfo->md5);
        $money = $this->escapeStr($userInfo->money);

        //perform checks
        if ($result = $this->query('SELECT id FROM users WHERE LOWER(email) = "' . strtolower($email) . '"')) {
            if ($result->num_rows > 0)
                return RegistrationResult::ERR_EMAIL_EXISTS;
        } else {
            return RegistrationResult::ERR_DB_ERROR;
        }
        
        //insert to database
        if ($this->query('INSERT INTO users (name, email, md5, money, is_manager) VALUES ("' .
                         $name . '", "' . $email . '", "' . $md5 .'", ' . $money . ', ' . (($userInfo->isManager === true) ? '1' : '0') . ')') === true) {
            return RegistrationResult::OK;
        } else {
            return RegistrationResult::ERR_DB_ERROR;
        }
    }

    // returns UpdateUserResult
    public function updateUserInfo($id, $userInfo) {
        $name  = $this->escapeStr($userInfo->name);
        $email = $this->escapeStr($userInfo->email);
        $md5   = $this->escapeStr($userInfo->md5);
        $money = $this->escapeStr($userInfo->money);
        
        //perform checks
        if ($result = $this->query('SELECT id FROM users WHERE LOWER(email) = "' . strtolower($email) . '" AND id != ' . $id)) {
            if ($result->num_rows > 0)
                return UpdateUserResult::ERR_EMAIL_EXISTS;
        } else {
            return UpdateUserResult::ERR_DB_ERROR;
        }
        
        //update in database
        if ($this->query('UPDATE users SET name = "' . $name . '", email = "' . $email . '", md5 = "' . $md5 . '", money = ' . $money . ', is_manager = ' . (($userInfo->isManager === true) ? '1' : '0') . ' WHERE id = ' . $id) === true) {
            return UpdateUserResult::OK;
        } else {
            return UpdateUserResult::ERR_DB_ERROR;
        }
    }

    public function close() {
        $this->mysqli->close();
    }

    public function getArticles($forManagement = false) {
        $queryText = 'SELECT * FROM articles_with' . ($forManagement ? '' : 'out') . '_outdated_discounts';
        if ($result = $this->query($queryText)) {
            $ans = array();
            while ($row = $result->fetch_array(MYSQLI_NUM)) {
                array_push($ans, $row);
            }
            return $ans;
        } else {
            return false;
        }
    }

    public function addNewArticle($name, $description, $price, $discount, $discountActiveTill, $instancesCount, $managerUserId) {
        $name               = $this->escapeStr($name);
        $description        = $this->escapeStr($description);
        $price              = $this->escapeStr($price);
        $discount           = $this->escapeStr($discount);
        $discountActiveTill = $this->escapeStr($discountActiveTill);
        $instancesCount     = $this->escapeStr($instancesCount);
        $managerUserId      = $this->escapeStr($managerUserId);

        return ($this->query("INSERT INTO articles (name, description, price, discount, discount_active_till, avaliable, added_by_manager_id) VALUES (\"$name\", \"$description\", $price, $discount, \"$discountActiveTill\", $instancesCount, $managerUserId)"));
    }
    
    public function startTransaction() {
        return ($this->query("START TRANSACTION"));
    }

    public function commitTransaction() {
        return ($this->query("COMMIT"));
    }

    public function updateArticlePriceAndDiscount($id, $price, $discount, $discountActiveTill, $managerUserId) {
        $id                 = $this->escapeStr($id);
        $price              = $this->escapeStr($price);
        $discount           = $this->escapeStr($discount);
        $discountActiveTill = $this->escapeStr($discountActiveTill);
        $managerUserId      = $this->escapeStr($managerUserId);

        return ($this->query("CALL update_article_price_and_discount($id, $price, $discount, \"$discountActiveTill\", $managerUserId)"));
    }

    public function addArticleInstances($id, $newInstCount, $managerUserId) {
        $id            = $this->escapeStr($id);
        $newInstCount  = $this->escapeStr($newInstCount);
        $managerUserId = $this->escapeStr($managerUserId);

        return ($this->query("CALL add_article_instances($id, $newInstCount, $managerUserId)"));
    }

    public function bookArticle($userId, $articleId, $count, $bookTill) {
        $userId    = $this->escapeStr($userId);
        $articleId = $this->escapeStr($articleId);
        $count     = $this->escapeStr($count);
        $bookTill  = $this->escapeStr($bookTill);

        return ($this->query("CALL book_article($userId, $articleId, $count, \"$bookTill\")"));
    }

    public function buyArticle($userId, $articleId, $count) {
        $userId    = $this->escapeStr($userId);
        $articleId = $this->escapeStr($articleId);
        $count     = $this->escapeStr($count);

        return ($this->query("CALL buy_article($userId, $articleId, $count)"));
    }

    public function getBookedArticles($userId) {
        $userId    = $this->escapeStr($userId);

        if ($result = $this->query('SELECT b.id, a.name, a.description, a.price, IF(a.discount_active_till >= NOW(), a.discount, 0), IF(a.discount_active_till >= NOW(), a.discount_active_till, "0000-00-00 00:00:00"), b.count, IF(a.discount_active_till >= NOW(), CEIL(a.price * (1.0 - a.discount / 100.0)), a.price), b.booked_till FROM booked AS b LEFT OUTER JOIN articles AS a ON a.id = b.article_id WHERE b.user_id = ' . $userId . ' AND b.booked_till >= NOW() ORDER BY b.id')) {
            $ans = array();
            while ($row = $result->fetch_array(MYSQLI_NUM)) {
                array_push($ans, $row);
            }
            return $ans;
        } else {
            return false;
        }
    }

    public function buyOutArticle($bookingId, $userId, $count) {
        $bookingId = $this->escapeStr($bookingId);
        $userId    = $this->escapeStr($userId);
        $count     = $this->escapeStr($count);

        return  ($this->query("CALL buy_out_article($bookingId, $userId, $count)"));
    }

    public function getBoughtArticles($userId) {
        $userId    = $this->escapeStr($userId);

        if ($result = $this->query('SELECT a.name, a.description, b.count, b.bought_on, b.money_spent FROM bought AS b LEFT OUTER JOIN articles AS a ON a.id = b.article_id WHERE b.user_id = ' . $userId . ' ORDER BY b.id')) {
            $ans = array();
            while ($row = $result->fetch_array(MYSQLI_NUM)) {
                array_push($ans, $row);
            }
            return $ans;
        } else {
            return false;
        }
    }

    public function removeBooking($bookingId, $userId) {
        $bookingId = $this->escapeStr($bookingId);
        $userId    = $this->escapeStr($userId);

        return  ($this->query("CALL remove_booking($bookingId, $userId)"));
    }

    private function getFirstValueOfQuery($q) {
        $ans = "";
        if ($result = $this->query($q)) {
            if ($row = $result->fetch_array(MYSQLI_NUM)) {
                $ans = $row[0];
            }
        }
        return ($ans != "") ? $ans : "0";
    }

    public function getStats() {
        $stats['All different articles count']                               = $this->getFirstValueOfQuery('SELECT COUNT(*) FROM articles');
        $stats['All different avaliable articles count']                     = $this->getFirstValueOfQuery('SELECT COUNT(*) FROM articles WHERE avaliable > 0');
        $stats['All avaliable articles count']                               = $this->getFirstValueOfQuery('SELECT SUM(avaliable) FROM articles');
        $stats['All avaliable articles total price (<b>with</b> discounts)'] = $this->getFirstValueOfQuery('SELECT SUM(IF(discount_active_till >= NOW(), CEIL(price * (1.0 - discount / 100.0)), price) * avaliable) FROM articles');;
        $stats['All avaliable articles total price (without discounts)']     = $this->getFirstValueOfQuery('SELECT SUM(price * avaliable) FROM articles');
        $stats['All booked articles count']                                  = $this->getFirstValueOfQuery('SELECT SUM(count) FROM booked');
        $stats['All sold articles count']                                    = $this->getFirstValueOfQuery('SELECT SUM(count) FROM bought');
        $stats['Total profit']                                               = $this->getFirstValueOfQuery('SELECT SUM(money_spent) FROM bought');;

        return $stats;
    }
}

?>
