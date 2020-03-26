<?php
/**
 * Created by PhpStorm.
 * User: mmcmurray
 * Date: 12/22/18
 * Time: 5:35 AM
 */

//conditional includes
if ( !is_admin() ) {
    /*global $pagenow;
     if ( 'xxx.php' == $pagenow) {
         include_once('public/public.php');
         include_once('src/public_ajax.php');
     }*/
    $request_uri = (isset($_SERVER['REQUEST_URI']) ?  $_SERVER['REQUEST_URI'] : null);//pages to not load this on
    if ( strpos($request_uri, 'user_archive') != true) {
        include_once('src/public.php');
    }



}else if ( defined( 'DOING_AJAX' )) {
    include_once('src/public_ajax.php');
    include_once('src/ajax.php');

    add_action( 'wp_ajax_go_new_task_from_template', 'go_new_task_from_template');
    add_action( 'wp_ajax_go_clone_post_new_menu_bar', 'go_clone_post_new_menu_bar');
    add_action( 'wp_ajax_go_update_admin_view', 'go_update_admin_view' );

}else{
    add_action( 'admin_action_go_new_task_from_template_as_draft', 'go_new_task_from_template_as_draft' );
    /*global $pagenow;
    if ( 'xxx.php' == $pagenow) {
        include_once('src/admin.php');

    }*/
}

//always include
include_once('src/functions.php');