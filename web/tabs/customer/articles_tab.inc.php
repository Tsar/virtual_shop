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
    </tr>
<?php
        if ($articles = $this->dbm->getArticles()) {
            $i = 0;
            $ids = array();
            foreach ($articles as $a) {
?>
    <?php tr($i); ?>
        <td><?php echo $a[1]; ?></td>
        <td><font size=2><?php echo $a[2]; ?></font></td>
        <td align="right"><?php echo $a[3] * (1.0 - $a[4] / 100.0); ?></td>
        <td align="right"><?php echo $a[4] == 0 ? "-" : $a[4] . " %"; ?></td>
        <td align="center"><?php echo $a[4] == 0 ? "-" : $a[5]; ?></td>
        <td align="right"><?php echo $a[6]; ?></td>
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
            /*
            foreach ($ids as $id) {
                $this->dbm->updateArticlePriceAndDiscount($id, $_POST["price$id"], $_POST["discount$id"], $_POST["discount$id" . "ActiveTill"], $this->userId);
                $newInstCount = $_POST["add$id" . "Instances"];
                if ($newInstCount !== "" && is_numeric($newInstCount)) {
                    $this->dbm->addArticleInstances($id, $newInstCount, $this->userId);
                }
            }
            */
            $this->dbm->commitTransaction();
        }
    }
}

?>
