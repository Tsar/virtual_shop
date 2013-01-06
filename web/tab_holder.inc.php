<?php

require_once('tabs/abstract_tab.inc.php');
require_once('style.inc.php');

class TabHolder {
    private $tabs;

    function __construct() {
        $this->tabs = array();
    }

    public function addTab(AbstractTab &$tab) {
        array_push($this->tabs, $tab);
    }

    public function display(AbstractTab &$currentTab) {
        displayTabs($currentTab, $this->tabs);
        $currentTab->displayContent();
    }

    public function displayByPage($page) {
        foreach ($this->tabs as &$tab) {
            if ($tab->getTabInfo()->page === $page) {
                $this->display($tab);
                return;
            }
        }

        // default is the first tab
        $this->display($this->tabs[0]);
    }
}

?>
