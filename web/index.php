<?php
    require_once('tabs/fatal_error_tab.inc.php');
    require_once('tabs/authorization_tab.inc.php');
    require_once('tabs/registration_tab.inc.php');
    require_once('tabs/profile_tab.inc.php');
    require_once('tabs/logout_tab.inc.php');

    require_once('tab_holder.inc.php');
    require_once('database_manager.inc.php');
    require_once('style.inc.php');
    require_once('settings.inc.php');

    $selfLink = htmlspecialchars($_SERVER['PHP_SELF']);

    $tabHolder = new TabHolder();

    $dbm = new DatabaseManager();

    if (!$dbm->connect($db_server, $db_user, $db_passwd, $db_name)) {
        $connError = $dbm->getConnError();

        $fatalErrorTab = new FatalErrorTab("<p><b><font color=#cc0000>Failed to connect to MySQL: (" . $connError[0] . ") " . $connError[1] . "</font></b></p>");

        $tabHolder->addTab($fatalErrorTab);

        html_page_start();
        $tabHolder->display($fatalErrorTab);
        html_page_end();
    } else {

        session_start();
        session_regenerate_id(true);

        $authorizationTab = new AuthorizationTab($selfLink, $dbm);
        $registrationTab = new RegistrationTab($selfLink, $dbm);

        $page = "";
        if (isset($_GET['page'])) {
            $page = htmlentities($_GET['page']);
        }

        $user_id = UserCheckResult::USER_NOT_LOGGED_IN;

        // if registration form submitted
        if ($registrationTab->isSubmitted()) {
            $page = $registrationTab->getTabInfo()->page;
            $registrationTab->handleSubmit();

        // if login form submitted
        } else if ($authorizationTab->isSubmitted()) {
            $authorizationTab->handleSubmit();
            $user_id = $authorizationTab->getUserId();

        // if session data is set
        } else if (isset($_SESSION['email']) && isset($_SESSION['md5']) && $_SESSION['email'] !== "" && $_SESSION['md5'] !== "") {
            $user_id = $dbm->checkUserMD5($_SESSION['email'], $_SESSION['md5']);
            if ($user_id < UserCheckResult::MIN_VALID_USER_ID && $user_id !== UserCheckResult::DEFAULT_ADMIN) {
                $_SESSION['email'] = "";
                $_SESSION['md5'] = "";
            }
        }

        // if valid user logged in
        if ($user_id >= UserCheckResult::MIN_VALID_USER_ID) {

            if ($page === "logout") {
                $_SESSION['email'] = "";
                $_SESSION['md5'] = "";
                $user_id = UserCheckResult::USER_NOT_LOGGED_IN;

            } else {

                $user_info = $dbm->getUserInfo($user_id);

                $profileTab = new ProfileTab($selfLink, $dbm, $user_id, $user_info);
                $logoutTab = new LogoutTab();
                
                $tabHolder->addTab($profileTab);

                // if update profile form submitted
                if ($profileTab->isSubmitted()) {
                    $page = $profileTab->getTabInfo()->page;
                    $profileTab->handleSubmit();
                }

                // if a manager is logged in
                if ($user_info->isManager) {
                    

                // if a customer is logged in
                } else {
                    
                }

                $tabHolder->addTab($logoutTab);
            }
        }

        // if not logged in
        if ($user_id < UserCheckResult::MIN_VALID_USER_ID) {
            $tabHolder->addTab($authorizationTab);
            $tabHolder->addTab($registrationTab);
        }

        html_page_start();
        $tabHolder->displayByPage($page);
        html_page_end();

        $dbm->close();
    }
?>
