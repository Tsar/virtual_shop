<?php

require_once('tabs/abstract_tab.inc.php');
require_once('style.inc.php');
require_once('database_manager.inc.php');

class AuthorizationTab extends AbstractTab {
    private $formAction;
    private $dbm;
    private $errorInfo;
    private $userId;

    function __construct($formAction, DatabaseManager &$dbm) {
        $this->formAction = $formAction;
        $this->dbm = $dbm;
        $this->errorInfo = "";
        $this->userId = UserCheckResult::USER_NOT_LOGGED_IN;
    }

    public function getTabInfo() {
        return new TabInfo("Authorization", "login");
    }

    public function displayContent() {
        display_content_start_block();
        display_error_or_info_if_any($this->errorInfo, "");
        $i = 0;
?>
<form method="post" action="<?php echo $this->formAction; ?>">
    <table id="submitTable">
        <?php tr($i); ?>
            <td>Email:</td>
            <td><input type="text" size="20" name="email"></td>
        </tr>
        <?php tr($i); ?>
            <td>Password:</td>
            <td><input type="password" size="20" name="password"></td>
        </tr>
        <?php tr($i); ?>
            <td colspan="2"><center><input type="submit" name="submitLogin" value="Login"></center></td>
        </tr>
    </table>
</form>
<?php
        display_content_end_block();
    }

    public function isSubmitted() {
        return (isset($_POST['submitLogin']) && isset($_POST['email']) && isset($_POST['password']));
    }

    public function handleSubmit() {
        $email = $_POST['email'];
        $md5 = md5($_POST['password']);
        $this->userId = $this->dbm->checkUserMD5($email, $md5);
        if ($this->userId >= UserCheckResult::MIN_VALID_USER_ID) {
            $_SESSION['email'] = $email;
            $_SESSION['md5'] = $md5;
        } else if ($this->userId == UserCheckResult::USER_INVALID) {
            $this->errorInfo = "Incorrect login or password";
        } else if ($this->userId == UserCheckResult::DB_ERROR) {
            $this->errorInfo = "Database query error";
        }
    }

    public function getUserId() {
        return $this->userId;
    }
}

?>
