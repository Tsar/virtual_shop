<?php

/*
function getClientIP() {
    $client_ip = ( !empty($HTTP_SERVER_VARS['REMOTE_ADDR']) ) ? $HTTP_SERVER_VARS['REMOTE_ADDR'] : ( ( !empty($HTTP_ENV_VARS['REMOTE_ADDR']) ) ? $HTTP_ENV_VARS['REMOTE_ADDR'] : getenv('REMOTE_ADDR') );
    return $client_ip;
}
*/

function html_page_start() {
?>
<html>
<head>
<link rel="stylesheet" href="style.css" type="text/css" />
<meta http-equiv="content-type" content="text/html; charset=utf-8" />
<title>Virtual Shop</title>
</head>
<body>

<h1 align=center>Virtual Shop</h1>

<div id="wrap">
<?php
}

function html_page_end() {
?>
</div>

</body>
</html>
<?php
}

// $activeTab - ref to current tab; $tabs - array of tabs
function displayTabs(AbstractTab &$activeTab, $tabs) {
    $tabsCount = count($tabs);
    if ($tabsCount <= 0)
        return;

    $tabs = array_reverse($tabs);
    echo "<ul id=\"lineTabs$tabsCount\">\n";
    $tabNumber = 0;
    foreach ($tabs as &$tab) {
        $tabInfo = $tab->getTabInfo();
        if ($tabInfo->page === "") {
            echo '    <li><a href=""';
        } else {
            echo '    <li><a href="?page=' . $tabInfo->page . '"';
        }
        if ($tabInfo->title === $activeTab->getTabInfo()->title && $tabInfo->page === $activeTab->getTabInfo()->page) {
            echo ' class="active"';
        }
        echo ">" . $tabInfo->title . "</a></li>\n";
        ++$tabNumber;
    }
    echo "</ul>\n";
}

function display_content_start_block() {
    echo "<div id=\"content\">\n";
}

function display_content_end_block() {
    echo "\n</div>\n";
}

function display_content($content) {
    display_content_start_block();
    echo $content;
    display_content_end_block();
}

function display_error_or_info_if_any($error, $info) {
    if ($error != "") {
        echo "<p><b><font color=#cc0000>$error</font></b></p>\n";
    }
    if ($info != "") {
        echo "<p><b><font color=#009900>$info</font></b></p>\n";
    }
}

function tr(&$i) {
    echo ($i % 2 == 0) ? "<tr class=\"odd\">\n" : "<tr class=\"even\">\n";
    ++$i;
}

?>
