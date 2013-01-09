<?php

require_once('tabs/abstract_tab.inc.php');
require_once('style.inc.php');
require_once('database_manager.inc.php');

class BookedTab extends AbstractTab {
    private $formAction;
    private $dbm;
    private $errorInfo;
    private $successInfo;
    private $userId;
    private $money;

    function __construct($formAction, DatabaseManager &$dbm, $userId, $money) {
        $this->formAction = $formAction;
        $this->dbm = $dbm;
        $this->errorInfo = "";
        $this->successInfo = "";
        $this->userId = $userId;
        $this->money = $money;
    }

    public function getTabInfo() {
        return new TabInfo("Booked", "booked");
    }

    public function displayContent() {
        display_content_start_block();
        display_error_or_info_if_any($this->errorInfo, $this->successInfo);
?>
<p>Your cash: <b><?php echo $this->money; ?></b>.</p>
<form method="post" action="<?php echo $this->formAction; ?>">
<table id="infoTable">
    <tr>
        <th>Name</th>
        <th>Description</th>
        <th>Price<br /><font size="2">with<br />discount</font></th>
        <th>Discount</th>
        <th>Discount<br />active till</th>
        <th>Booked<br />count</th>
        <th>Booked till</th>
        <th>Buy out</th>
    </tr>
<?php
        if ($articles = $this->dbm->getBookedArticles($this->userId)) {
            $i = 0;
            $ids = array();
            foreach ($articles as $a) {
                array_push($ids, $a[0]);
?>
    <?php tr($i); ?>
        <td><?php echo $a[1]; ?></td>
        <td><font size=2><?php echo $a[2]; ?></font></td>
        <td align="right"><?php echo $a[7]; ?></td>
        <td align="right"><?php echo $a[4] == 0 ? "-" : $a[4] . " %"; ?></td>
        <td align="center"><font size=2><?php echo $a[4] == 0 ? "-" : $a[5]; ?></font></td>
        <td align="right"><?php echo $a[6]; ?></td>
        <td align="center"><font size=2><?php echo $a[8]; ?></font></td>
        <td><input type="checkbox" id="buy<?php echo $a[0]; ?>" name="buy<?php echo $a[0]; ?>" value="on" /><input type="text" size="2" name="buyCount<?php echo $a[0]; ?>" value="<?php echo $a[6]; ?>" onclick="document.getElementById('buy<?php echo $a[0]; ?>').checked = true;" onchange="document.getElementById('buy<?php echo $a[0]; ?>').checked = true;" /></td>
    </tr>
<?php
            }
        }
?>
</table>
<p><center><input type="submit" name="submitBuyOut" value="Buy out"></center></p>
<input type="hidden" name="ids" value="<?php echo implode(",", $ids); ?>">
</form>
<?php
        display_content_end_block();
    }

    public function isSubmitted() {
        return (isset($_POST['submitBuyOut']) && isset($_POST['ids']));
    }

    public function handleSubmit() {
        $ids = explode(",", $_POST['ids']);
        if (!empty($ids)) {
            $this->dbm->startTransaction();

            foreach ($ids as $id) {
                if (isset($_POST["buy$id"]) && $_POST["buy$id"] === "on" && is_numeric($_POST["buyCount$id"])) {
                    $this->dbm->buyOutArticle($id, $this->userId, $_POST["buyCount$id"]);
                }
            }

            $this->dbm->commitTransaction();
            $this->money = $this->dbm->getUserInfo($this->userId)->money;
        }
    }
}

?>
