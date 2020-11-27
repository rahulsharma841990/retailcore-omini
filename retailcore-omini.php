<?php
/**
* Retailcore - OMNI
*
* @package           PluginPackage
* @author            Retailcore® Technologies
* @copyright         2020 Retailcore - OMNI
* @license           GPL-2.0-or-later
*
* @wordpress-plugin
* Plugin Name:       Retailcore - OMNI
* Plugin URI:        https://www.retailcore.in
* Description:       Retailcore plugins provides you the options sync your retailcore products into your woocommerce portal
* Version:           1.0.4
* Requires at least: 5.1
* Requires PHP:      7.2
* Author:            Retailcore® Technologies
* Author URI:        https://www.retailcore.in
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
    wp_register_style( 'retailcore_custom_wp_admin_css', plugins_url(RETAILCORE__PLUGIN_DIR_NAME.'/css/retail-core-style.css?ref='.rand(1111,9999)), false, '1.0.1' );
    wp_enqueue_style( 'retailcore_custom_wp_admin_css' );

    wp_register_style( 'retailcore_bootstrap_theme_wp_admin_css', plugins_url(RETAILCORE__PLUGIN_DIR_NAME.'/css/bootstrap-theme.css'), false, '1.0.1' );
    wp_enqueue_style( 'retailcore_bootstrap_theme_wp_admin_css' );

    wp_register_script('retailcore_omini_custom_script', plugins_url(RETAILCORE__PLUGIN_DIR_NAME.'/js/retail-core-omini-script.js?ref='.rand(1111,9999)),false, true);
    wp_enqueue_script('retailcore_omini_custom_script');

}
add_action('admin_enqueue_scripts', 'retailcore_load_custom_wp_admin_style');


function retail_core_omini_enqueue_style(){
    wp_register_style( 'retailcore_bootstrap_wp_admin_css', plugins_url(RETAILCORE__PLUGIN_DIR_NAME.'/css/bootstrap.css'), false, '1.0.1' );
    wp_enqueue_style( 'retailcore_bootstrap_wp_admin_css' );
}



function create_retailcore_logs_plugin_database_table()
{
    global $wpdb;

    $tblname = 'retailcore_place_order_logs';
    $wp_track_table = $wpdb->prefix . "$tblname";

    #Check to see if the table exists already, if not, then create it

    if($wpdb->get_var( "show tables like '$wp_track_table'" ) != $wp_track_table)
    {
        $sql = "CREATE TABLE `". $wp_track_table . "` ( ";
        $sql .= "  `id`  int(11)   NOT NULL auto_increment, ";
        $sql .= "  `order_id`  varchar(255)   NOT NULL, ";
        $sql .= "  `response`  varchar(255)   NOT NULL, ";
        $sql .= "  `date`  datetime  NOT NULL, ";
        $sql .= "  `status`  varchar(255)  NOT NULL, ";
        $sql .= "  PRIMARY KEY `order_id` (`id`) ";
        $sql .= ") ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ; ";
        require_once( ABSPATH . '/wp-admin/includes/upgrade.php' );
        dbDelta($sql);
    }
}

register_activation_hook( __FILE__, 'create_retailcore_logs_plugin_database_table' );



/**
 * Register a custom menu page.
 */
function retailcore_register_sidebar_menu_option() {
    global $submenu;
    add_menu_page(
        'Retailcore - OMNI',
        'Retailcore-OMNI',
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

    add_submenu_page('retailcore_omini','Retailcore - About','Sync',
        'manage_options','sync_retail_core_plugins','retail_core_omini_sync_products');

    add_submenu_page('retailcore_omini','Retailcore - About','Logs',
        'manage_options','retail_core_order_sync_logs','retail_core_order_sync_logs');
}

function retail_core_order_sync_logs(){
    retail_core_omini_enqueue_style();
    global $wpdb;
    $tblname = 'retailcore_place_order_logs';
    $wp_track_table = $wpdb->prefix . "$tblname";
    $logsData = $wpdb->get_results( "SELECT * FROM $wp_track_table" );
    require_once RETAILCORE__PLUGIN_DIR.'/views/order-sync-log.php';
}


add_action('woocommerce_checkout_order_processed', 'retailcore_place_order', 10, 1);

function retailcore_place_order($order_id){
    $orderDetails = wc_get_order($order_id);
    $paymentMethod = $orderDetails->get_payment_method();
    if($paymentMethod == 'cod'){
        require_once( RETAILCORE__PLUGIN_DIR . 'classes/CurlRequest.php' );
        require_once( RETAILCORE__PLUGIN_DIR . 'classes/OminiCurlRequest.php' );
        OminiCurlRequest::placeOrder($order_id);
    }
}

add_action( 'woocommerce_order_status_processing', 'retailcore_omini_woocommerce_payment_complete' );

function retailcore_omini_woocommerce_payment_complete($order_id){
    $orderDetails = wc_get_order($order_id);
    $orderDetails = wc_get_order($order_id);
    $paymentMethod = $orderDetails->get_payment_method();
    if($paymentMethod != 'cod'){
        require_once( RETAILCORE__PLUGIN_DIR . 'classes/CurlRequest.php' );
        require_once( RETAILCORE__PLUGIN_DIR . 'classes/OminiCurlRequest.php' );
        OminiCurlRequest::placeOrder($order_id);
    }
}

/**
 * Display a custom menu page
 */
function retail_core_license_page(){
    retail_core_omini_enqueue_style();
    require_once RETAILCORE__PLUGIN_DIR.'/views/activation.php';
}

function retail_core_omini_sync_products(){
    retail_core_omini_enqueue_style();
    require_once RETAILCORE__PLUGIN_DIR.'/views/sync-products.php';
}

function retail_core_omini_about_page(){
    esc_html_e( 'Admin About Pages', 'textdomain' );
}

if (is_admin()) {

    require_once( RETAILCORE__PLUGIN_DIR . 'classes/CurlRequest.php' );
    require_once( RETAILCORE__PLUGIN_DIR . 'classes/OminiCurlRequest.php' );
    add_action( 'init', array( 'OminiCurlRequest', 'init' ));
}

add_action( 'wp_ajax_retail_core_sync_products', 'retail_core_sync_products' );

function retail_core_sync_products(){
    $insertedCount = OminiCurlRequest::syncProduct();
    echo json_encode(['inserted_count'=>$insertedCount]);
    exit;
}


add_action( 'wp_ajax_retail_core_sync_single_order', 'retail_core_sync_single_order' );
add_action( 'wp_ajax_retail_core_delete_order_log', 'retail_core_delete_order_log' );

function retail_core_sync_single_order(){
    $orderId = intval($_POST['order_id']);
    $response = OminiCurlRequest::placeOrder($orderId,true);
    if($response['Success'] == 'False'){
        echo json_encode(['status'=>false,'response'=>$response]);
    }else{
        echo json_encode(['status'=>true,'response'=>$response]);
    }
    exit;
}

function retail_core_delete_order_log(){
    global $wpdb;
    $tblname = 'retailcore_place_order_logs';
    $wp_track_table = $wpdb->prefix . "$tblname";
    $orderId = intval($_POST['order_id']);
    $wpdb->delete($wp_track_table,['order_id'=>$orderId]);
    exit;
}


add_action('init', 'retail_core_parse_query_param_to_sync');

function retail_core_parse_query_param_to_sync(){
    if(isset($_GET['product_sync'])){
        $tokenToVerify = esc_attr($_GET['product_sync']);
        if($tokenToVerify == 'AwtWrlly3SEY4UW3jB82dRBEhNkv1xh6'){
            require_once( RETAILCORE__PLUGIN_DIR . 'classes/CurlRequest.php' );
            require_once( RETAILCORE__PLUGIN_DIR . 'classes/OminiCurlRequest.php' );
            OminiCurlRequest::syncProduct();
        }
    }
}