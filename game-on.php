<?php
/*
Plugin Name: Game-On
Plugin URI: http://maclab.guhsd.net/game-on
Description: Gamification tools for teachers.
Author: Valhalla Mac Lab
Author URI: https://github.com/TheMacLab/game-on/blob/master/README.md
Version: 4.7b
*/

add_filter('plupload_default_settings', function ($settings) {
    $settings['resize'] = array(
        'enabled' => true,
        'width' => 1920,
        'height' => 1920,
        'quality' => 80,
        'preserve_headers' => false
    );

    return $settings;
});

$go_debug = true;//set to true when coding
global $go_debug;

$go_js_version = 4.71;
global $go_js_version;

$go_css_version = 4.70;
global $go_css_version;

///////////////////////////////
//INCLUDE RESOURCES BEFORE GO
///////////////////////////////
//include_once('includes/acf/acf.php');
include( 'includes/wp-frontend-media-master/frontend-media.php' );
include_once('includes/wp-term-order/wp-term-order.php'); //try to load only on admin pages

//Include external js and css resources from cdns
//can these be given local fallbacks
include_once('includes/go_enqueue_includes.php');
add_action( 'wp_enqueue_scripts', 'go_includes' );
add_action( 'admin_enqueue_scripts', 'go_includes' );



////////////////////////////
/// CONDITIONAL INCLUDES
/////////////////////////////

//INCLUDE CSS AND JS FILES
if ( !is_admin() ) { //IF PUBLIC FACING PAGE

    include_once('js/go_enque_js.php');
    add_action( 'wp_enqueue_scripts', 'go_scripts' );

    include_once('styles/go_enque_styles.php');
    add_action( 'wp_enqueue_scripts', 'go_styles' );

}
else if ( defined( 'DOING_AJAX' )) { //ELSE THIS IS AN AJAX CALL
    //there is a way to include in ajax, but I don't know if we need to.
    //Updates

    //Admin

}
else {//ELSE THIS IS AN ADMIN PAGE
    //admin js
    include_once('js/go_enque_js_admin.php');

    //admin css
    include_once('styles/go_enque_styles_admin.php');

    add_action( 'admin_enqueue_scripts', 'go_admin_scripts' );
    add_action( 'admin_enqueue_scripts', 'go_admin_styles' );


}

//INCLUDE PHP
if ( is_admin() ) {

    include_once('includes/acf/acf.php');
    include_once('includes/wp-acf-unique_id-master/acf-unique_id.php');

    include_once('custom-acf-fields/acf-order-posts/acf-order-posts.php');

    include_once('modules/clipboard/includes.php');
    include_once('modules/tools/includes.php');
    include_once('modules/user_profiles/includes.php');

    if ($go_debug == true) {
        //add_filter('acf/settings/show_admin', '__return_false');
        add_filter('acf/settings/save_json', 'go_acf_json_save_point');
    }

    function go_acf_json_save_point( $path ) {

        // update path
        $path = (plugin_dir_path(__FILE__) . 'acf-json');


        // return
        return $path;

    }

    add_filter('acf/settings/load_json', 'go_acf_json_load_point');

    function go_acf_json_load_point( $paths ) {

        // remove original path (optional)
        unset($paths[0]);

        // append path
        $paths[] = (plugin_dir_path(__FILE__) . 'acf-json');


        // return
        return $paths;

    }


    include_once('custom-acf-fields/acf-level2-taxonomy/acf-level2-taxonomy.php');
    include_once('custom-acf-fields/acf-quiz/acf-quiz.php');

    include_once('custom-acf-fields/go-acf-functions.php');

    include_once('custom-acf-fields/go_enque_js_acf.php');
    add_action( 'admin_enqueue_scripts', 'go_acf_scripts' );

}
else if ( defined( 'DOING_AJAX' )) {

    add_action( 'wp_ajax_check_if_top_term', 'go_check_if_top_term' ); //for term order //OK

}
else{
    //INCLUDES on Public Pages
    //include_once('includes/acf/acf.php');
}


////////////////////////
//INCLUDE ON ALL PAGES
/////////////////////////
//main directory
//include_once('go_acf_groups.php'); //the ACF fields for the admin pages

include_once('core/includes.php');

//These have their own conditional includes
include_once('modules/feedback/includes.php');
include_once('modules/admin_bar/includes.php');
include_once('modules/map/includes.php');
include_once('modules/messages/includes.php');
include_once('modules/quiz/includes.php');
include_once('modules/stats/includes.php');
include_once('modules/store/includes.php');
include_once('modules/tasks/includes.php');
include_once('modules/user_blogs/includes.php');


/*
    * Plugin Activation Hooks
    */
register_activation_hook( __FILE__, 'go_update_db_ms' );
register_activation_hook( __FILE__, 'go_open_comments' );
register_activation_hook( __FILE__, 'go_tsk_actv_activate' );
//register_activation_hook( __FILE__, 'go_map_activate' );
//register_activation_hook( __FILE__, 'go_reader_activate' );
//register_activation_hook( __FILE__, 'go_store_activate' );
register_activation_hook( __FILE__, 'go_media_access' );
register_activation_hook( __FILE__, 'go_flush_rewrites' );
register_activation_hook( __FILE__, 'go_v5_update_db' );



////////////////////////////
/// ALL PAGES & AJAX
////////////////////////////

//create non-persistent cache group
//This is used by the transients
wp_cache_add_non_persistent_groups( 'go_single' );

/*
 * User Data
 */
add_action( 'delete_user', 'go_user_delete' ); //this should change for Multisite
add_action( 'user_register', 'go_user_registration' ); //this should change for Multisite

/**
 * Miscellaneous Filters
 */
// mitigating compatibility issues with Jetpack plugin by Automatic
// (https://wordpress.org/plugins/jetpack/).
add_filter( 'jetpack_enable_open_graph', '__return_false' );


/**
 * Debugging Functions
 */

function go_write_log($log) {
    if (true === WP_DEBUG) {
        if (is_array($log) || is_object($log)) {
            error_log(print_r($log, true));
        } else {
            error_log($log);
        }
    }
}

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
// we only care to add scripts and styles if the user is logged in.
    if ( is_user_logged_in() ) {

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
}
add_action( 'wp_enqueue_scripts', 'go_login_session_expired' );

// make sure the stylesheet appears on the lightboxed login iframe
function go_login_session_expired_styles() {
    wp_enqueue_style( 'wp_auth_check','/wp-includes/css/wp-auth-check.css', array( 'dashicons' ), NULL, 'all' );
}
add_action( 'login_enqueue_scripts', 'go_login_session_expired_styles' );




?>
