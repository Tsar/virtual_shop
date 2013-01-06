<?php

require_once('tabs/abstract_tab.inc.php');
require_once('style.inc.php');

class FatalErrorTab extends AbstractTab {
    private $fatalErrorInfo;

    function __construct($fatalErrorInfo) {
        $this->fatalErrorInfo = $fatalErrorInfo;
    }

    public function getTabInfo() {
        return new TabInfo("Fatal error", "");
    }

    public function displayContent() {
        display_content($this->fatalErrorInfo);
    }
    
    public function isSubmitted() {
        return false;
    }
    
    public function handleSubmit() {
    }
}

?>
