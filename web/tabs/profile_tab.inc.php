<?php

require_once('tabs/abstract_tab.inc.php');
require_once('style.inc.php');
require_once('database_manager.inc.php');

class ProfileTab extends AbstractTab {
    private $formAction;
    private $dbm;
    private $errorInfo;
    private $successInfo;
    private $userId;
    private $userInfo;

    function __construct($formAction, DatabaseManager &$dbm, $userId, UserInfo $userInfo) {
        $this->formAction = $formAction;
        $this->dbm = $dbm;
        $this->errorInfo = "";
        $this->successInfo = "";
        $this->userId = $userId;
        //$this->userInfo = $this->dbm->getUserInfo($this->userId);
        $this->userInfo = $userInfo;
    }

    public function getTabInfo() {
        return new TabInfo("Profile", "profile");
    }

    public function displayContent() {
        display_content_start_block();
        display_error_or_info_if_any($this->errorInfo, $this->successInfo);
        $i = 0;
?>
<form method="post" action="<?php echo $this->formAction; ?>">
    <table id="submitTable">
        <?php tr($i); ?>
            <td>Name:</td>
            <td><input type="text" size="20" name="name" value="<?php echo $this->userInfo->name; ?>"></td>
        </tr>
        <?php tr($i); ?>
            <td>Role:</td>
            <td><b><?php echo $this->userInfo->isManager ? "Manager" : "Customer"; ?></b></td>
        </tr>
        <?php tr($i); ?>
            <td>E-mail:</td>
            <td><input type="text" size="20" name="email" value="<?php echo $this->userInfo->email; ?>"></td>
        </tr>
        <?php tr($i); ?>
            <td>New password:</td>
            <td><input type="password" size="20" name="newPassword"> (leave empty if you don't need to change it)</td>
        </tr>
        <?php tr($i); ?>
            <td>Repeat new password:</td>
            <td><input type="password" size="20" name="newPassword2"></td>
        </tr>
        <?php tr($i); ?>
            <td>Current password:</td>
            <td><input type="password" size="20" name="password"> (required for updating profile)</td>
        </tr>
        <?php tr($i); ?>
            <td colspan="2"><center><input type="submit" name="submitUpdateProfile" value="Update profile"></center></td>
        </tr>
    </table>
</form>
<?php
        display_content_end_block();
    }

    public function isSubmitted() {
        return (isset($_POST['submitUpdateProfile']) && isset($_POST['name']) && isset($_POST['email']) &&
                isset($_POST['newPassword']) && isset($_POST['newPassword2']) && isset($_POST['password']));
    }

    public function handleSubmit() {
        $this->userInfo->name = $_POST['name'];
        $this->userInfo->email = $_POST['email'];

        if (md5($_POST['password']) !== $this->userInfo->md5) {
            $this->errorInfo = "Current password incorrect";
        } else if ($_POST['newPassword'] !== "" && $_POST['newPassword'] !== $_POST['newPassword2']) {
            $this->errorInfo = "New passwords did not match";
        } else if ($_POST['name'] === "") {
            $this->errorInfo = "Empty name not allowed";
        } else if ($_POST['email'] === "") {
            $this->errorInfo = "Empty email not allowed";

        } else {
            if ($_POST['newPassword'] !== "") {
                $this->userInfo->md5 = md5($_POST['newPassword']);
            }

            $updRes = $this->dbm->updateUserInfo($this->userId, $this->userInfo);
            if ($updRes === UpdateUserResult::OK) {
                $this->successInfo = "Profile updated successfully";
                $this->userInfo = $this->dbm->getUserInfo($this->userId);
                $_SESSION['email'] = $this->userInfo->email;
                $_SESSION['md5'] = $this->userInfo->md5;

            } else if ($updRes === UpdateUserResult::ERR_EMAIL_EXISTS) {
                $this->errorInfo = "Such email already registered";
            } else if ($updRes === UpdateUserResult::ERR_DB_ERROR) {
                $this->errorInfo = "Database query error";
            }
        }
    }
}

?>
