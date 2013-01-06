<?php

require_once('tabs/abstract_tab.inc.php');
require_once('style.inc.php');
require_once('database_manager.inc.php');

class RegistrationTab extends AbstractTab {
    private $formAction;
    private $dbm;
    private $errorInfo;
    private $successInfo;
    private $userInfo;

    function __construct($formAction, DatabaseManager &$dbm) {
        $this->formAction = $formAction;
        $this->dbm = $dbm;
        $this->errorInfo = "";
        $this->successInfo = "";
        $this->userInfo = new UserInfo("", "", "", 0, false);
    }

    public function getTabInfo() {
        return new TabInfo("Registration", "register");
    }

    public function displayContent() {
        display_content_start_block();
        display_error_or_info_if_any($this->errorInfo, $this->successInfo);
?>
<form method="post" action="<?php echo $this->formAction; ?>">
    <table id="submitTable">
        <?php tr($i); ?>
            <td>Name:</td>
            <td><input type="text" size="20" name="name" value="<?php echo $this->userInfo->name; ?>"></td>
        </tr>
        <?php tr($i); ?>
            <td>Email:</td>
            <td><input type="text" size="20" name="email" value="<?php echo $this->userInfo->email; ?>"></td>
        </tr>
        <?php tr($i); ?>
            <td>Password:</td>
            <td><input type="password" size="20" name="password"></td>
        </tr>
        <?php tr($i); ?>
            <td>Repeat password:</td>
            <td><input type="password" size="20" name="password2"></td>
        </tr>
        <?php tr($i); ?>
            <td colspan="2"><center><input type="submit" name="submitRegister" value="Register"></center></td>
        </tr>
    </table>
</form>
<?php
        display_content_end_block();
    }

    public function isSubmitted() {
        return (isset($_POST['submitRegister']) && isset($_POST['name']) && isset($_POST['email']) && isset($_POST['password']) && isset($_POST['password2']));
    }

    public function handleSubmit() {
        $this->userInfo->name = $_POST['name'];
        $this->userInfo->email = $_POST['email'];
        $this->userInfo->md5 = md5($_POST['password']);
        
        $pwd = $_POST['password'];
        if ($pwd !== $_POST['password2']) {
            $this->errorInfo = "Passwords did not match";
        } else if ($this->userInfo->name === "") {
            $this->errorInfo = "Empty name not allowed";
        } else if ($this->userInfo->email === "") {
            $this->errorInfo = "Empty email not allowed";
        } else if ($pwd === "") {
            $this->errorInfo = "Empty password not allowed";

        } else {
            $regRes = $this->dbm->registerNewUser($this->userInfo);
            if ($regRes === RegistrationResult::OK) {
                $this->successInfo = "Registered successfully";
                $this->userInfo = new UserInfo("", "", "", 0, false);
            } else if ($regRes === RegistrationResult::ERR_EMAIL_EXISTS) {
                $this->errorInfo = "Such email already registered";
            } else if ($regRes === RegistrationResult::ERR_DB_ERROR) {
                $this->errorInfo = "Database query error";
            }
        }
    }
}

?>
