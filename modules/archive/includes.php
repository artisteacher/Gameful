<?php
/**
 * Created by PhpStorm.
 * User: mmcmurray
 * Date: 12/22/18
 * Time: 5:35 AM
 */

/**
 * Create Blog Archive Module
 *
 * Dependencies:
 * Clipboard Module--functions for the archive tool
 * User Blog Module
 * Login Module--frontend archive link is on the profile page
 *
 */


//conditional includes

if (!is_admin()) {//is the frontend

    $request_uri = (isset($_SERVER['REQUEST_URI']) ?  $_SERVER['REQUEST_URI'] : null);
    $request_uri = str_replace('?updated=true', '', $request_uri);
    if ($request_uri == '/profile/') {
        add_action( 'wp_enqueue_scripts', 'go_archive_frontend_scripts' );
        function go_archive_frontend_scripts($hook){
            wp_localize_script( 'go_frontend', 'go_make_user_archive_zip_nonce', wp_create_nonce( 'go_make_user_archive_zip' ));
        }
    }
    //include_once('src/public_ajax.php');

} else if (defined('DOING_AJAX')) {
    $ajax_action  = (isset($_POST['action']) ?  $_POST['action'] : null);
    if($ajax_action === 'go_make_user_archive_zip' || $ajax_action === 'go_create_user_list' || $ajax_action === 'go_delete_temp_archive' || $ajax_action === 'go_zip_archive') {
        include_once('src/ajax.php');
        //include_once('src/public_ajax.php');
        include_once('src/admin_ajax.php');

        //add_action('wp_ajax_go_make_admin_archive', 'go_make_admin_archive'); //OK
        add_action('wp_ajax_go_make_user_archive_zip', 'go_make_user_archive_zip'); //OK
        add_action('wp_ajax_go_delete_temp_archive', 'go_delete_temp_archive'); //OK
        //add_action('wp_ajax_go_archive_progress', 'go_archive_progress');
        add_action('wp_ajax_go_create_user_list', 'go_create_user_list');
        add_action('wp_ajax_go_zip_archive', 'go_zip_archive');
    }
} else {//is an admin page
    include_once('src/admin.php');
    $page = (isset($_REQUEST['page']) ?  $_REQUEST['page'] : null);
    //
    //only load nonces on pages where needed
    if($page = 'tool_blog_archive'){
        include_once('src/admin_ajax.php');
        go_clean_up_archive_temp_folder();//clean up old files in the archive_temp_folder
        add_action( 'admin_enqueue_scripts', 'go_archive_admin_scripts' );
        function go_archive_admin_scripts($hook){
            //wp_localize_script( 'go_admin_user', 'go_make_admin_archive', wp_create_nonce( 'go_make_admin_archive' ) );
            wp_localize_script( 'go_admin_user', 'go_make_user_archive_zip_nonce', wp_create_nonce( 'go_make_user_archive_zip' ) );
            wp_localize_script( 'go_admin_user', 'go_delete_temp_archive_nonce', wp_create_nonce( 'go_delete_temp_archive' ) );
           // wp_localize_script( 'go_admin_user', 'go_archive_progress_nonce', wp_create_nonce( 'go_archive_progress' ) );
            wp_localize_script( 'go_admin_user', 'go_clipboard_stats_nonce', wp_create_nonce( 'go_clipboard_stats' ) );
            wp_localize_script( 'go_admin_user', 'go_create_user_list_nonce', wp_create_nonce( 'go_create_user_list' ) );
            wp_localize_script( 'go_admin_user', 'go_zip_archive_nonce', wp_create_nonce( 'go_zip_archive' ) );
            wp_localize_script( 'go_admin_user', 'is_tools_archive_page', 'true');//used to run on DOM ready JS
        }
    }
}

//always include
//include_once('src/functions.php');

