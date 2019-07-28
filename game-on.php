<?php
/*
Plugin Name: Gameful Pro
Plugin URI: http://gameful.me
Description: Gamification tools for teachers. Forked from the Game On Project.
Author: Gameful.me
Author URI: https://github.com/mcmick/Gameful
Version: 5.01
*/


//$go_debug = true;//set to true when coding

if ($domain ==='gameondev') {
    $is_gameful = false;
    $go_debug = true;

}else{
    $go_debug = false;
    $is_gameful = true;
}

global $go_debug;
global $is_gameful;

//stop the heartbeat when testing
if ($go_debug){

    add_action( 'init', 'go_stop_heartbeat', 1 );
    function go_stop_heartbeat() {
            //wp_deregister_script('heartbeat');
    }

    //if currently coding, set the ACF save point
    //if should be off for releases to stop accidental changing of the ACF data
    if (is_admin()){
        //add_filter('acf/settings/show_admin', '__return_false');
        add_filter('acf/settings/save_json', 'go_acf_json_save_point');
        function go_acf_json_save_point( $path ) {
            // update path
            $path = (plugin_dir_path(__FILE__) . 'acf-json');
            // return
            return $path;
        }

    }


}
else{
    function remove_acf_menu() {
        remove_menu_page('edit.php?post_type=acf-field-group');
    }
    add_action( 'admin_menu', 'remove_acf_menu', 999);

    function redirect_acf_admin(){
        $user_id = get_current_user_id();
        $super = is_super_admin( $user_id );
        $request = (isset($_REQUEST['post_type'][0]) ?  $_REQUEST['post_type'][0] : null);
        if (!$super && $request ==='acf-field-group'){
            wp_redirect(admin_url());
            exit;
        }

    }
    add_action('admin_init', 'redirect_acf_admin');
}

$go_js_version = 5.04;
global $go_js_version;

$go_css_version = 5.04;
global $go_css_version;

//////
 //INCLUDE DEPENDENCIES
////////
//ACF is loaded conditionally below
//Include external js and css resources from cdns
//can these be given local fallbacks
include_once('includes/go_enqueue_includes.php');
add_action( 'wp_enqueue_scripts', 'go_includes' );
add_action( 'admin_enqueue_scripts', 'go_includes' );

//https://www.advancedcustomfields.com/resources/local-json/
//Loads ACF fields from JSON file as needed
add_filter('acf/settings/load_json', 'go_acf_json_load_point');
function go_acf_json_load_point( $paths ) {
    // remove original path (optional)
    unset($paths[0]);
    // append path
    $paths[] = (plugin_dir_path(__FILE__) . 'acf-json');
    // return
    return $paths;
}




////////////////////////////
/// CONDITIONAL INCLUDES
/////////////////////////////
//INCLUDE CSS AND JS FILES
if ( !is_admin() ) { //IF PUBLIC FACING PAGE
    include_once('js/go_enque_js.php');
    include_once('js/localize_scripts.php');

    include_once('styles/go_enque_styles.php');
}
else if ( defined( 'DOING_AJAX' )) { //ELSE THIS IS AN AJAX CALL
    //if this is an ajax call, skip the enqueue functions
}
else {//ELSE THIS IS AN ADMIN PAGE

    //admin js
    include_once('js/go_enque_js_admin.php');
    include_once('js/localize_scripts.php');

    //admin css
    include_once('styles/go_enque_styles_admin.php');
}

//INCLUDE PHP
//These files have their own conditional includes

//Core files include the functions that are used across several modules
include_once('core/includes.php');

include_once('includes/includes.php');

//Modules
include_once('modules/admin_bar/includes.php');
include_once('modules/archive/includes.php');
include_once('modules/clipboard/includes.php');
include_once('modules/feedback/includes.php');
include_once('modules/login/includes.php');
include_once('modules/map/includes.php');
include_once('modules/messages/includes.php');
include_once('modules/quiz/includes.php');
include_once('modules/stats/includes.php');
include_once('modules/store/includes.php');
include_once('modules/tasks/includes.php');
include_once('modules/tools/includes.php');
include_once('modules/user_blogs/includes.php');
include_once('modules/term-order/includes.php'); //try to load only on admin pages


/////
 // Plugin Activation Hooks
 /////
register_activation_hook( __FILE__, 'go_update_db_ms' );
register_activation_hook( __FILE__, 'go_open_comments' );
//register_activation_hook( __FILE__, 'go_tsk_actv_activate' );
//register_activation_hook( __FILE__, 'go_map_activate' );
//register_activation_hook( __FILE__, 'go_reader_activate' );
//register_activation_hook( __FILE__, 'go_store_activate' );
register_activation_hook( __FILE__, 'go_media_access' );
register_activation_hook( __FILE__, 'go_flush_rewrites' );


register_deactivation_hook( __FILE__, 'flush_rewrite_rules' );





/////////////////////
 //ALL PAGES & AJAX
///////////////////
//create non-persistent cache group
//This is used by the transients
wp_cache_add_non_persistent_groups( 'go_single' );

//User Data
add_action( 'delete_user', 'go_user_delete' ); //this should change for Multisite?
//add_action( 'user_register', 'go_user_registration' ); //this should change for Multisite?

/////////////////////////
 // Miscellaneous Filters
///////////////////////////
// mitigating compatibility issues with Jetpack plugin by Automatic
// (https://wordpress.org/plugins/jetpack/).
add_filter( 'jetpack_enable_open_graph', '__return_false' );


////////////////////////
 // Debugging Functions
 ////////////////////////

function go_write_log($log) {
    if (true === WP_DEBUG) {
        if (is_array($log) || is_object($log)) {
            error_log(print_r($log, true));
        } else {
            error_log($log);
        }
    }
}

//this function is used for debugging only
//it is only called when debug is set to true
//you must set a breakpoint below to get the total query time--it is not output
function go_total_query_time(){
    global $wpdb;
    $queries = $wpdb->queries;
    $total_time = 0;
    foreach($queries as $query){
        $total_time =  $total_time + $query[1];
    }
    $total_time = $total_time;//set a breakpoint here to monitor query times.
}



//This is the code that puts the login modal on the frontend
//code used
//check if logged in: https://wordpress.stackexchange.com/questions/69814/check-if-user-is-logged-in-using-jquery
//and the JS https://wordpress.stackexchange.com/questions/163292/how-can-i-test-the-login-for-an-expired-session
//and the heavylifting below: //https://stackoverflow.com/questions/48698142/ajax-overlay-if-user-session-expires-wordpress-frontend
function go_login_session_expired() {

    if ( is_user_logged_in() ) {//if user is logged in load the heartbeat and modal
        // add javascript file
        wp_register_script( 'wp_auth_check', '/wp-includes/js/wp-auth-check.js' , array('heartbeat'), false, 1);
        wp_localize_script( 'wp_auth_check', 'authcheckL10n', array(
           'interval' => apply_filters( 'wp_auth_check_interval', 1 * MINUTE_IN_SECONDS ), // default interval is 3 minutes
        ) );
        wp_enqueue_script ('wp_auth_check');

        // add css file
        wp_enqueue_style( 'wp_auth_check','/wp-includes/css/wp-auth-check.css', array( 'dashicons' ), NULL, 'all' );

        // add the login html to the page
        add_action( 'wp_print_footer_scripts', 'wp_auth_check_html', 5 );
    }
    else{//if user isn't logged in, just load the modal and not the heartbeat.
        wp_register_script( 'wp_auth_check', '/wp-includes/js/wp-auth-check.js' , array(), true, 1);
        //wp_enqueue_script ('wp_auth_check');

        // add css file
        wp_enqueue_style( 'wp_auth_check','/wp-includes/css/wp-auth-check.css', array( 'dashicons' ), NULL, 'all' );

        add_action( 'wp_print_footer_scripts', 'wp_auth_check_html', 5 );
    }
}
add_action( 'wp_enqueue_scripts', 'go_login_session_expired' );


// Makes sure the plugin is defined before trying to use it
if ( ! function_exists( 'is_plugin_active_for_network' ) ) {
    require_once( ABSPATH . '/wp-admin/includes/plugin.php' );
}

function go_is_ms_active_network_wide(){
    $myfile = plugin_basename(__FILE__);
    $is_ms = is_plugin_active_for_network($myfile );
    return $is_ms;
}



//https://wordpress.stackexchange.com/questions/137545/custom-login-iframe-doesnt-work
remove_action( 'login_init', 'send_frame_options_header' );
remove_action( 'admin_init', 'send_frame_options_header' );

add_filter('wp_auth_check_same_domain', 'go_allow_same_orgin');
function go_allow_same_orgin($same_domain){

    return true;
}

//add_filter('login_url', 'go_login_url_filter');
function go_login_url_filter($login_url, $redirect, $force_reauth){
    $blog_id = get_current_blog_id();
    $go_login_link = get_site_url(1, 'login');
    $go_login_link = network_site_url ('signin?redirect_to='.$go_login_link.'?blog_id='.$blog_id);
    return $go_login_link;
}



