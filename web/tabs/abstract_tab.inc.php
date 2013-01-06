<?php

class TabInfo {
    public $title;
    public $page;

    function __construct($title, $page) {
        $this->title = $title;
        $this->page = $page;
    }
}

abstract class AbstractTab {
    // Returns tab title
    abstract public function getTabInfo();

    // Displays content
    abstract public function displayContent();
    
    // Returns true, if was submitted
    abstract public function isSubmitted();
    
    // Handles submit
    abstract public function handleSubmit();
}

?>
