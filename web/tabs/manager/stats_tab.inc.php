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
        display_content_end_block();
    }
    
    public function isSubmitted() {
        return false;
    }
    
    public function handleSubmit() {
    }
}

?>
