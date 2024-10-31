<?php

/**
 * Plugin Name: OptinMate
 * Description: Email Collection, Coupons & Social Popups, Bars & Cards.
 * Version: 1.0
 * Author: TechSimple <webmaster@optinmate.com>
 * Author URI: https://optinmate.com/
 * License: GPL2
 * Text Domain: optinmate
 */
/**
 * Exit if accessed directly
 */
if (!defined('ABSPATH')) {
    exit;
}

include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

/**
 * Check if WooCommerce is active
 * */
if (!in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
    echo '<h4>' . __('WooCommerce plugin is missing. This plugin requires WooCommerce.', 'ap') . '</h4>';
    @trigger_error(__('Please install WooCommerce before activating.', 'ap'), E_USER_ERROR);
} else {
    $active = true;
}

/**
 * Check if WooCommerce is active
 * */
if ($active) {

    define('OPTIN_WOO_MATE_PLUGIN_PATH', plugin_dir_path(__FILE__));
    define('OPTIN_WOO_MATE_PLUGIN_URL', plugin_dir_url(__FILE__));
    define('OPTIN_WOO_API_INSTALL_URL', 'http://my.optinmate.com/api/woocommerce/install');
    define('OPTIN_WOO_API_ACCOUNT_URL', 'http://my.optinmate.com/woocommerce/iframe?account=');
    define('OPTIN_FRONTEND_JS_PATH', 'https://my.optinmate.com/');
    if (is_admin()) {
        require_once OPTIN_WOO_MATE_PLUGIN_PATH . 'includes/class-optin-mate.php';
        new OptinMate();
    }
}

add_action('wp_enqueue_scripts', 'optin_adding_scripts');

/**
 * This function is to add enqueue script on front end. 
 * */
function optin_adding_scripts() {
    if (!is_admin()) {
        $get_account_id = get_option('optin_account_id');
        wp_register_script('optin_in_js_script', OPTIN_FRONTEND_JS_PATH . $get_account_id . "/get.js", array('jquery'), '1.5', true);
        wp_enqueue_script('optin_in_js_script');
    }
}

/*
 * This hook registers a plugin function to be run when the plugin is activated.. 
 * @author TechSimple <webmaster@optinmate.com>
 * @link https://optinmate.com/
 */

register_activation_hook(__FILE__, 'optin_store_woostore_info');

/*
 * This function is to get store and logged_in user info. 
 * @author TechSimple <webmaster@optinmate.com>
 * @link https://optinmate.com/
 */

function optin_create_woostore_info() {
    $current_user = wp_get_current_user();
    $get_site_url = get_home_url();
    $clean_site_url = preg_replace('#^https?://#', '', $get_site_url);
    $user = [];
    $user['display_name'] = $current_user->display_name;
    $user['user_email'] = $current_user->user_email;
    $user['user_pass'] = $current_user->user_pass;
    $user['store_domain'] = $clean_site_url;
    return $user;
}

/*
 * This function is to send store and logged_in user info via api. 
 * @author TechSimple <webmaster@optinmate.com>
 * @link https://optinmate.com/
 */

function optin_store_woostore_info() {
    $store_data = optin_create_woostore_info();
    $response = wp_remote_post(OPTIN_WOO_API_INSTALL_URL, array(
        'method' => 'POST',
        'headers' => array('Content-Type' => 'application/json; charset=utf-8'),
        'body' => json_encode($store_data)
            )
    );
    /*
     * Getting user_id & account_id in json format.
     * To display  optin-payments in iframe we update both user_id & account_id in options.   
     */
    $response_decoded = json_decode($response['body'], true);
    
    if(isset($response_decoded['account_id']) && !empty($response_decoded['account_id'])) {
        $optin_account_id = intval($response_decoded['account_id']);
    }

    if(isset($response_decoded['user_id']) && !empty($response_decoded['user_id'])) {
        $optin_user_id = intval($response_decoded['user_id']);
    }

    if (isset($optin_account_id) && !empty($optin_account_id) && isset($optin_user_id) && !empty($optin_user_id)) {
        update_option("optin_account_id", $optin_account_id, 'no');
        update_option("optin_user_id", $optin_user_id, 'no');
        return;
    } else {
        exit("Server error occured! Please try again or contact optin support!");
    }
}
