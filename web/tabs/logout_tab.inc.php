<?php

require_once('tabs/abstract_tab.inc.php');

class LogoutTab extends AbstractTab {
    public function getTabInfo() {
        return new TabInfo("Logout", "logout");
    }

    public function displayContent() {
    }
    
    public function isSubmitted() {
        return false;
    }
    
    public function handleSubmit() {
    }
}

?>
