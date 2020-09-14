<?php
/**
* Retailcore - OMINI
*
* @package           PluginPackage
* @author            Rahul Sharma
* @copyright         2020 Retailcore - OMINI
* @license           GPL-2.0-or-later
*
* @wordpress-plugin
* Plugin Name:       Retailcore - OMINI
* Plugin URI:        https://www.aiinnovation.in/retailcore/
* Description:       Retailcore plugins provides you the options sync your retailcore products into your woocommerce portal
* Version:           1.0.0
* Requires at least: 5.1
* Requires PHP:      7.2
* Author:            Rahul Sharma
* Author URI:        https://www.aiinnovation.in/retailcore/
* Text Domain:       retailcore-omini
* License:           GPL v2 or later
* License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
*/

if ( !function_exists( 'add_action' ) ) {
    echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
    exit;
}

define( 'RETAILCORE__VERSION', '1.0.0' );
define( 'RETAILCORE__MINIMUM_WP_VERSION', '4.0' );
define( 'RETAILCORE__PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'RETAILCORE__PLUGIN_DIR_URL', plugin_dir_url( __FILE__ ) );
define( 'RETAILCORE__PLUGIN_DIR_NAME','retailcore-omini');

function do_not_found_woocommerce_plugin() {
    require_once RETAILCORE__PLUGIN_DIR.'/notice/no-woocommerce.php';
}

if( !in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
    add_action('admin_notices', 'do_not_found_woocommerce_plugin');
}else{
    add_action( 'admin_menu', 'retailcore_register_sidebar_menu_option' );
}


function retailcore_load_custom_wp_admin_style(){
    wp_register_style( 'retailcore_custom_wp_admin_css', plugins_url(RETAILCORE__PLUGIN_DIR_NAME.'/css/retail-core-style.css'), false, '1.0.1' );
    wp_enqueue_style( 'retailcore_custom_wp_admin_css' );

    wp_register_style( 'retailcore_bootstrap_wp_admin_css', plugins_url(RETAILCORE__PLUGIN_DIR_NAME.'/css/bootstrap.css'), false, '1.0.1' );
    wp_enqueue_style( 'retailcore_bootstrap_wp_admin_css' );

    wp_register_style( 'retailcore_bootstrap_theme_wp_admin_css', plugins_url(RETAILCORE__PLUGIN_DIR_NAME.'/css/bootstrap-theme.css'), false, '1.0.1' );
    wp_enqueue_style( 'retailcore_bootstrap_theme_wp_admin_css' );
}
add_action('admin_enqueue_scripts', 'retailcore_load_custom_wp_admin_style');

/**
 * Register a custom menu page.
 */
function retailcore_register_sidebar_menu_option() {
    global $submenu;
    add_menu_page(
        'Retailcore - OMINI',
        'Retailcore-OMINI',
        'manage_options',
        'retailcore_omini',
        'retail_core_license_page',
        plugins_url( RETAILCORE__PLUGIN_DIR_NAME.'/assets/icon.png' ),
        60
    );
    add_submenu_page('retailcore_omini','Retailcore License','License',
        'manage_options','retailcore_license_activation','retail_core_license_page');
    $submenu['retailcore_omini'][0][0] = 'Activation';

    add_submenu_page('retailcore_omini','Retailcore - About','About',
        'manage_options','about_retail_core_omini','retail_core_omini_about_page');
}


/**
 * Display a custom menu page
 */
function retail_core_license_page(){
    require_once RETAILCORE__PLUGIN_DIR.'/views/activation.php';
}

function retail_core_omini_about_page(){
    esc_html_e( 'Admin About Page', 'textdomain' );
}

