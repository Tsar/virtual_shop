<?php

require_once('tabs/abstract_tab.inc.php');
require_once('style.inc.php');
require_once('database_manager.inc.php');

class StatsTab extends AbstractTab {
    private $dbm;

    function __construct(DatabaseManager &$dbm) {
        $this->dbm = $dbm;
    }

    public function getTabInfo() {
        return new TabInfo("Statistics", "stats");
    }

    public function displayContent() {
        display_content_start_block();
        $i = 0;
        $stats = $this->dbm->getStats();
?>
<table id="infoTable">
    <tr>
        <th>Statistic name</th>
        <th>Statistic value</th>
    </tr>
<?php
        foreach ($stats as $stn => $stv) {
?>
    <?php tr($i); ?>
        <td align="left"><?php echo $stn; ?></td>
        <td align="center"><?php echo $stv; ?></td>
    </tr>
<?php
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
