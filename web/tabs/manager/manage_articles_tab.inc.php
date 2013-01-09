<?php

require_once('tabs/abstract_tab.inc.php');
require_once('style.inc.php');
require_once('database_manager.inc.php');

class ManageArticlesTab extends AbstractTab {
    private $formAction;
    private $dbm;
    private $errorInfo;
    private $successInfo;
    private $userId;

    private $aName;
    private $aDescription;
    private $aPrice;
    private $aDiscount;
    private $aDiscountActiveTill;

    function __construct($formAction, DatabaseManager &$dbm, $userId) {
        $this->formAction = $formAction;
        $this->dbm = $dbm;
        $this->errorInfo = "";
        $this->successInfo = "";
        $this->userId = $userId;

        $this->aName = "";
        $this->aDescription = "";
        $this->aPrice = "";
        $this->aDiscount = "";
        $this->aDiscountActiveTill = "";
        $this->aInstancesCount = "";
    }

    public function getTabInfo() {
        return new TabInfo("Manage art.", "manage_articles");
    }

    public function displayContent() {
        display_content_start_block();
        display_error_or_info_if_any($this->errorInfo, $this->successInfo);
        $i = 0;
?>
<p><b>Add new article</b></p>
<form method="post" action="<?php echo $this->formAction; ?>">
    <table id="submitTable">
        <?php tr($i); ?>
            <td>Name:</td>
            <td><input type="text" size="20" name="name" value="<?php echo $this->aName; ?>"></td>
        </tr>
        <?php tr($i); ?>
            <td>Description:</td>
            <td><input type="text" size="20" name="description" value="<?php echo $this->aDescription; ?>"></td>
        </tr>
        <?php tr($i); ?>
            <td>Price:</td>
            <td><input type="text" size="20" name="price" value="<?php echo $this->aPrice; ?>"></td>
        </tr>
        <?php tr($i); ?>
            <td>Instances count:</td>
            <td><input type="text" size="20" name="instancesCount" value="<?php echo $this->aInstancesCount; ?>"></td>
        </tr>
        <?php tr($i); ?>
            <td>Discount:</td>
            <td><input type="text" size="20" name="discount" value="<?php echo $this->aDiscount; ?>"></td>
        </tr>
        <?php tr($i); ?>
            <td>Discount active till:</td>
            <td><input type="text" size="20" name="discountActiveTill" value="<?php echo $this->aDiscountActiveTill; ?>"> <font size="2">(ex.: 2015-07-23 16:30:00)</font></td>
        </tr>
        <?php tr($i); ?>
            <td colspan="2"><center><input type="submit" name="submitNewArticle" value="New article"></center></td>
        </tr>
    </table>
</form>
<br />
<p><b>Add instances and manage discounts</b></p>
<form method="post" action="<?php echo $this->formAction; ?>">
<table id="infoTable">
    <tr>
        <th>Name</th>
        <th>Description</th>
        <th>Price</th>
        <th>Discount</th>
        <th>Discount<br />active till</th>
        <th>Avaliable<br />Booked<br />Bought</th>
        <th>Add<br />instances</th>
        <th>Added<br />by</th>
        <th>Last<br />modified<br />by</th>
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
        <td align="center"><input type="text" size="5" name="price<?php echo $a[0]; ?>" value="<?php echo $a[3]; ?>" /></td>
        <td align="center"><input type="text" size="3" name="discount<?php echo $a[0]; ?>" value="<?php echo $a[4]; ?>" />%</td>
        <td align="center"><input type="text" name="discount<?php echo $a[0]; ?>ActiveTill" value="<?php echo $a[5]; ?>" /></td>
        <td><?php echo $a[6] . " / " . $a[7] . " / " . $a[8]; ?></td>
        <td align="center"><input type="text" size="3" name="add<?php echo $a[0]; ?>Instances" value="" /></td>
        <td><font size=2><?php echo $a[9]; ?></font></td>
        <td><font size=2><?php echo $a[10]; ?></font></td>
    </tr>
<?php
            }
        }
?>
</table>
<p><center><input type="submit" name="submitUpdate" value="Update"></center></p>
<input type="hidden" name="ids" value="<?php echo implode(",", $ids); ?>">
</form>
<?php
        display_content_end_block();
    }
    
    public function isSubmitted() {
        return (isset($_POST['submitNewArticle']) && isset($_POST['name']) && isset($_POST['description']) && isset($_POST['price']) && isset($_POST['discount']) && isset($_POST['discountActiveTill'])) ||
               (isset($_POST['submitUpdate']) && isset($_POST['ids']));
    }
    
    public function handleSubmit() {
        if (isset($_POST['submitNewArticle'])) {
            $this->aName = $_POST['name'];
            $this->aDescription = $_POST['description'];
            $this->aPrice = $_POST['price'];
            $this->aInstancesCount = $_POST['instancesCount'];
            $this->aDiscount = $_POST['discount'];
            $this->aDiscountActiveTill = $_POST['discountActiveTill'];

            if ($this->aName === "") {
                $this->errorInfo = "Article name can't be empty";
            } else if (!is_numeric($this->aPrice)) {
                $this->errorInfo = "Article price should be numeric";
            } else if (!is_numeric($this->aInstancesCount)) {
                $this->errorInfo = "Article instances count should be numeric";
            } else if ($this->aDiscount !== "" && !is_numeric($this->aDiscount)) {
                $this->errorInfo = "Article discount should be numeric or empty";
            } else {
                if ($this->dbm->addNewArticle($this->aName, $this->aDescription, $this->aPrice, ($this->aDiscount === "") ? 0 : $this->aDiscount, $this->aDiscountActiveTill, $this->aInstancesCount, $this->userId)) {
                    $this->successInfo = "Article added successfully";
                } else {
                    $this->errorInfo = "Database query error";
                }
            }
        } else if (isset($_POST['submitUpdate'])) {
            $ids = explode(",", $_POST['ids']);
            if (!empty($ids)) {
                $this->dbm->startTransaction();

                foreach ($ids as $id) {
                    $this->dbm->updateArticlePriceAndDiscount($id, $_POST["price$id"], $_POST["discount$id"], $_POST["discount$id" . "ActiveTill"], $this->userId);
                    $newInstCount = $_POST["add$id" . "Instances"];
                    if ($newInstCount !== "" && is_numeric($newInstCount)) {
                        $this->dbm->addArticleInstances($id, $newInstCount, $this->userId);
                    }
                }

                $this->dbm->commitTransaction();
            }
        }
    }
}

?>
