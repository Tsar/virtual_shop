<?php

require_once('tabs/abstract_tab.inc.php');
require_once('style.inc.php');
require_once('database_manager.inc.php');

class ArticlesTab extends AbstractTab {
    private $formAction;
    private $dbm;
    private $errorInfo;
    private $successInfo;
    private $userId;

    function __construct($formAction, DatabaseManager &$dbm, $userId) {
        $this->formAction = $formAction;
        $this->dbm = $dbm;
        $this->errorInfo = "";
        $this->successInfo = "";
        $this->userId = $userId;
    }

    public function getTabInfo() {
        return new TabInfo("Articles", "articles");
    }

    public function displayContent() {
        display_content_start_block();
        display_error_or_info_if_any($this->errorInfo, $this->successInfo);
?>
<form method="post" action="<?php echo $this->formAction; ?>">
<table id="infoTable">
    <tr>
        <th>Name</th>
        <th>Description</th>
        <th>Price<br /><font size="2">with<br />discount</font></th>
        <th>Discount</th>
        <th>Discount<br />active till</th>
        <th>Avaliable</th>
        <th>Book<br /><font size="2">for 48 hours</font></th>
        <th>Buy</th>
    </tr>
<?php
        if ($articles = $this->dbm->getArticles()) {
            $i = 0;
            $ids = array();
            foreach ($articles as $a) {
                array_push($ids, $a[0]);
?>
    <?php tr($i); ?>
        <td><?php echo $a[1]; ?></td>
        <td><font size=2><?php echo $a[2]; ?></font></td>
        <td align="right"><?php echo $a[3] * (1.0 - $a[4] / 100.0); ?></td>
        <td align="right"><?php echo $a[4] == 0 ? "-" : $a[4] . " %"; ?></td>
        <td align="center"><?php echo $a[4] == 0 ? "-" : $a[5]; ?></td>
        <td align="right"><?php echo $a[6]; ?></td>
        <td><input type="checkbox" id="book<?php echo $a[0]; ?>" name="book<?php echo $a[0]; ?>" value="on" /><input type="text" size="2" name="bookCount<?php echo $a[0]; ?>" value="1" onclick="document.getElementById('book<?php echo $a[0]; ?>').checked = true;" onchange="document.getElementById('book<?php echo $a[0]; ?>').checked = true;" /></td>
        <td><input type="checkbox" id="buy<?php  echo $a[0]; ?>" name="buy<?php  echo $a[0]; ?>" value="on" /><input type="text" size="2" name="buyCount<?php  echo $a[0]; ?>" value="1" onclick="document.getElementById('buy<?php  echo $a[0]; ?>').checked = true;" onchange="document.getElementById('buy<?php  echo $a[0]; ?>').checked = true;" /></td>
    </tr>
<?php
            }
        }
?>
</table>
<p><center><input type="submit" name="submitBookAndBuy" value="Book and Buy"></center></p>
<input type="hidden" name="ids" value="<?php echo implode(",", $ids); ?>">
</form>
<?php
        display_content_end_block();
    }

    public function isSubmitted() {
        return (isset($_POST['submitBookAndBuy']) && isset($_POST['ids']));
    }

    public function handleSubmit() {
        $ids = explode(",", $_POST['ids']);
        if (!empty($ids)) {
            $this->dbm->startTransaction();

            foreach ($ids as $id) {
                if (isset($_POST["book$id"]) && $_POST["book$id"] === "on" && is_numeric($_POST["bookCount$id"])) {
                    $this->dbm->bookArticle($this->userId, $id, $_POST["bookCount$id"], date('Y-m-d H:i:s', time() + 86400 * 2));
                }

                if (isset($_POST["buy$id"]) && $_POST["buy$id"] === "on" && is_numeric($_POST["buyCount$id"])) {
                    $this->dbm->buyArticle($this->userId, $id, $_POST["buyCount$id"]);
                }
            }

            $this->dbm->commitTransaction();
        }
    }
}

?>
