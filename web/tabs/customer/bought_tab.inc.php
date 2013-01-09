<?php

require_once('tabs/abstract_tab.inc.php');
require_once('style.inc.php');
require_once('database_manager.inc.php');

class BoughtTab extends AbstractTab {
    private $dbm;
    private $userId;

    function __construct(DatabaseManager &$dbm, $userId) {
        $this->dbm = $dbm;
        $this->userId = $userId;
    }

    public function getTabInfo() {
        return new TabInfo("Bought", "bought");
    }

    public function displayContent() {
        display_content_start_block();
?>
<table id="infoTable">
    <tr>
        <th>Name</th>
        <th>Description</th>
        <th>Bought count</th>
        <th>Bought on</th>
        <th>Money spent</th>
    </tr>
<?php
        if ($articles = $this->dbm->getBoughtArticles($this->userId)) {
            $i = 0;
            foreach ($articles as $a) {
?>
    <?php tr($i); ?>
        <td><?php echo $a[0]; ?></td>
        <td><font size=2><?php echo $a[1]; ?></font></td>
        <td align="right"><?php echo $a[2]; ?></td>
        <td align="center"><font size=2><?php echo $a[3]; ?></font></td>
        <td align="right"><?php echo $a[4]; ?></td>
    </tr>
<?php
            }
        }
?>
</table>
<?php
        display_content_end_block();
    }

    public function isSubmitted() {
        return false;
    }

    public function handleSubmit() {
    }
}

?>
