<?php

class OptinMate {

    /**
     * This is construct of class
     * @author TechSimple <webmaster@optinmate.com>
     * @link https://optinmate.com/
     */
    public function __construct() {
        add_action('admin_menu', array($this, 'optin_create_menu_page'));
    }

    /* This function is to create menu page in admin area.
     * @author TechSimple <webmaster@optinmate.com>
     * @link https://optinmate.com/
     */

    public function optin_create_menu_page() {
        add_menu_page('OptinMate', 'OptinMate', 'manage_options', 'optinmate-top-level-page', array($this, 'optin_display_iframe'), '', 'Null');
    }

    /*
     * This function is to display iframe in admin dashboard. 
     * @author TechSimple <webmaster@optinmate.com>
     * @link https://optinmate.com/
     */

    public function optin_display_iframe() {
        $get_account_id = get_option('optin_account_id');
        $get_user_id = get_option('optin_user_id');

        $optin_account_id = intval($get_account_id);
        if (!$optin_account_id) {
            $optin_account_id = '';
        }

        $optin_user_id = intval($get_user_id);
        if (!$optin_user_id) {
            $optin_user_id = '';
        }

        if (!empty($optin_account_id) && !empty($optin_user_id)) {
            $url = OPTIN_WOO_API_ACCOUNT_URL . $optin_account_id . "&user=" . $optin_user_id;
            ?>
            <iframe src = "<?php echo $url; ?>" width = "100%" height = "750">;
            </iframe>
        <?php
        } else {
            echo '<h3>There is a problem with your installation. Please try to reinstall plugin.</h3>';
        }
    }

}
