<?php
    require_once('tabs/fatal_error_tab.inc.php');
    require_once('tabs/authorization_tab.inc.php');
    require_once('tabs/registration_tab.inc.php');
    require_once('tabs/profile_tab.inc.php');
    require_once('tabs/logout_tab.inc.php');

    require_once('tabs/customer/articles_tab.inc.php');
    require_once('tabs/customer/booked_tab.inc.php');

    require_once('tabs/manager/manage_articles_tab.inc.php');

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

                $articlesTab = new ArticlesTab($selfLink, $dbm, $user_id, $user_info->money);
                if ($articlesTab->isSubmitted()) {
                    $page = $articlesTab->getTabInfo()->page;
                    $articlesTab->handleSubmit();
                }

                $bookedTab = new BookedTab($selfLink, $dbm, $user_id, $user_info->money);
                if ($bookedTab->isSubmitted()) {
                    $page = $bookedTab->getTabInfo()->page;
                    $bookedTab->handleSubmit();
                }

                $tabHolder->addTab($articlesTab);
                $tabHolder->addTab($bookedTab);

                $profileTab = new ProfileTab($selfLink, $dbm, $user_id, $user_info);
                $logoutTab = new LogoutTab();
                
                // if update profile form submitted
                if ($profileTab->isSubmitted()) {
                    $page = $profileTab->getTabInfo()->page;
                    $profileTab->handleSubmit();
                }

                // if a manager is logged in
                if ($user_info->isManager) {
                    $manageArticlesTab = new ManageArticlesTab($selfLink, $dbm, $user_id);
                    if ($manageArticlesTab->isSubmitted()) {
                        $page = $manageArticlesTab->getTabInfo()->page;
                        $manageArticlesTab->handleSubmit();
                    }
                    $tabHolder->addTab($manageArticlesTab);

                // if a customer is logged in
                } else {

                }

                $tabHolder->addTab($profileTab);
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
