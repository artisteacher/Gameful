<?php
/**
 * Created by PhpStorm.
 * User: mmcmurray
 * Date: 12/22/18
 * Time: 5:35 AM
 */

//conditional includes

if ( !is_admin() ) {
    //get variables for conditional includes
    //$page = (isset($_REQUEST['page']) ?  $_REQUEST['page'] : null);
    $request_uri = (isset($_SERVER['REQUEST_URI']) ?  $_SERVER['REQUEST_URI'] : null);
    $request_uri = str_replace('?updated=true', '', $request_uri);

    if ($request_uri == '/profile/' || $request_uri == '/registration/') {
        $acf_location = dirname(__DIR__) . '/../includes/acf/acf.php';
        include($acf_location);

        $acf_location = dirname(__DIR__) . '/../custom-acf-fields/acf-level2-taxonomy/acf-level2-taxonomy.php';
        include_once($acf_location);
        //include_once('custom-acf-fields/acf-level2-taxonomy/acf-level2-taxonomy.php');
        //include_once('public/public.php');

        add_action( 'wp_enqueue_scripts', 'go_login_scripts' );
        function go_login_scripts($hook){
            //wp_localize_script( 'go_admin_user', 'go_make_user_archive_zip_nonce', wp_create_nonce( 'go_make_user_archive_zip' ) );
            wp_localize_script( 'go_frontend', 'is_login_page', 'true');//used to run on DOM ready JS
            go_include_password_checker();
        }
    }
}else if ( defined( 'DOING_AJAX' )) {
    //include_once('src/ajax.php');

    add_action( 'wp_ajax_go_update_password', 'go_update_password' ); //OK
    add_action( 'wp_ajax_go_update_password_lightbox', 'go_update_password_lightbox' ); //OK
    $action  = (isset($_POST['action']) ?  $_POST['action'] : null);
    if ($action==='go_update_password_lightbox') {
        $acf_location = dirname(__FILE__) . '/../../includes/acf/acf.php';
        include($acf_location);
    }
}else{
    include_once('src/admin.php');
}

//always include
include_once('src/functions.php');

